<?php
/**
 * PHP信令服务器
 * 基于WebSocket的简单信令服务器实现
 * 不依赖Node.js，使用PHP内置功能
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 0); // 关闭错误显示，避免HTML错误页面
ini_set('log_errors', 1); // 启用错误日志

// 设置CORS头
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 设置错误处理器
set_error_handler(function($severity, $message, $file, $line) {
    error_log("[SignalingServer] PHP Error: $message in $file:$line");
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 设置异常处理器
set_exception_handler(function($exception) {
    error_log("[SignalingServer] Uncaught Exception: " . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '服务器内部错误',
        'details' => $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

class PHPSignalingServer {
    private $rooms = [];
    private $users = [];
    private $dataFile = 'signaling-data.json';
    
    public function __construct() {
        $this->loadData();
        // 暂时禁用过期数据清理，避免清理测试数据
        // $this->cleanupExpiredData();
    }
    
    /**
     * 加载数据
     */
    private function loadData() {
        error_log("[SignalingServer] 开始加载数据...");
        
        if (file_exists($this->dataFile)) {
            error_log("[SignalingServer] 数据文件存在: " . $this->dataFile);
            
            $jsonData = file_get_contents($this->dataFile);
            error_log("[SignalingServer] 文件内容长度: " . strlen($jsonData));
            
            $data = json_decode($jsonData, true);
            
            if ($data === null) {
                error_log("[SignalingServer] JSON解析失败: " . json_last_error_msg());
                error_log("[SignalingServer] JSON数据: " . substr($jsonData, 0, 200));
                return;
            }
            
            $this->rooms = $data['rooms'] ?? [];
            $this->users = $data['users'] ?? [];
            
            error_log("[SignalingServer] 数据加载完成 - 房间数: " . count($this->rooms) . ", 用户数: " . count($this->users));
            error_log("[SignalingServer] 房间列表: " . implode(', ', array_keys($this->rooms)));
            error_log("[SignalingServer] 用户列表: " . implode(', ', array_keys($this->users)));
        } else {
            error_log("[SignalingServer] 数据文件不存在: " . $this->dataFile);
            error_log("[SignalingServer] 当前工作目录: " . getcwd());
        }
    }
    
    /**
     * 保存数据
     */
    private function saveData() {
        $data = [
            'rooms' => $this->rooms,
            'users' => $this->users,
            'timestamp' => time()
        ];
        file_put_contents($this->dataFile, json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 清理过期数据
     */
    private function cleanupExpiredData() {
        $currentTime = time();
        $expiredTime = $currentTime - (24 * 60 * 60); // 24小时前的数据视为过期
        
        $cleanedRooms = 0;
        $cleanedUsers = 0;
        
        // 清理过期房间
        foreach ($this->rooms as $roomId => $room) {
            if (isset($room['createdAt'])) {
                $roomTime = strtotime($room['createdAt']);
                if ($roomTime < $expiredTime) {
                    unset($this->rooms[$roomId]);
                    $cleanedRooms++;
                }
            }
        }
        
        // 清理过期用户
        foreach ($this->users as $userId => $user) {
            if (isset($user['lastSeen'])) {
                $userTime = strtotime($user['lastSeen']);
                if ($userTime < $expiredTime) {
                    unset($this->users[$userId]);
                    $cleanedUsers++;
                }
            }
        }
        
        if ($cleanedRooms > 0 || $cleanedUsers > 0) {
            $this->saveData();
            error_log("[SignalingServer] 清理完成 - 房间: {$cleanedRooms}, 用户: {$cleanedUsers}");
        }
    }
    
    /**
     * 处理请求
     */
    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = $_GET['path'] ?? '';
            
            error_log("[SignalingServer] 处理请求: $method $path");
            
            switch ($path) {
                case '/health':
                    return $this->healthCheck();
                    
                case '/status':
                    return $this->getStatus();
                    
                case '/room':
                    return $this->handleRoomRequest();
                    
                case '/message':
                    return $this->handleMessage();
                    
                case '/poll':
                    return $this->handlePoll();
                    
                case '/room_info':
                    return $this->handleRoomInfo();
                    
                case '/direct_room_info':
                    return $this->handleDirectRoomInfo();
                    
                default:
                    // 如果没有指定路径，根据请求方法处理
                    if ($method === 'POST') {
                        return $this->handleMessage();
                    } else {
                        return $this->healthCheck();
                    }
            }
        } catch (Exception $e) {
            error_log("[SignalingServer] 处理请求时出错: " . $e->getMessage());
            return [
                'success' => false,
                'error' => '处理请求失败',
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 健康检查
     */
    private function healthCheck() {
        $activeUsers = 0;
        $activeRooms = 0;
        
        // 统计活跃用户
        foreach ($this->users as $user) {
            if ($user['status'] === 'online') {
                $activeUsers++;
            }
        }
        
        // 统计活跃房间
        foreach ($this->rooms as $room) {
            if (!empty($room['participants'])) {
                $activeRooms++;
            }
        }
        
        // 添加调试信息
        error_log("[SignalingServer] 健康检查 - 房间数: " . count($this->rooms) . ", 用户数: " . count($this->users));
        
        return [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'rooms' => count($this->rooms),
            'active_rooms' => $activeRooms,
            'users' => count($this->users),
            'active_users' => $activeUsers,
            'server' => 'PHP Signaling Server v2.0',
            'data_file' => $this->dataFile,
            'data_file_exists' => file_exists($this->dataFile),
            'debug_info' => [
                'rooms_array' => array_keys($this->rooms),
                'users_array' => array_keys($this->users)
            ]
        ];
    }
    
    /**
     * 获取状态
     */
    private function getStatus() {
        return [
            'success' => true,
            'data' => [
                'server' => 'PHPSignalingServer',
                'version' => '1.0.0',
                'uptime' => time() - ($_SERVER['REQUEST_TIME'] ?? time()),
                'rooms' => count($this->rooms),
                'users' => count($this->users),
                'memory' => memory_get_usage(true)
            ]
        ];
    }
    
    /**
     * 处理房间请求
     */
    private function handleRoomRequest() {
        $roomId = $_GET['roomId'] ?? '';
        
        if (empty($roomId)) {
            return [
                'success' => false,
                'error' => '房间ID不能为空'
            ];
        }
        
        if (isset($this->rooms[$roomId])) {
            return [
                'success' => true,
                'data' => [
                    'roomId' => $roomId,
                    'participants' => $this->rooms[$roomId]['participants'] ?? [],
                    'createdAt' => $this->rooms[$roomId]['createdAt'] ?? null
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => '房间不存在'
            ];
        }
    }
    
    /**
     * 处理消息
     */
    private function handleMessage() {
        try {
            $rawInput = file_get_contents('php://input');
            error_log("[SignalingServer] 收到原始输入: " . substr($rawInput, 0, 200));
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                $jsonError = json_last_error_msg();
                error_log("[SignalingServer] JSON解析失败: " . $jsonError);
                error_log("[SignalingServer] 原始数据: " . $rawInput);
                return [
                    'success' => false,
                    'error' => '无效的JSON数据: ' . $jsonError
                ];
            }
            
            $type = $input['type'] ?? '';
            $payload = $input['payload'] ?? [];
            
            error_log("[SignalingServer] 处理消息类型: " . $type);
            error_log("[SignalingServer] 消息载荷: " . json_encode($payload));
            
            switch ($type) {
                case 'join_room':
                    return $this->joinRoom($payload);
                    
                case 'leave_room':
                    return $this->leaveRoom($payload);
                    
                case 'call_invitation':
                    return $this->handleCallInvitation($payload);
                    
                case 'call_response':
                    return $this->handleCallResponse($payload);
                    
                case 'offer':
                    return $this->handleOffer($payload);
                    
                case 'answer':
                    return $this->handleAnswer($payload);
                    
                case 'ice_candidate':
                    return $this->handleIceCandidate($payload);
                    
                case 'user_status':
                    return $this->handleUserStatus($payload);
                    
                case 'ping':
                    return $this->handlePing($payload);
                    
                case 'get_room_info':
                    return $this->handleGetRoomInfo($payload);
                    
                default:
                    return [
                        'success' => false,
                        'error' => '未知消息类型: ' . $type
                    ];
            }
        } catch (Exception $e) {
            error_log("[SignalingServer] 处理消息时出错: " . $e->getMessage());
            return [
                'success' => false,
                'error' => '处理消息失败',
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 处理轮询
     */
    private function handlePoll() {
        try {
            $userId = $_GET['userId'] ?? '';
            $lastCheck = $_GET['lastCheck'] ?? 0;
            
            if (empty($userId)) {
                return [
                    'success' => false,
                    'error' => '用户ID不能为空'
                ];
            }
            
            error_log("[SignalingServer] 处理轮询请求 - 用户: $userId, 最后检查时间: $lastCheck");
            
            // 获取用户的消息队列
            $messages = $this->getUserMessages($userId, $lastCheck);
            
            return [
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'timestamp' => time()
                ]
            ];
        } catch (Exception $e) {
            error_log("[SignalingServer] 处理轮询时出错: " . $e->getMessage());
            return [
                'success' => false,
                'error' => '处理轮询失败',
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 处理直接房间信息请求（不通过消息队列）
     */
    private function handleDirectRoomInfo() {
        $userId = $_GET['userId'] ?? '';
        $roomId = $_GET['roomId'] ?? '';
        
        if (empty($userId)) {
            return [
                'success' => false,
                'error' => '用户ID不能为空'
            ];
        }
        
        // 如果指定了房间ID，返回该房间信息
        if (!empty($roomId)) {
            if (isset($this->rooms[$roomId])) {
                error_log("[SignalingServer] 直接返回房间信息，房间 {$roomId} 有 " . count($this->rooms[$roomId]['participants']) . " 个参与者");
                
                return [
                    'success' => true,
                    'data' => [
                        'type' => 'room_info',
                        'payload' => [
                            'roomId' => $roomId,
                            'participants' => array_values($this->rooms[$roomId]['participants'])
                        ]
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'error' => '房间不存在'
                ];
            }
        }
        
        // 如果用户ID存在，返回用户所在房间信息
        if (isset($this->users[$userId])) {
            $userRoomId = $this->users[$userId]['roomId'] ?? '';
            if (!empty($userRoomId) && isset($this->rooms[$userRoomId])) {
                error_log("[SignalingServer] 直接返回用户所在房间信息，房间 {$userRoomId} 有 " . count($this->rooms[$userRoomId]['participants']) . " 个参与者");
                
                return [
                    'success' => true,
                    'data' => [
                        'type' => 'room_info',
                        'payload' => [
                            'roomId' => $userRoomId,
                            'participants' => array_values($this->rooms[$userRoomId]['participants'])
                        ]
                    ]
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => '未找到房间信息'
        ];
    }
    
    /**
     * 处理房间信息请求
     */
    private function handleRoomInfo() {
        $userId = $_GET['userId'] ?? '';
        $roomId = $_GET['roomId'] ?? '';
        
        if (empty($userId)) {
            return [
                'success' => false,
                'error' => '用户ID不能为空'
            ];
        }
        
        // 如果指定了房间ID，返回该房间信息
        if (!empty($roomId)) {
            if (isset($this->rooms[$roomId])) {
                return [
                    'success' => true,
                    'data' => [
                        'roomId' => $roomId,
                        'participants' => array_values($this->rooms[$roomId]['participants'])
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'error' => '房间不存在'
                ];
            }
        }
        
        // 如果用户ID存在，返回用户所在房间信息
        if (isset($this->users[$userId]) && !empty($this->users[$userId]['roomId'])) {
            $userRoomId = $this->users[$userId]['roomId'];
            if (isset($this->rooms[$userRoomId])) {
                return [
                    'success' => true,
                    'data' => [
                        'roomId' => $userRoomId,
                        'participants' => array_values($this->rooms[$userRoomId]['participants'])
                    ]
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => '用户不在任何房间中'
        ];
    }
    
    /**
     * 加入房间
     */
    private function joinRoom($payload) {
        $roomId = $payload['roomId'] ?? '';
        $userId = $payload['userId'] ?? '';
        $username = $payload['username'] ?? 'Unknown';
        
        if (empty($roomId) || empty($userId)) {
            return [
                'success' => false,
                'error' => '房间ID和用户ID不能为空'
            ];
        }
        
        // 确保用户ID是字符串格式
        $userId = (string)$userId;
        
        // 创建或更新房间
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [
                'id' => $roomId,
                'participants' => [],
                'createdAt' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];
        }
        
        // 检查用户是否已经在房间中
        $userAlreadyInRoom = isset($this->rooms[$roomId]['participants'][$userId]);
        
        // 添加或更新用户到房间
        $this->rooms[$roomId]['participants'][$userId] = [
            'id' => $userId,
            'username' => $username,
            'joinedAt' => date('Y-m-d H:i:s'),
            'status' => 'online'
        ];
        
        // 更新用户信息
        $this->users[$userId] = [
            'id' => $userId,
            'username' => $username,
            'roomId' => $roomId,
            'lastSeen' => date('Y-m-d H:i:s'),
            'status' => 'online'
        ];
        
        // 通知房间内其他用户（无论是否是新用户）
        $this->broadcastToRoom($roomId, [
            'type' => 'user_joined',
            'payload' => [
                'userId' => $userId,
                'username' => $username
            ]
        ], $userId);
        
        // 广播房间信息给所有参与者
        $this->broadcastToRoom($roomId, [
            'type' => 'room_info',
            'payload' => [
                'roomId' => $roomId,
                'participants' => array_values($this->rooms[$roomId]['participants'])
            ]
        ]);
        
        if (!$userAlreadyInRoom) {
            error_log("[SignalingServer] 用户 {$username} (ID: {$userId}) 加入房间 {$roomId}");
        } else {
            error_log("[SignalingServer] 用户 {$username} (ID: {$userId}) 重新加入房间 {$roomId}");
        }
        
        $this->saveData();
        
        return [
            'success' => true,
            'data' => [
                'roomId' => $roomId,
                'participants' => array_values($this->rooms[$roomId]['participants']),
                'isNewUser' => !$userAlreadyInRoom
            ]
        ];
    }
    
    /**
     * 离开房间
     */
    private function leaveRoom($payload) {
        $roomId = $payload['roomId'] ?? '';
        $userId = $payload['userId'] ?? '';
        
        if (empty($roomId) || empty($userId)) {
            return [
                'success' => false,
                'error' => '房间ID和用户ID不能为空'
            ];
        }
        
        if (isset($this->rooms[$roomId])) {
            // 从房间移除用户
            if (isset($this->rooms[$roomId]['participants'][$userId])) {
                unset($this->rooms[$roomId]['participants'][$userId]);
                
                // 通知房间内其他用户
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_left',
                    'payload' => [
                        'userId' => $userId
                    ]
                ]);
                
                // 如果房间为空，删除房间
                if (empty($this->rooms[$roomId]['participants'])) {
                    unset($this->rooms[$roomId]);
                }
            }
        }
        
        // 更新用户状态
        if (isset($this->users[$userId])) {
            $this->users[$userId]['status'] = 'offline';
            $this->users[$userId]['lastSeen'] = date('Y-m-d H:i:s');
        }
        
        $this->saveData();
        
        return [
            'success' => true,
            'data' => [
                'roomId' => $roomId,
                'userId' => $userId
            ]
        ];
    }
    
    /**
     * 处理通话邀请
     */
    private function handleCallInvitation($payload) {
        $callId = $payload['callId'] ?? '';
        $type = $payload['type'] ?? '';
        $participants = $payload['participants'] ?? [];
        $fromUserId = $payload['fromUserId'] ?? '';
        
        error_log("[SignalingServer] 处理通话邀请 - callId: $callId, type: $type, fromUserId: $fromUserId, participants: " . count($participants));
        
        if (empty($callId)) {
            error_log("[SignalingServer] 通话邀请参数无效 - callId为空");
            return [
                'success' => false,
                'error' => '通话邀请参数无效 - callId为空'
            ];
        }
        
        // 如果没有参与者列表，尝试从房间信息获取
        if (empty($participants)) {
            error_log("[SignalingServer] 参与者列表为空，尝试从房间信息获取");
            // 这里可以添加从房间信息获取参与者的逻辑
        }
        
        $sentCount = 0;
        
        // 转发给目标用户
        foreach ($participants as $participant) {
            // 确保用户ID是字符串格式
            $targetUserId = (string)$participant['id'];
            $username = $participant['username'] ?? 'Unknown';
            
            error_log("[SignalingServer] 发送通话邀请给用户: $targetUserId ($username)");
            
            $this->sendMessageToUser($targetUserId, [
                'type' => 'call_invitation',
                'payload' => [
                    'callId' => $callId,
                    'type' => $type,
                    'fromUserId' => $fromUserId,
                    'participants' => $participants
                ]
            ]);
            
            $sentCount++;
        }
        
        error_log("[SignalingServer] 通话邀请发送完成 - 发送给 $sentCount 个用户");
        
        return [
            'success' => true,
            'data' => [
                'callId' => $callId,
                'sent' => $sentCount
            ]
        ];
    }
    
    /**
     * 处理通话响应
     */
    private function handleCallResponse($payload) {
        $callId = $payload['callId'] ?? '';
        $accepted = $payload['accepted'] ?? false;
        $fromUserId = $payload['fromUserId'] ?? '';
        
        if (empty($callId)) {
            return [
                'success' => false,
                'error' => '通话响应参数无效'
            ];
        }
        
        // 转发给发起者
        $this->sendMessageToUser($fromUserId, [
            'type' => 'call_response',
            'payload' => [
                'callId' => $callId,
                'accepted' => $accepted,
                'fromUserId' => $payload['userId'] ?? ''
            ]
        ]);
        
        return [
            'success' => true,
            'data' => [
                'callId' => $callId,
                'accepted' => $accepted
            ]
        ];
    }
    
    /**
     * 处理Offer
     */
    private function handleOffer($payload) {
        $offer = $payload['offer'] ?? null;
        $targetUserId = $payload['targetUserId'] ?? '';
        $fromUserId = $payload['fromUserId'] ?? '';
        
        error_log("[SignalingServer] 处理Offer - fromUserId: {$fromUserId}, targetUserId: {$targetUserId}");
        
        if (!$offer) {
            error_log("[SignalingServer] Offer数据无效");
            return [
                'success' => false,
                'error' => 'Offer数据无效'
            ];
        }
        
        // 如果指定了目标用户，发送给目标用户
        if (!empty($targetUserId)) {
            error_log("[SignalingServer] 发送Offer给目标用户: {$targetUserId}");
            $this->sendMessageToUser($targetUserId, [
                'type' => 'offer',
                'payload' => [
                    'offer' => $offer,
                    'fromUserId' => $fromUserId
                ]
            ]);
        } else {
            // 如果没有指定目标用户，广播给房间内所有其他用户
            if (!empty($fromUserId) && isset($this->users[$fromUserId])) {
                $roomId = $this->users[$fromUserId]['roomId'] ?? '';
                error_log("[SignalingServer] 广播Offer到房间: {$roomId}");
                if (!empty($roomId) && isset($this->rooms[$roomId])) {
                    $participantCount = count($this->rooms[$roomId]['participants']);
                    error_log("[SignalingServer] 房间 {$roomId} 参与者数量: {$participantCount}");
                    
                    $this->broadcastToRoom($roomId, [
                        'type' => 'offer',
                        'payload' => [
                            'offer' => $offer,
                            'fromUserId' => $fromUserId
                        ]
                    ], $fromUserId);
                } else {
                    error_log("[SignalingServer] 房间 {$roomId} 不存在");
                }
            } else {
                error_log("[SignalingServer] 用户 {$fromUserId} 不存在或未加入房间");
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'type' => 'offer',
                'forwarded' => true
            ]
        ];
    }
    
    /**
     * 处理Answer
     */
    private function handleAnswer($payload) {
        $answer = $payload['answer'] ?? null;
        $targetUserId = $payload['targetUserId'] ?? '';
        $fromUserId = $payload['fromUserId'] ?? '';
        
        error_log("[SignalingServer] 处理Answer - fromUserId: {$fromUserId}, targetUserId: {$targetUserId}");
        
        if (!$answer) {
            error_log("[SignalingServer] Answer数据无效");
            return [
                'success' => false,
                'error' => 'Answer数据无效'
            ];
        }
        
        // 如果指定了目标用户，发送给目标用户
        if (!empty($targetUserId)) {
            error_log("[SignalingServer] 发送Answer给目标用户: {$targetUserId}");
            $this->sendMessageToUser($targetUserId, [
                'type' => 'answer',
                'payload' => [
                    'answer' => $answer,
                    'fromUserId' => $fromUserId
                ]
            ]);
        } else {
            // 如果没有指定目标用户，广播给房间内所有其他用户
            if (!empty($fromUserId) && isset($this->users[$fromUserId])) {
                $roomId = $this->users[$fromUserId]['roomId'] ?? '';
                error_log("[SignalingServer] 广播Answer到房间: {$roomId}");
                if (!empty($roomId) && isset($this->rooms[$roomId])) {
                    $participantCount = count($this->rooms[$roomId]['participants']);
                    error_log("[SignalingServer] 房间 {$roomId} 参与者数量: {$participantCount}");
                    
                    $this->broadcastToRoom($roomId, [
                        'type' => 'answer',
                        'payload' => [
                            'answer' => $answer,
                            'fromUserId' => $fromUserId
                        ]
                    ], $fromUserId);
                } else {
                    error_log("[SignalingServer] 房间 {$roomId} 不存在");
                }
            } else {
                error_log("[SignalingServer] 用户 {$fromUserId} 不存在或未加入房间");
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'type' => 'answer',
                'forwarded' => true
            ]
        ];
    }
    
    /**
     * 处理ICE候选
     */
    private function handleIceCandidate($payload) {
        $candidate = $payload['candidate'] ?? null;
        $targetUserId = $payload['targetUserId'] ?? '';
        $fromUserId = $payload['fromUserId'] ?? '';
        
        if (!$candidate) {
            return [
                'success' => false,
                'error' => 'ICE候选数据无效'
            ];
        }
        
        // 如果指定了目标用户，发送给目标用户
        if (!empty($targetUserId)) {
            $this->sendMessageToUser($targetUserId, [
                'type' => 'ice_candidate',
                'payload' => [
                    'candidate' => $candidate,
                    'fromUserId' => $fromUserId
                ]
            ]);
        } else {
            // 如果没有指定目标用户，广播给房间内所有其他用户
            if (!empty($fromUserId) && isset($this->users[$fromUserId])) {
                $roomId = $this->users[$fromUserId]['roomId'] ?? '';
                if (!empty($roomId) && isset($this->rooms[$roomId])) {
                    $this->broadcastToRoom($roomId, [
                        'type' => 'ice_candidate',
                        'payload' => [
                            'candidate' => $candidate,
                            'fromUserId' => $fromUserId
                        ]
                    ], $fromUserId);
                }
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'type' => 'ice_candidate',
                'forwarded' => true
            ]
        ];
    }
    
    /**
     * 处理用户状态
     */
    private function handleUserStatus($payload) {
        $userId = $payload['userId'] ?? '';
        $status = $payload['status'] ?? '';
        
        if (empty($userId)) {
            return [
                'success' => false,
                'error' => '用户ID不能为空'
            ];
        }
        
        if (isset($this->users[$userId])) {
            $this->users[$userId]['status'] = $status;
            $this->users[$userId]['lastSeen'] = date('Y-m-d H:i:s');
            
            // 通知房间内其他用户
            $roomId = $this->users[$userId]['roomId'] ?? '';
            if (!empty($roomId)) {
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_status_update',
                    'payload' => [
                        'userId' => $userId,
                        'username' => $this->users[$userId]['username'],
                        'status' => $status
                    ]
                ], $userId);
            }
        }
        
        $this->saveData();
        
        return [
            'success' => true,
            'data' => [
                'userId' => $userId,
                'status' => $status
            ]
        ];
    }
    
    /**
     * 处理心跳
     */
    private function handlePing($payload) {
        $userId = $payload['userId'] ?? '';
        
        if (!empty($userId) && isset($this->users[$userId])) {
            $this->users[$userId]['lastSeen'] = date('Y-m-d H:i:s');
        }
        
        return [
            'success' => true,
            'data' => [
                'type' => 'pong',
                'timestamp' => time()
            ]
        ];
    }
    
    /**
     * 处理获取房间信息请求
     */
    private function handleGetRoomInfo($payload) {
        $userId = $payload['userId'] ?? '';
        $roomId = $payload['roomId'] ?? '';
        
        if (empty($userId)) {
            return [
                'success' => false,
                'error' => '用户ID不能为空'
            ];
        }
        
        // 如果指定了房间ID，返回该房间信息
        if (!empty($roomId)) {
            if (isset($this->rooms[$roomId])) {
                // 发送房间信息给用户
                $this->sendMessageToUser($userId, [
                    'type' => 'room_info',
                    'payload' => [
                        'roomId' => $roomId,
                        'participants' => array_values($this->rooms[$roomId]['participants'])
                    ]
                ]);
                
                // 同时广播给房间内所有其他用户，让他们知道有新用户请求房间信息
                $this->broadcastToRoom($roomId, [
                    'type' => 'room_info',
                    'payload' => [
                        'roomId' => $roomId,
                        'participants' => array_values($this->rooms[$roomId]['participants'])
                    ]
                ], $userId);
                
                error_log("[SignalingServer] 房间信息已发送给用户 {$userId}，房间 {$roomId} 有 " . count($this->rooms[$roomId]['participants']) . " 个参与者");
                
                return [
                    'success' => true,
                    'data' => [
                        'roomId' => $roomId,
                        'participants' => array_values($this->rooms[$roomId]['participants'])
                    ]
                ];
            } else {
                // 如果房间不存在，自动创建房间
                $this->rooms[$roomId] = [
                    'id' => $roomId,
                    'participants' => [],
                    'createdAt' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ];
                
                // 发送空房间信息给用户
                $this->sendMessageToUser($userId, [
                    'type' => 'room_info',
                    'payload' => [
                        'roomId' => $roomId,
                        'participants' => []
                    ]
                ]);
                
                error_log("[SignalingServer] 创建新房间 {$roomId} 并发送给用户 {$userId}");
                
                return [
                    'success' => true,
                    'data' => [
                        'roomId' => $roomId,
                        'participants' => []
                    ]
                ];
            }
        }
        
        // 如果用户ID存在，返回用户所在房间信息
        if (isset($this->users[$userId]) && !empty($this->users[$userId]['roomId'])) {
            $userRoomId = $this->users[$userId]['roomId'];
            if (isset($this->rooms[$userRoomId])) {
                // 发送房间信息给用户
                $this->sendMessageToUser($userId, [
                    'type' => 'room_info',
                    'payload' => [
                        'roomId' => $userRoomId,
                        'participants' => array_values($this->rooms[$userRoomId]['participants'])
                    ]
                ]);
                
                // 同时广播给房间内所有其他用户
                $this->broadcastToRoom($userRoomId, [
                    'type' => 'room_info',
                    'payload' => [
                        'roomId' => $userRoomId,
                        'participants' => array_values($this->rooms[$userRoomId]['participants'])
                    ]
                ], $userId);
                
                error_log("[SignalingServer] 用户 {$userId} 所在房间 {$userRoomId} 有 " . count($this->rooms[$userRoomId]['participants']) . " 个参与者");
                
                return [
                    'success' => true,
                    'data' => [
                        'roomId' => $userRoomId,
                        'participants' => array_values($this->rooms[$userRoomId]['participants'])
                    ]
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => '用户不在任何房间中'
        ];
    }
    
    /**
     * 向房间广播消息
     */
    private function broadcastToRoom($roomId, $message, $excludeUserId = null) {
        if (!isset($this->rooms[$roomId])) {
            return;
        }
        
        foreach ($this->rooms[$roomId]['participants'] as $userId => $user) {
            if ($excludeUserId && $userId === $excludeUserId) {
                continue;
            }
            
            $this->sendMessageToUser($userId, $message);
        }
    }
    
    /**
     * 向用户发送消息
     */
    private function sendMessageToUser($userId, $message) {
        // 确保用户ID是字符串格式
        $originalUserId = $userId;
        $userId = (string)$userId;
        
        error_log("[SignalingServer] 尝试发送消息给用户: {$originalUserId} -> {$userId}");
        error_log("[SignalingServer] 当前用户列表: " . implode(', ', array_keys($this->users)));
        
        // 尝试多种用户ID格式匹配
        $matchedUserId = null;
        
        // 1. 直接匹配
        if (isset($this->users[$userId])) {
            $matchedUserId = $userId;
        }
        // 2. 数字格式匹配
        else if (isset($this->users[(string)(int)$userId])) {
            $matchedUserId = (string)(int)$userId;
        }
        // 3. 遍历所有用户ID，尝试模糊匹配
        else {
            foreach (array_keys($this->users) as $existingUserId) {
                if ((string)$existingUserId === $userId || 
                    (string)$existingUserId === (string)(int)$userId ||
                    (string)(int)$existingUserId === $userId) {
                    $matchedUserId = $existingUserId;
                    break;
                }
            }
        }
        
        if (!$matchedUserId) {
            error_log("[SignalingServer] 用户 {$userId} 不存在，无法发送消息");
            error_log("[SignalingServer] 可用用户ID: " . implode(', ', array_keys($this->users)));
            return;
        }
        
        $userId = $matchedUserId;
        error_log("[SignalingServer] 匹配到用户ID: {$userId}");
        
        // 将消息存储到用户的消息队列
        if (!isset($this->users[$userId]['messages'])) {
            $this->users[$userId]['messages'] = [];
        }
        
        $this->users[$userId]['messages'][] = [
            'message' => $message,
            'timestamp' => time()
        ];
        
        error_log("[SignalingServer] 消息已发送给用户 {$userId}，消息类型: {$message['type']}，队列大小: " . count($this->users[$userId]['messages']));
        
        // 限制消息队列大小
        if (count($this->users[$userId]['messages']) > 100) {
            array_shift($this->users[$userId]['messages']);
        }
        
        // 立即保存数据，确保消息被持久化
        $this->saveData();
    }
    
    /**
     * 获取用户消息
     */
    private function getUserMessages($userId, $lastCheck) {
        $originalUserId = $userId;
        $userId = (string)$userId;
        
        error_log("[SignalingServer] 获取用户消息: {$originalUserId} -> {$userId}, lastCheck: {$lastCheck}");
        
        if (!isset($this->users[$userId])) {
            error_log("[SignalingServer] 用户 {$userId} 不存在，无法获取消息");
            return [];
        }
        
        if (!isset($this->users[$userId]['messages'])) {
            error_log("[SignalingServer] 用户 {$userId} 没有消息队列");
            return [];
        }
        
        $messages = [];
        foreach ($this->users[$userId]['messages'] as $msg) {
            if ($msg['timestamp'] > $lastCheck) {
                // 返回完整的消息对象，包括timestamp
                $messages[] = [
                    'message' => $msg['message'],
                    'timestamp' => $msg['timestamp']
                ];
            }
        }
        
        error_log("[SignalingServer] 用户 {$userId} 有 " . count($messages) . " 条新消息");
        
        return $messages;
    }
    
    /**
     * 404错误
     */
    private function notFound() {
        http_response_code(404);
        return [
            'success' => false,
            'error' => '接口不存在'
        ];
    }
}

// 创建服务器实例并处理请求
$server = new PHPSignalingServer();
$response = $server->handleRequest();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
