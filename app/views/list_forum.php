<?php
// list_forum.php - 论坛列表页面

// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();

require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/models/User.php';

// 检查用户是否已登录（会话已在router.php中启动）
if (!isset($_SESSION['user_id'])) {
    header('Location: /CHATTING/auth/login');
    exit;
}

// 为navbar组件准备数据（与其他页面保持一致）
$currentTab = 'forums';
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Chat.php';

$userModel = new User();
$chatModel = new Chat();
$user = $userModel->getUserById($_SESSION['user_id']);
$rooms = $chatModel->getUserRooms($_SESSION['user_id']);
$friends = $userModel->getFriends($_SESSION['user_id']);
$pendingRequests = $userModel->getPendingRequests($_SESSION['user_id']);

// 初始化数据库连接
$db = Database::getInstance();

// 获取用户群组列表
$groups = [];
try {
    $groupsQuery = "SELECT cr.id, cr.name, cr.avatar, cr.created_at,
                           COUNT(crm.user_id) as member_count
                    FROM chat_rooms cr
                    JOIN chat_room_members crm ON cr.id = crm.room_id
                    WHERE cr.type = 'group' AND crm.user_id = ?
                    GROUP BY cr.id
                    ORDER BY cr.name ASC";
    $groups = $db->fetchAll($groupsQuery, [$_SESSION['user_id']]);
} catch (Exception $e) {
    $groups = [];
}

// 获取用户已加入的论坛列表（用于navbar）
$forums = $userModel->getUserForums($_SESSION['user_id']);

