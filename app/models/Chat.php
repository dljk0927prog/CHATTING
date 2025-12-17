<?php
require_once BASE_PATH . '/config/Database.php';

class Chat {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 创建或获取私聊房间
    public function getOrCreatePrivateRoom($userId1, $userId2) {
        // 检查是否已存在私聊房间
        $sql = "SELECT cr.id FROM chat_rooms cr
                JOIN chat_room_members crm1 ON cr.id = crm1.room_id
                JOIN chat_room_members crm2 ON cr.id = crm2.room_id
                WHERE cr.type = 'private' 
                AND crm1.user_id = ? AND crm2.user_id = ?
                AND crm1.user_id != crm2.user_id";
        
        $room = $this->db->fetch($sql, [$userId1, $userId2]);
        
        if ($room) {
            return $room['id'];
        }
        
        // 创建新的私聊房间
        try {
            $this->db->getConnection()->beginTransaction();
            
            // 创建房间
            $sql = "INSERT INTO chat_rooms (type, created_by) VALUES ('private', ?)";
            $this->db->query($sql, [$userId1]);
            $roomId = $this->db->lastInsertId();
            
            // 添加成员
            $sql = "INSERT INTO chat_room_members (room_id, user_id) VALUES (?, ?)";
            $this->db->query($sql, [$roomId, $userId1]);
            $this->db->query($sql, [$roomId, $userId2]);
            
            $this->db->getConnection()->commit();
            return $roomId;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw new Exception('创建聊天房间失败: ' . $e->getMessage());
        }
    }
    
    // 发送消息
    public function sendMessage($roomId, $senderId, $content, $quotedMessageId = null, $messageType = 'text', $filePath = null) {
        try {
            // 检查是否被对方封锁（仅私聊房间）
            $roomInfo = $this->getRoomInfo($roomId, $senderId);
            if ($roomInfo && $roomInfo['type'] === 'private') {
                // 获取对方用户ID
                $sql = "SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?";
                $otherUser = $this->db->fetch($sql, [$roomId, $senderId]);
                
                if ($otherUser) {
                    // 检查是否被对方封锁
                    $sql = "SELECT id FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?";
                    $isBlocked = $this->db->fetch($sql, [$otherUser['user_id'], $senderId]);
                    
                    if ($isBlocked) {
                        return ['success' => false, 'message' => '您已被对方封锁，无法发送消息'];
                    }
                }
            }
            
            $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path, quoted_message_id) VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, [$roomId, $senderId, $content, $messageType, $filePath, $quotedMessageId]);
            
            $messageId = $this->db->lastInsertId();
            
            // 标记发送者已读
            $this->markMessageAsRead($messageId, $senderId);
            
