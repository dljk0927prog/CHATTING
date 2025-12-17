/**
 * 信令服务器
 * 基于Node.js和Socket.IO的WebSocket服务器
 * 用于处理通话信令和房间管理
 */

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const path = require('path');

class SignalingServer {
    constructor(port = 3000) {
        this.port = port;
        this.app = express();
        this.server = http.createServer(this.app);
        this.io = socketIo(this.server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

        this.rooms = new Map(); // 房间管理
        this.users = new Map(); // 用户管理
        
        this.setupRoutes();
        this.setupSocketHandlers();
        
        console.log('[SignalingServer] 信令服务器已初始化');
    }
    
    /**
     * 设置路由
     */
    setupRoutes() {
        // 健康检查
        this.app.get('/health', (req, res) => {
            res.json({
                status: 'ok',
                timestamp: new Date().toISOString(),
                rooms: this.rooms.size,
                users: this.users.size
            });
        });
        
        // 获取房间信息
        this.app.get('/room/:roomId', (req, res) => {
            const roomId = req.params.roomId;
            const room = this.rooms.get(roomId);
            
            if (room) {
                res.json({
                    success: true,
                    data: {
                        roomId: roomId,
                        participants: Array.from(room.participants),
                        createdAt: room.createdAt
                    }
                });
            } else {
                res.status(404).json({
                    success: false,
                    error: '房间不存在'
                });
            }
        });
        
        // 获取服务器状态
        this.app.get('/status', (req, res) => {
            res.json({
                success: true,
                data: {
                    server: 'SignalingServer',
                    version: '2.0.0',
                    uptime: process.uptime(),
                    rooms: this.rooms.size,
                    users: this.users.size,
                    memory: process.memoryUsage()
                }
            });
        });
    }
    
    /**
     * 设置Socket事件处理
     */
    setupSocketHandlers() {
        this.io.on('connection', (socket) => {
            console.log(`[SignalingServer] 用户连接: ${socket.id}`);
            
            // 用户加入
            socket.on('join', (data) => {
                this.handleUserJoin(socket, data);
            });
            
            // 用户离开
            socket.on('leave', (data) => {
                this.handleUserLeave(socket, data);
            });
            
            // 通话邀请
            socket.on('call_invitation', (data) => {
                this.handleCallInvitation(socket, data);
            });
            
            // 通话响应
            socket.on('call_response', (data) => {
                this.handleCallResponse(socket, data);
            });
            
            // WebRTC信令
            socket.on('offer', (data) => {
                this.handleOffer(socket, data);
            });
            
            socket.on('answer', (data) => {
                this.handleAnswer(socket, data);
            });
            
            socket.on('ice_candidate', (data) => {
                this.handleIceCandidate(socket, data);
            });
            
            // 用户状态更新
            socket.on('user_status', (data) => {
                this.handleUserStatus(socket, data);
            });
            
            // 心跳
            socket.on('ping', () => {
                socket.emit('pong');
            });
            
            // 断开连接
            socket.on('disconnect', () => {
                this.handleUserDisconnect(socket);
            });
        });
    }
    
    /**
     * 处理用户加入
     */
    handleUserJoin(socket, data) {
        try {
            const { roomId, userId, username } = data;
            
            if (!roomId || !userId) {
                socket.emit('error', { message: '房间ID和用户ID不能为空' });
                return;
            }
            
            // 加入房间
            socket.join(roomId);
            
            // 更新用户信息
            this.users.set(socket.id, {
                id: userId,
                username: username || 'Unknown',
                roomId: roomId,
            socketId: socket.id,
            joinedAt: new Date()
        });

            // 创建或更新房间
            if (!this.rooms.has(roomId)) {
                this.rooms.set(roomId, {
                    id: roomId,
                    participants: new Set(),
                    createdAt: new Date(),
                    status: 'active'
                });
            }
            
            const room = this.rooms.get(roomId);
            room.participants.add(socket.id);
        
        // 通知房间内其他用户
            socket.to(roomId).emit('user_joined', {
                userId: userId,
            username: username,
                socketId: socket.id
        });

            // 发送房间信息给新用户
            socket.emit('room_info', {
            roomId: roomId,
                participants: Array.from(room.participants).map(socketId => {
                    const user = this.users.get(socketId);
                    return user ? {
                        id: user.id,
                        username: user.username,
                        socketId: socketId
                    } : null;
                }).filter(Boolean)
            });
            
            console.log(`[SignalingServer] 用户 ${username} 加入房间 ${roomId}`);
            
        } catch (error) {
            console.error('[SignalingServer] 处理用户加入失败:', error);
            socket.emit('error', { message: '加入房间失败' });
        }
    }
    
    /**
     * 处理用户离开
     */
    handleUserLeave(socket, data) {
        try {
            const { roomId } = data;
            const user = this.users.get(socket.id);
            
            if (user && roomId) {
                // 从房间移除
                socket.leave(roomId);
                
                const room = this.rooms.get(roomId);
                if (room) {
                    room.participants.delete(socket.id);
                    
                    // 如果房间为空，删除房间
                    if (room.participants.size === 0) {
                        this.rooms.delete(roomId);
                    } else {
                        // 通知房间内其他用户
                        socket.to(roomId).emit('user_left', {
                            userId: user.id,
                            username: user.username,
                            socketId: socket.id
                        });
                    }
                }
                
                // 删除用户信息
                this.users.delete(socket.id);
                
                console.log(`[SignalingServer] 用户 ${user.username} 离开房间 ${roomId}`);
            }
            
        } catch (error) {
            console.error('[SignalingServer] 处理用户离开失败:', error);
        }
    }
    
    /**
     * 处理通话邀请
     */
    handleCallInvitation(socket, data) {
        try {
            const { callId, type, participants, fromUserId } = data;
            
            if (!callId || !participants || !Array.isArray(participants)) {
                socket.emit('error', { message: '通话邀请参数无效' });
            return;
        }

            // 转发给目标用户
            participants.forEach(participant => {
                const targetSocket = this.findSocketByUserId(participant.id);
                if (targetSocket) {
                    targetSocket.emit('call_invitation', {
                        callId: callId,
                        type: type,
                        fromUserId: fromUserId,
                        participants: participants
                    });
                }
            });
            
            console.log(`[SignalingServer] 通话邀请已发送: ${callId}`);
            
        } catch (error) {
            console.error('[SignalingServer] 处理通话邀请失败:', error);
            socket.emit('error', { message: '发送通话邀请失败' });
        }
    }
    
    /**
     * 处理通话响应
     */
    handleCallResponse(socket, data) {
        try {
            const { callId, accepted, fromUserId } = data;
            
            if (!callId) {
                socket.emit('error', { message: '通话响应参数无效' });
            return;
        }

            // 转发给发起者
            const callerSocket = this.findSocketByUserId(fromUserId);
            if (callerSocket) {
                callerSocket.emit('call_response', {
                    callId: callId,
                    accepted: accepted,
                    fromUserId: this.users.get(socket.id)?.id
                });
            }
            
            console.log(`[SignalingServer] 通话响应已发送: ${callId}, 接受: ${accepted}`);
            
        } catch (error) {
            console.error('[SignalingServer] 处理通话响应失败:', error);
            socket.emit('error', { message: '发送通话响应失败' });
        }
    }
    
    /**
     * 处理WebRTC Offer
     */
    handleOffer(socket, data) {
        try {
            const { offer, targetUserId } = data;
            
            if (!offer) {
                socket.emit('error', { message: 'Offer数据无效' });
            return;
        }

            // 转发给目标用户
            if (targetUserId) {
                const targetSocket = this.findSocketByUserId(targetUserId);
                if (targetSocket) {
                    targetSocket.emit('offer', {
                        offer: offer,
                        fromUserId: this.users.get(socket.id)?.id
                    });
                }
            } else {
                // 广播给房间内其他用户
                const user = this.users.get(socket.id);
                if (user && user.roomId) {
                    socket.to(user.roomId).emit('offer', {
                        offer: offer,
                        fromUserId: user.id
                    });
                }
            }
            
            console.log('[SignalingServer] Offer已转发');
            
        } catch (error) {
            console.error('[SignalingServer] 处理Offer失败:', error);
            socket.emit('error', { message: '转发Offer失败' });
        }
    }
    
    /**
     * 处理WebRTC Answer
     */
    handleAnswer(socket, data) {
        try {
            const { answer, targetUserId } = data;
            
            if (!answer) {
                socket.emit('error', { message: 'Answer数据无效' });
            return;
        }

            // 转发给目标用户
            if (targetUserId) {
                const targetSocket = this.findSocketByUserId(targetUserId);
                if (targetSocket) {
                    targetSocket.emit('answer', {
                        answer: answer,
                        fromUserId: this.users.get(socket.id)?.id
                    });
                }
        } else {
                // 广播给房间内其他用户
                const user = this.users.get(socket.id);
                if (user && user.roomId) {
                    socket.to(user.roomId).emit('answer', {
                        answer: answer,
                        fromUserId: user.id
                    });
                }
            }
            
            console.log('[SignalingServer] Answer已转发');
            
        } catch (error) {
            console.error('[SignalingServer] 处理Answer失败:', error);
            socket.emit('error', { message: '转发Answer失败' });
        }
    }
    
    /**
     * 处理ICE候选
     */
    handleIceCandidate(socket, data) {
        try {
            const { candidate, targetUserId } = data;
            
            if (!candidate) {
                socket.emit('error', { message: 'ICE候选数据无效' });
            return;
        }

            // 转发给目标用户
            if (targetUserId) {
                const targetSocket = this.findSocketByUserId(targetUserId);
                if (targetSocket) {
                    targetSocket.emit('ice_candidate', {
                        candidate: candidate,
                        fromUserId: this.users.get(socket.id)?.id
                    });
                }
            } else {
                // 广播给房间内其他用户
                const user = this.users.get(socket.id);
                if (user && user.roomId) {
                    socket.to(user.roomId).emit('ice_candidate', {
                        candidate: candidate,
                        fromUserId: user.id
                    });
                }
            }
            
        } catch (error) {
            console.error('[SignalingServer] 处理ICE候选失败:', error);
            socket.emit('error', { message: '转发ICE候选失败' });
        }
    }
    
    /**
     * 处理用户状态更新
     */
    handleUserStatus(socket, data) {
        try {
            const { status } = data;
            const user = this.users.get(socket.id);
            
            if (user) {
                user.status = status;
                user.lastSeen = new Date();
                
                // 通知房间内其他用户
                if (user.roomId) {
                    socket.to(user.roomId).emit('user_status_update', {
                        userId: user.id,
                        username: user.username,
                        status: status
                    });
                }
            }
            
        } catch (error) {
            console.error('[SignalingServer] 处理用户状态更新失败:', error);
        }
    }
    
    /**
     * 处理用户断开连接
     */
    handleUserDisconnect(socket) {
        try {
            const user = this.users.get(socket.id);
            
            if (user) {
                const room = this.rooms.get(user.roomId);
            if (room) {
                room.participants.delete(socket.id);
                
                // 通知房间内其他用户
                    socket.to(user.roomId).emit('user_left', {
                        userId: user.id,
                        username: user.username,
                        socketId: socket.id
                    });
                    
                    // 如果房间为空，删除房间
                if (room.participants.size === 0) {
                        this.rooms.delete(user.roomId);
                    }
                }
                
                this.users.delete(socket.id);
                console.log(`[SignalingServer] 用户 ${user.username} 断开连接`);
            }
            
        } catch (error) {
            console.error('[SignalingServer] 处理用户断开连接失败:', error);
        }
    }
    
    /**
     * 根据用户ID查找Socket
     */
    findSocketByUserId(userId) {
        for (const [socketId, user] of this.users) {
            if (user.id === userId) {
                return this.io.sockets.sockets.get(socketId);
            }
        }
        return null;
    }
    
    /**
     * 启动服务器
     */
    start() {
        this.server.listen(this.port, () => {
            console.log(`[SignalingServer] 信令服务器已启动，端口: ${this.port}`);
            console.log(`[SignalingServer] 健康检查: http://localhost:${this.port}/health`);
            console.log(`[SignalingServer] 服务器状态: http://localhost:${this.port}/status`);
        });
    }
    
    /**
     * 停止服务器
     */
    stop() {
        this.server.close(() => {
            console.log('[SignalingServer] 信令服务器已停止');
        });
    }
}

// 启动服务器
if (require.main === module) {
    const server = new SignalingServer(3000);
    server.start();

// 优雅关闭
process.on('SIGINT', () => {
        console.log('\n[SignalingServer] 正在关闭服务器...');
        server.stop();
        process.exit(0);
    });
}

module.exports = SignalingServer;
