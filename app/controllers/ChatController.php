
<?php
require_once BASE_PATH . '/config/Database.php';
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Friendship.php';
require_once MODEL_PATH . '/Chat.php';
require_once MODEL_PATH . '/Favorites.php';

class ChatController {
    private $userModel;
    private $friendshipModel;
    private $chatModel;
    private $favoritesModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->friendshipModel = new Friendship();
        $this->chatModel = new Chat();
        $this->favoritesModel = new Favorites();
        
        if (!$this->isLoggedIn()) {
            // 检查是否是 AJAX 请求
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => '会话已过期，请重新登录',
                    'debug' => [
                        'session_id' => session_id(),
                        'session_status' => session_status(),
                        'user_id' => $_SESSION['user_id'] ?? 'not_set',
                        'session_data' => $_SESSION ?? 'empty'
                    ]
                ]);
                exit;
            } else {
                $this->redirect('/auth/login');
            }
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
        
        $this->render('dashboard/index', [
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
    
    // 显示视频通话页面
    public function videoCall() {
        $userId = $_SESSION['user_id'];
        
        // 获取用户信息
        $user = $this->userModel->getUserById($userId);
        
        // 获取好友列表（用于选择通话对象）
        $friends = $this->userModel->getFriends($userId);
        
        $this->render('chat/videoCall', [
            'user' => $user,
            'friends' => $friends
        ]);
    }
    
    // 显示特定聊天房间
    public function room() {
        $userId = $_SESSION['user_id'];
        $roomId = $_GET['id'] ?? null;
        
        if (!$roomId) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 获取房间信息
        $room = $this->chatModel->getRoomInfo($roomId, $userId);
        if (!$room) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 检查房间类型，如果是群组，重定向到群组页面
        if ($room['type'] === 'group') {
            $this->redirect('/chat/group?id=' . $roomId);
            return;
        }
        
        // 获取房间消息
        $messages = $this->chatModel->getRoomMessages($roomId, $userId);
        
        // 标记消息为已读
        $this->chatModel->markRoomMessagesAsRead($roomId, $userId);
        
        // 获取用户信息（用于navbar）
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
        
        $this->render('chat/room', [
            'room' => $room,
            'messages' => $messages,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'forumInvites' => $forumInvites,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'chats',
            'currentRoomId' => $roomId
        ]);
    }
    
    // 发送消息
    public function sendMessage() {
        // 在方法开始就设置错误报告级别，防止警告输出
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 0);
        
        // 设置文件上传限制
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '250M');
        ini_set('max_file_uploads', '20');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        $content = trim($_POST['content'] ?? '');
        $quotedMessageId = $_POST['quoted_message_id'] ?? null;
        
        if (!$roomId || empty($content)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        $result = $this->chatModel->sendMessage($roomId, $userId, $content, $quotedMessageId);
        
        if ($result['success']) {
            // 获取完整的消息数据
            $messageData = $this->chatModel->getMessageById($result['message_id']);
            if ($messageData) {
                $result['message'] = $messageData;
            }
        }
        
        $this->jsonResponse($result);
    }
    
    // 发送文件
    public function sendFile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        $fileType = $_POST['file_type'] ?? 'file';
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '房间ID不能为空']);
            return;
        }
        
        // 检查是否有文件上传
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => '文件上传失败']);
            return;
        }
        
        $file = $_FILES['file'];
        
        // 检查文件大小 (限制为100MB)
        if ($file['size'] > 100 * 1024 * 1024) {
            $this->jsonResponse(['success' => false, 'message' => '文件大小不能超过100MB']);
            return;
        }
        
        // 创建上传目录
        $uploadDir = BASE_PATH . '/public/uploads/files/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 生成唯一文件名
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // 移动上传的文件
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->jsonResponse(['success' => false, 'message' => '文件保存失败']);
            return;
        }
        
        // 生成文件URL
        $fileUrl = '/CHATTING/public/uploads/files/' . $fileName;
        
        // 根据文件类型生成消息内容
        $content = '';
        switch ($fileType) {
            case 'image':
                $content = '[图片] ' . $file['name'];
                break;
            case 'video':
                $content = '[视频] ' . $file['name'];
                break;
            default:
                $content = '[文件] ' . $file['name'];
                break;
        }
        
        // 保存文件信息到数据库
        $result = $this->chatModel->sendFileMessage($roomId, $userId, $content, $fileUrl, $fileType, $file['name'], $file['size']);
        
        if ($result['success']) {
            $result['file_url'] = $fileUrl;
            $result['file_name'] = $file['name'];
            $result['file_size'] = $file['size'];
        }
        
        $this->jsonResponse($result);
    }
    
    // 发送语音消息
    public function sendVoiceMessage() {
        // 在方法开始就设置错误报告级别，防止警告输出
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 0);
        
        // 设置文件上传限制
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '250M');
        ini_set('max_file_uploads', '20');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '房间ID不能为空']);
            return;
        }
        
        // 检查用户是否在房间中
        if (!$this->chatModel->isUserInRoom($userId, $roomId)) {
            $this->jsonResponse(['success' => false, 'message' => '您不在该房间中']);
            return;
        }
        
        // 检查是否有语音文件
        if (!isset($_FILES['voice_file']) || $_FILES['voice_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => '语音文件上传失败']);
            return;
        }
        
        $voiceFile = $_FILES['voice_file'];
        
        // 验证文件类型
        $allowedTypes = ['audio/wav', 'audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/webm', 'audio/webm;codecs=opus'];
        if (!in_array($voiceFile['type'], $allowedTypes)) {
            $this->jsonResponse(['success' => false, 'message' => '不支持的音频格式']);
            return;
        }
        
        // 验证文件大小（限制为10MB）
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($voiceFile['size'] > $maxSize) {
            $this->jsonResponse(['success' => false, 'message' => '语音文件过大，请控制在10MB以内']);
            return;
        }
        
        // 创建上传目录
        $uploadDir = BASE_PATH . '/public/uploads/voices/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 生成唯一文件名
        $fileExtension = pathinfo($voiceFile['name'], PATHINFO_EXTENSION);
        if (empty($fileExtension)) {
            // 根据MIME类型确定扩展名
            switch ($voiceFile['type']) {
                case 'audio/webm':
                case 'audio/webm;codecs=opus':
                    $fileExtension = 'webm';
                    break;
                case 'audio/mpeg':
                case 'audio/mp3':
                    $fileExtension = 'mp3';
                    break;
                case 'audio/ogg':
                    $fileExtension = 'ogg';
                    break;
                default:
                    $fileExtension = 'wav';
                    break;
            }
        }
        $fileName = uniqid('voice_') . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // 移动文件
        if (!move_uploaded_file($voiceFile['tmp_name'], $filePath)) {
            // 如果move_uploaded_file失败，尝试使用copy（用于测试环境）
            if (!copy($voiceFile['tmp_name'], $filePath)) {
                $this->jsonResponse(['success' => false, 'message' => '文件保存失败']);
                return;
            }
        }
        
        // 生成文件URL
        $voiceUrl = 'public/uploads/voices/' . $fileName;
        
        // 保存语音消息到数据库
        $content = '[语音消息]';
        $result = $this->chatModel->sendVoiceMessage($roomId, $userId, $content, $voiceUrl);
        
        if ($result['success']) {
            $result['voice_url'] = '/CHATTING/' . $voiceUrl;
            $result['message_id'] = $result['message_id'];
        }
        
        $this->jsonResponse($result);
    }
    
    // 发送多个文件
    public function sendMultipleFiles() {
        // 在方法开始就设置错误报告级别，防止警告输出
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 0);
        
        // 设置文件上传限制
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '250M');
        ini_set('max_file_uploads', '20');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        // 开始输出缓冲，防止任何意外输出
        ob_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_end_clean();
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        // 调试信息
        $debugInfo = [
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? 'not_set',
            'is_logged_in' => $this->isLoggedIn(),
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
            'files_received' => count($_FILES),
            'post_data' => $_POST
        ];
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        $fileType = $_POST['file_type'] ?? 'file';
        $fileCount = intval($_POST['file_count'] ?? 0);
        
        // 开始输出缓冲，防止任何意外输出
        ob_start();
        
        if (!$roomId || $fileCount === 0) {
            ob_end_clean();
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查文件数量限制
        if ($fileCount > 10) {
            ob_end_clean();
            $this->jsonResponse(['success' => false, 'message' => '最多只能上传10个文件']);
            return;
        }
        
        $fileUrls = [];
        $fileNames = [];
        $totalSize = 0;
        
        // 创建上传目录
        $uploadDir = BASE_PATH . '/public/uploads/files/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 处理文件 - 支持 files[] 格式
        $processedFiles = 0;
        
        if (isset($_FILES['files'])) {
            $files = $_FILES['files'];
            
            // 检查是否是多个文件
            if (is_array($files['name'])) {
                // 多个文件
                for ($i = 0; $i < count($files['name']); $i++) {
                    if (empty($files['name'][$i])) {
                        continue;
                    }
                    
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    
                    // 检查文件大小 (限制为100MB)
                    if ($files['size'][$i] > 100 * 1024 * 1024) {
                        ob_end_clean();
                        $this->jsonResponse(['success' => false, 'message' => '文件大小不能超过100MB']);
                        return;
                    }
                    
                    $totalSize += $files['size'][$i];
                    
                    // 生成唯一文件名
                    $fileExtension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '_' . time() . '_' . $i . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    // 移动上传的文件
                    if (!move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                        ob_end_clean();
                        $this->jsonResponse(['success' => false, 'message' => '文件保存失败']);
                        return;
                    }
                    
                    // 生成文件URL
                    $fileUrl = '/CHATTING/public/uploads/files/' . $fileName;
                    $fileUrls[] = $fileUrl;
                    $fileNames[] = $files['name'][$i];
                    $processedFiles++;
                }
            } else {
                // 单个文件
                if (!empty($files['name']) && $files['error'] === UPLOAD_ERR_OK) {
                    // 检查文件大小 (限制为100MB)
                    if ($files['size'] > 100 * 1024 * 1024) {
                        ob_end_clean();
                        $this->jsonResponse(['success' => false, 'message' => '文件大小不能超过100MB']);
                        return;
                    }
                    
                    $totalSize += $files['size'];
                    
                    // 生成唯一文件名
                    $fileExtension = pathinfo($files['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '_' . time() . '_0.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    // 移动上传的文件
                    if (!move_uploaded_file($files['tmp_name'], $filePath)) {
                        ob_end_clean();
                        $this->jsonResponse(['success' => false, 'message' => '文件保存失败']);
                        return;
                    }
                    
                    // 生成文件URL
                    $fileUrl = '/CHATTING/public/uploads/files/' . $fileName;
                    $fileUrls[] = $fileUrl;
                    $fileNames[] = $files['name'];
                    $processedFiles++;
                }
            }
        }
        
        if (empty($fileUrls)) {
            ob_end_clean();
            $this->jsonResponse([
                'success' => false, 
                'message' => '没有成功上传的文件',
                'debug' => [
                    'fileCount' => $fileCount,
                    'filesReceived' => count($_FILES),
                    'filesKeys' => array_keys($_FILES),
                    'processedFiles' => $processedFiles
                ]
            ]);
            return;
        }
        
        // 根据文件类型和数量生成消息内容
        $fileTypeLabel = '';
        switch ($fileType) {
            case 'image':
                $fileTypeLabel = '[图片]';
                break;
            case 'video':
                $fileTypeLabel = '[视频]';
                break;
            default:
                $fileTypeLabel = '[文件]';
                break;
        }
        
        if (count($fileUrls) === 1) {
            $content = $fileTypeLabel . ' ' . $fileNames[0];
        } else {
            $content = $fileTypeLabel . ' ' . count($fileUrls) . ' 个文件';
        }
        
        // 保存文件信息到数据库
        try {
            $result = $this->chatModel->sendMultipleFileMessage($roomId, $userId, $content, $fileUrls, $fileType, $fileNames, $totalSize);
            
            if ($result['success']) {
                $result['file_urls'] = $fileUrls;
                $result['file_names'] = $fileNames;
                $result['file_count'] = count($fileUrls);
            }
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => '数据库操作失败: ' . $e->getMessage()];
        }
        
        // 清理输出缓冲并返回JSON
        ob_end_clean();
        $this->jsonResponse($result);
    }
    
    // 搜索用户
    public function searchUsers() {
        $keyword = $_GET['q'] ?? '';
        
        if (empty($keyword)) {
            $this->jsonResponse(['users' => []]);
            return;
        }
        
        $users = $this->userModel->searchUsers($keyword, $_SESSION['user_id']);
        $this->jsonResponse(['users' => $users]);
    }
    
    // 发送好友请求
    public function sendFriendRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $friendId = $_POST['friend_id'] ?? null;
        
        if (!$friendId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        $result = $this->friendshipModel->sendFriendRequest($userId, $friendId);
        $this->jsonResponse($result);
    }
    
    // 处理好友请求
    public function handleFriendRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $friendId = $_POST['friend_id'] ?? null;
        $action = $_POST['action'] ?? '';
        
        if (!$friendId || !in_array($action, ['accept', 'reject'])) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        if ($action === 'accept') {
            $result = $this->friendshipModel->acceptFriendRequest($userId, $friendId);
        } else {
            $result = $this->friendshipModel->rejectFriendRequest($userId, $friendId);
        }
        
        $this->jsonResponse($result);
    }
    
    // 开始私聊
    public function startChat() {
        $userId = $_SESSION['user_id'];
        $friendId = $_GET['friend_id'] ?? null;
        
        if (!$friendId) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 检查是否是好友
        if (!$this->friendshipModel->isFriend($userId, $friendId)) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 获取或创建私聊房间
        $roomId = $this->chatModel->getOrCreatePrivateRoom($userId, $friendId);
        
        $this->redirect('/chat/room?id=' . $roomId);
    }
    
    // 获取新消息
    public function getNewMessages() {
        $userId = $_SESSION['user_id'];
        $roomId = $_GET['room_id'] ?? null;
        $lastMessageId = $_GET['last_message_id'] ?? 0;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 获取新消息
        $sql = "SELECT m.*, u.username FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.room_id = ? AND m.id > ?
                ORDER BY m.created_at ASC";
        
        $db = Database::getInstance();
        $messages = $db->fetchAll($sql, [$roomId, $lastMessageId]);
        
        $this->jsonResponse(['success' => true, 'messages' => $messages]);
    }
    
    // 获取消息数据（用于图片预览）
    public function getMessageData() {
        $userId = $_SESSION['user_id'];
        $messageId = $_GET['id'] ?? null;
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        try {
            $sql = "SELECT m.*, u.username FROM messages m
                    JOIN users u ON m.sender_id = u.id
                    WHERE m.id = ? AND EXISTS (
                        SELECT 1 FROM chat_room_members crm 
                        JOIN chat_rooms cr ON crm.room_id = cr.id 
                        WHERE cr.id = m.room_id AND crm.user_id = ?
                    )";
            
            $db = Database::getInstance();
            $message = $db->fetch($sql, [$messageId, $userId]);
            
            if (!$message) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在或无权访问']);
                return;
            }
            
            $this->jsonResponse([
                'success' => true, 
                'message' => $message,
                'fileData' => $message['file_path']
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '获取消息数据失败: ' . $e->getMessage()]);
        }
    }
    
    // 创建群组
    public function createGroup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupName = trim($_POST['name'] ?? '');
        
        if (empty($groupName)) {
            $this->jsonResponse(['success' => false, 'message' => '群组名称不能为空']);
            return;
        }
        
        if (strlen($groupName) > 50) {
            $this->jsonResponse(['success' => false, 'message' => '群组名称不能超过50个字符']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // 开始事务
            $db->beginTransaction();
            
            // 创建群组房间
            $sql = "INSERT INTO chat_rooms (name, type, created_by) VALUES (?, 'group', ?)";
            $roomId = $db->insert($sql, [$groupName, $userId]);
            
            if (!$roomId) {
                throw new Exception('创建群组失败');
            }
            
            // 将创建者添加为群组成员
            $sql = "INSERT INTO chat_room_members (room_id, user_id) VALUES (?, ?)";
            $db->execute($sql, [$roomId, $userId]);
            
            // 提交事务
            $db->commit();
            
            $this->jsonResponse([
                'success' => true, 
                'message' => '群组创建成功！',
                'room_id' => $roomId
            ]);
            
        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '创建群组失败: ' . $e->getMessage()]);
        }
    }
    
    // 添加好友（通过用户名）
    public function addFriend() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $username = trim($_POST['username'] ?? '');
        
        if (empty($username)) {
            $this->jsonResponse(['success' => false, 'message' => '用户名不能为空']);
            return;
        }
        
        // 查找用户
        $user = $this->userModel->getUserByUsername($username);
        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => '用户不存在']);
            return;
        }
        
        $friendId = $user['id'];
        
        // 检查是否是自己
        if ($friendId == $userId) {
            $this->jsonResponse(['success' => false, 'message' => '不能添加自己为好友']);
            return;
        }
        
        // 检查是否已经是好友
        if ($this->friendshipModel->isFriend($userId, $friendId)) {
            $this->jsonResponse(['success' => false, 'message' => '已经是好友了']);
            return;
        }
        
        // 发送好友请求
        $result = $this->friendshipModel->sendFriendRequest($userId, $friendId);
        $this->jsonResponse($result);
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
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, [$userId]);
    }
    
    // 显示群组聊天页面
    public function group() {
        $userId = $_SESSION['user_id'];
        $groupId = $_GET['id'] ?? null;
        
        if (!$groupId) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 获取群组信息
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 检查房间类型，如果是私聊，重定向到私聊页面
        if ($group['type'] === 'private') {
            $this->redirect('/chat/room?id=' . $groupId);
            return;
        }
        
        // 获取群组消息
        $messages = $this->chatModel->getRoomMessages($groupId, $userId);
        
        // 标记消息为已读
        $this->chatModel->markRoomMessagesAsRead($groupId, $userId);
        
        // 获取群组成员列表
        $members = $this->chatModel->getGroupMembers($groupId);
        
        // 获取用户信息（用于navbar）
        $user = $this->userModel->getUserById($userId);
        
        // 获取聊天房间列表
        $rooms = $this->chatModel->getUserRooms($userId);
        
        // 获取好友列表
        $friends = $this->userModel->getFriends($userId);
        
        // 获取待处理的好友请求
        $pendingRequests = $this->userModel->getPendingRequests($userId);
        
        // 获取用户群组列表
        $groups = $this->getUserGroups($userId);
        
        // 获取用户论坛列表
        $forums = $this->userModel->getUserForums($userId);
        
        $this->render('chat/group', [
            'group' => $group,
            'messages' => $messages,
            'members' => $members,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'chats',
            'currentGroupId' => $groupId
        ]);
    }
    
    // 显示群组设置页面
    public function groupSettings() {
        $userId = $_SESSION['user_id'];
        $groupId = $_GET['id'] ?? null;
        
        if (!$groupId) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 获取群组信息
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group) {
            $this->redirect('/dashboard');
            return;
        }
        
        // 检查是否是群组成员
        $sql = "SELECT id FROM chat_room_members WHERE room_id = ? AND user_id = ?";
        $db = Database::getInstance();
        $isMember = $db->fetch($sql, [$groupId, $userId]);
        
        if (!$isMember) {
            $this->redirect('/chat/group?id=' . $groupId);
            return;
        }
        
        // 确保 created_by 字段存在
        if (!isset($group['created_by'])) {
            // 如果 created_by 字段不存在，从数据库重新获取
            $sql = "SELECT created_by FROM chat_rooms WHERE id = ?";
            $createdBy = $db->fetch($sql, [$groupId]);
            if ($createdBy) {
                $group['created_by'] = $createdBy['created_by'];
            }
        }
        
        // 获取群组成员列表
        $members = $this->chatModel->getGroupMembers($groupId);
        
        // 获取用户信息（用于navbar）
        $user = $this->userModel->getUserById($userId);
        
        // 获取聊天房间列表
        $rooms = $this->chatModel->getUserRooms($userId);
        
        // 获取好友列表
        $friends = $this->userModel->getFriends($userId);
        
        // 获取待处理的好友请求
        $pendingRequests = $this->userModel->getPendingRequests($userId);
        
        // 获取用户群组列表
        $groups = $this->getUserGroups($userId);
        
        // 获取用户论坛列表
        $forums = $this->userModel->getUserForums($userId);
        
        $this->render('chat/groupSettings', [
            'group' => $group,
            'members' => $members,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'groups',
            'currentGroupId' => $groupId
        ]);
    }
    
    // 更新群组名称
    public function updateGroupName() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        $newName = trim($_POST['name'] ?? '');
        
        if (!$groupId || empty($newName)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        if (strlen($newName) > 50) {
            $this->jsonResponse(['success' => false, 'message' => '群组名称不能超过50个字符']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group || !isset($group['created_by']) || $group['created_by'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $sql = "UPDATE chat_rooms SET name = ? WHERE id = ? AND created_by = ?";
            $db = Database::getInstance();
            $result = $db->execute($sql, [$newName, $groupId, $userId]);
            
            if ($result > 0) {
                $this->jsonResponse(['success' => true, 'message' => '群组名称更新成功']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '更新失败']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
        }
    }
    
    // 更新群组头像
    public function updateGroupAvatar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        
        if (!$groupId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group || !isset($group['created_by']) || $group['created_by'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        // 处理文件上传
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => '文件上传失败']);
            return;
        }
        
        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $this->jsonResponse(['success' => false, 'message' => '只支持 JPG、PNG、GIF 格式']);
            return;
        }
        
        if ($file['size'] > $maxSize) {
            $this->jsonResponse(['success' => false, 'message' => '文件大小不能超过 2MB']);
            return;
        }
        
        // 生成文件名
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'group_avatar_' . $groupId . '_' . time() . '.' . $extension;
        $uploadPath = BASE_PATH . '/public/uploads/avatars/' . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // 更新数据库
            $sql = "UPDATE chat_rooms SET avatar = ? WHERE id = ?";
            $db = Database::getInstance();
            $db->execute($sql, [$fileName, $groupId]);
            
            $this->jsonResponse(['success' => true, 'message' => '头像更新成功']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => '文件保存失败']);
        }
    }
    
    // 添加群组成员
    public function addGroupMember() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        $username = trim($_POST['username'] ?? '');
        
        if (!$groupId || empty($username)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主或管理员
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group) {
            $this->jsonResponse(['success' => false, 'message' => '群组不存在或您不是群组成员']);
            return;
        }
        
        // 检查用户权限（群主或管理员）
        $isOwner = isset($group['created_by']) && $group['created_by'] == $userId;
        $isAdmin = false;
        
        if (!$isOwner) {
            // 检查是否是管理员
            $sql = "SELECT role FROM chat_room_members WHERE room_id = ? AND user_id = ?";
            $db = Database::getInstance();
            $memberInfo = $db->fetch($sql, [$groupId, $userId]);
            $isAdmin = $memberInfo && $memberInfo['role'] === 'admin';
        }
        
        if (!$isOwner && !$isAdmin) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足，只有群主和管理员可以邀请成员']);
            return;
        }
        
        // 查找用户
        $user = $this->userModel->getUserByUsername($username);
        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => '用户不存在']);
            return;
        }
        
        $memberId = $user['id'];
        
        // 检查是否已经是成员
        $sql = "SELECT id FROM chat_room_members WHERE room_id = ? AND user_id = ?";
        $db = Database::getInstance();
        $existing = $db->fetch($sql, [$groupId, $memberId]);
        
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => '用户已经是群组成员']);
            return;
        }
        
        try {
            $sql = "INSERT INTO chat_room_members (room_id, user_id) VALUES (?, ?)";
            $db->execute($sql, [$groupId, $memberId]);
            
            $this->jsonResponse(['success' => true, 'message' => '成员添加成功']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '添加失败: ' . $e->getMessage()]);
        }
    }
    
    // 移除群组成员
    public function removeGroupMember() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        $memberId = $_POST['member_id'] ?? null;
        
        if (!$groupId || !$memberId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group || !isset($group['created_by']) || $group['created_by'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        // 不能踢出自己
        if ($memberId == $userId) {
            $this->jsonResponse(['success' => false, 'message' => '不能踢出自己']);
            return;
        }
        
        try {
            $sql = "DELETE FROM chat_room_members WHERE room_id = ? AND user_id = ?";
            $db = Database::getInstance();
            $result = $db->execute($sql, [$groupId, $memberId]);
            
            if ($result > 0) {
                $this->jsonResponse(['success' => true, 'message' => '成员已移除']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '移除失败']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '移除失败: ' . $e->getMessage()]);
        }
    }
    
    // 退出群组
    public function leaveGroup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        
        if (!$groupId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group) {
            $this->jsonResponse(['success' => false, 'message' => '群组不存在']);
            return;
        }
        
        // 群主不能退出，只能解散
        if (isset($group['created_by']) && $group['created_by'] == $userId) {
            $this->jsonResponse(['success' => false, 'message' => '群主不能退出群组，请解散群组']);
            return;
        }
        
        try {
            $sql = "DELETE FROM chat_room_members WHERE room_id = ? AND user_id = ?";
            $db = Database::getInstance();
            $result = $db->execute($sql, [$groupId, $userId]);
            
            if ($result > 0) {
                $this->jsonResponse(['success' => true, 'message' => '已退出群组']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '退出失败']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '退出失败: ' . $e->getMessage()]);
        }
    }
    
    // 解散群组
    public function deleteGroup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        
        if (!$groupId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group || !isset($group['created_by']) || $group['created_by'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $db->beginTransaction();
            
            // 删除消息
            $sql = "DELETE FROM messages WHERE room_id = ?";
            $db->execute($sql, [$groupId]);
            
            // 删除成员
            $sql = "DELETE FROM chat_room_members WHERE room_id = ?";
            $db->execute($sql, [$groupId]);
            
            // 删除群组
            $sql = "DELETE FROM chat_rooms WHERE id = ? AND created_by = ?";
            $result = $db->execute($sql, [$groupId, $userId]);
            
            $db->commit();
            
            if ($result > 0) {
                $this->jsonResponse(['success' => true, 'message' => '群组已解散']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '解散失败']);
            }
        } catch (Exception $e) {
            $db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '解散失败: ' . $e->getMessage()]);
        }
    }
    
    // 提升成员为管理员
    public function promoteToAdmin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        $memberId = $_POST['member_id'] ?? null;
        
        if (!$groupId || !$memberId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group || !isset($group['created_by']) || $group['created_by'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        // 不能提升自己
        if ($memberId == $userId) {
            $this->jsonResponse(['success' => false, 'message' => '不能提升自己']);
            return;
        }
        
        try {
            $sql = "UPDATE chat_room_members SET role = 'admin' WHERE room_id = ? AND user_id = ?";
            $db = Database::getInstance();
            $result = $db->execute($sql, [$groupId, $memberId]);
            
            if ($result > 0) {
                $this->jsonResponse(['success' => true, 'message' => '已设为管理员']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '设置失败']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '设置失败: ' . $e->getMessage()]);
        }
    }
    
    // 取消管理员权限
    public function demoteToMember() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = $_POST['group_id'] ?? null;
        $memberId = $_POST['member_id'] ?? null;
        
        if (!$groupId || !$memberId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查是否是群主
        $group = $this->chatModel->getGroupInfo($groupId, $userId);
        if (!$group || !isset($group['created_by']) || $group['created_by'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        // 不能取消自己的权限
        if ($memberId == $userId) {
            $this->jsonResponse(['success' => false, 'message' => '不能取消自己的权限']);
            return;
        }
        
        try {
            $sql = "UPDATE chat_room_members SET role = 'member' WHERE room_id = ? AND user_id = ?";
            $db = Database::getInstance();
            $result = $db->execute($sql, [$groupId, $memberId]);
            
            if ($result > 0) {
                $this->jsonResponse(['success' => true, 'message' => '已取消管理员权限']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '取消失败']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '取消失败: ' . $e->getMessage()]);
        }
    }
    
    // 置顶聊天
    public function pinRoom() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否有权限操作这个房间
        $sql = "SELECT id FROM chat_room_members WHERE room_id = ? AND user_id = ?";
        $db = Database::getInstance();
        $isMember = $db->fetch($sql, [$roomId, $userId]);
        
        if (!$isMember) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            // 检查是否已经置顶
            $sql = "SELECT pinned FROM chat_room_members WHERE room_id = ? AND user_id = ?";
            $currentStatus = $db->fetch($sql, [$roomId, $userId]);
            
            $newStatus = $currentStatus['pinned'] ? 0 : 1;
            
            // 更新置顶状态
            $sql = "UPDATE chat_room_members SET pinned = ? WHERE room_id = ? AND user_id = ?";
            $result = $db->execute($sql, [$newStatus, $roomId, $userId]);
            
            if ($result > 0) {
                $message = $newStatus ? '已置顶聊天' : '已取消置顶';
                $this->jsonResponse([
                    'success' => true, 
                    'message' => $message,
                    'pinned' => $newStatus
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '操作失败']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
        }
    }
    
    // 删除聊天（不删除好友）
    public function deleteRoom() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        $roomType = $_POST['room_type'] ?? 'private';
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            if ($roomType === 'group') {
                // 群组：检查是否是群主
                $sql = "SELECT created_by FROM chat_rooms WHERE id = ? AND type = 'group'";
                $group = $db->fetch($sql, [$roomId]);
                
                if (!$group) {
                    $this->jsonResponse(['success' => false, 'message' => '群组不存在']);
                    return;
                }
                
                if ($group['created_by'] != $userId) {
                    $this->jsonResponse(['success' => false, 'message' => '只有群主可以删除群组']);
                    return;
                }
                
                // 删除群组（包括所有消息和成员）
                $db->beginTransaction();
                
                $sql = "DELETE FROM messages WHERE room_id = ?";
                $db->execute($sql, [$roomId]);
                
                $sql = "DELETE FROM chat_room_members WHERE room_id = ?";
                $db->execute($sql, [$roomId]);
                
                $sql = "DELETE FROM chat_rooms WHERE id = ?";
                $result = $db->execute($sql, [$roomId]);
                
                $db->commit();
                
                if ($result > 0) {
                    $this->jsonResponse(['success' => true, 'message' => '群组已删除']);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => '删除失败']);
                }
            } else {
                // 私聊：只删除用户与这个房间的关联
                $sql = "DELETE FROM chat_room_members WHERE room_id = ? AND user_id = ?";
                $result = $db->execute($sql, [$roomId, $userId]);
                
                if ($result > 0) {
                    // 检查是否还有其他成员
                    $sql = "SELECT COUNT(*) as count FROM chat_room_members WHERE room_id = ?";
                    $remainingMembers = $db->fetch($sql, [$roomId]);
                    
                    if ($remainingMembers['count'] == 0) {
                        // 如果没有其他成员，删除房间和消息
                        $db->beginTransaction();
                        
                        $sql = "DELETE FROM messages WHERE room_id = ?";
                        $db->execute($sql, [$roomId]);
                        
                        $sql = "DELETE FROM chat_rooms WHERE id = ?";
                        $db->execute($sql, [$roomId]);
                        
                        $db->commit();
                    }
                    
                    $this->jsonResponse(['success' => true, 'message' => '聊天已删除']);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => '删除失败']);
                }
            }
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            $this->jsonResponse(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
        }
    }
    
    // 获取房间信息
    public function getRoomInfo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否有权限访问这个房间
        $sql = "SELECT id FROM chat_room_members WHERE room_id = ? AND user_id = ?";
        $db = Database::getInstance();
        $isMember = $db->fetch($sql, [$roomId, $userId]);
        
        if (!$isMember) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            // 获取房间基本信息
            $sql = "SELECT cr.*, 
                           CASE 
                               WHEN cr.type = 'group' THEN cr.name
                               ELSE u.username
                           END as display_name,
                           u.username as other_user_name,
                           u.avatar as other_user_avatar,
                           u.status as other_user_status
                    FROM chat_rooms cr
                    LEFT JOIN chat_room_members crm ON cr.id = crm.room_id AND crm.user_id != ?
                    LEFT JOIN users u ON crm.user_id = u.id
                    WHERE cr.id = ?";
            
            $roomInfo = $db->fetch($sql, [$userId, $roomId]);
            
            if (!$roomInfo) {
                $this->jsonResponse(['success' => false, 'message' => '房间不存在']);
                return;
            }
            
            // 获取最后一条消息
            $sql = "SELECT content FROM messages WHERE room_id = ? ORDER BY created_at DESC LIMIT 1";
            $lastMessage = $db->fetch($sql, [$roomId]);
            
            $roomInfo['last_message'] = $lastMessage ? $lastMessage['content'] : null;
            
            $this->jsonResponse([
                'success' => true, 
                'roomInfo' => $roomInfo
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '获取信息失败: ' . $e->getMessage()]);
        }
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
    
    // 渲染视图
    private function render($view, $data = []) {
        extract($data);
        include VIEW_PATH . '/' . $view . '.php';
    }
    
    // 检查是否是 AJAX 请求
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    
    // 获取分享联系人列表
    public function getShareContacts() {
        try {
            $userId = $_SESSION['user_id'];
            $db = Database::getInstance();
            
            // 获取用户的所有聊天房间，按最近消息时间排序
            $sql = "SELECT cr.id, cr.name, cr.type, cr.created_at,
                           CASE 
                               WHEN cr.type = 'private' THEN 
                                   CASE 
                                       WHEN crm1.user_id = ? THEN crm2.user_id
                                       ELSE crm1.user_id
                                   END
                               ELSE cr.id
                           END as contact_id,
                           CASE 
                               WHEN cr.type = 'private' THEN 
                                   CASE 
                                       WHEN crm1.user_id = ? THEN u2.username
                                       ELSE u1.username
                                   END
                               ELSE cr.name
                           END as contact_name,
                           CASE 
                               WHEN cr.type = 'private' THEN 
                                   CASE 
                                       WHEN crm1.user_id = ? THEN u2.avatar
                                       ELSE u1.avatar
                                   END
                               ELSE NULL
                           END as contact_avatar,
                           m.content as last_message,
                           m.created_at as last_message_time
                    FROM chat_rooms cr
                    JOIN chat_room_members crm1 ON cr.id = crm1.room_id AND crm1.user_id = ?
                    LEFT JOIN chat_room_members crm2 ON cr.id = crm2.room_id AND crm2.user_id != ?
                    LEFT JOIN users u1 ON crm1.user_id = u1.id
                    LEFT JOIN users u2 ON crm2.user_id = u2.id
                    LEFT JOIN (
                        SELECT room_id, content, created_at,
                               ROW_NUMBER() OVER (PARTITION BY room_id ORDER BY created_at DESC) as rn
                        FROM messages
                    ) m ON cr.id = m.room_id AND m.rn = 1
                    ORDER BY COALESCE(m.created_at, cr.created_at) DESC";
            
            $contacts = $db->fetchAll($sql, [$userId, $userId, $userId, $userId, $userId]);
            
            // 格式化联系人数据
            $formattedContacts = array_map(function($contact) {
                return [
                    'id' => $contact['contact_id'],
                    'name' => $contact['contact_name'],
                    'avatar' => $contact['contact_avatar'],
                    'last_message' => $contact['last_message'],
                    'type' => $contact['type']
                ];
            }, $contacts);
            
            $this->jsonResponse(['success' => true, 'contacts' => $formattedContacts]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '获取联系人失败: ' . $e->getMessage()]);
        }
    }
    
    // 分享消息
    public function shareMessage() {
        $messageId = $_POST['message_id'] ?? null;
        $contactIds = $_POST['contact_ids'] ?? '';
        
        if (!$messageId || empty($contactIds)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $db = Database::getInstance();
            
            // 获取要分享的消息
            $sql = "SELECT content, message_type, file_path FROM messages WHERE id = ?";
            $message = $db->fetch($sql, [$messageId]);
            
            if (!$message) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在']);
            }
            
            $contactIdArray = explode(',', $contactIds);
            $sharedCount = 0;
            
            foreach ($contactIdArray as $contactId) {
                // 检查是否为私聊或群聊
                $sql = "SELECT cr.id, cr.type 
                        FROM chat_rooms cr
                        JOIN chat_room_members crm ON cr.id = crm.room_id
                        WHERE crm.user_id = ? AND (
                            (cr.type = 'private' AND EXISTS (
                                SELECT 1 FROM chat_room_members crm2 
                                WHERE crm2.room_id = cr.id AND crm2.user_id = ?
                            ))
                            OR (cr.type = 'group' AND cr.id = ?)
                        )";
                
                $room = $db->fetch($sql, [$userId, $contactId, $contactId]);
                
                if ($room) {
                    // 创建分享消息
                    $shareContent = "[分享消息]\n" . $message['content'];
                    $sql = "INSERT INTO messages (room_id, sender_id, content, message_type, file_path) VALUES (?, ?, ?, ?, ?)";
                    $db->execute($sql, [$room['id'], $userId, $shareContent, $message['message_type'], $message['file_path']]);
                    $sharedCount++;
                }
            }
            
            if ($sharedCount > 0) {
                $this->jsonResponse(['success' => true, 'message' => "消息已分享到 {$sharedCount} 个聊天"]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '没有可分享的聊天']);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '分享失败: ' . $e->getMessage()]);
        }
    }
    
    // 置顶/取消置顶消息
    public function pinMessage() {
        $messageId = $_POST['message_id'] ?? null;
        $roomId = $_POST['room_id'] ?? null;
        $action = $_POST['action'] ?? 'pin'; // pin 或 unpin
        
        if (!$messageId || !$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $db = Database::getInstance();
            
            // 检查用户是否在聊天室中
            $sql = "SELECT 1 FROM chat_room_members WHERE room_id = ? AND user_id = ?";
            $isMember = $db->fetch($sql, [$roomId, $userId]);
            
            if (!$isMember) {
                $this->jsonResponse(['success' => false, 'message' => '无权限操作此聊天室']);
                return;
            }
            
            // 检查消息是否存在且属于该聊天室
            $sql = "SELECT id, is_pinned FROM messages WHERE id = ? AND room_id = ?";
            $message = $db->fetch($sql, [$messageId, $roomId]);
            
            if (!$message) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在']);
                return;
            }
            
            if ($action === 'unpin') {
                // 取消置顶
                $sql = "UPDATE messages SET is_pinned = 0 WHERE id = ?";
                $result = $db->execute($sql, [$messageId]);
                
                if ($result) {
                    $this->jsonResponse(['success' => true, 'message' => '已取消置顶']);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => '取消置顶失败']);
                }
            } else {
                // 置顶消息
                // 先取消该聊天室中所有其他消息的置顶状态
                $sql = "UPDATE messages SET is_pinned = 0 WHERE room_id = ?";
                $db->execute($sql, [$roomId]);
                
                // 置顶当前消息
                $sql = "UPDATE messages SET is_pinned = 1 WHERE id = ?";
                $result = $db->execute($sql, [$messageId]);
                
                if ($result) {
                    $this->jsonResponse(['success' => true, 'message' => '消息已置顶']);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => '置顶失败']);
                }
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
        }
    }
    
    // 显示房间详细信息页面
    public function roomDetails() {
        $roomId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$roomId) {
            $this->redirect('/dashboard');
            return;
        }
        
        try {
            // 获取房间信息
            $roomInfo = $this->chatModel->getRoomInfo($roomId, $userId);
            
            if (!$roomInfo) {
                // 调试信息：记录为什么重定向
                error_log("roomDetails: getRoomInfo returned null for roomId=$roomId, userId=$userId");
                $this->redirect('/dashboard');
                return;
            }
            
            // 获取用户信息
            $user = $this->userModel->getUserById($userId);
            
            // 获取房间成员信息（如果是群组）
            $members = [];
            if ($roomInfo['type'] === 'group') {
                $members = $this->chatModel->getRoomMembers($roomId);
            }
            
            // 如果是私聊，获取好友备注信息
            if ($roomInfo['type'] === 'private') {
                $friendId = $this->chatModel->getFriendIdFromRoom($roomId, $userId);
                if ($friendId) {
                    $roomInfo['nickname'] = $this->friendshipModel->getFriendNickname($userId, $friendId);
                }
            }
            
            // 获取最近的消息（用于显示最后活动时间等）
            $recentMessages = $this->chatModel->getRoomMessages($roomId, $userId, 10);
            
            $this->render('chat/roomDetails', [
                'user' => $user,
                'roomInfo' => $roomInfo,
                'members' => $members,
                'recentMessages' => $recentMessages
            ]);
            
        } catch (Exception $e) {
            error_log('Error in roomDetails: ' . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }
    
    // 显示封锁列表页面
    public function blockedList() {
        $userId = $_SESSION['user_id'];
        
        try {
            // 获取用户信息
            $user = $this->userModel->getUserById($userId);
            
            // 获取封锁列表
            $blockedUsers = $this->getBlockedUsers($userId);
            
            // 获取聊天房间列表（用于navbar）
            $rooms = $this->chatModel->getUserRooms($userId);
            
            // 获取好友列表
            $friends = $this->userModel->getFriends($userId);
            
            // 获取待处理的好友请求
            $pendingRequests = $this->userModel->getPendingRequests($userId);
            
            // 获取用户群组列表
            $groups = $this->getUserGroups($userId);
            
            $this->render('chat/blockedList', [
                'user' => $user,
                'blockedUsers' => $blockedUsers,
                'rooms' => $rooms,
                'friends' => $friends,
                'pendingRequests' => $pendingRequests,
                'groups' => $groups
            ]);
        } catch (Exception $e) {
            error_log('Error in blockedList: ' . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }
    
    // 封锁好友
    public function blockFriend() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
        }
        
        try {
            // 获取房间信息
            $room = $this->chatModel->getRoomInfo($roomId, $userId);
            if (!$room || $room['type'] !== 'private') {
                $this->jsonResponse(['success' => false, 'message' => '房间不存在或不是私聊']);
            }
            
            // 获取对方用户ID
            $sql = "SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?";
            $db = Database::getInstance();
            $otherUser = $db->fetch($sql, [$roomId, $userId]);
            
            if (!$otherUser) {
                $this->jsonResponse(['success' => false, 'message' => '无法找到对方用户']);
            }
            
            // 添加封锁记录
            $sql = "INSERT INTO blocked_users (blocker_id, blocked_id, room_id, created_at) VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE created_at = NOW()";
            $db->execute($sql, [$userId, $otherUser['user_id'], $roomId]);
            
            $this->jsonResponse(['success' => true, 'message' => '好友已封锁']);
        } catch (Exception $e) {
            error_log('Error in blockFriend: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '封锁失败']);
        }
    }
    
    // 解除封锁
    public function unblockFriend() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $userId = $_SESSION['user_id'];
        $blockedId = $_POST['blocked_id'] ?? null;
        
        if (!$blockedId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
        }
        
        try {
            $sql = "DELETE FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?";
            $db = Database::getInstance();
            $db->execute($sql, [$userId, $blockedId]);
            
            $this->jsonResponse(['success' => true, 'message' => '已解除封锁']);
        } catch (Exception $e) {
            error_log('Error in unblockFriend: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '解除封锁失败']);
        }
    }
    
    // 删除好友
    public function deleteFriend() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
        }
        
        try {
            // 获取房间信息
            $room = $this->chatModel->getRoomInfo($roomId, $userId);
            if (!$room || $room['type'] !== 'private') {
                $this->jsonResponse(['success' => false, 'message' => '房间不存在或不是私聊']);
            }
            
            // 获取对方用户ID
            $sql = "SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?";
            $db = Database::getInstance();
            $otherUser = $db->fetch($sql, [$roomId, $userId]);
            
            if (!$otherUser) {
                $this->jsonResponse(['success' => false, 'message' => '无法找到对方用户']);
            }
            
            // 删除好友关系
            $sql = "DELETE FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
            $db->execute($sql, [$userId, $otherUser['user_id'], $otherUser['user_id'], $userId]);
            
            // 删除房间
            $sql = "DELETE FROM chat_rooms WHERE id = ?";
            $db->execute($sql, [$roomId]);
            
            $this->jsonResponse(['success' => true, 'message' => '好友已删除']);
        } catch (Exception $e) {
            error_log('Error in deleteFriend: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '删除失败']);
        }
    }
    
    // 获取封锁用户列表
    private function getBlockedUsers($userId) {
        $sql = "SELECT bu.blocked_id, bu.room_id, bu.created_at as blocked_at,
                       u.username, u.avatar, u.status
                FROM blocked_users bu
                JOIN users u ON bu.blocked_id = u.id
                WHERE bu.blocker_id = ?
                ORDER BY bu.created_at DESC";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, [$userId]);
    }
    
    // JSON响应
    // 收藏页面
    public function favorites() {
        $userId = $_SESSION['user_id'];
        try {
            $user = $this->userModel->getUserById($userId);
            $rooms = $this->chatModel->getUserRooms($userId);
            $friends = $this->userModel->getFriends($userId);
            $pendingRequests = $this->userModel->getPendingRequests($userId);
            $groups = $this->getUserGroups($userId);
            
            // 获取收藏数据
            $type = $_GET['type'] ?? null;
            $favorites = $this->favoritesModel->getUserFavorites($userId, $type);
            $stats = $this->favoritesModel->getFavoritesStats($userId);
            
            $this->render('favorites', [
                'user' => $user, 'rooms' => $rooms, 'friends' => $friends,
                'pendingRequests' => $pendingRequests, 'groups' => $groups,
                'favorites' => $favorites, 'stats' => $stats
            ]);
        } catch (Exception $e) {
            error_log('Error in favorites: ' . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }
    
    // 获取收藏详细数据
    public function getFavoriteData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $favoriteId = $_GET['id'] ?? null;
        if (!$favoriteId) {
            $this->jsonResponse(['success' => false, 'message' => '缺少收藏ID']);
        }
        
        $userId = $_SESSION['user_id'];
        $favorite = $this->favoritesModel->getFavoriteById($favoriteId, $userId);
        
        if (!$favorite) {
            $this->jsonResponse(['success' => false, 'message' => '收藏不存在']);
        }
        
        $this->jsonResponse([
            'success' => true,
            'metadata' => $favorite['metadata'],
            'file_path' => $favorite['file_path'],
            'title' => $favorite['title']
        ]);
    }
    
    // 获取群组成员
    public function getGroupMembers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $roomId = $_GET['room_id'] ?? null;
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '缺少房间ID']);
        }
        
        $userId = $_SESSION['user_id'];
        
        // 检查用户是否在群组中
        $isMember = $this->chatModel->isUserInRoom($userId, $roomId);
        if (!$isMember) {
            $this->jsonResponse(['success' => false, 'message' => '您不是群组成员']);
        }
        
        // 获取群组成员
        $members = $this->chatModel->getRoomMembers($roomId);
        
        $this->jsonResponse([
            'success' => true,
            'members' => $members
        ]);
    }
    
    // 添加收藏
    public function addFavorite() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $userId = $_SESSION['user_id'];
        $type = $_POST['type'] ?? null;
        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;
        $filePath = $_POST['file_path'] ?? null;
        $fileSize = $_POST['file_size'] ?? null;
        $thumbnail = $_POST['thumbnail'] ?? null;
        $tags = $_POST['tags'] ?? null;
        
        if (!$type || !$title) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
        }
        
        $metadata = null;
        if (isset($_POST['metadata'])) {
            $metadata = json_decode($_POST['metadata'], true);
        }
        
        $result = $this->favoritesModel->addFavorite(
            $userId, $type, $title, $content, $filePath, $fileSize, $thumbnail, $metadata, $tags
        );
        
        $this->jsonResponse($result);
    }
    
    // 删除收藏
    public function deleteFavorite() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
        
        $userId = $_SESSION['user_id'];
        $favoriteId = $_POST['favorite_id'] ?? null;
        
        if (!$favoriteId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
        }
        
        $result = $this->favoritesModel->deleteFavorite($favoriteId, $userId);
        $this->jsonResponse($result);
    }
    
    // 获取收藏列表
    public function getFavorites() {
        $userId = $_SESSION['user_id'];
        $type = $_GET['type'] ?? null;
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $favorites = $this->favoritesModel->getUserFavorites($userId, $type, $limit, $offset);
        $stats = $this->favoritesModel->getFavoritesStats($userId);
        
        $this->jsonResponse([
            'success' => true,
            'favorites' => $favorites,
            'stats' => $stats
        ]);
    }
    
    // 搜索收藏
    public function searchFavorites() {
        $userId = $_SESSION['user_id'];
        $keyword = $_GET['keyword'] ?? '';
        $type = $_GET['type'] ?? null;
        
        if (empty($keyword)) {
            $this->jsonResponse(['success' => false, 'message' => '搜索关键词不能为空']);
        }
        
        $favorites = $this->favoritesModel->searchFavorites($userId, $keyword, $type);
        
        $this->jsonResponse([
            'success' => true,
            'favorites' => $favorites
        ]);
    }
    
    // 收藏消息
    public function favoriteMessage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $messageId = $_POST['message_id'] ?? null;
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // 获取消息信息
            $sql = "SELECT m.*, u.username, cr.name as room_name, cr.type as room_type
                    FROM messages m
                    JOIN users u ON m.sender_id = u.id
                    JOIN chat_rooms cr ON m.room_id = cr.id
                    WHERE m.id = ? AND EXISTS (
                        SELECT 1 FROM chat_room_members crm 
                        WHERE crm.room_id = m.room_id AND crm.user_id = ?
                    )";
            
            $message = $db->fetch($sql, [$messageId, $userId]);
            
            if (!$message) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在或无权访问']);
                return;
            }
            
            // 检查是否已经收藏过（通过message_id检查）
            $sql = "SELECT id FROM favorites WHERE user_id = ? AND JSON_EXTRACT(metadata, '$.message_id') = ?";
            $existing = $db->fetch($sql, [$userId, $messageId]);
            
            if ($existing) {
                // 如果已收藏，则取消收藏
                $result = $this->favoritesModel->deleteFavorite($existing['id'], $userId);
                if ($result['success']) {
                    $this->jsonResponse(['success' => true, 'message' => '已取消收藏', 'is_favorited' => false]);
                } else {
                    $this->jsonResponse($result);
                }
                return;
            }
            
            // 确定收藏类型
            $type = 'text';
            $title = '';
            $content = $message['content'];
            $filePath = null;
            $fileSize = null;
            $thumbnail = null;
            $metadata = null;
            
            if (!empty($message['file_path'])) {
                $fileData = json_decode($message['file_path'], true);
                if ($fileData && isset($fileData['urls'])) {
                    // 保存第一个文件的URL作为主要文件路径
                    $filePath = $fileData['urls'][0] ?? '';
                    $fileSize = $fileData['total_size'] ?? 0;
                    
                    // 根据文件类型设置收藏类型
                    $firstFile = $fileData['urls'][0] ?? '';
                    $extension = strtolower(pathinfo($firstFile, PATHINFO_EXTENSION));
                    
                    // 确定主要类型（基于第一个文件）
                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $isVideo = in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv']);
                    
                    if ($isImage) {
                        $type = 'image';
                        $title = '[图片] ' . ($fileData['names'][0] ?? '图片');
                        $thumbnail = $firstFile;
                    } elseif ($isVideo) {
                        $type = 'video';
                        $title = '[视频] ' . ($fileData['names'][0] ?? '视频');
                    } else {
                        $type = 'file';
                        $title = '[文件] ' . ($fileData['names'][0] ?? '文件');
                    }
                    
                    // 如果有多个文件，更新标题显示文件数量
                    if (count($fileData['urls']) > 1) {
                        $title .= ' (' . count($fileData['urls']) . '个文件)';
                    }
                } else {
                    // 旧格式文件路径
                    $filePath = $message['file_path'];
                    $extension = strtolower(pathinfo($message['file_path'], PATHINFO_EXTENSION));
                    
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $type = 'image';
                        $title = '[图片] ' . basename($message['file_path']);
                        $thumbnail = $message['file_path'];
                    } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])) {
                        $type = 'video';
                        $title = '[视频] ' . basename($message['file_path']);
                    } else {
                        $type = 'file';
                        $title = '[文件] ' . basename($message['file_path']);
                    }
                }
            } else {
                // 文本消息
                $title = mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : '');
            }
            
            // 添加来源信息到metadata
            $metadata = [
                'message_id' => $messageId,
                'room_id' => $message['room_id'],
                'room_name' => $message['room_name'],
                'room_type' => $message['room_type'],
                'sender_username' => $message['username'],
                'created_at' => $message['created_at']
            ];
            
            // 如果有多个文件，将完整文件信息添加到metadata
            if (!empty($message['file_path'])) {
                $fileData = json_decode($message['file_path'], true);
                if ($fileData && isset($fileData['urls']) && count($fileData['urls']) > 1) {
                    $metadata['files'] = $fileData;
                }
            }
            
            // 添加收藏
            $result = $this->favoritesModel->addFavorite(
                $userId, $type, $title, $content, $filePath, $fileSize, $thumbnail, $metadata, null
            );
            
            if ($result['success']) {
                $result['is_favorited'] = true;
            }
            
            $this->jsonResponse($result);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
        }
    }
    
    // 检查消息是否已收藏
    public function checkMessageFavoriteStatus() {
        $userId = $_SESSION['user_id'];
        $messageIds = $_GET['message_ids'] ?? '';
        
        if (empty($messageIds)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $messageIdArray = explode(',', $messageIds);
            $favoritedMessages = [];
            
            foreach ($messageIdArray as $messageId) {
                $sql = "SELECT id FROM favorites WHERE user_id = ? AND JSON_EXTRACT(metadata, '$.message_id') = ?";
                $existing = $db->fetch($sql, [$userId, $messageId]);
                if ($existing) {
                    $favoritedMessages[] = $messageId;
                }
            }
            
            $this->jsonResponse([
                'success' => true,
                'favorited_messages' => $favoritedMessages
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '检查失败: ' . $e->getMessage()]);
        }
    }

    // 获取房间消息（API方法）
    public function getRoomMessages() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $roomId = $_GET['room_id'] ?? null;
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '房间ID不能为空']);
            return;
        }
        
        try {
            // 检查用户是否在房间中
            $isInRoom = $this->chatModel->isUserInRoom($userId, $roomId);
            if ($isInRoom === false) {
                error_log("getRoomMessages failed - database query failed for isUserInRoom");
                $this->jsonResponse(['success' => false, 'message' => '数据库查询失败']);
                return;
            }
            if (!$isInRoom) {
                $this->jsonResponse(['success' => false, 'message' => '您不在该房间中']);
                return;
            }
            
            // 获取房间消息
            $messages = $this->chatModel->getRoomMessages($roomId, $userId);
            
            if ($messages === false) {
                error_log("getRoomMessages failed - database query failed for getRoomMessages");
                $this->jsonResponse(['success' => false, 'message' => '获取消息失败']);
                return;
            }
            
            $this->jsonResponse([
                'success' => true,
                'messages' => $messages
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '获取消息失败: ' . $e->getMessage()]);
        }
    }
    
    // 获取通话邀请
    public function getCallInvitations() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $roomId = $_GET['roomId'] ?? null;
        $userId = $_GET['userId'] ?? $_SESSION['user_id'];
        
        if (!$roomId) {
            $this->jsonResponse(['success' => false, 'message' => '房间ID不能为空']);
            return;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // 查询针对当前用户的通话邀请
            $stmt = $db->prepare("
                SELECT * FROM call_invitations 
                WHERE room_id = ? AND target_user_id = ? 
                AND status = 'inviting' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                ORDER BY created_at DESC
            ");
            $stmt->execute([$roomId, $userId]);
            $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse(['success' => true, 'invitations' => $invitations]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '获取通话邀请失败: ' . $e->getMessage()]);
        }
    }

    // 撤回消息
    public function recallMessage() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_POST['message_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        try {
            // 检查消息是否存在且属于当前用户
            $message = $this->chatModel->getMessageById($messageId);
            if (!$message || $message['sender_id'] != $userId) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在或无权限操作']);
                return;
            }
            
            // 检查是否在2分钟内
            $messageAge = time() - strtotime($message['created_at']);
            if ($messageAge > 120) {
                $this->jsonResponse(['success' => false, 'message' => '超过2分钟，无法撤回']);
                return;
            }
            
            // 标记消息为已撤回
            $result = $this->chatModel->recallMessage($messageId);
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '消息已撤回']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '撤回失败']);
            }
            
        } catch (Exception $e) {
            error_log("撤回消息失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '撤回失败']);
        }
    }
    
    // 删除消息
    public function deleteMessage() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_POST['message_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        try {
            // 检查消息是否存在且属于当前用户
            $message = $this->chatModel->getMessageById($messageId);
            if (!$message || $message['sender_id'] != $userId) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在或无权限操作']);
                return;
            }
            
            // 标记消息为已删除（仅对当前用户隐藏）
            $result = $this->chatModel->deleteMessageForUser($messageId, $userId);
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '消息已删除']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '删除失败']);
            }
            
        } catch (Exception $e) {
            error_log("删除消息失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '删除失败']);
        }
    }
    
    // 修改消息
    public function editMessage() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_POST['message_id'] ?? null;
        $content = $_POST['content'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$messageId || !$content) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID和内容不能为空']);
            return;
        }
        
        try {
            // 检查消息是否存在且属于当前用户
            $message = $this->chatModel->getMessageById($messageId);
            if (!$message || $message['sender_id'] != $userId) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在或无权限操作']);
                return;
            }
            
            // 检查是否为文本消息
            if ($message['message_type'] !== 'text' || !empty($message['file_path'])) {
                $this->jsonResponse(['success' => false, 'message' => '只能修改文本消息']);
                return;
            }
            
            // 更新消息内容
            $result = $this->chatModel->editMessage($messageId, $content);
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '消息修改成功']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '修改失败']);
            }
            
        } catch (Exception $e) {
            error_log("修改消息失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '修改失败']);
        }
    }
    
    // 切换收藏状态
    public function toggleFavorite() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_POST['message_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        try {
            // 检查消息是否存在
            $message = $this->chatModel->getMessageById($messageId);
            if (!$message) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在']);
                return;
            }
            
            // 切换收藏状态
            $result = $this->favoritesModel->toggleFavorite($userId, $messageId, $message);
            
            // toggleFavorite 返回 true 表示收藏，false 表示取消收藏，null 表示失败
            if ($result !== null) {
                $this->jsonResponse(['success' => true, 'favorited' => $result, 'message' => $result ? '已收藏' : '已取消收藏']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '操作失败']);
            }
            
        } catch (Exception $e) {
            error_log("切换收藏状态失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '操作失败']);
        }
    }
    
    // 切换置顶状态
    public function togglePin() {
        // 添加调试信息
        error_log("togglePin called - POST data: " . print_r($_POST, true));
        error_log("togglePin called - Session data: " . print_r($_SESSION, true));
        
        if (!$this->isAjaxRequest()) {
            error_log("togglePin failed - Not AJAX request");
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_POST['message_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        error_log("togglePin - messageId: $messageId, userId: $userId");
        
        if (!$messageId) {
            error_log("togglePin failed - messageId is empty");
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        if (!$userId) {
            error_log("togglePin failed - userId is empty");
            $this->jsonResponse(['success' => false, 'message' => '用户未登录']);
            return;
        }
        
        try {
            // 检查消息是否存在
            $message = $this->chatModel->getMessageById($messageId);
            error_log("togglePin - message found: " . print_r($message, true));
            
            if ($message === false) {
                error_log("togglePin failed - database query failed for getMessageById");
                $this->jsonResponse(['success' => false, 'message' => '数据库查询失败 - 获取消息']);
                return;
            }
            
            if (!$message) {
                error_log("togglePin failed - message not found");
                $this->jsonResponse(['success' => false, 'message' => '消息不存在']);
                return;
            }
            
            // 检查用户是否在房间中
            $isInRoom = $this->chatModel->isUserInRoom($userId, $message['room_id']);
            error_log("togglePin - isUserInRoom result: " . var_export($isInRoom, true));
            
            if ($isInRoom === false) {
                error_log("togglePin failed - database query failed for isUserInRoom");
                $this->jsonResponse(['success' => false, 'message' => '数据库查询失败 - 权限检查']);
                return;
            }
            
            if (!$isInRoom) {
                error_log("togglePin failed - user not in room");
                $this->jsonResponse(['success' => false, 'message' => '您不在该房间中']);
                return;
            }
            
            // 切换置顶状态
            $result = $this->chatModel->togglePin($messageId, $userId);
            error_log("togglePin - result: " . var_export($result, true));
            
            // togglePin 返回 true 表示置顶，false 表示取消置顶，null 表示失败
            if ($result !== null) {
                $this->jsonResponse(['success' => true, 'pinned' => $result, 'message' => $result ? '已置顶' : '已取消置顶']);
            } else {
                error_log("togglePin failed - togglePin returned null");
                $this->jsonResponse(['success' => false, 'message' => '操作失败 - 权限不足或数据库错误']);
            }
            
        } catch (Exception $e) {
            error_log("切换置顶状态失败: " . $e->getMessage());
            error_log("切换置顶状态失败 - 堆栈跟踪: " . $e->getTraceAsString());
            $this->jsonResponse(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
        }
    }
    
    // 获取引用消息ID
    public function getQuotedMessageId() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_GET['message_id'] ?? null;
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $sql = "SELECT quoted_message_id FROM messages WHERE id = ?";
            $result = $db->fetch($sql, [$messageId]);
            
            if ($result && $result['quoted_message_id']) {
                $this->jsonResponse(['success' => true, 'quoted_message_id' => $result['quoted_message_id']]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '没有找到引用消息']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '查询失败: ' . $e->getMessage()]);
        }
    }
    
    // 根据ID获取消息详情
    public function getMessageById() {
        // 添加调试信息
        error_log("getMessageById called - HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not_set'));
        error_log("getMessageById called - isAjaxRequest: " . ($this->isAjaxRequest() ? 'true' : 'false'));
        
        if (!$this->isAjaxRequest()) {
            error_log("getMessageById failed - Not AJAX request");
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_GET['message_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$messageId) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID不能为空']);
            return;
        }
        
        try {
            $message = $this->chatModel->getMessageById($messageId);
            if (!$message) {
                $this->jsonResponse(['success' => false, 'message' => '消息不存在']);
                return;
            }
            
            $this->jsonResponse(['success' => true, 'message' => $message]);
            
        } catch (Exception $e) {
            error_log("获取消息详情失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '获取消息失败']);
        }
    }
    
    // 获取转发接收者列表
    public function getRecipients() {
        // 添加调试信息
        error_log("getRecipients called - HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not_set'));
        error_log("getRecipients called - isAjaxRequest: " . ($this->isAjaxRequest() ? 'true' : 'false'));
        
        if (!$this->isAjaxRequest()) {
            error_log("getRecipients failed - Not AJAX request");
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // 获取好友列表和群组列表
            $friends = $this->userModel->getFriends($userId);
            $groups = $this->chatModel->getUserGroups($userId);
            
            $recipients = [];
            
            // 添加好友
            foreach ($friends as $friend) {
                $recipients[] = [
                    'id' => 'user_' . $friend['id'],
                    'name' => $friend['username'],
                    'type' => 'user',
                    'avatar' => $friend['avatar'] ?? null
                ];
            }
            
            // 添加群组
            foreach ($groups as $group) {
                $recipients[] = [
                    'id' => 'group_' . $group['id'],
                    'name' => $group['name'],
                    'type' => 'group',
                    'avatar' => $group['avatar'] ?? null
                ];
            }
            
            $this->jsonResponse(['success' => true, 'recipients' => $recipients]);
            
        } catch (Exception $e) {
            error_log("获取接收者列表失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '获取接收者列表失败']);
        }
    }
    
    // 转发消息
    public function forwardMessage() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $messageId = $_POST['message_id'] ?? null;
        $recipients = $_POST['recipients'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$messageId || !$recipients) {
            $this->jsonResponse(['success' => false, 'message' => '消息ID和接收者不能为空']);
            return;
        }
        
        try {
            // 解析接收者列表
            $recipients = json_decode($recipients, true);
            if (!is_array($recipients) || empty($recipients)) {
                $this->jsonResponse(['success' => false, 'message' => '接收者列表无效']);
                return;
            }
            
            // 获取原消息
            $originalMessage = $this->chatModel->getMessageById($messageId);
            if (!$originalMessage) {
                $this->jsonResponse(['success' => false, 'message' => '原消息不存在']);
                return;
            }
            
            $successCount = 0;
            $errors = [];
            
            // 转发给每个接收者
            foreach ($recipients as $recipientId) {
                try {
                    if (strpos($recipientId, 'user_') === 0) {
                        // 转发给用户
                        $targetUserId = substr($recipientId, 5);
                        $result = $this->chatModel->forwardToUser($originalMessage, $targetUserId, $userId);
                    } elseif (strpos($recipientId, 'group_') === 0) {
                        // 转发给群组
                        $targetGroupId = substr($recipientId, 6);
                        $result = $this->chatModel->forwardToGroup($originalMessage, $targetGroupId, $userId);
                    } else {
                        continue;
                    }
                    
                    if ($result) {
                        $successCount++;
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            if ($successCount > 0) {
                $this->jsonResponse(['success' => true, 'message' => "已转发给 {$successCount} 个接收者"]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '转发失败: ' . implode(', ', $errors)]);
            }
            
        } catch (Exception $e) {
            error_log("转发消息失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '转发失败']);
        }
    }
    
    // 清空聊天记录
    public function clearHistory() {
        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $roomId = $_POST['room_id'] ?? null;
        $roomType = $_POST['room_type'] ?? null;
        $userId = $_SESSION['user_id'];
        
        // 添加调试信息
        error_log("clearHistory called - roomId: $roomId, roomType: $roomType, userId: $userId");
        
        if (!$roomId || !$roomType) {
            error_log("clearHistory failed - missing roomId or roomType");
            $this->jsonResponse(['success' => false, 'message' => '房间ID和类型不能为空']);
            return;
        }
        
        try {
            // 首先检查房间是否存在
            $roomInfo = $this->chatModel->getRoomInfo($roomId, $userId);
            error_log("clearHistory - roomInfo check: " . ($roomInfo ? 'found' : 'not found'));
            
            if (!$roomInfo) {
                $this->jsonResponse(['success' => false, 'message' => '房间不存在或您没有权限访问']);
                return;
            }
            
            error_log("clearHistory - roomInfo details: " . json_encode($roomInfo));
            
            // 检查用户是否有权限清空该房间的聊天记录
            if ($roomType === 'group') {
                // 检查用户是否在群组中
                $isInGroup = $this->chatModel->isUserInGroup($userId, $roomId);
                error_log("clearHistory - isUserInGroup result: " . ($isInGroup ? 'true' : 'false'));
                if (!$isInGroup) {
                    $this->jsonResponse(['success' => false, 'message' => '您没有权限清空此群组的聊天记录']);
                    return;
                }
                
                // 对于群组，还需要检查用户是否是群主或管理员
                $isOwner = $this->chatModel->isGroupOwner($userId, $roomId);
                $isAdmin = $this->chatModel->isGroupAdmin($userId, $roomId);
                error_log("clearHistory - isOwner: " . ($isOwner ? 'true' : 'false') . ", isAdmin: " . ($isAdmin ? 'true' : 'false'));
                
                // 如果用户是群主（created_by），或者用户是管理员，则允许清空
                if (!$isOwner && !$isAdmin) {
                    $this->jsonResponse(['success' => false, 'message' => '只有群主和管理员可以清空群聊记录']);
                    return;
                }
            } else {
                // 检查用户是否在私聊房间中
                $isInRoom = $this->chatModel->isUserInRoom($userId, $roomId);
                error_log("clearHistory - isUserInRoom result: " . ($isInRoom ? 'true' : 'false'));
                if (!$isInRoom) {
                    $this->jsonResponse(['success' => false, 'message' => '您没有权限清空此聊天记录']);
                    return;
                }
            }
            
            // 清空聊天记录
            error_log("clearHistory - calling clearChatHistory");
            $result = $this->chatModel->clearChatHistory($roomId, $roomType);
            error_log("clearHistory - clearChatHistory result: " . ($result ? 'true' : 'false'));
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '聊天记录已清空']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '清空聊天记录失败']);
            }
            
        } catch (Exception $e) {
            error_log("清空聊天记录失败: " . $e->getMessage());
            error_log("清空聊天记录失败 - 堆栈跟踪: " . $e->getTraceAsString());
            $this->jsonResponse(['success' => false, 'message' => '清空聊天记录失败: ' . $e->getMessage()]);
        }
    }

    // 设置好友备注
    public function setFriendNickname() {
        try {
            $userId = $_SESSION['user_id'];
            $roomId = $_POST['room_id'] ?? null;
            $nickname = trim($_POST['nickname'] ?? '');
            
            if (!$roomId) {
                $this->jsonResponse(['success' => false, 'message' => '房间ID不能为空']);
                return;
            }
            
            // 验证房间是否存在且用户有权限
            $room = $this->chatModel->getRoomById($roomId);
            if (!$room) {
                $this->jsonResponse(['success' => false, 'message' => '房间不存在']);
                return;
            }
            
            // 只允许私聊设置备注
            if ($room['type'] !== 'private') {
                $this->jsonResponse(['success' => false, 'message' => '只能为私聊好友设置备注']);
                return;
            }
            
            // 验证用户是否在房间中
            if (!$this->chatModel->isUserInRoom($userId, $roomId)) {
                $this->jsonResponse(['success' => false, 'message' => '您没有权限设置此好友的备注']);
                return;
            }
            
            // 获取好友ID
            $friendId = $this->chatModel->getFriendIdFromRoom($roomId, $userId);
            if (!$friendId) {
                $this->jsonResponse(['success' => false, 'message' => '无法找到好友信息']);
                return;
            }
            
            // 设置备注
            $result = $this->friendshipModel->setFriendNickname($userId, $friendId, $nickname);
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '备注设置成功']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '备注设置失败']);
            }
            
        } catch (Exception $e) {
            error_log("设置好友备注失败: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => '设置备注失败: ' . $e->getMessage()]);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
