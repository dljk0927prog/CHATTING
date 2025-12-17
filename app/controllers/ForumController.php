<?php
require_once dirname(__DIR__) . '/../config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Chat.php';

class ForumController {
    private $userModel;
    private $chatModel;
    private $db;
    
    public function __construct() {
        $this->userModel = new User();
        $this->chatModel = new Chat();
        $this->db = Database::getInstance();
        
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/login');
        }
    }
    
    // 显示论坛内部页面（帖子列表）
    public function view() {
        // 设置防缓存头部
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        
        // 添加调试头部
        header('X-Debug-Timestamp: ' . time());
        header('X-Debug-Request-ID: ' . uniqid());
        header('X-Debug-Forum-ID: ' . ($_GET['id'] ?? 'none'));
        
        $userId = $_SESSION['user_id'];
        $forumId = $_GET['id'] ?? null;
        
        // 记录详细的调试信息
        error_log("ForumController::view() - 用户ID: $userId, 请求论坛ID: $forumId, 时间: " . date('Y-m-d H:i:s'));
        error_log("ForumController::view() - REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        error_log("ForumController::view() - QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? '无'));
        error_log("ForumController::view() - GET参数: " . json_encode($_GET));
        error_log("ForumController::view() - 会话ID: " . session_id());
        
        // 强制清除任何可能的缓存
        if (isset($_GET['t'])) {
            error_log("ForumController::view() - 检测到时间戳参数: " . $_GET['t']);
        }
        
        // 强制清除所有可能的缓存机制
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('ETag: "' . md5(time() . $forumId) . '"');
        
        // 添加调试头部
        header('X-Debug-Forum-ID: ' . $forumId);
        header('X-Debug-Timestamp: ' . time());
        
        if (!$forumId) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取论坛信息
        $forum = $this->getForumInfo($forumId);
        if (!$forum) {
            $this->redirect('/list_forum');
            return;
        }
        
        
        // 检查用户是否是论坛成员
        $isMember = $this->isForumMember($userId, $forumId);
        if (!$isMember) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取论坛帖子列表
        $posts = $this->getForumPosts($forumId);
        
        // 获取用户信息（用于navbar）
        $user = $this->userModel->getUserById($userId);
        $rooms = $this->chatModel->getUserRooms($userId);
        $friends = $this->userModel->getFriends($userId);
        $pendingRequests = $this->userModel->getPendingRequests($userId);
        $forumInvites = $this->userModel->getUserForumInvites($userId);
        $groups = $this->getUserGroups($userId);
        $forums = $this->userModel->getUserForums($userId);
        
        
        $this->render('forum/view', [
            'forum' => $forum,
            'posts' => $posts,
            'isMember' => $isMember,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'forumInvites' => $forumInvites,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'forums',
            'currentForumId' => $forumId
        ]);
    }
    
    // 显示论坛设置页面
    public function settings() {
        // 设置防缓存头部
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $userId = $_SESSION['user_id'];
        $forumId = $_GET['id'] ?? null;
        
        if (!$forumId) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取论坛信息
        $forum = $this->getForumInfo($forumId);
        if (!$forum) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 检查用户是否是论坛管理员或创建者
        $userRole = $this->getUserForumRole($userId, $forumId);
        if (!in_array($userRole, ['admin', 'creator'])) {
            $this->redirect('/forum/view?id=' . $forumId);
            return;
        }
        
        // 获取论坛成员列表
        $members = $this->getForumMembers($forumId);
        
        // 获取待处理的加入请求
        $pendingRequests = $this->getPendingJoinRequests($forumId);
        error_log("论坛设置 - 论坛ID: $forumId, 待处理请求数量: " . count($pendingRequests));
        
        // 获取用户信息（用于navbar）
        $user = $this->userModel->getUserById($userId);
        $rooms = $this->chatModel->getUserRooms($userId);
        $friends = $this->userModel->getFriends($userId);
        $pendingFriendRequests = $this->userModel->getPendingRequests($userId);
        $forumInvites = $this->userModel->getUserForumInvites($userId);
        $groups = $this->getUserGroups($userId);
        $forums = $this->userModel->getUserForums($userId);
        
        $this->render('forum/settings', [
            'forum' => $forum,
            'members' => $members,
            'pendingRequests' => $pendingRequests,
            'userRole' => $userRole,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingFriendRequests' => $pendingFriendRequests,
            'forumInvites' => $forumInvites,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'forums',
            'currentForumId' => $forumId
        ]);
    }
    
    // 显示帖子详情页面
    public function post() {
        $userId = $_SESSION['user_id'];
        $postId = $_GET['id'] ?? null;
        
        if (!$postId) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取帖子信息
        $post = $this->getPostInfo($postId);
        if (!$post) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 检查用户是否是论坛成员
        $isMember = $this->isForumMember($userId, $post['forum_id']);
        if (!$isMember) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取帖子回复
        $replies = $this->getPostReplies($postId);
        
        // 增加浏览量
        $this->incrementPostViews($postId);
        
        // 获取用户信息（用于navbar）
        $user = $this->userModel->getUserById($userId);
        $rooms = $this->chatModel->getUserRooms($userId);
        $friends = $this->userModel->getFriends($userId);
        $pendingRequests = $this->userModel->getPendingRequests($userId);
        $forumInvites = $this->userModel->getUserForumInvites($userId);
        $groups = $this->getUserGroups($userId);
        $forums = $this->userModel->getUserForums($userId);
        
        $this->render('forum/post', [
            'post' => $post,
            'replies' => $replies,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'forumInvites' => $forumInvites,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'forums',
            'currentForumId' => $post['forum_id']
        ]);
    }
    
    // 创建论坛
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $signature = trim($_POST['signature'] ?? '');
        
        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => '论坛名称不能为空']);
            return;
        }
        
        // 检查论坛名称是否已存在
        $checkSql = "SELECT id FROM forums WHERE name = ?";
        $existing = $this->db->fetch($checkSql, [$name]);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => '论坛名称已存在']);
            return;
        }
        
        try {
            // 创建论坛
            $sql = "INSERT INTO forums (name, description, signature, creator_id, is_public, max_members, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $this->db->query($sql, [$name, $description, $signature, $userId, true, 1000]);
            
            $forumId = $this->db->lastInsertId();
            
            // 将创建者添加为论坛成员
            $memberSql = "INSERT INTO forum_members (forum_id, user_id, role, joined_at) VALUES (?, ?, 'creator', NOW())";
            $this->db->query($memberSql, [$forumId, $userId]);
            
            $this->jsonResponse([
                'success' => true, 
                'message' => '论坛创建成功',
                'forum_id' => $forumId,
                'redirect_url' => '/CHATTING/forum/view?id=' . $forumId
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '创建失败: ' . $e->getMessage()]);
        }
    }
    
    // 创建帖子
    public function createPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        if (!$forumId || empty($title) || empty($content)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否是论坛成员
        if (!$this->isForumMember($userId, $forumId)) {
            $this->jsonResponse(['success' => false, 'message' => '您不是该论坛成员']);
            return;
        }
        
        try {
            $sql = "INSERT INTO forum_posts (forum_id, author_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())";
            $this->db->query($sql, [$forumId, $userId, $title, $content]);
            
            $postId = $this->db->lastInsertId();
            
            // 获取新创建的帖子完整信息
            $postData = $this->getPostWithDetails($postId);
            
            $this->jsonResponse([
                'success' => true, 
                'message' => '帖子创建成功',
                'post_id' => $postId,
                'post' => $postData
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '创建失败: ' . $e->getMessage()]);
        }
    }
    
    // 创建带媒体文件的帖子
    public function createPostWithMedia() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        // 调试信息
        error_log("createPostWithMedia - 用户ID: $userId");
        error_log("createPostWithMedia - 论坛ID: $forumId");
        error_log("createPostWithMedia - 标题: $title");
        error_log("createPostWithMedia - 内容: $content");
        error_log("createPostWithMedia - POST数据: " . json_encode($_POST));
        
        if (!$forumId || empty($title) || empty($content)) {
            error_log("createPostWithMedia - 参数验证失败: forumId=$forumId, title='$title', content='$content'");
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否是论坛成员
        if (!$this->isForumMember($userId, $forumId)) {
            $this->jsonResponse(['success' => false, 'message' => '您不是该论坛成员']);
            return;
        }
        
        try {
            // 开始事务
            $this->db->beginTransaction();
            
            // 创建帖子
            $sql = "INSERT INTO forum_posts (forum_id, author_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())";
            $this->db->query($sql, [$forumId, $userId, $title, $content]);
            
            $postId = $this->db->lastInsertId();
            
            // 处理上传的媒体文件
            $uploadedFiles = [];
            if (isset($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
                $uploadDir = dirname(__DIR__) . '/../public/uploads/files/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileCount = count($_FILES['media_files']['name']);
                error_log("createPostWithMedia - 接收到 {$fileCount} 个文件");
                
                // 调试：记录所有接收到的文件名
                for ($j = 0; $j < $fileCount; $j++) {
                    error_log("createPostWithMedia - 文件 {$j}: " . $_FILES['media_files']['name'][$j]);
                }
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = $_FILES['media_files']['name'][$i];
                        $fileTmpName = $_FILES['media_files']['tmp_name'][$i];
                        $fileSize = $_FILES['media_files']['size'][$i];
                        $fileType = $_FILES['media_files']['type'][$i];
                        
                        // 验证文件类型
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm', 'video/quicktime'];
                        if (!in_array($fileType, $allowedTypes)) {
                            continue; // 跳过不支持的文件类型
                        }
                        
                        // 验证文件大小 (最大50MB)
                        if ($fileSize > 50 * 1024 * 1024) {
                            continue; // 跳过过大的文件
                        }
                        
                        // 生成唯一文件名
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                        $uniqueFileName = 'post_' . $postId . '_' . time() . '_' . $i . '.' . $extension;
                        $filePath = $uploadDir . $uniqueFileName;
                        
                        // 移动文件
                        if (move_uploaded_file($fileTmpName, $filePath)) {
                            $uploadedFiles[] = $uniqueFileName;
                            error_log("createPostWithMedia - 文件上传成功: {$fileName} -> {$uniqueFileName}");
                            
                            // 保存文件信息到数据库
                            $fileSql = "INSERT INTO forum_post_files (post_id, filename, original_name, file_type, file_size, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())";
                            $this->db->query($fileSql, [$postId, $uniqueFileName, $fileName, $fileType, $fileSize]);
                            error_log("createPostWithMedia - 数据库记录已保存: post_id={$postId}, filename={$uniqueFileName}");
                        } else {
                            error_log("createPostWithMedia - 文件移动失败: {$fileName}");
                        }
                    }
                }
            }
            
            // 提交事务
            $this->db->commit();
            
            // 获取新创建的帖子完整信息
            $postData = $this->getPostWithDetails($postId);
            
            $this->jsonResponse([
                'success' => true, 
                'message' => '帖子创建成功',
                'post_id' => $postId,
                'post' => $postData,
                'uploaded_files' => $uploadedFiles
            ]);
        } catch (Exception $e) {
            // 回滚事务
            $this->db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '创建失败: ' . $e->getMessage()]);
        }
    }
    
    // 回复帖子
    public function replyPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $postId = $_POST['post_id'] ?? null;
        $content = trim($_POST['content'] ?? '');
        $replyTo = $_POST['reply_to'] ?? null;
        
        if (!$postId || empty($content)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查帖子是否存在
        $post = $this->getPostInfo($postId);
        if (!$post) {
            $this->jsonResponse(['success' => false, 'message' => '帖子不存在']);
            return;
        }
        
        // 检查用户是否是论坛成员
        if (!$this->isForumMember($userId, $post['forum_id'])) {
            $this->jsonResponse(['success' => false, 'message' => '您不是该论坛成员']);
            return;
        }
        
        try {
            $sql = "INSERT INTO forum_replies (post_id, author_id, content, reply_to_id, created_at) VALUES (?, ?, ?, ?, NOW())";
            $this->db->query($sql, [$postId, $userId, $content, $replyTo]);
            
            $replyId = $this->db->lastInsertId();
            
            $this->jsonResponse([
                'success' => true, 
                'message' => '回复成功',
                'reply_id' => $replyId
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '回复失败: ' . $e->getMessage()]);
        }
    }
    
    // 获取论坛信息
    private function getForumInfo($forumId) {
        // 记录调试信息
        error_log("getForumInfo() - 查询论坛ID: $forumId, 时间: " . date('Y-m-d H:i:s'));
        
        $sql = "SELECT f.*, u.username as creator_name, u.avatar as creator_avatar,
                       (SELECT COUNT(*) FROM forum_members fm WHERE fm.forum_id = f.id) as member_count,
                       (SELECT COUNT(*) FROM forum_posts fp WHERE fp.forum_id = f.id) as post_count
                FROM forums f
                LEFT JOIN users u ON f.creator_id = u.id
                WHERE f.id = ?";
        $result = $this->db->fetch($sql, [$forumId]);
        
        if ($result) {
            error_log("getForumInfo() - 查询成功: ID=" . $result['id'] . ", 名称=" . $result['name']);
        } else {
            error_log("getForumInfo() - 查询失败: 论坛ID $forumId 不存在");
        }
        
        return $result;
    }
    
    // 获取论坛帖子列表
    private function getForumPosts($forumId, $limit = 20, $offset = 0) {
        $sql = "SELECT fp.*, u.username, u.avatar,
                       (SELECT COUNT(*) FROM forum_replies fr WHERE fr.post_id = fp.id) as reply_count
                FROM forum_posts fp
                JOIN users u ON fp.author_id = u.id
                WHERE fp.forum_id = ?
                ORDER BY fp.is_pinned DESC, fp.created_at DESC
                LIMIT ? OFFSET ?";
        $posts = $this->db->fetchAll($sql, [$forumId, $limit, $offset]);
        
        // 为每个帖子添加媒体文件信息和最近5个回复
        foreach ($posts as &$post) {
            $post['media_files'] = $this->getPostMediaFiles($post['id']);
            $post['recent_replies'] = $this->getRecentPostReplies($post['id'], 5);
        }
        
        return $posts;
    }
    
    // 获取帖子详细信息（用于AJAX返回）
    private function getPostWithDetails($postId) {
        $sql = "SELECT fp.*, u.username, u.avatar,
                       (SELECT COUNT(*) FROM forum_replies fr WHERE fr.post_id = fp.id) as reply_count
                FROM forum_posts fp
                JOIN users u ON fp.author_id = u.id
                WHERE fp.id = ?";
        $post = $this->db->fetch($sql, [$postId]);
        
        if ($post) {
            // 添加媒体文件信息和最近5个回复
            $post['media_files'] = $this->getPostMediaFiles($post['id']);
            $post['recent_replies'] = $this->getRecentPostReplies($post['id'], 5);
            
            // 调试信息
            error_log("getPostWithDetails - 帖子ID: {$postId}, 媒体文件数量: " . count($post['media_files']));
            foreach ($post['media_files'] as $index => $media) {
                error_log("getPostWithDetails - 媒体文件 {$index}: filename={$media['filename']}, original_name={$media['original_name']}");
            }
        }
        
        return $post;
    }
    
    // 获取帖子信息
    private function getPostInfo($postId) {
        $sql = "SELECT fp.*, u.username, u.avatar, f.name as forum_name
                FROM forum_posts fp
                JOIN users u ON fp.author_id = u.id
                JOIN forums f ON fp.forum_id = f.id
                WHERE fp.id = ?";
        $post = $this->db->fetch($sql, [$postId]);
        
        if ($post) {
            // 获取帖子的媒体文件
            $post['media_files'] = $this->getPostMediaFiles($postId);
        }
        
        return $post;
    }
    
    // 获取帖子的媒体文件
    private function getPostMediaFiles($postId) {
        $sql = "SELECT * FROM forum_post_files WHERE post_id = ? ORDER BY uploaded_at ASC";
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    // 获取帖子回复
    private function getPostReplies($postId) {
        $sql = "SELECT fr.*, u.username, u.avatar, ru.username as reply_to_username
                FROM forum_replies fr
                JOIN users u ON fr.author_id = u.id
                LEFT JOIN forum_replies fr2 ON fr.reply_to_id = fr2.id
                LEFT JOIN users ru ON fr2.author_id = ru.id
                WHERE fr.post_id = ?
                ORDER BY fr.created_at ASC";
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    // 获取帖子的最近回复（用于列表显示）
    private function getRecentPostReplies($postId, $limit = 5) {
        $sql = "SELECT fr.*, u.username, u.avatar, ru.username as reply_to_username
                FROM forum_replies fr
                JOIN users u ON fr.author_id = u.id
                LEFT JOIN forum_replies fr2 ON fr.reply_to_id = fr2.id
                LEFT JOIN users ru ON fr2.author_id = ru.id
                WHERE fr.post_id = ?
                ORDER BY fr.created_at DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$postId, $limit]);
    }
    
    // 检查用户是否是论坛成员
    private function isForumMember($userId, $forumId) {
        $sql = "SELECT id FROM forum_members WHERE forum_id = ? AND user_id = ?";
        $result = $this->db->fetch($sql, [$forumId, $userId]);
        return $result !== false;
    }
    
    // 获取用户在论坛中的角色
    private function getUserForumRole($userId, $forumId) {
        $sql = "SELECT role FROM forum_members WHERE forum_id = ? AND user_id = ?";
        $result = $this->db->fetch($sql, [$forumId, $userId]);
        return $result ? $result['role'] : null;
    }
    
    // 获取论坛成员列表
    private function getForumMembers($forumId) {
        $sql = "SELECT fm.*, u.username, u.avatar, u.status, fm.joined_at
                FROM forum_members fm
                JOIN users u ON fm.user_id = u.id
                WHERE fm.forum_id = ?
                ORDER BY fm.role DESC, fm.joined_at ASC";
        return $this->db->fetchAll($sql, [$forumId]);
    }
    
    // 获取待处理的加入请求
    private function getPendingJoinRequests($forumId) {
        $sql = "SELECT fjr.*, u.username, u.avatar
                FROM forum_join_requests fjr
                JOIN users u ON fjr.user_id = u.id
                WHERE fjr.forum_id = ? AND fjr.status = 'pending'
                ORDER BY fjr.requested_at ASC";
        $requests = $this->db->fetchAll($sql, [$forumId]);
        error_log("getPendingJoinRequests - 论坛ID: $forumId, SQL: $sql, 结果数量: " . count($requests));
        return $requests;
    }
    
    // 增加帖子浏览量
    private function incrementPostViews($postId) {
        $sql = "UPDATE forum_posts SET view_count = view_count + 1 WHERE id = ?";
        $this->db->query($sql, [$postId]);
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
        return $this->db->fetchAll($sql, [$userId]);
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
        include __DIR__ . '/../views/' . $view . '.php';
    }
    
    // 更新论坛设置
    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $maxMembers = intval($_POST['max_members'] ?? 1000);
        $isPublic = intval($_POST['is_public'] ?? 1);
        
        if (!$forumId || empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否是论坛管理员或创建者
        $userRole = $this->getUserForumRole($userId, $forumId);
        if (!in_array($userRole, ['admin', 'creator'])) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $sql = "UPDATE forums SET name = ?, description = ?, max_members = ?, is_public = ?, updated_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$name, $description, $maxMembers, $isPublic, $forumId]);
            
            $this->jsonResponse(['success' => true, 'message' => '设置更新成功']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
        }
    }
    
    // 处理加入请求
    public function handleJoinRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $requestId = $_POST['request_id'] ?? null;
        $action = $_POST['action'] ?? null;
        
        if (!$requestId || !in_array($action, ['approved', 'rejected'])) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            // 获取请求信息
            $sql = "SELECT fjr.*, f.name as forum_name FROM forum_join_requests fjr 
                    JOIN forums f ON fjr.forum_id = f.id 
                    WHERE fjr.id = ?";
            $request = $this->db->fetch($sql, [$requestId]);
            
            if (!$request) {
                $this->jsonResponse(['success' => false, 'message' => '请求不存在']);
                return;
            }
            
            // 检查用户权限
            $userRole = $this->getUserForumRole($userId, $request['forum_id']);
            if (!in_array($userRole, ['admin', 'creator'])) {
                $this->jsonResponse(['success' => false, 'message' => '权限不足']);
                return;
            }
            
            if ($action === 'approved') {
                // 添加为论坛成员
                $sql = "INSERT INTO forum_members (forum_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())";
                $this->db->query($sql, [$request['forum_id'], $request['user_id']]);
            }
            
            // 更新请求状态
            $sql = "UPDATE forum_join_requests SET status = ?, processed_at = NOW(), processed_by = ? WHERE id = ?";
            $this->db->query($sql, [$action, $userId, $requestId]);
            
            $message = $action === 'approved' ? '已同意加入请求' : '已拒绝加入请求';
            $this->jsonResponse(['success' => true, 'message' => $message]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
        }
    }
    
    // 更新成员角色
    public function updateMemberRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $targetUserId = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? null;
        
        if (!$targetUserId || !in_array($role, ['admin', 'member'])) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 获取目标用户的论坛ID
        $sql = "SELECT forum_id FROM forum_members WHERE user_id = ?";
        $membership = $this->db->fetch($sql, [$targetUserId]);
        
        if (!$membership) {
            $this->jsonResponse(['success' => false, 'message' => '用户不是论坛成员']);
            return;
        }
        
        // 检查用户权限（必须是创建者）
        $userRole = $this->getUserForumRole($userId, $membership['forum_id']);
        if ($userRole !== 'creator') {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $sql = "UPDATE forum_members SET role = ? WHERE user_id = ? AND forum_id = ?";
            $this->db->query($sql, [$role, $targetUserId, $membership['forum_id']]);
            
            $this->jsonResponse(['success' => true, 'message' => '角色更新成功']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
        }
    }
    
    // 移除成员
    public function removeMember() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $targetUserId = $_POST['user_id'] ?? null;
        
        if (!$targetUserId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 获取目标用户的论坛ID
        $sql = "SELECT forum_id FROM forum_members WHERE user_id = ?";
        $membership = $this->db->fetch($sql, [$targetUserId]);
        
        if (!$membership) {
            $this->jsonResponse(['success' => false, 'message' => '用户不是论坛成员']);
            return;
        }
        
        // 检查用户权限（必须是创建者）
        $userRole = $this->getUserForumRole($userId, $membership['forum_id']);
        if ($userRole !== 'creator') {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $sql = "DELETE FROM forum_members WHERE user_id = ? AND forum_id = ?";
            $this->db->query($sql, [$targetUserId, $membership['forum_id']]);
            
            $this->jsonResponse(['success' => true, 'message' => '成员已移除']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '移除失败: ' . $e->getMessage()]);
        }
    }
    
    // 删除论坛
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        
        if (!$forumId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否是论坛创建者
        $userRole = $this->getUserForumRole($userId, $forumId);
        if ($userRole !== 'creator') {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // 删除论坛相关的所有数据
            // 1. 删除帖子附件
            $sql = "DELETE fpf FROM forum_post_files fpf 
                    JOIN forum_posts fp ON fpf.post_id = fp.id 
                    WHERE fp.forum_id = ?";
            $this->db->query($sql, [$forumId]);
            
            // 2. 删除回复
            $sql = "DELETE fr FROM forum_replies fr 
                    JOIN forum_posts fp ON fr.post_id = fp.id 
                    WHERE fp.forum_id = ?";
            $this->db->query($sql, [$forumId]);
            
            // 3. 删除帖子
            $sql = "DELETE FROM forum_posts WHERE forum_id = ?";
            $this->db->query($sql, [$forumId]);
            
            // 4. 删除成员关系
            $sql = "DELETE FROM forum_members WHERE forum_id = ?";
            $this->db->query($sql, [$forumId]);
            
            // 5. 删除加入请求
            $sql = "DELETE FROM forum_join_requests WHERE forum_id = ?";
            $this->db->query($sql, [$forumId]);
            
            // 6. 删除论坛
            $sql = "DELETE FROM forums WHERE id = ?";
            $this->db->query($sql, [$forumId]);
            
            $this->db->commit();
            $this->jsonResponse(['success' => true, 'message' => '论坛已删除']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
        }
    }
    
    // 退出论坛
    public function leaveForum() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        
        if (!$forumId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否是论坛成员
        $userRole = $this->getUserForumRole($userId, $forumId);
        if (!$userRole) {
            $this->jsonResponse(['success' => false, 'message' => '您不是此论坛的成员']);
            return;
        }
        
        // 创建者不能退出论坛，只能删除或解散
        if ($userRole === 'creator') {
            $this->jsonResponse(['success' => false, 'message' => '创建者不能退出论坛，请使用删除或解散功能']);
            return;
        }
        
        try {
            // 从论坛成员中移除用户
            $sql = "DELETE FROM forum_members WHERE forum_id = ? AND user_id = ?";
            $this->db->query($sql, [$forumId, $userId]);
            
            $this->jsonResponse(['success' => true, 'message' => '已成功退出论坛']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '退出失败: ' . $e->getMessage()]);
        }
    }
    
    // 解散论坛
    public function dissolve() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        
        if (!$forumId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户是否是论坛创建者
        $userRole = $this->getUserForumRole($userId, $forumId);
        if ($userRole !== 'creator') {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // 移除所有成员（除了创建者）
            $sql = "DELETE FROM forum_members WHERE forum_id = ? AND user_id != ?";
            $this->db->query($sql, [$forumId, $userId]);
            
            // 删除所有待处理的加入请求
            $sql = "DELETE FROM forum_join_requests WHERE forum_id = ?";
            $this->db->query($sql, [$forumId]);
            
            // 将论坛设置为私有状态
            $sql = "UPDATE forums SET is_public = 0 WHERE id = ?";
            $this->db->query($sql, [$forumId]);
            
            $this->db->commit();
            $this->jsonResponse(['success' => true, 'message' => '论坛已解散，所有成员已被移除']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '解散失败: ' . $e->getMessage()]);
        }
    }
    
    // 更新论坛头像
    public function updateForumAvatar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;

        if (!$forumId) {
            $this->jsonResponse(['success' => false, 'message' => '论坛ID不能为空']);
            return;
        }

        // 检查用户权限
        $userRole = $this->getUserForumRole($userId, $forumId);
        if (!$userRole || ($userRole !== 'creator' && $userRole !== 'admin')) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }

        // 检查文件上传
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => '文件上传失败']);
            return;
        }

        $file = $_FILES['avatar'];
        
        // 验证文件类型
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->jsonResponse(['success' => false, 'message' => '只支持 JPG、PNG、GIF、WebP 格式的图片']);
            return;
        }

        // 验证文件大小 (最大5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->jsonResponse(['success' => false, 'message' => '图片大小不能超过5MB']);
            return;
        }

        // 创建上传目录
        $uploadDir = dirname(__DIR__) . '/../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 生成唯一文件名
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'forum_' . $forumId . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        // 移动文件
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // 更新数据库
            $sql = "UPDATE forums SET avatar = ? WHERE id = ?";
            $this->db->execute($sql, [$fileName, $forumId]);
            
            $this->jsonResponse(['success' => true, 'message' => '头像更新成功', 'avatar' => $fileName]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => '文件保存失败']);
        }
    }

    // 编辑帖子
    public function editPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // GET请求：显示编辑页面
            $this->editPostPage();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // POST请求：处理编辑提交
            $this->processEditPost();
        } else {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
        }
    }
    
    // 处理编辑帖子提交
    private function processEditPost() {
        $userId = $_SESSION['user_id'];
        $postId = $_POST['post_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        if (!$postId || empty($title) || empty($content)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 获取帖子信息
        $post = $this->getPostInfo($postId);
        if (!$post) {
            $this->jsonResponse(['success' => false, 'message' => '帖子不存在']);
            return;
        }
        
        // 检查权限：只有作者可以编辑
        if ($post['author_id'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // 更新帖子内容
            $sql = "UPDATE forum_posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$title, $content, $postId]);
            
            // 处理新上传的附件
            if (isset($_FILES['new_attachments']) && is_array($_FILES['new_attachments']['name'])) {
                $uploadDir = dirname(__DIR__) . '/../public/uploads/files/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileCount = count($_FILES['new_attachments']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['new_attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = $_FILES['new_attachments']['name'][$i];
                        $fileTmpName = $_FILES['new_attachments']['tmp_name'][$i];
                        $fileSize = $_FILES['new_attachments']['size'][$i];
                        $fileType = $_FILES['new_attachments']['type'][$i];
                        
                        // 验证文件类型
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm', 'video/quicktime'];
                        if (!in_array($fileType, $allowedTypes)) {
                            continue; // 跳过不支持的文件类型
                        }
                        
                        // 验证文件大小 (最大50MB)
                        if ($fileSize > 50 * 1024 * 1024) {
                            continue; // 跳过过大的文件
                        }
                        
                        // 生成唯一文件名
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                        $uniqueFileName = 'post_' . $postId . '_' . time() . '_' . $i . '.' . $extension;
                        $filePath = $uploadDir . $uniqueFileName;
                        
                        // 移动文件
                        if (move_uploaded_file($fileTmpName, $filePath)) {
                            // 保存文件信息到数据库
                            $fileSql = "INSERT INTO forum_post_files (post_id, filename, original_name, file_type, file_size, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())";
                            $this->db->query($fileSql, [$postId, $uniqueFileName, $fileName, $fileType, $fileSize]);
                        }
                    }
                }
            }
            
            $this->db->commit();
            $this->jsonResponse(['success' => true, 'message' => '帖子更新成功']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
        }
    }
    
    // 删除帖子
    public function deletePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $postId = $_POST['post_id'] ?? null;
        
        if (!$postId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 获取帖子信息
        $post = $this->getPostInfo($postId);
        if (!$post) {
            $this->jsonResponse(['success' => false, 'message' => '帖子不存在']);
            return;
        }
        
        // 检查权限：只有作者可以删除
        if ($post['author_id'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // 删除帖子附件文件
            $mediaFiles = $this->getPostMediaFiles($postId);
            foreach ($mediaFiles as $file) {
                $filePath = dirname(__DIR__) . '/../public/uploads/files/' . $file['filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // 删除帖子附件记录
            $sql = "DELETE FROM forum_post_files WHERE post_id = ?";
            $this->db->query($sql, [$postId]);
            
            // 删除回复
            $sql = "DELETE FROM forum_replies WHERE post_id = ?";
            $this->db->query($sql, [$postId]);
            
            // 删除帖子
            $sql = "DELETE FROM forum_posts WHERE id = ?";
            $this->db->query($sql, [$postId]);
            
            $this->db->commit();
            $this->jsonResponse(['success' => true, 'message' => '帖子删除成功']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->jsonResponse(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
        }
    }
    
    // 显示编辑帖子页面
    private function editPostPage() {
        $userId = $_SESSION['user_id'];
        $postId = $_GET['id'] ?? null;
        
        if (!$postId) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取帖子信息
        $post = $this->getPostInfo($postId);
        if (!$post) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 检查权限：只有作者可以编辑
        if ($post['author_id'] != $userId) {
            $this->redirect('/forum/post?id=' . $postId);
            return;
        }
        
        // 检查用户是否是论坛成员
        $isMember = $this->isForumMember($userId, $post['forum_id']);
        if (!$isMember) {
            $this->redirect('/list_forum');
            return;
        }
        
        // 获取用户信息（用于navbar）
        $user = $this->userModel->getUserById($userId);
        $rooms = $this->chatModel->getUserRooms($userId);
        $friends = $this->userModel->getFriends($userId);
        $pendingRequests = $this->userModel->getPendingRequests($userId);
        $forumInvites = $this->userModel->getUserForumInvites($userId);
        $groups = $this->getUserGroups($userId);
        $forums = $this->userModel->getUserForums($userId);
        
        $this->render('forum/editPost', [
            'post' => $post,
            'user' => $user,
            'rooms' => $rooms,
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'forumInvites' => $forumInvites,
            'groups' => $groups,
            'forums' => $forums,
            'currentTab' => 'forums',
            'currentForumId' => $post['forum_id']
        ]);
    }

    // 删除附件
    public function removeAttachment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $fileId = $_POST['file_id'] ?? null;
        
        if (!$fileId) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            // 获取附件信息
            $sql = "SELECT fpf.*, fp.author_id FROM forum_post_files fpf 
                    JOIN forum_posts fp ON fpf.post_id = fp.id 
                    WHERE fpf.id = ?";
            $file = $this->db->fetch($sql, [$fileId]);
            
            if (!$file) {
                $this->jsonResponse(['success' => false, 'message' => '附件不存在']);
                return;
            }
            
            // 检查权限：只有帖子作者可以删除附件
            if ($file['author_id'] != $userId) {
                $this->jsonResponse(['success' => false, 'message' => '权限不足']);
                return;
            }
            
            // 删除文件
            $filePath = dirname(__DIR__) . '/../public/uploads/files/' . $file['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // 删除数据库记录
            $sql = "DELETE FROM forum_post_files WHERE id = ?";
            $this->db->query($sql, [$fileId]);
            
            $this->jsonResponse(['success' => true, 'message' => '附件删除成功']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
        }
    }

    // 邀请成员
    public function inviteMember() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        $username = trim($_POST['username'] ?? '');
        
        if (!$forumId || empty($username)) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 检查用户权限（必须是创建者或管理员）
        $userRole = $this->getUserForumRole($userId, $forumId);
        if (!in_array($userRole, ['admin', 'creator'])) {
            $this->jsonResponse(['success' => false, 'message' => '权限不足']);
            return;
        }
        
        // 检查要邀请的用户是否存在
        $sql = "SELECT id FROM users WHERE username = ?";
        $invitedUser = $this->db->fetch($sql, [$username]);
        if (!$invitedUser) {
            $this->jsonResponse(['success' => false, 'message' => '用户不存在']);
            return;
        }
        
        $invitedUserId = $invitedUser['id'];
        
        // 检查是否已经是论坛成员
        if ($this->isForumMember($invitedUserId, $forumId)) {
            $this->jsonResponse(['success' => false, 'message' => '该用户已经是论坛成员']);
            return;
        }
        
        // 检查是否已经发送过邀请
        $sql = "SELECT id FROM forum_invite_requests WHERE forum_id = ? AND invited_user_id = ? AND status = 'pending'";
        $existingInvite = $this->db->fetch($sql, [$forumId, $invitedUserId]);
        if ($existingInvite) {
            $this->jsonResponse(['success' => false, 'message' => '已经向该用户发送过邀请']);
            return;
        }
        
        try {
            // 创建邀请请求
            $sql = "INSERT INTO forum_invite_requests (forum_id, invited_user_id, invited_by_user_id, status, invited_at, expires_at) 
                    VALUES (?, ?, ?, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))";
            $this->db->query($sql, [$forumId, $invitedUserId, $userId]);
            
            $this->jsonResponse(['success' => true, 'message' => '邀请发送成功']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '邀请失败: ' . $e->getMessage()]);
        }
    }
    
    // 处理邀请请求
    public function handleInvite() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $inviteId = $_POST['invite_id'] ?? null;
        $action = $_POST['action'] ?? null;
        
        if (!$inviteId || !in_array($action, ['accept', 'reject'])) {
            $this->jsonResponse(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        try {
            // 获取邀请信息
            $sql = "SELECT fir.*, f.name as forum_name FROM forum_invite_requests fir 
                    JOIN forums f ON fir.forum_id = f.id 
                    WHERE fir.id = ? AND fir.invited_user_id = ? AND fir.status = 'pending'";
            $invite = $this->db->fetch($sql, [$inviteId, $userId]);
            
            if (!$invite) {
                $this->jsonResponse(['success' => false, 'message' => '邀请不存在或已过期']);
                return;
            }
            
            // 检查邀请是否过期
            if (strtotime($invite['expires_at']) < time()) {
                $this->jsonResponse(['success' => false, 'message' => '邀请已过期']);
                return;
            }
            
            if ($action === 'accept') {
                // 检查论坛是否还有空位
                $sql = "SELECT COUNT(*) as member_count FROM forum_members WHERE forum_id = ?";
                $memberCount = $this->db->fetch($sql, [$invite['forum_id']]);
                
                $sql = "SELECT max_members FROM forums WHERE id = ?";
                $forum = $this->db->fetch($sql, [$invite['forum_id']]);
                
                if ($memberCount['member_count'] >= $forum['max_members']) {
                    $this->jsonResponse(['success' => false, 'message' => '论坛成员已满']);
                    return;
                }
                
                // 添加为论坛成员
                $sql = "INSERT INTO forum_members (forum_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())";
                $this->db->query($sql, [$invite['forum_id'], $userId]);
                
                $message = '成功加入论坛 "' . $invite['forum_name'] . '"';
            } else {
                $message = '已拒绝论坛邀请';
            }
            
            // 更新邀请状态
            $sql = "UPDATE forum_invite_requests SET status = ?, responded_at = NOW() WHERE id = ?";
            $status = $action === 'accept' ? 'accepted' : 'rejected';
            $this->db->query($sql, [$status, $inviteId]);
            
            $response = ['success' => true, 'message' => $message];
            if ($action === 'accept') {
                $response['forum_id'] = $invite['forum_id'];
            }
            
            $this->jsonResponse($response);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
        }
    }
    
    // 申请加入论坛
    public function requestJoin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '请求方法错误']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $forumId = $_POST['forum_id'] ?? null;
        
        if (!$forumId) {
            $this->jsonResponse(['success' => false, 'message' => '论坛ID不能为空']);
            return;
        }
        
        // 检查论坛是否存在
        $forum = $this->getForumInfo($forumId);
        if (!$forum) {
            $this->jsonResponse(['success' => false, 'message' => '论坛不存在']);
            return;
        }
        
        // 检查用户是否已经是论坛成员
        if ($this->isForumMember($userId, $forumId)) {
            $this->jsonResponse(['success' => false, 'message' => '您已经是该论坛的成员']);
            return;
        }
        
        // 检查是否已经有待处理的申请
        $sql = "SELECT id FROM forum_join_requests WHERE forum_id = ? AND user_id = ? AND status = 'pending'";
        $existingRequest = $this->db->fetch($sql, [$forumId, $userId]);
        if ($existingRequest) {
            $this->jsonResponse(['success' => false, 'message' => '您已经申请过加入该论坛，请等待审核']);
            return;
        }
        
        try {
            // 创建加入请求
            $sql = "INSERT INTO forum_join_requests (forum_id, user_id, status, requested_at) VALUES (?, ?, 'pending', NOW())";
            $this->db->query($sql, [$forumId, $userId]);
            
            $this->jsonResponse(['success' => true, 'message' => '申请已提交，等待管理员审核']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '申请失败: ' . $e->getMessage()]);
        }
    }

    // 获取用户的论坛邀请列表
    public function getUserForumInvites($userId) {
        $sql = "SELECT fir.*, f.name as forum_name, u.username as inviter_username, u.avatar as inviter_avatar
                FROM forum_invite_requests fir
                JOIN forums f ON fir.forum_id = f.id
                JOIN users u ON fir.invited_by_user_id = u.id
                WHERE fir.invited_user_id = ? AND fir.status = 'pending'
                ORDER BY fir.invited_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    // 返回JSON响应
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
