<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Friendship.php';
require_once __DIR__ . '/../models/Chat.php';

class DashboardController {
    private $userModel;
    private $friendshipModel;
    private $chatModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->friendshipModel = new Friendship();
        $this->chatModel = new Chat();
        
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/login');
        }
    }
    
    // 显示聊天主页面
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // 获取用户信息
        $user = $this->userModel->getUserById($userId);
        
        // 获取聊天房间列表
        $rooms = $this->chatModel->getUserRooms($userId);
        
        // 获取好友列表
        $friends = $this->userModel->getFriends($userId);
        
        // 获取待处理的好友请求
        $pendingRequests = $this->userModel->getPendingRequests($userId);
        
        // 获取论坛邀请
        $forumInvites = $this->userModel->getUserForumInvites($userId);
        
        // 获取用户群组列表
        $groups = $this->getUserGroups($userId);
        
        // 获取用户论坛列表
        $forums = $this->userModel->getUserForums($userId);
        
        $this->render('dashboard/dashboard', [
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'forumInvites' => $forumInvites,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'chats'
        ]);
    }
    
    // 检查用户是否已登录
    private function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // 重定向
    private function redirect($path) {
        header("Location: /Chat_System" . $path);
        exit;
    }
    
    // 获取用户群组列表
    private function getUserGroups($userId) {
        $sql = "SELECT cr.id, cr.name, cr.avatar, cr.created_at,
                       COUNT(crm.user_id) as member_count
                FROM chat_rooms cr
                JOIN chat_room_members crm ON cr.id = crm.room_id
                WHERE cr.type = 'group' AND crm.user_id = ?
                GROUP BY cr.id
                ORDER BY cr.name ASC";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, [$userId]);
    }
    
    // 渲染视图
    private function render($view, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $view . '.php';
    }

    // 侧边栏实时数据（聊天列表、在线状态、未读数）
    public function getSidebarData() {
        $userId = $_SESSION['user_id'];
        $activeRoomId = isset($_GET['active_room_id']) ? (int)$_GET['active_room_id'] : 0;
        $user = $this->userModel->getUserById($userId);
        $rooms = $this->chatModel->getUserRooms($userId);
        $friends = $this->userModel->getFriends($userId);

        $roomsData = array_map(function ($room) use ($activeRoomId) {
            $unread = (int)($room['unread_count'] ?? 0);
            if ($activeRoomId > 0 && (int)$room['id'] === $activeRoomId) {
                $unread = 0;
            }
            return [
                'id' => (int)$room['id'],
                'type' => $room['type'],
                'display_name' => $room['display_name'] ?? '',
                'nickname' => $room['nickname'] ?? null,
                'avatar' => $room['avatar'] ?? null,
                'status' => $room['status'] ?? 'offline',
                'last_message' => $room['last_message'] ?? null,
                'last_message_time' => $room['last_message_time'] ?? null,
                'unread_count' => $unread,
                'pinned' => !empty($room['pinned']),
            ];
        }, $rooms);

        $friendsData = array_map(function ($friend) {
            return [
                'id' => (int)$friend['id'],
                'username' => $friend['username'] ?? '',
                'nickname' => $friend['nickname'] ?? null,
                'avatar' => $friend['avatar'] ?? null,
                'status' => $friend['status'] ?? 'offline',
            ];
        }, $friends);

        $this->jsonResponse([
            'success' => true,
            'user' => [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'status' => $user['status'] ?? 'offline',
            ],
            'rooms' => $roomsData,
            'friends' => $friendsData,
        ]);
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
