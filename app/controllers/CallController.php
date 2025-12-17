<?php
/**
 * 通话控制器
 * 处理通话相关的请求和逻辑
 */

class CallController {
    private $userModel;
    private $callConfig;
    
    public function __construct() {
        require_once BASE_PATH . '/app/models/User.php';
        $this->userModel = new User();
        $this->callConfig = require_once BASE_PATH . '/config/call-config.php';
    }
    
    /**
     * 发起通话
     */
    public function initiateCall() {
        try {
            // 验证用户登录
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $this->sendErrorResponse('用户未登录', 401);
                return;
            }
            
            // 获取请求参数
            $targetUserId = $_POST['target_user_id'] ?? '';
            $callType = $_POST['call_type'] ?? 'voice';
            
            if (empty($targetUserId)) {
                $this->sendErrorResponse('目标用户ID不能为空', 400);
                return;
            }
            
            // 验证目标用户是否存在
            $targetUser = $this->userModel->getUserById($targetUserId);
            if (!$targetUser) {
                $this->sendErrorResponse('目标用户不存在', 404);
                return;
            }
            
            // 验证是否为好友关系
            if (!$this->userModel->areFriends($_SESSION['user_id'], $targetUserId)) {
                $this->sendErrorResponse('只能与好友进行通话', 403);
                return;
            }
            
            // 生成房间ID
            $roomId = $this->generateRoomId();
            
            // 创建通话记录
            $callData = [
                'room_id' => $roomId,
                'caller_id' => $_SESSION['user_id'],
                'callee_id' => $targetUserId,
                'call_type' => $callType,
                'status' => 'initiating',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // 这里可以保存到数据库
            // $this->saveCallRecord($callData);
            
            // 返回通话信息
            $this->sendSuccessResponse([
                'room_id' => $roomId,
                'call_type' => $callType,
                'target_user' => [
                    'id' => $targetUser['id'],
                    'username' => $targetUser['username'],
                    'avatar' => $targetUser['avatar'] ?? null
                ],
                'caller' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'] ?? 'Unknown'
                ]
            ]);
            
        } catch (Exception $e) {
            $this->sendErrorResponse('发起通话失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 接听通话
     */
    public function acceptCall() {
        try {
            // 验证用户登录
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $this->sendErrorResponse('用户未登录', 401);
                return;
            }
            
            // 获取请求参数
            $roomId = $_POST['room_id'] ?? '';
            
            if (empty($roomId)) {
                $this->sendErrorResponse('房间ID不能为空', 400);
                return;
            }
            
            // 验证通话是否存在
            // $callRecord = $this->getCallRecord($roomId);
            // if (!$callRecord) {
            //     $this->sendErrorResponse('通话不存在', 404);
            //     return;
            // }
            
            // 更新通话状态
            // $this->updateCallStatus($roomId, 'accepted');
            
            $this->sendSuccessResponse([
                'room_id' => $roomId,
                'status' => 'accepted'
            ]);
            
        } catch (Exception $e) {
            $this->sendErrorResponse('接听通话失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 拒绝通话
     */
    public function rejectCall() {
        try {
            // 验证用户登录
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $this->sendErrorResponse('用户未登录', 401);
                return;
            }
            
            // 获取请求参数
            $roomId = $_POST['room_id'] ?? '';
            
            if (empty($roomId)) {
                $this->sendErrorResponse('房间ID不能为空', 400);
                return;
            }
            
            // 更新通话状态
            // $this->updateCallStatus($roomId, 'rejected');
            
            $this->sendSuccessResponse([
                'room_id' => $roomId,
                'status' => 'rejected'
            ]);
            
        } catch (Exception $e) {
            $this->sendErrorResponse('拒绝通话失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 结束通话
     */
    public function endCall() {
        try {
            // 验证用户登录
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $this->sendErrorResponse('用户未登录', 401);
                return;
            }
            
            // 获取请求参数
            $roomId = $_POST['room_id'] ?? '';
            
            if (empty($roomId)) {
                $this->sendErrorResponse('房间ID不能为空', 400);
                return;
            }
            
            // 更新通话状态
            // $this->updateCallStatus($roomId, 'ended');
            
            $this->sendSuccessResponse([
                'room_id' => $roomId,
                'status' => 'ended'
            ]);
            
        } catch (Exception $e) {
            $this->sendErrorResponse('结束通话失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取通话状态
     */
    public function getCallStatus() {
        try {
            // 验证用户登录
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $this->sendErrorResponse('用户未登录', 401);
                return;
            }
            
            // 获取请求参数
            $roomId = $_GET['room_id'] ?? '';
            
            if (empty($roomId)) {
                $this->sendErrorResponse('房间ID不能为空', 400);
                return;
            }
            
            // 获取通话状态
            // $callRecord = $this->getCallRecord($roomId);
            
            $this->sendSuccessResponse([
                'room_id' => $roomId,
                'status' => 'active', // 这里应该从数据库获取实际状态
                'participants' => [
                    [
                        'id' => $_SESSION['user_id'],
                        'username' => $_SESSION['username'] ?? 'Unknown',
                        'status' => 'online'
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            $this->sendErrorResponse('获取通话状态失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取通话配置
     */
    public function getCallConfig() {
        try {
            $this->sendSuccessResponse($this->callConfig);
            
        } catch (Exception $e) {
            $this->sendErrorResponse('获取通话配置失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 生成房间ID
     */
    private function generateRoomId() {
        return 'call_' . time() . '_' . bin2hex(random_bytes(8));
    }
    
    /**
     * 发送成功响应
     */
    private function sendSuccessResponse($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * 发送错误响应
     */
    private function sendErrorResponse($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code
        ]);
        exit;
    }
}