            return ['success' => true, 'message_id' => $messageId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '发送消息失败: ' . $e->getMessage()];
        }
    }
    
    // 发送文件消息
    public function sendFileMessage($roomId, $senderId, $content, $fileUrl, $fileType, $fileName, $fileSize) {
        try {
            // 检查是否被对方封锁（仅私聊房间）
            $roomInfo = $this->getRoomInfo($roomId, $senderId);
            if ($roomInfo && $roomInfo['type'] === 'private') {
                // 获取对方用户ID
                $sql = "SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?";
                $otherUser = $this->db->fetch($sql, [$roomId, $senderId]);
                
                if ($otherUser) {
                    // 检查是否被对方封锁
                    $sql = "SELECT id FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?";
                    $isBlocked = $this->db->fetch($sql, [$otherUser['user_id'], $senderId]);
                    
                    if ($isBlocked) {
                        return ['success' => false, 'message' => '您已被对方封锁，无法发送文件'];
                    }
                }
            }
            
            // 映射文件类型到数据库支持的message_type
            $messageType = $this->mapFileTypeToMessageType($fileType);
            
            $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path) VALUES (?, ?, ?, ?, ?)";
            $this->db->query($sql, [$roomId, $senderId, $content, $messageType, $fileUrl]);
            
            $messageId = $this->db->lastInsertId();
            
            // 标记发送者已读
            $this->markMessageAsRead($messageId, $senderId);
            
            return ['success' => true, 'message_id' => $messageId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '发送文件失败: ' . $e->getMessage()];
        }
    }
    
    // 发送多文件消息
    public function sendMultipleFileMessage($roomId, $senderId, $content, $fileUrls, $fileType, $fileNames, $totalSize) {
        try {
            // 开始输出缓冲，防止任何意外输出
            ob_start();
            
            // 检查是否被对方封锁（仅私聊房间）
            $roomInfo = $this->getRoomInfo($roomId, $senderId);
            if ($roomInfo && $roomInfo['type'] === 'private') {
                // 获取对方用户ID
                $sql = "SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?";
                $otherUser = $this->db->fetch($sql, [$roomId, $senderId]);
                
                if ($otherUser) {
                    // 检查是否被对方封锁
                    $sql = "SELECT id FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?";
                    $isBlocked = $this->db->fetch($sql, [$otherUser['user_id'], $senderId]);
                    
                    if ($isBlocked) {
                        ob_end_clean();
                        return ['success' => false, 'message' => '您已被对方封锁，无法发送文件'];
                    }
                }
            }
            
            // 映射文件类型到数据库支持的message_type
            $messageType = $this->mapFileTypeToMessageType($fileType);
            
            // 将文件URLs和文件名序列化存储
            $fileData = json_encode([
                'urls' => $fileUrls,
                'names' => $fileNames,
                'count' => count($fileUrls),
                'total_size' => $totalSize
            ]);
            
            if ($fileData === false) {
                ob_end_clean();
                return ['success' => false, 'message' => '文件数据序列化失败'];
            }
            
            $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path) VALUES (?, ?, ?, ?, ?)";
            $this->db->query($sql, [$roomId, $senderId, $content, $messageType, $fileData]);
            
            $messageId = $this->db->lastInsertId();
            
            // 标记发送者已读
            $this->markMessageAsRead($messageId, $senderId);
            
            // 清理输出缓冲
            ob_end_clean();
            
            return ['success' => true, 'message_id' => $messageId];
        } catch (Exception $e) {
            // 清理输出缓冲
            if (ob_get_level()) {
                ob_end_clean();
            }
            return ['success' => false, 'message' => '发送文件失败: ' . $e->getMessage()];
        }
    }
    
    // 发送语音消息
    public function sendVoiceMessage($roomId, $senderId, $content, $voiceUrl) {
        try {
            // 插入语音消息
            $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path, created_at) 
                    VALUES (?, ?, ?, 'voice', ?, NOW())";
            
            $this->db->query($sql, [$roomId, $senderId, $content, $voiceUrl]);
            
            $messageId = $this->db->lastInsertId();
            
            // 标记发送者已读
            $this->markMessageAsRead($messageId, $senderId);
            
            return ['success' => true, 'message_id' => $messageId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '发送语音消息失败: ' . $e->getMessage()];
        }
    }
    
    // 获取房间消息
    public function getRoomMessages($roomId, $userId, $limit = 50, $offset = 0) {
        $sql = "SELECT m.*, u.username, u.avatar, 
                       CASE WHEN mrs.read_at IS NOT NULL THEN 1 ELSE 0 END as is_read_by_user,
                       qm.content as quoted_content, qm.message_type as quoted_type, qm.file_path as quoted_file_path,
                       qu.username as quoted_username
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                LEFT JOIN message_read_status mrs ON m.id = mrs.message_id AND mrs.user_id = ?
                LEFT JOIN user_hidden_messages uhm ON m.id = uhm.message_id AND uhm.user_id = ?
                LEFT JOIN messages qm ON m.quoted_message_id = qm.id
                LEFT JOIN users qu ON qm.sender_id = qu.id
                WHERE m.room_id = ? AND uhm.message_id IS NULL
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";
        
        $messages = $this->db->fetchAll($sql, [$userId, $userId, $roomId, $limit, $offset]);
        
        if ($messages === false) {
            error_log("getRoomMessages failed - database query failed");
            return false;
        }
        
        return array_reverse($messages); // 按时间正序返回
    }
    
    // 获取用户的所有聊天房间
    public function getUserRooms($userId) {
        $sql = "SELECT cr.id, cr.name, cr.type, cr.created_at, crm.pinned,
                       CASE 
                           WHEN cr.type = 'private' THEN 
                               u.username
                           ELSE cr.name
                       END as display_name,
                       CASE 
                           WHEN cr.type = 'private' THEN 
                               f.nickname
                           ELSE NULL
                       END as nickname,
                       CASE 
                           WHEN cr.type = 'private' THEN 
                               u.avatar
                           ELSE cr.avatar
                       END as avatar,
                       CASE 
                           WHEN cr.type = 'private' THEN 
                               u.status
                           ELSE 'offline'
                       END as status,
                       (SELECT content FROM messages WHERE room_id = cr.id ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE room_id = cr.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                       (SELECT COUNT(*) FROM messages WHERE room_id = cr.id AND sender_id != ? AND id NOT IN 
                        (SELECT message_id FROM message_read_status WHERE user_id = ?)) as unread_count
                FROM chat_rooms cr
                JOIN chat_room_members crm ON cr.id = crm.room_id
                LEFT JOIN users u ON (cr.type = 'private' AND u.id = (
                    SELECT crm2.user_id FROM chat_room_members crm2 
                    WHERE crm2.room_id = cr.id AND crm2.user_id != ?
                ))
                LEFT JOIN friendships f ON (cr.type = 'private' AND f.user_id = ? AND f.friend_id = u.id AND f.status = 'accepted')
                LEFT JOIN blocked_users bu ON (bu.blocker_id = ? AND bu.blocked_id = u.id) OR (bu.blocker_id = u.id AND bu.blocked_id = ?)
                WHERE crm.user_id = ? AND (cr.type = 'group' OR bu.id IS NULL)
                ORDER BY crm.pinned DESC, last_message_time DESC";
        
        return $this->db->fetchAll($sql, [$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
    }
    
    // 标记消息为已读
    public function markMessageAsRead($messageId, $userId) {
        $sql = "INSERT IGNORE INTO message_read_status (message_id, user_id) VALUES (?, ?)";
        $this->db->query($sql, [$messageId, $userId]);
    }
    
    // 标记房间所有消息为已读
    public function markRoomMessagesAsRead($roomId, $userId) {
        $sql = "INSERT IGNORE INTO message_read_status (message_id, user_id)
                SELECT m.id, ? FROM messages m
                WHERE m.room_id = ? AND m.sender_id != ?";
        $this->db->query($sql, [$userId, $roomId, $userId]);
    }
    
    // 获取房间信息
    public function getRoomInfo($roomId, $userId) {
        // 先获取房间基本信息
        $sql = "SELECT cr.*, 
                       CASE 
                           WHEN cr.type = 'private' THEN 
                               (SELECT u.username FROM users u 
                                JOIN chat_room_members crm ON u.id = crm.user_id 
                                WHERE crm.room_id = cr.id AND u.id != ?)
                           ELSE cr.name
                       END as display_name,
                       CASE 
                           WHEN cr.type = 'private' THEN 
                               (SELECT u.avatar FROM users u 
                                JOIN chat_room_members crm ON u.id = crm.user_id 
                                WHERE crm.room_id = cr.id AND u.id != ?)
                           ELSE cr.avatar
                       END as avatar
                FROM chat_rooms cr
                JOIN chat_room_members crm ON cr.id = crm.room_id
                WHERE cr.id = ? AND crm.user_id = ?";
        
        $room = $this->db->fetch($sql, [$userId, $userId, $roomId, $userId]);
        
        if (!$room) {
            return null;
        }
        
        // 获取最后一条消息
        $sql = "SELECT content, created_at FROM messages 
                WHERE room_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";
        $lastMessage = $this->db->fetch($sql, [$roomId]);
        
        if ($lastMessage) {
            $room['last_message'] = $lastMessage['content'];
            $room['last_message_time'] = $lastMessage['created_at'];
        } else {
            $room['last_message'] = null;
            $room['last_message_time'] = null;
        }
        
        // 如果是私聊，获取聊天对象的状态和ID
        if ($room['type'] === 'private') {
            $sql = "SELECT u.id, u.status FROM users u 
                    JOIN chat_room_members crm ON u.id = crm.user_id 
                    WHERE crm.room_id = ? AND u.id != ?";
            $partner = $this->db->fetch($sql, [$roomId, $userId]);
            $room['partner_id'] = $partner ? $partner['id'] : null;
            $room['partner_status'] = $partner ? $partner['status'] : 'offline';
        } else {
            $room['partner_id'] = null;
            $room['partner_status'] = 'offline';
        }
        
        return $room;
    }
    
    // 获取群组信息
    public function getGroupInfo($groupId, $userId) {
        // 首先检查用户是否是群组成员
        $sql = "SELECT id FROM chat_room_members WHERE room_id = ? AND user_id = ?";
        $isMember = $this->db->fetch($sql, [$groupId, $userId]);
        
        if (!$isMember) {
            return null;
        }
        
        // 获取群组基本信息
        $sql = "SELECT * FROM chat_rooms WHERE id = ? AND type = 'group'";
        $group = $this->db->fetch($sql, [$groupId]);
        
        if (!$group) {
            return null;
        }
        
        // 获取群主用户名
        if ($group['created_by']) {
            $sql = "SELECT username FROM users WHERE id = ?";
            $createdByUser = $this->db->fetch($sql, [$group['created_by']]);
            $group['created_by_name'] = $createdByUser ? $createdByUser['username'] : null;
        } else {
            $group['created_by_name'] = null;
        }
        
        // 获取成员数量
        $sql = "SELECT COUNT(*) as member_count FROM chat_room_members WHERE room_id = ?";
        $memberCount = $this->db->fetch($sql, [$groupId]);
        $group['member_count'] = $memberCount['member_count'];
        
        // 设置显示名称
        $group['display_name'] = $group['name'];
        
        return $group;
    }
    
    // 获取群组成员列表
    public function getGroupMembers($groupId) {
        $sql = "SELECT u.id, u.username, u.avatar, u.status, u.last_seen,
                       crm.joined_at, crm.role,
                       CASE WHEN cr.created_by = u.id THEN 1 ELSE 0 END as is_creator
                FROM chat_room_members crm
                JOIN users u ON crm.user_id = u.id
                JOIN chat_rooms cr ON crm.room_id = cr.id
                WHERE crm.room_id = ?
                ORDER BY crm.role DESC, crm.joined_at ASC";
        
        return $this->db->fetchAll($sql, [$groupId]);
    }
    
    // 映射文件类型到数据库支持的message_type
    private function mapFileTypeToMessageType($fileType) {
        // 数据库ENUM支持的值: 'text', 'image', 'file', 'voice', 'video'
        switch ($fileType) {
            case 'image':
                return 'image';
            case 'video':
                return 'video';
            case 'file':
            default:
                return 'file';
        }
    }
    
    // 获取房间成员信息
    public function getRoomMembers($roomId) {
        $sql = "SELECT u.id, u.username, u.avatar, u.status, crm.joined_at, crm.role
                FROM chat_room_members crm
                JOIN users u ON crm.user_id = u.id
                WHERE crm.room_id = ?
                ORDER BY crm.joined_at ASC";
        
        return $this->db->fetchAll($sql, [$roomId]);
    }
    
    // 检查用户是否在房间中
    public function isUserInRoom($userId, $roomId) {
        $sql = "SELECT COUNT(*) as count FROM chat_room_members WHERE user_id = ? AND room_id = ?";
        $result = $this->db->fetch($sql, [$userId, $roomId]);
        
        if ($result === false) {
            error_log("isUserInRoom failed - database query failed");
            return false;
        }
        
        return $result['count'] > 0;
    }
    
    // 检查用户是否在群组中
    public function isUserInGroup($userId, $groupId) {
        $sql = "SELECT COUNT(*) as count FROM chat_room_members WHERE user_id = ? AND room_id = ?";
        $result = $this->db->fetch($sql, [$userId, $groupId]);
        
        if ($result === false) {
            error_log("isUserInGroup failed - database query failed");
            return false;
        }
        
        return $result['count'] > 0;
    }
    
    // 获取消息详情
    public function getMessageById($messageId) {
        $sql = "SELECT m.*, u.username, u.avatar,
                       qm.content as quoted_content,
                       qu.username as quoted_sender_name,
                       qu.avatar as quoted_sender_avatar
                FROM messages m 
                LEFT JOIN users u ON m.sender_id = u.id 
                LEFT JOIN messages qm ON m.quoted_message_id = qm.id
                LEFT JOIN users qu ON qm.sender_id = qu.id
                WHERE m.id = ?";
        $result = $this->db->fetch($sql, [$messageId]);
        
        if ($result === false) {
            error_log("getMessageById failed - database query failed");
            return false;
        }
        
        return $result;
    }
    
    // 撤回消息
    public function recallMessage($messageId) {
        $sql = "UPDATE messages SET is_recalled = 1 WHERE id = ?";
        $stmt = $this->db->query($sql, [$messageId]);
        
        if ($stmt === false) {
            error_log("Chat::recallMessage failed - database query failed");
            return false;
        }
        
        // 检查是否有行被更新
        $rowCount = $stmt->rowCount();
        error_log("Chat::recallMessage - rowCount: $rowCount");
        
        return $rowCount > 0;
    }
    
    // 删除消息（仅对用户隐藏）
    public function deleteMessageForUser($messageId, $userId) {
        // 这里可以实现一个用户消息删除表，或者使用JSON字段存储删除的用户ID
        // 为了简化，我们直接删除消息（实际应用中可能需要更复杂的逻辑）
        $sql = "UPDATE messages SET is_deleted = 1 WHERE id = ? AND sender_id = ?";
        $stmt = $this->db->query($sql, [$messageId, $userId]);
        
        if ($stmt === false) {
            error_log("Chat::deleteMessageForUser failed - database query failed");
            return false;
        }
        
        // 检查是否有行被更新
        $rowCount = $stmt->rowCount();
        error_log("Chat::deleteMessageForUser - rowCount: $rowCount");
        
        return $rowCount > 0;
    }
    
    // 修改消息
    public function editMessage($messageId, $content) {
        $sql = "UPDATE messages SET content = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$content, $messageId]);
        
        if ($stmt === false) {
            error_log("Chat::editMessage failed - database query failed");
            return false;
        }
        
        // 检查是否有行被更新
        $rowCount = $stmt->rowCount();
        error_log("Chat::editMessage - rowCount: $rowCount");
        
        return $rowCount > 0;
    }
    
    // 切换置顶状态
    public function togglePin($messageId, $userId) {
        error_log("Chat::togglePin called - messageId: $messageId, userId: $userId");
        
        // 检查消息是否存在
        $sql = "SELECT id, is_pinned, room_id FROM messages WHERE id = ?";
        $message = $this->db->fetch($sql, [$messageId]);
        
        error_log("Chat::togglePin - message found: " . print_r($message, true));
        
        if ($message === false) {
            error_log("Chat::togglePin failed - database query failed");
            return null;
        }
        
        if (!$message) {
            error_log("Chat::togglePin failed - message not found");
            return null;
        }
        
        // 检查用户是否在房间中
        $isInRoom = $this->isUserInRoom($userId, $message['room_id']);
        error_log("Chat::togglePin - isUserInRoom result: " . var_export($isInRoom, true));
        
        if ($isInRoom === false) {
            error_log("Chat::togglePin failed - database query failed for isUserInRoom");
            return null;
        }
        
        if (!$isInRoom) {
            error_log("Chat::togglePin failed - user not in room");
            return null;
        }
        
        if ($message['is_pinned']) {
            // 取消置顶
            error_log("Chat::togglePin - unpinning message");
            $sql = "UPDATE messages SET is_pinned = 0 WHERE id = ?";
            $result = $this->db->query($sql, [$messageId]);
            error_log("Chat::togglePin - unpin query result: " . var_export($result, true));
            
            if ($result === false) {
                error_log("Chat::togglePin failed - unpin query failed");
                return null;
            }
            
            return false;
        } else {
            // 先取消该聊天室中所有其他消息的置顶状态
            error_log("Chat::togglePin - pinning message, first unpinning others");
            $sql = "SELECT room_id FROM messages WHERE id = ?";
            $room = $this->db->fetch($sql, [$messageId]);
            
            if ($room) {
                $sql = "UPDATE messages SET is_pinned = 0 WHERE room_id = ?";
                $result = $this->db->query($sql, [$room['room_id']]);
                error_log("Chat::togglePin - unpin others query result: " . var_export($result, true));
                
                if ($result === false) {
                    error_log("Chat::togglePin failed - unpin others query failed");
                    return null;
                }
            }
            
            // 置顶当前消息
            $sql = "UPDATE messages SET is_pinned = 1 WHERE id = ?";
            $result = $this->db->query($sql, [$messageId]);
            error_log("Chat::togglePin - pin query result: " . var_export($result, true));
            
            if ($result === false) {
                error_log("Chat::togglePin failed - pin query failed");
                return null;
            }
            
            return true;
        }
    }
    
    // 获取用户群组列表
    public function getUserGroups($userId) {
        $sql = "SELECT cr.id, cr.name, cr.created_at, cr.avatar,
                       COUNT(crm.user_id) as member_count,
                       u.username as created_by_name
                FROM chat_rooms cr
                JOIN chat_room_members crm ON cr.id = crm.room_id
                LEFT JOIN users u ON cr.created_by = u.id
                WHERE cr.type = 'group' AND crm.user_id = ?
                GROUP BY cr.id, cr.name, cr.created_at, cr.avatar, u.username
                ORDER BY cr.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    // 转发消息给用户
    public function forwardToUser($originalMessage, $targetUserId, $senderId) {
        // 获取或创建与目标用户的私聊房间
        $roomId = $this->getOrCreatePrivateRoom($senderId, $targetUserId);
        
        // 创建转发消息
        $forwardedContent = "[转发] " . $originalMessage['content'];
        
        $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        return $this->db->query($sql, [
            $roomId,
            $senderId,
            $forwardedContent,
            $originalMessage['message_type'],
            $originalMessage['file_path']
        ]);
    }
    
    // 转发消息给群组
    public function forwardToGroup($originalMessage, $targetGroupId, $senderId) {
        // 获取群组的房间ID
        $sql = "SELECT room_id FROM groups WHERE id = ?";
        $group = $this->db->fetch($sql, [$targetGroupId]);
        
        if (!$group) {
            throw new Exception('群组不存在');
        }
        
        $roomId = $group['room_id'];
        
        // 检查用户是否在群组中
        if (!$this->isUserInRoom($senderId, $roomId)) {
            throw new Exception('您不在该群组中');
        }
        
        // 创建转发消息
        $forwardedContent = "[转发] " . $originalMessage['content'];
        
        $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        return $this->db->query($sql, [
            $roomId,
            $senderId,
            $forwardedContent,
            $originalMessage['message_type'],
            $originalMessage['file_path']
        ]);
    }
    
    // 获取置顶消息
    public function getPinnedMessages($roomId, $userId) {
        $sql = "SELECT m.*, u.username, u.avatar, m.updated_at as pinned_at
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.room_id = ? AND m.is_pinned = 1
                ORDER BY m.updated_at DESC";
        
        return $this->db->fetchAll($sql, [$roomId]);
    }

    // 清空聊天记录
    public function clearChatHistory($roomId, $roomType) {
        try {
            error_log("clearChatHistory called - roomId: $roomId, roomType: $roomType");
            
            // 参考getRoomInfo方法，直接删除该房间的所有消息，不需要区分room_type
            $sql = "DELETE FROM messages WHERE room_id = ?";
            
            error_log("clearChatHistory - executing SQL: $sql with roomId: $roomId");
            $stmt = $this->db->query($sql, [$roomId]);
            error_log("clearChatHistory - query result: " . ($stmt ? 'success' : 'failed'));
            
            if ($stmt) {
                // 参考getRoomInfo方法，更新chat_rooms表（不是rooms表）
                $updateSql = "UPDATE chat_rooms SET last_message = NULL, last_message_time = NULL WHERE id = ?";
                error_log("clearChatHistory - updating room info with SQL: $updateSql");
                $updateResult = $this->db->query($updateSql, [$roomId]);
                error_log("clearChatHistory - update result: " . ($updateResult ? 'success' : 'failed'));
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("清空聊天记录失败: " . $e->getMessage());
            return false;
        }
    }

    // 检查用户是否是群主
    public function isGroupOwner($userId, $groupId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM chat_rooms WHERE id = ? AND created_by = ?";
            $result = $this->db->fetch($sql, [$groupId, $userId]);
            
            if ($result === false) {
                error_log("isGroupOwner failed - database query failed");
                return false;
            }
            
            $isOwner = $result['count'] > 0;
            error_log("isGroupOwner - userId: $userId, groupId: $groupId, result: " . ($isOwner ? 'true' : 'false'));
            return $isOwner;
            
        } catch (Exception $e) {
            error_log("isGroupOwner error: " . $e->getMessage());
            return false;
        }
    }
    
    // 检查用户是否是群管理员
    public function isGroupAdmin($userId, $groupId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM chat_room_members WHERE user_id = ? AND room_id = ? AND role = 'admin'";
            $result = $this->db->fetch($sql, [$userId, $groupId]);
            
            if ($result === false) {
                error_log("isGroupAdmin failed - database query failed");
                return false;
            }
            
            $isAdmin = $result['count'] > 0;
            error_log("isGroupAdmin - userId: $userId, groupId: $groupId, result: " . ($isAdmin ? 'true' : 'false'));
            return $isAdmin;
            
        } catch (Exception $e) {
            error_log("isGroupAdmin error: " . $e->getMessage());
            return false;
        }
    }
    
    // 获取数据库实例
    public function getDb() {
        return $this->db;
    }
    
    // 获取房间信息
    public function getRoomById($roomId) {
        $sql = "SELECT * FROM chat_rooms WHERE id = ?";
        $result = $this->db->query($sql, [$roomId]);
        return $result->fetch(PDO::FETCH_ASSOC);
    }
    
    // 从房间获取好友ID
    public function getFriendIdFromRoom($roomId, $userId) {
        $sql = "SELECT crm2.user_id as friend_id
                FROM chat_room_members crm1
                JOIN chat_room_members crm2 ON crm1.room_id = crm2.room_id
                JOIN chat_rooms cr ON crm1.room_id = cr.id
                WHERE crm1.room_id = ? AND crm1.user_id = ? 
                AND crm2.user_id != ? AND cr.type = 'private'";
        $result = $this->db->query($sql, [$roomId, $userId, $userId]);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['friend_id'] : null;
    }
}
?>
