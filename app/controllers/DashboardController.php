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
        header("Location: /CHATTING" . $path);
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
}
?>
