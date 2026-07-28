<?php
/**
 * 通话邀请控制器
 * 处理语音/视频通话邀请的发送、接收、接受、拒绝等操作
 */

class CallInvitationController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * 发送群组通话邀请
     */
    public function sendGroupCallInvitation() {
        try {
            // 获取POST数据
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'], $input['type'], $input['roomId'], $input['callerId'])) {
                throw new Exception('缺少必要参数');
            }
            
            $callId = $input['id'];
            $callType = $input['type'];
            $roomId = $input['roomId'];
            $callerId = $input['callerId'];
            $callerName = $input['callerName'] ?? '未知用户';
            
            // 检查是否已有进行中的群组通话邀请
            $stmt = $this->db->prepare("
                SELECT id FROM call_invitations 
                WHERE room_id = ? AND status = 'inviting' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
            ");
            $stmt->execute([$roomId]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception('该群组已有进行中的通话邀请');
            }
            
            // 获取群组成员列表
            $groupMembers = $this->getGroupMembers($roomId);
            if (empty($groupMembers)) {
                throw new Exception('群组成员为空');
            }
            
            // 插入群组通话邀请记录
            $stmt = $this->db->prepare("
                INSERT INTO call_invitations 
                (id, room_id, caller_id, caller_name, call_type, target_user_id, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NULL, 'inviting', NOW())
            ");
            
            $result = $stmt->execute([
                $callId,
                $roomId,
                $callerId,
                $callerName,
                $callType
            ]);
            
            if ($result) {
                // 通知所有群组成员（除了发起者）
                $this->notifyGroupMembers($roomId, $callId, $callType, $callerName, $callerId, $callerId);
                
                echo json_encode([
                    'success' => true,
                    'message' => '群组通话邀请发送成功',
                    'callId' => $callId,
                    'members' => $groupMembers
                ]);
            } else {
                throw new Exception('发送群组通话邀请失败');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 发送通话邀请
     */
    public function sendCallInvitation() {
        try {
            // 获取POST数据
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'], $input['type'], $input['roomId'], $input['callerId'])) {
                throw new Exception('缺少必要参数');
            }
            
            $callId = $input['id'];
            $callType = $input['type'];
            $roomId = $input['roomId'];
            $callerId = $input['callerId'];
            $callerName = $input['callerName'] ?? '未知用户';
            $targetUserId = $input['targetUserId'] ?? null;
            
            // 检查是否已有进行中的通话邀请
            $stmt = $this->db->prepare("
                SELECT id FROM call_invitations 
                WHERE room_id = ? AND status = 'inviting' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
            ");
            $stmt->execute([$roomId]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception('该房间已有进行中的通话邀请');
            }
            
            // 插入通话邀请记录
            $stmt = $this->db->prepare("
                INSERT INTO call_invitations 
                (id, room_id, caller_id, caller_name, call_type, target_user_id, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'inviting', NOW())
            ");
            
            $result = $stmt->execute([
                $callId,
                $roomId,
                $callerId,
                $callerName,
                $callType,
                $targetUserId
            ]);
            
            if ($result) {
                // 通知目标用户
                $this->notifyTargetUser($roomId, $callId, $callType, $callerName, $targetUserId, $callerId);
                
                echo json_encode([
                    'success' => true,
                    'message' => '通话邀请发送成功',
                    'callId' => $callId
                ]);
            } else {
                throw new Exception('发送通话邀请失败');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取通话状态
     */
    public function getCallStatus() {
        try {
            $callId = $_GET['callId'] ?? '';
            
            if (empty($callId)) {
                throw new Exception('缺少通话ID');
            }
            
            $stmt = $this->db->prepare("
                SELECT * FROM call_invitations 
                WHERE id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$callId]);
            $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invitation) {
                throw new Exception('通话邀请不存在');
            }
            
            // 检查是否超时（30秒）
            $createdAt = strtotime($invitation['created_at']);
            if (time() - $createdAt > 30) {
                // 更新状态为超时
                $this->updateCallStatus($callId, 'timeout');
                $invitation['status'] = 'timeout';
            }
            
            echo json_encode([
                'success' => true,
                'status' => $invitation['status'],
                'callData' => [
                    'id' => $invitation['id'],
                    'type' => $invitation['call_type'],
                    'roomId' => $invitation['room_id'],
                    'callerId' => $invitation['caller_id'],
                    'callerName' => $invitation['caller_name'],
                    'participants' => $this->getGroupMembers($invitation['room_id'])
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 接受通话邀请
     */
    public function acceptCallInvitation() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $callId = $input['callId'] ?? '';
            $acceptorId = $_SESSION['user_id'] ?? 0;
            
            if (empty($callId) || !$acceptorId) {
                throw new Exception('缺少必要参数');
            }
            
            // 更新通话状态
            $this->updateCallStatus($callId, 'accepted');
            
            // 通知发起者
            $this->notifyCaller($callId, 'accepted');
            
            echo json_encode([
                'success' => true,
                'message' => '通话邀请已接受'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 拒绝通话邀请
     */
    public function rejectCallInvitation() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $callId = $input['callId'] ?? '';
            $rejectorId = $_SESSION['user_id'] ?? 0;
            
            if (empty($callId) || !$rejectorId) {
                throw new Exception('缺少必要参数');
            }
            
            // 更新通话状态
            $this->updateCallStatus($callId, 'rejected');
            
            // 通知发起者
            $this->notifyCaller($callId, 'rejected');
            
            echo json_encode([
                'success' => true,
                'message' => '通话邀请已拒绝'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 取消通话邀请
     */
    public function cancelCallInvitation() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $callId = $input['callId'] ?? '';
            
            if (empty($callId)) {
                throw new Exception('缺少通话ID');
            }
            
            // 更新通话状态
            $this->updateCallStatus($callId, 'cancelled');
            
            echo json_encode([
                'success' => true,
                'message' => '通话邀请已取消'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 更新通话状态
     */
    private function updateCallStatus($callId, $status) {
        $stmt = $this->db->prepare("
            UPDATE call_invitations 
            SET status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $callId]);
    }
    
    /**
     * 通知目标用户
     */
    private function notifyTargetUser($roomId, $callId, $callType, $callerName, $targetUserId, $callerId = null) {
        try {
            // 通过信令服务器发送通话邀请
            $signalingData = [
                'type' => 'call_invitation',
                'payload' => [
                    'callId' => $callId,
                    'type' => $callType,
                    'fromUserId' => $callerId ?: $callerName, // 使用用户ID或用户名作为标识
                    'participants' => [
                        [
                            'id' => $targetUserId,
                            'username' => 'Target User'
                        ]
                    ]
                ]
            ];
            
            // 发送到信令服务器
            $response = $this->sendToSignalingServer($signalingData);
            
            if ($response && isset($response['success']) && $response['success']) {
                error_log("Call invitation sent to signaling server for user $targetUserId in room $roomId: $callId ($callType) by $callerName");
            } else {
                error_log("Failed to send call invitation to signaling server: " . json_encode($response));
            }
            
        } catch (Exception $e) {
            error_log("Error sending call invitation to signaling server: " . $e->getMessage());
        }
    }
    
    /**
     * 发送数据到信令服务器
     */
    private function sendToSignalingServer($data) {
        try {
            $url = 'http://localhost/Chat_System/signaling-server.php';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return json_decode($response, true);
            } else {
                error_log("Signaling server returned HTTP $httpCode: $response");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error calling signaling server: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 通知群组成员
     */
    private function notifyGroupMembers($roomId, $callId, $callType, $callerName, $excludeUserId = null, $callerId = null) {
        try {
            // 获取群组成员列表
            $members = $this->getGroupMembers($roomId);
            
            // 过滤掉发起者
            if ($excludeUserId) {
                $members = array_filter($members, function($member) use ($excludeUserId) {
                    return $member['id'] != $excludeUserId;
                });
            }
            
            if (empty($members)) {
                error_log("No group members to notify for room $roomId");
                return;
            }
            
            // 通过信令服务器发送群组通话邀请
            $signalingData = [
                'type' => 'call_invitation',
                'payload' => [
                    'callId' => $callId,
                    'type' => $callType,
                    'fromUserId' => $callerId ?: $callerName,
                    'participants' => array_map(function($member) {
                        return [
                            'id' => $member['id'],
                            'username' => $member['username']
                        ];
                    }, $members)
                ]
            ];
            
            // 发送到信令服务器
            $response = $this->sendToSignalingServer($signalingData);
            
            if ($response && isset($response['success']) && $response['success']) {
                error_log("Group call invitation sent to signaling server for room $roomId: $callId ($callType) by $callerName, notified " . count($members) . " members");
                
                // 记录每个成员的通知
                foreach ($members as $member) {
                    error_log("Notified member: {$member['username']} (ID: {$member['id']})");
                }
            } else {
                error_log("Failed to send group call invitation to signaling server: " . json_encode($response));
            }
            
        } catch (Exception $e) {
            error_log("Failed to notify group members: " . $e->getMessage());
        }
    }
    
    /**
     * 通知发起者
     */
    private function notifyCaller($callId, $action) {
        // 这里可以实现WebSocket通知或其他实时通知机制
        error_log("Call invitation $callId was $action");
    }
    
    /**
     * 获取群组成员
     */
    private function getGroupMembers($roomId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.avatar 
            FROM group_members gm 
            JOIN users u ON gm.user_id = u.id 
            WHERE gm.group_id = ?
        ");
        $stmt->execute([$roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