try {
    $pdo = $db->getConnection();
    
    // 检查论坛表是否存在
    $checkTablesQuery = "SHOW TABLES LIKE 'forums'";
    $forumsTableExists = $db->query($checkTablesQuery)->rowCount() > 0;
    
    if ($forumsTableExists) {
        // 获取所有论坛列表
        $forumsQuery = "SELECT 
            f.*,
            u.username as creator_name,
            u.avatar as creator_avatar,
            (SELECT COUNT(*) FROM forum_members fm WHERE fm.forum_id = f.id) as member_count,
            (SELECT COUNT(*) FROM forum_posts fp WHERE fp.forum_id = f.id) as post_count,
            CASE WHEN EXISTS(SELECT 1 FROM forum_members fm WHERE fm.forum_id = f.id AND fm.user_id = ?) THEN 1 ELSE 0 END as is_member,
            CASE WHEN EXISTS(SELECT 1 FROM forum_join_requests fjr WHERE fjr.forum_id = f.id AND fjr.user_id = ? AND fjr.status = 'pending') THEN 1 ELSE 0 END as has_pending_request
            FROM forums f
            LEFT JOIN users u ON f.creator_id = u.id
            ORDER BY f.created_at DESC";
        
        $allForums = $db->fetchAll($forumsQuery, [$_SESSION['user_id'], $_SESSION['user_id']]);
        
        // 调试信息
        error_log("论坛数据调试 - 用户ID: " . $_SESSION['user_id']);
        error_log("论坛数据调试 - allForums 数量: " . count($allForums));
        error_log("论坛数据调试 - allForums 内容: " . json_encode($allForums));
        
        // 如果没有论坛数据，创建一些测试数据
        if (empty($allForums)) {
            error_log("没有论坛数据，尝试创建测试论坛");
            try {
                // 创建测试论坛
                $insertQuery = "INSERT INTO forums (name, description, creator_id, is_public, max_members, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $db->query($insertQuery, ['技术讨论', '分享编程技术、开发经验和学习心得', $_SESSION['user_id'], true, 1000]);
                $forumId = $db->lastInsertId();
                
                // 将创建者添加为成员
                $memberQuery = "INSERT INTO forum_members (forum_id, user_id, role, joined_at) VALUES (?, ?, 'creator', NOW())";
                $db->query($memberQuery, [$forumId, $_SESSION['user_id']]);
                
                error_log("创建测试论坛成功，ID: " . $forumId);
                
                // 重新获取论坛数据
                $allForums = $db->fetchAll($forumsQuery, [$_SESSION['user_id'], $_SESSION['user_id']]);
                error_log("重新获取后的论坛数量: " . count($allForums));
            } catch (Exception $e) {
                error_log("创建测试论坛失败: " . $e->getMessage());
            }
        }
        
        // 获取用户已加入的论坛
        $joinedForumsQuery = "SELECT 
            f.*,
            u.username as creator_name,
            u.avatar as creator_avatar,
            (SELECT COUNT(*) FROM forum_members fm WHERE fm.forum_id = f.id) as member_count,
            (SELECT COUNT(*) FROM forum_posts fp WHERE fp.forum_id = f.id) as post_count
            FROM forums f
            LEFT JOIN users u ON f.creator_id = u.id
            INNER JOIN forum_members fm ON f.id = fm.forum_id
            WHERE fm.user_id = ?
            ORDER BY f.created_at DESC";
        
        $joinedForums = $db->fetchAll($joinedForumsQuery, [$_SESSION['user_id']]);
    } else {
        // 如果论坛表不存在，返回空数组
        $allForums = [];
        $joinedForums = [];
    }
    
} catch (Exception $e) {
    error_log("Error in list_forum.php: " . $e->getMessage());
    $allForums = [];
    $joinedForums = [];
    $friends = [];
    $groups = [];
    $pendingRequests = [];
    $rooms = [];
    $user = null;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?php echo __('forum_list_title'); ?> - CHATTING</title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
    <style>
        .forum-page-container {
            display: flex;
            height: 100vh;
            background: #f8f9fa;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
            overflow-y: auto;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.9) 0%, 
                rgba(147, 51, 234, 0.85) 50%, 
                rgba(236, 72, 153, 0.9) 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 
                0 10px 30px rgba(59, 130, 246, 0.2),
                0 4px 15px rgba(147, 51, 234, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* 页面头部装饰性背景 */
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(147, 51, 234, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
            animation: floatBackground 25s ease-in-out infinite;
            z-index: -1;
        }
        
        /* 页面头部装饰性边框 */
        .page-header::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(45deg, 
                rgba(59, 130, 246, 0.3) 0%, 
                rgba(147, 51, 234, 0.3) 25%, 
                rgba(236, 72, 153, 0.3) 50%, 
                rgba(34, 197, 94, 0.3) 75%, 
                rgba(59, 130, 246, 0.3) 100%);
            border-radius: 23px;
            z-index: -2;
            animation: borderGlow 4s linear infinite;
        }
        
        .page-header:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 15px 40px rgba(59, 130, 246, 0.25),
                0 6px 20px rgba(147, 51, 234, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }
        
        .search-container {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.95) 0%, 
                rgba(248, 250, 252, 0.9) 100%);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.08),
                0 3px 10px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(59, 130, 246, 0.15);
            backdrop-filter: blur(10px);
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* 搜索容器装饰性背景 */
        .search-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(147, 51, 234, 0.06) 0%, transparent 50%);
            animation: floatBackground 35s ease-in-out infinite;
            z-index: -1;
        }
        
        .search-container:hover {
            transform: translateY(-1px);
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.1),
                0 4px 12px rgba(59, 130, 246, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            border-color: rgba(59, 130, 246, 0.25);
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 10;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        /* 页面头部装饰性表情符号 */
        .page-header-container {
            position: relative;
        }
        
        .page-header-container::before {
            content: '🌟';
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 24px;
            animation: sparkle 2.5s ease-in-out infinite;
            z-index: 10;
        }
        
        .page-header-container::after {
            content: '✨';
            position: absolute;
            bottom: 20px;
            left: 30px;
            font-size: 20px;
            animation: sparkle 3s ease-in-out infinite reverse;
            z-index: 10;
        }
        
        @keyframes sparkle {
            0%, 100% { 
                opacity: 0.3; 
                transform: scale(0.8) rotate(0deg); 
            }
            50% { 
                opacity: 1; 
                transform: scale(1.2) rotate(180deg); 
            }
        }
        
        .search-box {
            position: relative;
            margin-bottom: 15px;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 45px 15px 20px;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 30px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 10;
        }
        
        .search-input:focus {
            outline: none;
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 
                0 0 0 4px rgba(59, 130, 246, 0.1),
                0 4px 15px rgba(59, 130, 246, 0.2);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-1px);
        }
        
        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(59, 130, 246, 0.6);
            font-size: 1.2rem;
            z-index: 15;
            transition: all 0.3s ease;
        }
        
        .search-input:focus + .search-icon {
            color: rgba(59, 130, 246, 1);
            transform: translateY(-50%) scale(1.1);
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .filter-tab {
            padding: 10px 18px;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.9);
            color: #666;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .filter-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .filter-tab.active {
            border-color: rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(147, 51, 234, 0.8) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .filter-tab:hover {
            border-color: rgba(59, 130, 246, 0.4);
            color: rgba(59, 130, 246, 1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        .filter-tab:hover::before {
            left: 100%;
        }
        
        .filter-tab.active:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 1) 0%, rgba(147, 51, 234, 0.9) 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .forum-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .forum-card {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.95) 0%, 
                rgba(248, 250, 252, 0.9) 100%);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.08),
                0 3px 10px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(59, 130, 246, 0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            /* 确保卡片内容完全可见 */
            overflow: visible !important;
            min-height: auto !important;
            height: auto !important;
        }
        
        /* 装饰性背景图案 */
        .forum-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(147, 51, 234, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(236, 72, 153, 0.06) 0%, transparent 50%);
            animation: floatBackground 30s ease-in-out infinite;
            z-index: -1;
        }
        
        /* 装饰性边框光效 */
        .forum-card::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                rgba(59, 130, 246, 0.2) 0%, 
                rgba(147, 51, 234, 0.2) 25%, 
                rgba(236, 72, 153, 0.2) 50%, 
                rgba(34, 197, 94, 0.2) 75%, 
                rgba(59, 130, 246, 0.2) 100%);
            border-radius: 22px;
            z-index: -2;
            animation: borderGlow 5s linear infinite;
        }
        
        .forum-card:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 12px 35px rgba(0, 0, 0, 0.12),
                0 5px 15px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        /* 背景浮动动画 */
        @keyframes floatBackground {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-8px, -8px) rotate(0.5deg); }
            50% { transform: translate(8px, -4px) rotate(-0.5deg); }
            75% { transform: translate(-4px, 8px) rotate(0.3deg); }
        }
        
        /* 边框光效动画 */
        @keyframes borderGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .forum-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .forum-avatar-large {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            position: relative;
            overflow: hidden;
        }
        
        .forum-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .forum-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            line-height: 1.3;
        }
        
        .forum-creator {
            font-size: 0.8rem;
            color: #666;
            margin-top: 2px;
        }
        
        .forum-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .forum-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #17a2b8;
            display: block;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }
        
        .forum-actions {
            display: flex !important;
            gap: 10px;
            align-items: center;
            justify-content: center; /* 居中显示单个按钮 */
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
            /* 强制显示按钮区域 */
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            min-height: 50px !important;
        }
        
        .join-btn {
            flex: none;
            min-width: 120px;
            width: auto !important;
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            border: none !important;
            border-radius: 25px;
            cursor: pointer !important;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 999 !important;
            position: relative !important;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
            overflow: hidden;
        }
        
        .join-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .join-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
        }
        
        .join-btn:hover::before {
            left: 100%;
        }
        
        .join-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .join-btn:disabled {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.2);
        }
        
        .join-btn:disabled::before {
            display: none;
        }
        
        /* 进入论坛按钮特殊样式 */
        .view-btn {
            flex: none;
            min-width: 120px;
            width: auto !important;
            padding: 12px 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            border: none !important;
            border-radius: 25px;
            cursor: pointer !important;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 999 !important;
            position: relative !important;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            overflow: hidden;
            text-decoration: none;
            text-align: center;
        }
        
        .view-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%) !important;
            color: white !important;
            text-decoration: none;
        }
        
        .view-btn:hover::before {
            left: 100%;
        }
        
        .view-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .view-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
        }
        
        /* 按钮脉冲动画 */
        .join-btn:not(:disabled) {
            animation: pulse-glow 2s infinite;
        }
        
        .view-btn {
            animation: pulse-glow-green 2s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
            }
            50% {
                box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            }
        }
        
        @keyframes pulse-glow-green {
            0%, 100% {
                box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            }
            50% {
                box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
            }
        }
        
        /* 按钮加载状态 */
        .join-btn.loading {
            position: relative;
            color: transparent;
        }
        
        .join-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .view-btn.loading {
            position: relative;
            color: transparent;
        }
        
        .view-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        
        /* 强制显示所有按钮的通用规则 */
        .forum-card .forum-actions * {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* 确保按钮容器不被裁剪 */
        .forum-card {
            overflow: visible !important;
            max-height: none !important;
        }
        
        .pending-badge {
            background: #ffc107;
            color: #212529;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .member-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .empty-state-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-state-text {
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #17a2b8;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .forum-page-container {
                flex-direction: column;
            }
            
            .main-content {
                padding: 10px;
            }
            
            .page-header {
                padding: 15px;
            }
            
            .page-title {
                font-size: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .search-container {
                padding: 15px;
            }
            
            .search-input {
                font-size: 16px;
                padding: 12px 40px 12px 16px;
            }
            
            .forum-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .forum-card {
                padding: 15px;
            }
            
            .forum-header {
                margin-bottom: 12px;
            }
            
            .forum-avatar-large {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
            
            .forum-title {
                font-size: 1rem;
            }
            
            .forum-creator {
                font-size: 0.75rem;
            }
            
            .forum-description {
                font-size: 0.85rem;
                margin-bottom: 12px;
            }
            
            .forum-stats {
                margin-bottom: 12px;
                padding: 8px 0;
            }
            
            .stat-number {
                font-size: 1.1rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            .filter-tabs {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .filter-tab {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .forum-actions {
                flex-direction: column;
                gap: 8px;
                padding-top: 12px;
            }
            
            .join-btn {
                width: 100%;
                min-width: auto;
                padding: 14px 20px;
                font-size: 0.9rem;
                border-radius: 20px;
            }
            
            .view-btn {
                width: 100%;
                padding: 14px 20px;
                border-radius: 20px;
                font-size: 0.9rem;
            }
            
            .empty-state {
                padding: 40px 15px;
            }
            
            .empty-state-icon {
                font-size: 2.5rem;
            }
            
            .empty-state-title {
                font-size: 1.1rem;
            }
            
            .empty-state-text {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 5px;
            }
            
            .page-header {
                padding: 12px;
            }
            
            .page-title {
                font-size: 1.3rem;
            }
            
            .page-subtitle {
                font-size: 0.85rem;
            }
            
            .search-container {
                padding: 12px;
            }
            
            .search-input {
                padding: 10px 35px 10px 12px;
                font-size: 16px;
            }
            
            .search-icon {
                right: 12px;
                font-size: 1.1rem;
            }
            
            .forum-card {
                padding: 12px;
            }
            
            .forum-header {
                gap: 10px;
                margin-bottom: 10px;
            }
            
            .forum-avatar-large {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .forum-title {
                font-size: 0.95rem;
            }
            
            .forum-creator {
                font-size: 0.7rem;
            }
            
            .forum-description {
                font-size: 0.8rem;
                margin-bottom: 10px;
            }
            
            .forum-stats {
                margin-bottom: 10px;
                padding: 6px 0;
            }
            
            .stat-number {
                font-size: 1rem;
            }
            
            .stat-label {
                font-size: 0.7rem;
            }
            
            .filter-tabs {
                gap: 6px;
            }
            
            .filter-tab {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .join-btn {
                padding: 12px 16px;
                font-size: 0.85rem;
                border-radius: 18px;
            }
            
            .view-btn {
                padding: 12px 16px;
                font-size: 0.85rem;
                border-radius: 18px;
            }
            
            .empty-state {
                padding: 30px 10px;
            }
            
            .empty-state-icon {
                font-size: 2rem;
            }
            
            .empty-state-title {
                font-size: 1rem;
            }
            
            .empty-state-text {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="forum-page-container">
        <!-- 包含导航栏 -->
        <?php 
        include BASE_PATH . '/app/views/components/navbar.php'; 
        ?>

        <div class="main-content">
        <!-- 页面标题 -->
        <div class="page-header-container">
            <div class="page-header">
            <h1 class="page-title">
                💬 <?php echo __('forum_plaza_title'); ?>
                <span class="member-badge"><?php echo count($joinedForums); ?> <?php echo __('forum_joined_count'); ?></span>
            </h1>
            <p class="page-subtitle"><?php echo __('forum_discover_join'); ?></p>
            </div>
        </div>

        <!-- 搜索和筛选 -->
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="search-input" id="forumSearchInput" placeholder="<?php echo __('forum_search_placeholder'); ?>">
                <span class="search-icon">🔍</span>
            </div>
            
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all"><?php echo __('forum_filter_all'); ?></button>
                <button class="filter-tab" data-filter="joined"><?php echo __('forum_filter_joined'); ?></button>
                <button class="filter-tab" data-filter="available"><?php echo __('forum_filter_available'); ?></button>
                <button class="filter-tab" data-filter="pending"><?php echo __('forum_filter_pending'); ?></button>
            </div>
        </div>

        <!-- 论坛列表 -->
        <div id="forumContainer">
            <?php if (empty($allForums) && !isset($forumsTableExists)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🚀</div>
                    <h3 class="empty-state-title"><?php echo __('forum_not_initialized'); ?></h3>
                    <p class="empty-state-text">
                        <?php echo __('forum_init_description'); ?><br>
                        <code><?php echo __('forum_init_sql_file'); ?></code>
                    </p>
                    <div style="margin-top: 20px;">
                        <button class="view-btn" onclick="location.reload()"><?php echo __('forum_refresh_page'); ?></button>
                    </div>
                </div>
            <?php else: ?>
                <div class="loading">
                    <div class="spinner"></div>
                    <p><?php echo __('forum_loading'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>

    <script>
        // 论坛数据
        const allForums = <?php echo json_encode($allForums); ?>;
        const joinedForums = <?php echo json_encode($joinedForums); ?>;
        
        // 调试信息
        console.log('论坛数据调试:');
        console.log('allForums:', allForums);
        console.log('joinedForums:', joinedForums);
        console.log('allForums 长度:', allForums ? allForums.length : 'null');
        
        let currentFilter = 'all';
        let filteredForums = allForums;
        
        // 初始化页面
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM加载完成，开始渲染论坛');
            
            // 如果没有论坛数据，创建测试数据
            if (!allForums || allForums.length === 0) {
                console.log('没有论坛数据，创建测试数据');
                filteredForums = [{
                    id: 1,
                    name: '测试论坛1',
                    description: '这是第一个测试论坛，用于展示申请加入功能',
                    creator_name: '管理员',
                    member_count: 5,
                    post_count: 10,
                    created_at: new Date().toISOString(),
                    is_member: 0,
                    has_pending_request: 0,
                    avatar: null
                }, {
                    id: 2,
                    name: '测试论坛2',
                    description: '这是第二个测试论坛，用户已申请加入',
                    creator_name: '用户A',
                    member_count: 8,
                    post_count: 25,
                    created_at: new Date().toISOString(),
                    is_member: 0,
                    has_pending_request: 1,
                    avatar: null
                }, {
                    id: 3,
                    name: '测试论坛3',
                    description: '这是第三个测试论坛，用户已加入',
                    creator_name: '用户B',
                    member_count: 12,
                    post_count: 50,
                    created_at: new Date().toISOString(),
                    is_member: 1,
                    has_pending_request: 0,
                    avatar: null
                }];
                console.log('测试数据:', filteredForums);
            }
            
            renderForums();
            initializeSearch();
            initializeFilters();
        });
        
        // 渲染论坛列表
        function renderForums() {
            console.log('renderForums() 被调用');
            const container = document.getElementById('forumContainer');
            console.log('容器元素:', container);
            console.log('filteredForums:', filteredForums);
            console.log('filteredForums 长度:', filteredForums ? filteredForums.length : 'null');
            
            if (!filteredForums || filteredForums.length === 0) {
                console.log('没有论坛数据，显示空状态');
                container.innerHTML = getEmptyStateHTML();
                return;
            }
            
            console.log('开始生成论坛卡片HTML');
            const forumsHTML = filteredForums.map(forum => {
                console.log('处理论坛:', forum);
                return createForumCard(forum);
            }).join('');
            console.log('生成的HTML:', forumsHTML);
            container.innerHTML = `<div class="forum-grid">${forumsHTML}</div>`;
            
            // 强制显示申请加入按钮
            setTimeout(() => {
                const buttons = document.querySelectorAll('.forum-actions .join-btn');
                buttons.forEach(btn => {
                    btn.style.display = 'inline-block';
                    btn.style.visibility = 'visible';
                    btn.style.opacity = '1';
                    btn.style.zIndex = '999';
                    btn.style.position = 'relative';
                    console.log('强制显示申请加入按钮:', btn.textContent, btn.style.display);
                });
                
                const actions = document.querySelectorAll('.forum-actions');
                actions.forEach(action => {
                    action.style.display = 'flex';
                    action.style.visibility = 'visible';
                    action.style.opacity = '1';
                    action.style.height = 'auto';
                    action.style.minHeight = '50px';
                    action.style.justifyContent = 'center'; // 居中显示单个按钮
                    console.log('强制显示按钮区域:', action);
                });
            }, 100);
            console.log('论坛列表渲染完成');
        }
        
        // 创建论坛卡片HTML
        function createForumCard(forum) {
            const isJoined = forum.is_member == 1;
            const hasPendingRequest = forum.has_pending_request == 1;
            
            let statusBadge = '';
            let actionButton = '';
            
            if (isJoined) {
                statusBadge = '<span class="member-badge"><?php echo __('forum_joined'); ?></span>';
                actionButton = `<a href="/CHATTING/forum/view?id=${forum.id}" class="view-btn">🚀 <?php echo __('forum_enter_forum'); ?></a>`;
            } else if (hasPendingRequest) {
                statusBadge = '<span class="pending-badge"><?php echo __('forum_pending'); ?></span>';
                actionButton = `<button class="join-btn" disabled>⏳ <?php echo __('forum_applied'); ?></button>`;
            } else {
                actionButton = `<button class="join-btn" onclick="requestJoinForum(${forum.id})">➕ <?php echo __('forum_join_forum'); ?></button>`;
            }
            
            return `
                <div class="forum-card" data-forum-id="${forum.id}">
                    <div class="forum-header">
                        <div class="forum-avatar-large">
                            ${forum.avatar && forum.avatar !== 'default_forum_avatar.png' ? 
                                `<img src="/CHATTING/public/uploads/avatars/${forum.avatar}" alt="论坛头像">` :
                                forum.name.charAt(0).toUpperCase()
                            }
                        </div>
                        <div>
                            <h3 class="forum-title">${escapeHtml(forum.name)}</h3>
                            <p class="forum-creator"><?php echo __('forum_creator_label'); ?>: ${escapeHtml(forum.creator_name || '<?php echo __('profile_unknown'); ?>')}</p>
                            ${statusBadge}
                        </div>
                    </div>
                    
                    <div class="forum-description">
                        ${escapeHtml(forum.description || '<?php echo __('forum_no_description'); ?>')}
                    </div>
                    
                    <div class="forum-stats">
                        <div class="stat-item">
                            <span class="stat-number">${forum.member_count || 0}</span>
                            <span class="stat-label"><?php echo __('forum_members_label'); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">${forum.post_count || 0}</span>
                            <span class="stat-label"><?php echo __('forum_posts_label'); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">${formatDate(forum.created_at)}</span>
                            <span class="stat-label"><?php echo __('forum_created_time'); ?></span>
                        </div>
                    </div>
                    
                    <div class="forum-actions">
                        ${actionButton}
                    </div>
                </div>
            `;
        }
        
        // 获取空状态HTML
        function getEmptyStateHTML() {
            const messages = {
                all: { icon: '💬', title: '<?php echo __('forum_no_forums'); ?>', text: '<?php echo __('forum_no_forums_message'); ?>' },
                joined: { icon: '👥', title: '<?php echo __('forum_no_joined'); ?>', text: '<?php echo __('forum_no_joined_message'); ?>' },
                available: { icon: '🔍', title: '<?php echo __('forum_no_available'); ?>', text: '<?php echo __('forum_no_available_message'); ?>' },
                pending: { icon: '⏳', title: '<?php echo __('forum_no_pending'); ?>', text: '<?php echo __('forum_no_pending_message'); ?>' }
            };
            
            const message = messages[currentFilter] || messages.all;
            
            return `
                <div class="empty-state">
                    <div class="empty-state-icon">${message.icon}</div>
                    <h3 class="empty-state-title">${message.title}</h3>
                    <p class="empty-state-text">${message.text}</p>
                </div>
            `;
        }
        
        // 初始化搜索功能
        function initializeSearch() {
            const searchInput = document.getElementById('forumSearchInput');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = this.value.toLowerCase().trim();
                    filterForums(query);
                }, 300);
            });
        }
        
        // 初始化筛选功能
        function initializeFilters() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // 更新按钮状态
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // 更新筛选
                    currentFilter = this.dataset.filter;
                    applyFilter();
                });
            });
        }
        
        // 筛选论坛
        function filterForums(searchQuery = '') {
            let forums = allForums;
            
            // 应用搜索筛选
            if (searchQuery) {
                forums = forums.filter(forum => 
                    forum.name.toLowerCase().includes(searchQuery) ||
                    (forum.description && forum.description.toLowerCase().includes(searchQuery)) ||
                    (forum.creator_name && forum.creator_name.toLowerCase().includes(searchQuery))
                );
            }
            
            // 应用类型筛选
            switch (currentFilter) {
                case 'joined':
                    forums = forums.filter(forum => forum.is_member == 1);
                    break;
                case 'available':
                    forums = forums.filter(forum => forum.is_member == 0 && forum.has_pending_request == 0);
                    break;
                case 'pending':
                    forums = forums.filter(forum => forum.has_pending_request == 1);
                    break;
                case 'all':
                default:
                    // 显示所有论坛
                    break;
            }
            
            filteredForums = forums;
            renderForums();
        }
        
        // 应用筛选
        function applyFilter() {
            const searchInput = document.getElementById('forumSearchInput');
            filterForums(searchInput.value.toLowerCase().trim());
        }
        
        // 申请加入论坛
        function requestJoinForum(forumId) {
            if (!confirm('<?php echo __('forum_confirm_join'); ?>')) {
                return;
            }
            
            // 找到对应的按钮并添加加载状态
            const button = document.querySelector(`[data-forum-id="${forumId}"] .join-btn`);
            if (button) {
                button.classList.add('loading');
                button.disabled = true;
            }
            
            fetch('/CHATTING/forum/requestJoin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `forum_id=${forumId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 显示成功消息
                    showSuccessMessage('<?php echo __('forum_join_success'); ?>');
                    
                    // 更新按钮状态
                    if (button) {
                        button.classList.remove('loading');
                        button.textContent = '⏳ <?php echo __('forum_applied'); ?>';
                        button.disabled = true;
                        button.style.background = 'linear-gradient(135deg, #6c757d 0%, #5a6268 100%)';
                    }
                    
                    // 延迟刷新页面以更新状态
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    // 恢复按钮状态
                    if (button) {
                        button.classList.remove('loading');
                        button.disabled = false;
                    }
                    showErrorMessage('<?php echo __('forum_join_failed'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // 恢复按钮状态
                if (button) {
                    button.classList.remove('loading');
                    button.disabled = false;
                }
                showErrorMessage('<?php echo __('forum_join_failed_retry'); ?>');
            });
        }
        
        // 显示成功消息
        function showSuccessMessage(message) {
            showNotification(message, 'success');
        }
        
        // 显示错误消息
        function showErrorMessage(message) {
            showNotification(message, 'error');
        }
        
        // 通知函数
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            
            // 添加样式
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
                max-width: 350px;
                word-wrap: break-word;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            `;
            
            // 根据类型设置颜色
            switch (type) {
                case 'success':
                    notification.style.backgroundColor = '#28a745';
                    break;
                case 'error':
                    notification.style.backgroundColor = '#dc3545';
                    break;
                case 'warning':
                    notification.style.backgroundColor = '#ffc107';
                    notification.style.color = '#212529';
                    break;
                default:
                    notification.style.backgroundColor = '#17a2b8';
            }
            
            // 添加动画样式
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // 添加到页面
            document.body.appendChild(notification);
            
            // 3秒后自动移除
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        // 工具函数
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays === 1) {
                return '<?php echo __('forum_today'); ?>';
            } else if (diffDays === 2) {
                return '<?php echo __('forum_yesterday'); ?>';
            } else if (diffDays <= 7) {
                return `${diffDays}<?php echo __('forum_days_ago'); ?>`;
            } else {
                return date.toLocaleDateString('zh-CN');
            }
        }
    </script>
</body>
</html>
