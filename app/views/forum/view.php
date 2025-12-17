<?php
// 保护论坛变量不被意外修改
$originalForum = $forum;
$originalForumId = $forum['id'];
$originalForumName = $forum['name'];
?>
<?php
// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?php echo str_replace('{name}', htmlspecialchars($forum['name']), __('page_title_forum')); ?></title>
    <!-- 防缓存头部 -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/CHATTING/public/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* 强制刷新头像样式 - 时间戳: <?php echo time(); ?> - 只作用于内容区域 */
        .forum-container .forum-avatar {
            width: 200px !important;
            height: 200px !important;
            border-radius: 50%;
            border: 6px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .forum-container .forum-avatar-default {
            width: 200px !important;
            height: 200px !important;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            font-weight: bold;
            border: 6px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            backdrop-filter: blur(15px);
        }
    </style>
    <script>
        // 强制清除所有缓存
        console.log('页面加载，当前URL:', window.location.href);
        console.log('URL参数:', new URLSearchParams(window.location.search).get('id'));
        
        // 检查URL参数与显示内容是否匹配
        const urlId = new URLSearchParams(window.location.search).get('id');
        const displayedForumId = <?php echo $forum['id']; ?>;
        const displayedForumName = '<?php echo addslashes($forum['name']); ?>';
        
        console.log('URL参数ID:', urlId);
        console.log('显示论坛ID:', displayedForumId);
        console.log('显示论坛名称:', displayedForumName);
        
        if (urlId && urlId !== displayedForumId.toString()) {
            console.error('URL参数与显示内容不匹配！强制刷新页面');
            // 强制清除所有缓存并重新加载
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    names.forEach(function(name) {
                        caches.delete(name);
                    });
                });
            }
            // 使用时间戳强制刷新
            const newUrl = window.location.origin + window.location.pathname + '?id=' + urlId + '&t=' + Date.now();
            window.location.replace(newUrl);
        }
        
        // 如果是刷新操作，也清除缓存
        if (window.performance && window.performance.navigation.type === 1) {
            console.log('页面刷新，清除缓存');
            const currentId = new URLSearchParams(window.location.search).get('id');
            if (currentId) {
                window.location.href = window.location.origin + window.location.pathname + '?id=' + currentId + '&t=' + Date.now();
            }
        }
    </script>
    <style>
        .forum-container {
            height: 100%;
            padding: 20px;
            overflow-y: auto;
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            .forum-container {
                padding: 10px;
            }
            
            .forum-header {
                padding: 30px 20px;
                margin-bottom: 20px;
            }
            
            .forum-title-container {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .forum-title-section {
                align-items: center;
            }
            
            .forum-title {
                font-size: 1.8rem;
                margin-bottom: 10px;
            }
            
            .forum-title-section .forum-description {
                font-size: 1.2rem;
                margin-bottom: 20px;
            }
            
            .forum-container .forum-avatar {
                width: 120px !important;
                height: 120px !important;
                border: 4px solid rgba(255, 255, 255, 0.4);
            }
            
            .forum-container .forum-avatar-default {
                width: 120px !important;
                height: 120px !important;
                font-size: 3rem;
                border: 4px solid rgba(255, 255, 255, 0.4);
            }
            
            .forum-title {
                font-size: 1.8rem;
            }
            
            
            .forum-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .stat-item {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.9rem;
            }
            
            .forum-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 14px 20px;
            }
            
            .posts-container {
                gap: 15px;
            }
            
            .post-card {
                padding: 20px;
            }
            
            .post-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .post-title {
                font-size: 1.1rem;
            }
            
            .post-meta {
                font-size: 0.8rem;
            }
            
            .post-content {
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            .post-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .post-actions .btn {
                width: 100%;
                min-height: 44px;
            }
        }
        
        @media (max-width: 480px) {
            .forum-container {
                padding: 5px;
            }
            
            .forum-header {
                padding: 20px 15px;
                margin-bottom: 15px;
            }
            
            .forum-container .forum-avatar {
                width: 100px !important;
                height: 100px !important;
            }
            
            .forum-container .forum-avatar-default {
                width: 100px !important;
                height: 100px !important;
                font-size: 2.5rem;
            }
            
            .forum-title {
                font-size: 1.5rem;
                margin-bottom: 8px;
            }
            
            .forum-title-section .forum-description {
                font-size: 1.1rem;
                margin-bottom: 15px;
            }
            
            .stat-item {
                padding: 12px;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .stat-label {
                font-size: 0.85rem;
            }
            
            .post-card {
                padding: 15px;
            }
            
            .post-title {
                font-size: 1rem;
            }
            
            .post-meta {
                font-size: 0.75rem;
            }
            
            .post-content {
                font-size: 0.85rem;
            }
            
            .btn {
                min-height: 46px;
                font-size: 16px;
                padding: 12px 16px;
            }
            
            /* 移动端搜索样式 */
            .search-container {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .search-input {
                padding: 10px 40px 10px 14px;
                font-size: 16px;
            }
            
            .search-filters {
                gap: 6px;
            }
            
            .filter-btn {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .search-container {
                padding: 12px;
                margin-bottom: 12px;
            }
            
            .search-input {
                padding: 8px 35px 8px 12px;
                font-size: 16px;
            }
            
            .search-filters {
                gap: 4px;
            }
            
            .filter-btn {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
        }
        
        .forum-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.9) 0%, 
                rgba(147, 51, 234, 0.85) 50%, 
                rgba(236, 72, 153, 0.9) 100%);
            color: white;
            padding: 45px;
            border-radius: 25px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 15px 40px rgba(59, 130, 246, 0.25),
                0 8px 20px rgba(147, 51, 234, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* 装饰性背景图案 - 更深的颜色 */
        .forum-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(147, 51, 234, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(236, 72, 153, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 60% 30%, rgba(34, 197, 94, 0.15) 0%, transparent 50%);
            animation: floatBackground 25s ease-in-out infinite;
            z-index: -1;
        }
        
        /* 装饰性边框光效 - 更深的颜色 */
        .forum-header::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(45deg, 
                rgba(59, 130, 246, 0.4) 0%, 
                rgba(147, 51, 234, 0.4) 25%, 
                rgba(236, 72, 153, 0.4) 50%, 
                rgba(34, 197, 94, 0.4) 75%, 
                rgba(59, 130, 246, 0.4) 100%);
            border-radius: 28px;
            z-index: -2;
            animation: borderGlow 4s linear infinite;
        }
        
        /* 悬停效果 */
        .forum-header:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 20px 50px rgba(59, 130, 246, 0.3),
                0 10px 25px rgba(147, 51, 234, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        /* 装饰性表情符号 - 使用容器 */
        .forum-header-container {
            position: relative;
        }
        
        .forum-header-container::before {
            content: '🌟';
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 24px;
            animation: sparkle 2.5s ease-in-out infinite;
            z-index: 10;
        }
        
        .forum-header-container::after {
            content: '✨';
            position: absolute;
            bottom: 20px;
            left: 30px;
            font-size: 20px;
            animation: sparkle 3s ease-in-out infinite reverse;
            z-index: 10;
        }
        
        .forum-info {
            position: relative;
            z-index: 100;
        }
        
        .forum-title-container {
            display: flex;
            align-items: flex-start;
            gap: 35px;
            margin-bottom: 20px;
        }
        
        .forum-title-section {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .forum-avatar-container {
            flex-shrink: 0;
        }
        
        .forum-container .forum-avatar {
            width: 200px !important;
            height: 200px !important;
            border-radius: 50%;
            border: 6px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .forum-container .forum-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 45px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .forum-container .forum-avatar-default {
            width: 200px !important;
            height: 200px !important;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            font-weight: bold;
            border: 6px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            backdrop-filter: blur(15px);
        }
        
        .forum-container .forum-avatar-default:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: scale(1.05);
            box-shadow: 0 12px 45px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .forum-title {
            font-size: 3rem;
            font-weight: 800;
            margin: 0 0 15px 0;
            text-shadow: 0 3px 6px rgba(0,0,0,0.4);
            letter-spacing: -0.5px;
            flex: 1;
        }
        
        .forum-title-section .forum-description {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 25px;
            line-height: 1.5;
            font-weight: 400;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            font-style: italic;
        }
        
        .forum-stats {
            display: flex;
            gap: 30px;
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.25);
            padding: 8px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            color: white;
            font-weight: 500;
        }
        
        .stat-item:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
            color: white;
        }
        
        .stat-item .icon {
            font-size: 1.2rem;
        }
        
        .stat-item .value {
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        .creator-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px 20px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .creator-info:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: white;
        }
        
        .creator-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .creator-details {
            display: flex;
            flex-direction: column;
        }
        
        .creator-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 2px;
            color: white;
            font-weight: 500;
        }
        
        .creator-name {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        /* 全新的发表帖子按钮样式 */
        .new-post-buttons-container {
            margin: 25px 0;
            padding: 0;
            position: relative;
            z-index: 1000;
            width: 100%;
            display: block;
            background: transparent;
        }
        
        .new-post-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            padding: 25px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 248, 255, 0.9) 100%);
            border-radius: 25px;
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.1),
                0 4px 15px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(59, 130, 246, 0.2);
            backdrop-filter: blur(15px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* 装饰性背景图案 */
        .new-post-buttons::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(147, 51, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
            animation: floatBackground 20s ease-in-out infinite;
            z-index: -1;
        }
        
        /* 装饰性边框光效 */
        .new-post-buttons::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                rgba(59, 130, 246, 0.3) 0%, 
                rgba(147, 51, 234, 0.3) 25%, 
                rgba(236, 72, 153, 0.3) 50%, 
                rgba(34, 197, 94, 0.3) 75%, 
                rgba(59, 130, 246, 0.3) 100%);
            border-radius: 27px;
            z-index: -2;
            animation: borderGlow 3s linear infinite;
        }
        
        /* 悬停效果 */
        .new-post-buttons:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 15px 40px rgba(0, 0, 0, 0.15),
                0 6px 20px rgba(59, 130, 246, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        /* 背景浮动动画 */
        @keyframes floatBackground {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-10px, -10px) rotate(1deg); }
            50% { transform: translate(10px, -5px) rotate(-1deg); }
            75% { transform: translate(-5px, 10px) rotate(0.5deg); }
        }
        
        /* 边框光效动画 */
        @keyframes borderGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* 装饰性粒子效果 */
        .new-post-buttons-container {
            position: relative;
        }
        
        .new-post-buttons-container::before {
            content: '✨';
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 20px;
            animation: sparkle 2s ease-in-out infinite;
            z-index: 10;
        }
        
        .new-post-buttons-container::after {
            content: '💫';
            position: absolute;
            bottom: 15px;
            left: 25px;
            font-size: 16px;
            animation: sparkle 2.5s ease-in-out infinite reverse;
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
        
        .new-create-post-btn,
        .new-quick-post-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 15px 25px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            z-index: 1001;
            min-width: 160px;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
        }
        
        .new-create-post-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: 3px solid #2E7D32;
        }
        
        .new-create-post-btn:hover {
            background: linear-gradient(135deg, #45a049 0%, #4CAF50 100%);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
            border-color: #1B5E20;
        }
        
        .new-quick-post-btn {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            border: 3px solid #0D47A1;
        }
        
        .new-quick-post-btn:hover {
            background: linear-gradient(135deg, #1976D2 0%, #2196F3 100%);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
            border-color: #0277BD;
        }
        
        .btn-icon {
            font-size: 18px;
            line-height: 1;
        }
        
        .btn-text {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        /* 确保按钮在任何情况下都可见 */
        .new-create-post-btn:focus,
        .new-quick-post-btn:focus {
            outline: 3px solid #FFD700;
            outline-offset: 2px;
        }
        
        .new-create-post-btn:active,
        .new-quick-post-btn:active {
            transform: translateY(-1px) scale(1.02);
        }
        
        .posts-container {
            background: transparent;
            border-radius: 16px;
            overflow: visible;
            padding: 0;
        }
        
        .posts-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .posts-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .posts-count {
            color: #34495e;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .posts-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .post-item {
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(135, 206, 250, 0.2);
        }
        
        .post-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: rgba(135, 206, 250, 0.4);
        }
        
        /* 不同颜色的帖子卡片变体 */
        .post-item:nth-child(1) {
            background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
            border-color: rgba(135, 206, 250, 0.2);
        }
        
        .post-item:nth-child(2) {
            background: linear-gradient(135deg, #f0fff0 0%, #ffffff 100%);
            border-color: rgba(144, 238, 144, 0.2);
        }
        
        .post-item:nth-child(3) {
            background: linear-gradient(135deg, #fff8f0 0%, #ffffff 100%);
            border-color: rgba(255, 218, 185, 0.2);
        }
        
        .post-item:nth-child(4) {
            background: linear-gradient(135deg, #f8f0ff 0%, #ffffff 100%);
            border-color: rgba(221, 160, 221, 0.2);
        }
        
        .post-item:nth-child(5) {
            background: linear-gradient(135deg, #f0ffff 0%, #ffffff 100%);
            border-color: rgba(175, 238, 238, 0.2);
        }
        
        .post-item:nth-child(6) {
            background: linear-gradient(135deg, #fff0f5 0%, #ffffff 100%);
            border-color: rgba(255, 182, 193, 0.2);
        }
        
        /* 循环使用颜色 */
        .post-item:nth-child(7) {
            background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
            border-color: rgba(135, 206, 250, 0.2);
        }
        
        .post-item:nth-child(8) {
            background: linear-gradient(135deg, #f0fff0 0%, #ffffff 100%);
            border-color: rgba(144, 238, 144, 0.2);
        }
        
        .post-item:nth-child(9) {
            background: linear-gradient(135deg, #fff8f0 0%, #ffffff 100%);
            border-color: rgba(255, 218, 185, 0.2);
        }
        
        .post-item:nth-child(10) {
            background: linear-gradient(135deg, #f8f0ff 0%, #ffffff 100%);
            border-color: rgba(221, 160, 221, 0.2);
        }
        
        
        .post-item.pinned {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border: 2px solid rgba(255, 193, 7, 0.3);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.25);
            position: relative;
        }
        
        .post-item.pinned::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%);
            border-radius: 12px 12px 0 0;
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .post-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.4;
            transition: color 0.3s ease;
        }
        
        .post-item:hover .post-title {
            color: #667eea;
        }
        
        .post-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.85rem;
            color: #34495e;
            font-weight: 500;
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .author-avatar {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .post-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
            font-size: 0.8rem;
            color: #34495e;
            font-weight: 500;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .pin-indicator {
            background: #ffc107;
            color: #856404;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .post-content-preview {
            color: #34495e;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 400;
        }
        
        .post-media-preview {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }
        
        .media-count {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 500;
        }
        
        /* 媒体预览网格样式 */
        .media-preview-grid {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .media-preview-item {
            position: relative;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            flex-shrink: 0;
        }
        
        .media-preview-item:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .image-preview,
        .video-preview {
            width: 60px;
            height: 60px;
        }
        
        .image-preview img,
        .video-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .video-preview video {
            background: #000;
        }
        
        .media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            border-radius: 6px;
        }
        
        .media-preview-item:hover .media-overlay {
            opacity: 1;
        }
        
        .media-icon {
            color: white;
            font-size: 16px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        
        .file-preview {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4px;
        }
        
        .file-icon-large {
            font-size: 18px;
            margin-bottom: 2px;
        }
        
        .file-name-small {
            font-size: 8px;
            color: #666;
            word-break: break-all;
            line-height: 1;
        }
        
        .more-preview {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 600;
        }
        
        .more-count {
            font-size: 14px;
            font-weight: bold;
        }
        
        .more-text {
            font-size: 8px;
            opacity: 0.9;
        }
        
        /* 回复显示样式 */
        .post-replies-preview {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .replies-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .replies-preview-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .replies-preview-count {
            font-size: 0.8rem;
            color: #999;
        }
        
        .replies-preview-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .reply-preview-item {
            padding: 8px 0;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        
        .reply-preview-item:last-child {
            border-bottom: none;
        }
        
        .reply-preview-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .reply-preview-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .reply-preview-content {
            flex: 1;
            min-width: 0;
        }
        
        .reply-preview-author {
            font-size: 0.75rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }
        
        .reply-preview-text {
            font-size: 0.75rem;
            color: #666;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .reply-preview-time {
            font-size: 0.7rem;
            color: #999;
            margin-top: 3px;
        }
        
        .reply-preview-more {
            text-align: center;
            padding: 8px 0;
            font-size: 0.75rem;
            color: #667eea;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .reply-preview-more:hover {
            background-color: #f0f4ff;
        }
        
        /* 帖子卡片响应式设计 */
        @media (max-width: 768px) {
            .post-item {
                padding: 20px;
                margin-bottom: 12px;
                border-radius: 10px;
            }
            
            .posts-header {
                padding: 20px;
                border-radius: 12px 12px 0 0;
                margin-bottom: 15px;
            }
            
            .posts-list {
                gap: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .post-item {
                padding: 15px;
                margin-bottom: 10px;
                border-radius: 8px;
            }
            
            .posts-header {
                padding: 15px;
                border-radius: 10px 10px 0 0;
                margin-bottom: 12px;
            }
            
            .posts-list {
                gap: 10px;
            }
        }
        
        /* 回复预览响应式设计 */
        @media (max-width: 768px) {
            .post-replies-preview {
                margin-top: 12px;
                padding-top: 12px;
            }
            
            .reply-preview-item {
                padding: 6px 0;
                gap: 6px;
            }
            
            .reply-preview-avatar {
                width: 20px;
                height: 20px;
                font-size: 0.5rem;
            }
            
            .reply-preview-author {
                font-size: 0.7rem;
            }
            
            .reply-preview-text {
                font-size: 0.7rem;
                -webkit-line-clamp: 1;
            }
            
            .reply-preview-time {
                font-size: 0.65rem;
            }
            
            .replies-preview-title {
                font-size: 0.8rem;
            }
            
            .replies-preview-count {
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 480px) {
            .post-replies-preview {
                margin-top: 10px;
                padding-top: 10px;
            }
            
            .reply-preview-item {
                padding: 5px 0;
                gap: 5px;
            }
            
            .reply-preview-avatar {
                width: 18px;
                height: 18px;
                font-size: 0.45rem;
            }
            
            .reply-preview-author {
                font-size: 0.65rem;
            }
            
            .reply-preview-text {
                font-size: 0.65rem;
            }
            
            .reply-preview-time {
                font-size: 0.6rem;
            }
        }
        
        /* 媒体预览模态框样式 */
        .media-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }
        
        .media-modal-content {
            position: relative;
            margin: auto;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .media-modal img,
        .media-modal video {
            width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .media-modal-close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: #fff;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10001;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        
        .media-modal-close:hover {
            background: rgba(0,0,0,0.8);
        }
        
        .empty-posts {
            text-align: center;
            padding: 60px 20px;
            color: #34495e;
        }
        
        .empty-posts-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-posts h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .empty-posts p {
            margin-bottom: 20px;
            color: #34495e;
            font-weight: 500;
        }
        
        .btn-start-discussion {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-start-discussion:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        /* 搜索功能样式 */
        .search-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 15px;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }
        
        .search-clear-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .search-clear-btn:hover {
            background: #c82333;
            transform: translateY(-50%) scale(1.1);
        }
        
        .search-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            background: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-1px);
        }
        
        .filter-btn.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .filter-btn.active:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
        
        /* 搜索结果高亮 */
        .search-highlight {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: 600;
            color: #856404;
        }
        
        /* 搜索统计信息 */
        .search-results-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #64b5f6;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #1565c0;
            font-weight: 500;
            display: none;
        }
        
        .search-results-info.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* 无搜索结果样式 */
        .no-search-results {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        
        .no-search-results-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-search-results h3 {
            margin-bottom: 10px;
            color: #495057;
            font-weight: 600;
        }
        
        .no-search-results p {
            margin-bottom: 20px;
            color: #6c757d;
        }
        
        .btn-clear-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-clear-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- 引入侧边栏组件 -->
        <?php 
        include __DIR__ . '/../components/navbar.php';
        
        // 如果变量被修改，恢复原始值
        if ($forum['id'] != $originalForumId || $forum['name'] != $originalForumName) {
            $forum = $originalForum;
        }
        ?>
        
        <!-- 论坛内容区域 -->
        <div class="chat-area">
            <div class="forum-container">
                <!-- 论坛头部信息 -->
                <div class="forum-header-container">
                    <div class="forum-header">
                    <div class="forum-info">
                        <!-- 论坛标题和头像 -->
                        <div class="forum-title-container">
                            <!-- 论坛头像 -->
                            <div class="forum-avatar-container">
                                <?php 
                                if (!empty($forum['avatar']) && $forum['avatar'] !== 'default_forum_avatar.png' && file_exists(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $forum['avatar'])) {
                                    $timestamp = filemtime(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $forum['avatar']);
                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($forum['avatar']) . '?t=' . $timestamp . '" alt="' . __('avatar_forum') . '" class="forum-avatar">';
                                } else {
                                    echo '<div class="forum-avatar-default">' . strtoupper(substr($forum['name'], 0, 1)) . '</div>';
                                }
                                ?>
                            </div>
                            <div class="forum-title-section">
                                <h1 class="forum-title"><?php echo htmlspecialchars($forum['name']); ?></h1>
                                <p class="forum-description"><?php echo htmlspecialchars($forum['signature'] ?? $forum['description'] ?? __('forum_no_signature')); ?></p>
                            </div>
                        </div>
                        <!-- 创建者信息 -->
                        <?php if (isset($forum['creator_name']) || isset($forum['creator_username'])): ?>
                        <div class="creator-info">
                            <div class="creator-avatar">
                                <?php 
                                $creatorName = $forum['creator_name'] ?? $forum['creator_username'] ?? 'Unknown';
                                $creatorAvatar = $forum['creator_avatar'] ?? null;
                                if (!empty($creatorAvatar) && $creatorAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $creatorAvatar)) {
                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($creatorAvatar) . '" alt="' . __('avatar_creator') . '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
                                } else {
                                    echo strtoupper(substr($creatorName, 0, 1));
                                }
                                ?>
                            </div>
                            <div class="creator-details">
                                <div class="creator-label"><?php echo __('forum_creator'); ?></div>
                                <div class="creator-name"><?php echo htmlspecialchars($creatorName); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="forum-stats">
                            <div class="stat-item">
                                <span class="icon">👥</span>
                                <span class="value"><?php echo $forum['member_count']; ?></span>
                                <span><?php echo __('forum_members_count'); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="icon">📝</span>
                                <span class="value"><?php echo $forum['post_count']; ?></span>
                                <span><?php echo __('forum_posts_count'); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="icon">📅</span>
                                <span><?php echo __('forum_created_at'); ?> <?php echo date('Y-m-d', strtotime($forum['created_at'])); ?></span>
                            </div>
                        </div>
                        <!-- 按钮已移除，将在下方重新创建 -->
                    </div>
                </div>
                </div>
                
                <!-- 快速发布按钮区域 -->
                <div class="new-post-buttons-container">
                    <div class="new-post-buttons">
                        <button class="new-quick-post-btn" onclick="showQuickPostModal()" id="newQuickPostBtn">
                            <span class="btn-icon">➕</span>
                            <span class="btn-text"><?php echo __('forum_quick_post'); ?></span>
                        </button>
                    </div>
                </div>
                
                <!-- 搜索功能区域 -->
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" class="search-input" id="postSearchInput" placeholder="🔍 搜索帖子标题、内容或作者...">
                        <button class="search-clear-btn" id="searchClearBtn" onclick="clearSearch()" style="display: none;">✕</button>
                    </div>
                    <div class="search-filters">
                        <button class="filter-btn active" data-filter="all">全部</button>
                        <button class="filter-btn" data-filter="title">标题</button>
                        <button class="filter-btn" data-filter="content">内容</button>
                        <button class="filter-btn" data-filter="author">作者</button>
                        <button class="filter-btn" data-filter="pinned">置顶</button>
                    </div>
                </div>
                
                <!-- 帖子列表 -->
                <div class="posts-container">
                    <!-- 搜索结果信息 -->
                    <div class="search-results-info" id="searchResultsInfo">
                        <span id="searchResultsText"></span>
                    </div>
                    
                    <div class="posts-header">
                        <div>
                            <h2 class="posts-title"><?php echo __('forum_latest_posts'); ?></h2>
                            <span class="posts-count"><?php echo str_replace('{count}', count($posts), __('forum_total_posts')); ?></span>
                        </div>
                    </div>
                    
                    <?php if (empty($posts)): ?>
                        <div class="empty-posts">
                            <div class="empty-posts-icon">📝</div>
                            <h3><?php echo __('forum_no_posts_yet'); ?></h3>
                            <p><?php echo __('forum_start_discussion'); ?></p>
                            <button class="btn-start-discussion" onclick="showCreatePostModal()">
                                <?php echo __('forum_start_discussion_btn'); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <ul class="posts-list">
                            <?php foreach ($posts as $post): ?>
                                <li class="post-item <?php echo $post['is_pinned'] ? 'pinned' : ''; ?>" onclick="viewPost(<?php echo $post['id']; ?>)">
                                    <div class="post-header">
                                        <div>
                                            <div class="post-title">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                                <?php if ($post['is_pinned']): ?>
                                                    <span class="pin-indicator">置顶</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="post-meta">
                                                <div class="post-author">
                                                    <div class="author-avatar">
                                                        <?php 
                                                        $authorAvatar = $post['avatar'] ?? null;
                                                        if (!empty($authorAvatar) && $authorAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $authorAvatar)) {
                                                            echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($authorAvatar) . '" alt="头像" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
                                                        } else {
                                                            echo strtoupper(substr($post['username'], 0, 1));
                                                        }
                                                        ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($post['username']); ?></span>
                                                </div>
                                                <span>📅 <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></span>
                                                <?php if ($post['view_count'] > 0): ?>
                                                    <span>👁️ <?php echo $post['view_count']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="post-content-preview">
                                        <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)); ?>
                                        <?php if (strlen(strip_tags($post['content'])) > 150): ?>...<?php endif; ?>
                                        
                                        <!-- 直接显示媒体文件预览 -->
                                        <?php if (isset($post['media_files']) && !empty($post['media_files'])): ?>
                                        <div class="post-media-preview">
                                            <div class="media-preview-grid">
                                                <?php 
                                                $displayCount = 0;
                                                $maxDisplay = 5; // 最多显示5个
                                                foreach ($post['media_files'] as $media): 
                                                    if ($displayCount >= $maxDisplay) break;
                                                    
                                                    $filePath = '/CHATTING/public/uploads/files/' . $media['filename'];
                                                    $isImage = in_array($media['file_type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                                    $isVideo = in_array($media['file_type'], ['video/mp4', 'video/webm', 'video/quicktime']);
                                                ?>
                                                    <?php if ($isImage): ?>
                                                        <div class="media-preview-item image-preview" onclick="openMediaModal('<?php echo $filePath; ?>', 'image')">
                                                            <img src="<?php echo $filePath; ?>" alt="<?php echo htmlspecialchars($media['original_name']); ?>" loading="lazy">
                                                            <div class="media-overlay">
                                                                <span class="media-icon">🖼️</span>
                                                            </div>
                                                        </div>
                                                    <?php elseif ($isVideo): ?>
                                                        <div class="media-preview-item video-preview" onclick="openMediaModal('<?php echo $filePath; ?>', 'video')">
                                                            <video preload="metadata" muted>
                                                                <source src="<?php echo $filePath; ?>" type="<?php echo $media['file_type']; ?>">
                                                            </video>
                                                            <div class="media-overlay">
                                                                <span class="media-icon">▶️</span>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="media-preview-item file-preview">
                                                            <div class="file-icon-large">📄</div>
                                                            <div class="file-name-small"><?php echo htmlspecialchars(substr($media['original_name'], 0, 10)) . (strlen($media['original_name']) > 10 ? '...' : ''); ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php 
                                                    $displayCount++;
                                                endforeach; ?>
                                                
                                                <?php if (count($post['media_files']) > $maxDisplay): ?>
                                                    <div class="media-preview-item more-preview">
                                                        <div class="more-count">+<?php echo count($post['media_files']) - $maxDisplay; ?></div>
                                                        <div class="more-text">更多</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- 显示最近回复 -->
                                        <?php if (isset($post['recent_replies']) && !empty($post['recent_replies'])): ?>
                                        <div class="post-replies-preview">
                                            <div class="replies-preview-header">
                                                <div class="replies-preview-title">
                                                    <span>💬</span>
                                                    <span>最新回复</span>
                                                </div>
                                                <div class="replies-preview-count">
                                                    <?php echo count($post['recent_replies']); ?> / <?php echo $post['reply_count']; ?>
                                                </div>
                                            </div>
                                            <ul class="replies-preview-list">
                                                <?php foreach ($post['recent_replies'] as $reply): ?>
                                                <li class="reply-preview-item">
                                                    <div class="reply-preview-avatar">
                                                        <?php 
                                                        $replyAvatar = $reply['avatar'] ?? null;
                                                        if (!empty($replyAvatar) && $replyAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $replyAvatar)) {
                                                            echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($replyAvatar) . '" alt="头像">';
                                                        } else {
                                                            echo strtoupper(substr($reply['username'], 0, 1));
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="reply-preview-content">
                                                        <div class="reply-preview-author">
                                                            <?php echo htmlspecialchars($reply['username']); ?>
                                                            <?php if ($reply['reply_to_id']): ?>
                                                                <span style="color: #999; font-weight: normal;">回复 @<?php echo htmlspecialchars($reply['reply_to_username']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="reply-preview-text">
                                                            <?php echo htmlspecialchars($reply['content']); ?>
                                                        </div>
                                                        <div class="reply-preview-time">
                                                            <?php echo date('m-d H:i', strtotime($reply['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                                
                                                <?php if ($post['reply_count'] > 5): ?>
                                                <li class="reply-preview-more" onclick="viewPost(<?php echo $post['id']; ?>)">
                                                    查看全部 <?php echo $post['reply_count']; ?> 条回复 →
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="post-stats">
                                        <div class="stat">
                                            <span>💬</span>
                                            <span><?php echo $post['reply_count']; ?> 回复</span>
                                        </div>
                                        <div class="stat">
                                            <span>👁️</span>
                                            <span><?php echo $post['view_count']; ?> 浏览</span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 创建帖子模态框 -->
    <div id="createPostModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeCreatePostModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>发表新帖子</h3>
                    <button class="close-btn" onclick="closeCreatePostModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="createPostForm">
                        <input type="hidden" id="forumId" name="forum_id" value="<?php echo $forum['id']; ?>">
                        <div class="form-group">
                            <label for="postTitle">帖子标题</label>
                            <input type="text" id="postTitle" name="title" required maxlength="200" placeholder="请输入帖子标题">
                        </div>
                        <div class="form-group">
                            <label for="postContent">帖子内容</label>
                            <textarea id="postContent" name="content" required placeholder="请输入帖子内容" rows="8"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" onclick="closeCreatePostModal()" class="btn btn-secondary">取消</button>
                            <button type="submit" class="btn btn-primary">发表帖子</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 快速发布模态框 -->
    <div id="quickPostModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeQuickPostModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>快速发布</h3>
                    <button class="close-btn" onclick="closeQuickPostModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="quickPostForm" enctype="multipart/form-data">
                        <input type="hidden" id="quickForumId" name="forum_id" value="<?php echo $forum['id']; ?>">
                        <div class="form-group">
                            <label for="quickPostTitle">帖子标题</label>
                            <input type="text" id="quickPostTitle" name="title" required maxlength="200" placeholder="请输入帖子标题">
                        </div>
                        <div class="form-group">
                            <label for="quickPostContent">帖子内容</label>
                            <textarea id="quickPostContent" name="content" required placeholder="请输入帖子内容" rows="6"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="mediaFiles">上传媒体文件</label>
                            <div class="media-upload-area">
                                <input type="file" id="mediaFiles" name="media_files[]" multiple accept="image/*,video/*" style="display: none;">
                                <div class="upload-dropzone" onclick="document.getElementById('mediaFiles').click()">
                                    <div class="upload-icon">📁</div>
                                    <div class="upload-text">点击选择照片或视频</div>
                                    <div class="upload-hint">支持 JPG, PNG, GIF, MP4, MOV 格式</div>
                                </div>
                                <div id="mediaPreview" class="media-preview"></div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" onclick="closeQuickPostModal()" class="btn btn-secondary">取消</button>
                            <button type="submit" class="btn btn-primary">发布帖子</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
        }
        
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }
        
        .media-upload-area {
            margin-top: 10px;
        }
        
        .upload-dropzone {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .upload-dropzone:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .upload-dropzone.dragover {
            border-color: #667eea;
            background: #e8f2ff;
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .upload-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .upload-hint {
            font-size: 0.9rem;
            color: #666;
        }
        
        .media-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .media-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .media-item img,
        .media-item video {
            width: 120px;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        
        .media-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .media-item .remove-btn:hover {
            background: rgba(255, 0, 0, 1);
        }
    </style>

    <script>
        // 显示创建帖子模态框
        function showCreatePostModal() {
            console.log('显示创建帖子模态框');
            document.getElementById('createPostModal').style.display = 'block';
            document.getElementById('postTitle').focus();
        }
        
        // 确保新按钮功能正常
        document.addEventListener('DOMContentLoaded', function() {
            const newQuickBtn = document.getElementById('newQuickPostBtn');
            
            if (newQuickBtn) {
                newQuickBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('新快速发布按钮被点击');
                    showQuickPostModal();
                });
            }
            
            // 强制显示按钮
            if (newQuickBtn) {
                newQuickBtn.style.display = 'inline-flex';
                newQuickBtn.style.visibility = 'visible';
                newQuickBtn.style.opacity = '1';
            }
        });
        
        // 关闭创建帖子模态框
        function closeCreatePostModal() {
            document.getElementById('createPostModal').style.display = 'none';
            document.getElementById('createPostForm').reset();
        }
        
        // 显示快速发布模态框
        function showQuickPostModal() {
            document.getElementById('quickPostModal').style.display = 'block';
            document.getElementById('quickPostTitle').focus();
        }
        
        // 关闭快速发布模态框
        function closeQuickPostModal() {
            document.getElementById('quickPostModal').style.display = 'none';
            document.getElementById('quickPostForm').reset();
            document.getElementById('mediaPreview').innerHTML = '';
        }
        
        // 查看帖子详情
        function viewPost(postId) {
            window.location.href = `/CHATTING/forum/post?id=${postId}`;
        }
        
        // 初始化搜索功能
        function initializeSearch() {
            const searchInput = document.getElementById('postSearchInput');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const clearBtn = document.getElementById('searchClearBtn');
            
            // 搜索输入事件
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        currentSearchQuery = this.value.toLowerCase().trim();
                        performSearch();
                        updateClearButton();
                    }, 300);
                });
                
                // 回车搜索
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        currentSearchQuery = this.value.toLowerCase().trim();
                        performSearch();
                    }
                });
            }
            
            // 筛选按钮事件
            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // 更新按钮状态
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // 更新筛选类型
                    currentFilter = this.dataset.filter;
                    performSearch();
                });
            });
            
            // 清除按钮事件
            if (clearBtn) {
                clearBtn.addEventListener('click', clearSearch);
            }
        }
        
        // 执行搜索
        function performSearch() {
            let results = allPosts;
            
            // 应用搜索查询
            if (currentSearchQuery) {
                results = results.filter(post => {
                    const title = post.title.toLowerCase();
                    const content = post.content.toLowerCase();
                    const username = post.username.toLowerCase();
                    
                    switch (currentFilter) {
                        case 'title':
                            return title.includes(currentSearchQuery);
                        case 'content':
                            return content.includes(currentSearchQuery);
                        case 'author':
                            return username.includes(currentSearchQuery);
                        case 'pinned':
                            return post.is_pinned && (
                                title.includes(currentSearchQuery) ||
                                content.includes(currentSearchQuery) ||
                                username.includes(currentSearchQuery)
                            );
                        case 'all':
                        default:
                            return title.includes(currentSearchQuery) ||
                                   content.includes(currentSearchQuery) ||
                                   username.includes(currentSearchQuery);
                    }
                });
            } else {
                // 如果没有搜索查询，应用筛选器
                switch (currentFilter) {
                    case 'pinned':
                        results = results.filter(post => post.is_pinned);
                        break;
                    case 'all':
                    default:
                        // 显示所有帖子
                        break;
                }
            }
            
            filteredPosts = results;
            renderPosts();
            updateSearchResultsInfo();
        }
        
        // 渲染帖子列表
        function renderPosts() {
            const postsList = document.querySelector('.posts-list');
            const emptyPosts = document.querySelector('.empty-posts');
            
            if (!postsList) return;
            
            if (filteredPosts.length === 0) {
                // 显示无结果状态
                if (currentSearchQuery || currentFilter !== 'all') {
                    postsList.innerHTML = getNoSearchResultsHTML();
                } else {
                    postsList.innerHTML = getEmptyPostsHTML();
                }
                return;
            }
            
            // 渲染帖子
            postsList.innerHTML = filteredPosts.map(post => createPostHTML(post)).join('');
            
            // 添加点击事件
            postsList.querySelectorAll('.post-item').forEach(item => {
                item.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    viewPost(postId);
                });
            });
        }
        
        // 创建帖子HTML
        function createPostHTML(post) {
            const highlightedTitle = highlightText(post.title, currentSearchQuery);
            const highlightedContent = highlightText(post.content, currentSearchQuery);
            const highlightedUsername = highlightText(post.username, currentSearchQuery);
            
            return `
                <li class="post-item ${post.is_pinned ? 'pinned' : ''}" data-post-id="${post.id}">
                    <div class="post-header">
                        <div>
                            <div class="post-title">
                                ${highlightedTitle}
                                ${post.is_pinned ? '<span class="pin-indicator">置顶</span>' : ''}
                            </div>
                            <div class="post-meta">
                                <div class="post-author">
                                    <div class="author-avatar">
                                        ${post.avatar && post.avatar !== 'default_avatar.png' ? 
                                            `<img src="/CHATTING/public/uploads/avatars/${post.avatar}" alt="头像" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` :
                                            post.username.charAt(0).toUpperCase()
                                        }
                                    </div>
                                    <span>${highlightedUsername}</span>
                                </div>
                                <span>📅 ${formatDate(post.created_at)}</span>
                                ${post.view_count > 0 ? `<span>👁️ ${post.view_count}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="post-content-preview">
                        ${highlightedContent}
                        ${post.content.length > 150 ? '...' : ''}
                        
                        ${post.media_files && post.media_files.length > 0 ? createMediaPreviewHTML(post.media_files) : ''}
                        ${post.recent_replies && post.recent_replies.length > 0 ? createRepliesPreviewHTML(post) : ''}
                    </div>
                    <div class="post-stats">
                        <div class="stat">
                            <span>💬</span>
                            <span>${post.reply_count} 回复</span>
                        </div>
                        <div class="stat">
                            <span>👁️</span>
                            <span>${post.view_count} 浏览</span>
                        </div>
                    </div>
                </li>
            `;
        }
        
        // 高亮搜索文本
        function highlightText(text, query) {
            if (!query || !text) return escapeHtml(text);
            
            const escapedText = escapeHtml(text);
            const escapedQuery = escapeHtml(query);
            const regex = new RegExp(`(${escapedQuery})`, 'gi');
            
            return escapedText.replace(regex, '<span class="search-highlight">$1</span>');
        }
        
        // 创建媒体预览HTML
        function createMediaPreviewHTML(mediaFiles) {
            console.log('createMediaPreviewHTML 被调用，媒体文件:', mediaFiles);
            console.log('媒体文件数量:', mediaFiles.length);
            
            const maxDisplay = 5;
            let html = '<div class="post-media-preview"><div class="media-preview-grid">';
            
            for (let i = 0; i < Math.min(mediaFiles.length, maxDisplay); i++) {
                const media = mediaFiles[i];
                console.log(`处理媒体文件 ${i}:`, media);
                
                const filePath = '/CHATTING/public/uploads/files/' + media.filename;
                const isImage = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(media.file_type);
                const isVideo = ['video/mp4', 'video/webm', 'video/quicktime'].includes(media.file_type);
                
                if (isImage) {
                    html += `
                        <div class="media-preview-item image-preview" onclick="openMediaModal('${filePath}', 'image')">
                            <img src="${filePath}" alt="${escapeHtml(media.original_name)}" loading="lazy">
                            <div class="media-overlay">
                                <span class="media-icon">🖼️</span>
                            </div>
                        </div>
                    `;
                } else if (isVideo) {
                    html += `
                        <div class="media-preview-item video-preview" onclick="openMediaModal('${filePath}', 'video')">
                            <video preload="metadata" muted>
                                <source src="${filePath}" type="${media.file_type}">
                            </video>
                            <div class="media-overlay">
                                <span class="media-icon">▶️</span>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="media-preview-item file-preview">
                            <div class="file-icon-large">📄</div>
                            <div class="file-name-small">${escapeHtml(media.original_name.substring(0, 10))}${media.original_name.length > 10 ? '...' : ''}</div>
                        </div>
                    `;
                }
            }
            
            if (mediaFiles.length > maxDisplay) {
                html += `
                    <div class="media-preview-item more-preview">
                        <div class="more-count">+${mediaFiles.length - maxDisplay}</div>
                        <div class="more-text">更多</div>
                    </div>
                `;
            }
            
            html += '</div></div>';
            return html;
        }
        
        // 创建回复预览HTML
        function createRepliesPreviewHTML(post) {
            let html = `
                <div class="post-replies-preview">
                    <div class="replies-preview-header">
                        <div class="replies-preview-title">
                            <span>💬</span>
                            <span>最新回复</span>
                        </div>
                        <div class="replies-preview-count">
                            ${post.recent_replies.length} / ${post.reply_count}
                        </div>
                    </div>
                    <ul class="replies-preview-list">
            `;
            
            post.recent_replies.forEach(reply => {
                const highlightedContent = highlightText(reply.content, currentSearchQuery);
                const highlightedUsername = highlightText(reply.username, currentSearchQuery);
                
                html += `
                    <li class="reply-preview-item">
                        <div class="reply-preview-avatar">
                            ${reply.avatar && reply.avatar !== 'default_avatar.png' ? 
                                `<img src="/CHATTING/public/uploads/avatars/${reply.avatar}" alt="头像">` :
                                reply.username.charAt(0).toUpperCase()
                            }
                        </div>
                        <div class="reply-preview-content">
                            <div class="reply-preview-author">
                                ${highlightedUsername}
                                ${reply.reply_to_id ? `<span style="color: #999; font-weight: normal;">回复 @${escapeHtml(reply.reply_to_username)}</span>` : ''}
                            </div>
                            <div class="reply-preview-text">${highlightedContent}</div>
                            <div class="reply-preview-time">${formatDate(reply.created_at, 'MM-dd HH:mm')}</div>
                        </div>
                    </li>
                `;
            });
            
            if (post.reply_count > 5) {
                html += `
                    <li class="reply-preview-more" onclick="viewPost(${post.id})">
                        查看全部 ${post.reply_count} 条回复 →
                    </li>
                `;
            }
            
            html += '</ul></div>';
            return html;
        }
        
        // 获取无搜索结果HTML
        function getNoSearchResultsHTML() {
            return `
                <div class="no-search-results">
                    <div class="no-search-results-icon">🔍</div>
                    <h3>未找到相关帖子</h3>
                    <p>没有找到包含 "${currentSearchQuery}" 的帖子</p>
                    <button class="btn-clear-search" onclick="clearSearch()">清除搜索</button>
                </div>
            `;
        }
        
        // 获取空帖子HTML
        function getEmptyPostsHTML() {
            return `
                <div class="empty-posts">
                    <div class="empty-posts-icon">📝</div>
                    <h3>暂无帖子</h3>
                    <p>还没有人发表帖子，快来发表第一个帖子吧！</p>
                    <button class="btn-start-discussion" onclick="showQuickPostModal()">开始讨论</button>
                </div>
            `;
        }
        
        // 更新搜索结果信息
        function updateSearchResultsInfo() {
            const resultsInfo = document.getElementById('searchResultsInfo');
            const resultsText = document.getElementById('searchResultsText');
            
            if (!resultsInfo || !resultsText) return;
            
            if (currentSearchQuery || currentFilter !== 'all') {
                const totalPosts = allPosts.length;
                const foundPosts = filteredPosts.length;
                const filterText = currentFilter !== 'all' ? ` (${getFilterText(currentFilter)})` : '';
                
                resultsText.textContent = `找到 ${foundPosts} 个帖子${filterText}，共 ${totalPosts} 个帖子`;
                resultsInfo.classList.add('show');
            } else {
                resultsInfo.classList.remove('show');
            }
        }
        
        // 获取筛选器文本
        function getFilterText(filter) {
            const filterTexts = {
                'all': '全部',
                'title': '标题',
                'content': '内容',
                'author': '作者',
                'pinned': '置顶'
            };
            return filterTexts[filter] || '全部';
        }
        
        // 清除搜索
        function clearSearch() {
            const searchInput = document.getElementById('postSearchInput');
            const filterButtons = document.querySelectorAll('.filter-btn');
            
            if (searchInput) {
                searchInput.value = '';
            }
            
            // 重置筛选器
            filterButtons.forEach(btn => btn.classList.remove('active'));
            document.querySelector('[data-filter="all"]').classList.add('active');
            
            currentSearchQuery = '';
            currentFilter = 'all';
            filteredPosts = allPosts;
            
            renderPosts();
            updateSearchResultsInfo();
            updateClearButton();
        }
        
        // 更新清除按钮显示
        function updateClearButton() {
            const clearBtn = document.getElementById('searchClearBtn');
            const searchInput = document.getElementById('postSearchInput');
            
            if (clearBtn && searchInput) {
                if (searchInput.value.trim()) {
                    clearBtn.style.display = 'flex';
                } else {
                    clearBtn.style.display = 'none';
                }
            }
        }
        
        // 格式化日期
        function formatDate(dateString, format = 'YYYY-MM-DD HH:mm') {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (format === 'MM-dd HH:mm') {
                return date.toLocaleDateString('zh-CN', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
            }
            
            if (diffDays === 1) {
                return '今天';
            } else if (diffDays === 2) {
                return '昨天';
            } else if (diffDays <= 7) {
                return `${diffDays}天前`;
            } else {
                return date.toLocaleDateString('zh-CN');
            }
        }
        
        // HTML转义函数
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 动态添加新帖子到列表
        function addNewPostToList(postData) {
            console.log('addNewPostToList 被调用，帖子数据:', postData);
            
            const postsList = document.querySelector('.posts-list');
            if (!postsList) {
                console.error('找不到 .posts-list 元素');
                return;
            }
            
            console.log('找到帖子列表元素，当前帖子数量:', postsList.children.length);
            
            // 创建新帖子HTML
            const newPostHTML = createPostHTML(postData);
            console.log('生成的帖子HTML:', newPostHTML);
            
            // 添加到列表顶部
            postsList.insertAdjacentHTML('afterbegin', newPostHTML);
            
            // 添加点击事件
            const newPostItem = postsList.querySelector('.post-item[data-post-id="' + postData.id + '"]');
            if (newPostItem) {
                newPostItem.addEventListener('click', function() {
                    viewPost(postData.id);
                });
                console.log('新帖子点击事件已绑定');
            } else {
                console.error('找不到新添加的帖子元素');
            }
            
            // 更新客户端数据
            allPosts.unshift(postData);
            filteredPosts = allPosts;
            
            console.log('新帖子已添加到列表:', postData.title);
            console.log('更新后的 allPosts 长度:', allPosts.length);
        }
        
        // 更新帖子数量
        function updatePostCount() {
            const postsCountElement = document.querySelector('.posts-count');
            if (postsCountElement) {
                const currentCount = allPosts.length;
                postsCountElement.textContent = `共${currentCount}个帖子`;
            }
        }
        
        // 显示成功消息
        function showSuccessMessage(message) {
            showNotification(message, 'success');
        }
        
        // 显示错误消息
        function showErrorMessage(message) {
            showNotification(message, 'error');
        }
        
        // 显示通知
        function showNotification(message, type = 'info') {
            // 创建通知元素
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <span class="notification-icon">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
                    <span class="notification-message">${message}</span>
                </div>
            `;
            
            // 添加样式
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 300px;
                font-weight: 500;
            `;
            
            // 添加到页面
            document.body.appendChild(notification);
            
            // 显示动画
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // 自动隐藏
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        // 创建帖子表单提交
        // 搜索功能变量
        let allPosts = <?php echo json_encode($posts); ?>;
        let filteredPosts = allPosts;
        let currentSearchQuery = '';
        let currentFilter = 'all';
        let searchTimeout;
        
        // 等待DOM加载完成
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化搜索功能
            initializeSearch();
            
            const createPostForm = document.getElementById('createPostForm');
            if (createPostForm) {
                createPostForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;
                    
                    // 显示加载状态
                    submitBtn.disabled = true;
                    submitBtn.textContent = '发布中...';
                    
                    const formData = new FormData(this);
                    const forumId = document.getElementById('forumId').value;
                    
                    fetch('/CHATTING/forum/createPost', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `forum_id=${forumId}&title=${encodeURIComponent(formData.get('title'))}&content=${encodeURIComponent(formData.get('content'))}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('普通发布响应数据:', data);
                        if (data.success) {
                            // 成功处理
                            showSuccessMessage('帖子发布成功！');
                            
                            // 清空表单
                            this.reset();
                            
                            // 关闭模态框
                            closeCreatePostModal();
                            
                            // 动态添加新帖子到列表顶部
                            if (data.post) {
                                console.log('准备添加新帖子到列表:', data.post);
                                addNewPostToList(data.post);
                                updatePostCount();
                            } else {
                                console.error('服务器没有返回帖子数据');
                            }
                        } else {
                            showErrorMessage('发表失败: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('发表失败，请重试');
                    })
                    .finally(() => {
                        // 恢复按钮状态
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
                });
            }
        
            // 快速发布表单提交
            const quickPostForm = document.getElementById('quickPostForm');
            if (quickPostForm) {
                quickPostForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;
                    
                    // 显示加载状态
                    submitBtn.disabled = true;
                    submitBtn.textContent = '发布中...';
                    
                    const formData = new FormData(this);
                    const forumId = document.getElementById('quickForumId').value;
                    
                    // 添加forum_id参数
                    formData.append('forum_id', forumId);
                    
                    // 调试信息 - 检查FormData内容
                    console.log('快速发布表单数据:');
                    console.log('Forum ID:', forumId);
                    console.log('Title:', formData.get('title'));
                    console.log('Content:', formData.get('content'));
                    
                    // 检查FormData中的文件
                    const formDataFiles = [];
                    for (let pair of formData.entries()) {
                        if (pair[0] === 'media_files[]') {
                            formDataFiles.push(pair[1].name);
                        }
                    }
                    console.log('FormData中的文件:', formDataFiles);
                    
                    // 检查input元素中的文件
                    const mediaFiles = document.getElementById('mediaFiles').files;
                    const inputFiles = [];
                    for (let i = 0; i < mediaFiles.length; i++) {
                        inputFiles.push(mediaFiles[i].name);
                    }
                    console.log('Input元素中的文件:', inputFiles);
                    console.log('文件数量对比 - FormData:', formDataFiles.length, 'Input:', inputFiles.length);
                    
                    fetch('/CHATTING/forum/createPostWithMedia', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('快速发布响应数据:', data);
                        if (data.success) {
                            // 成功处理
                            showSuccessMessage('帖子发布成功！');
                            
                            // 清空表单
                            this.reset();
                            document.getElementById('mediaPreview').innerHTML = '';
                            
                            // 关闭模态框
                            closeQuickPostModal();
                            
                            // 动态添加新帖子到列表顶部
                            if (data.post) {
                                console.log('准备添加新帖子到列表:', data.post);
                                addNewPostToList(data.post);
                                updatePostCount();
                            } else {
                                console.error('服务器没有返回帖子数据');
                            }
                        } else {
                            showErrorMessage('发表失败: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('发表失败，请重试');
                    })
                    .finally(() => {
                        // 恢复按钮状态
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
                });
            }
        
            // 媒体文件处理
            const mediaFiles = document.getElementById('mediaFiles');
            if (mediaFiles) {
                // 移除可能存在的旧事件监听器
                mediaFiles.removeEventListener('change', handleFileChange);
                
                // 定义文件处理函数
                function handleFileChange(e) {
                    console.log('文件选择事件触发，文件数量:', e.target.files.length);
                    const files = Array.from(e.target.files);
                    const preview = document.getElementById('mediaPreview');
                    
                    // 清空预览区域
                    preview.innerHTML = '';
                    
                    // 去重处理 - 基于文件名和大小
                    const uniqueFiles = [];
                    const fileKeys = new Set();
                    
                    files.forEach(file => {
                        const key = `${file.name}_${file.size}_${file.type}`;
                        if (!fileKeys.has(key)) {
                            fileKeys.add(key);
                            uniqueFiles.push(file);
                        } else {
                            console.log('跳过重复文件:', file.name);
                        }
                    });
                    
                    console.log('去重后文件数量:', uniqueFiles.length);
                    
                    // 更新文件输入元素，只保留去重后的文件
                    const dt = new DataTransfer();
                    uniqueFiles.forEach(file => {
                        dt.items.add(file);
                    });
                    mediaFiles.files = dt.files;
                    
                    console.log('更新后的文件输入数量:', mediaFiles.files.length);
                    
                    uniqueFiles.forEach((file, index) => {
                        console.log(`处理文件 ${index + 1}:`, file.name, file.type);
                        const mediaItem = document.createElement('div');
                        mediaItem.className = 'media-item';
                        
                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            mediaItem.appendChild(img);
                        } else if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = URL.createObjectURL(file);
                            video.controls = true;
                            mediaItem.appendChild(video);
                        }
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'remove-btn';
                        removeBtn.innerHTML = '×';
                        removeBtn.onclick = function() {
                            // 从文件输入中移除文件
                            const dt = new DataTransfer();
                            Array.from(mediaFiles.files).forEach((f, i) => {
                                if (i !== index) {
                                    dt.items.add(f);
                                }
                            });
                            mediaFiles.files = dt.files;
                            mediaItem.remove();
                        };
                        mediaItem.appendChild(removeBtn);
                        
                        preview.appendChild(mediaItem);
                    });
                }
                
                // 添加事件监听器
                mediaFiles.addEventListener('change', handleFileChange);
            }
        
            // 拖拽上传功能
            const dropzone = document.querySelector('.upload-dropzone');
            if (dropzone) {
                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropzone.classList.add('dragover');
                });
                
                dropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                });
                
                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                    
                    const files = Array.from(e.dataTransfer.files);
                    const fileInput = document.getElementById('mediaFiles');
                    
                    console.log('拖拽文件事件触发，拖拽文件数量:', files.length);
                    console.log('现有文件数量:', fileInput.files ? fileInput.files.length : 0);
                    
                    // 创建新的FileList，合并现有文件和拖拽的文件
                    const dt = new DataTransfer();
                    const existingFiles = fileInput.files ? Array.from(fileInput.files) : [];
                    const allFiles = [...existingFiles, ...files];
                    
                    // 去重处理 - 基于文件名、大小和类型
                    const uniqueFiles = [];
                    const fileKeys = new Set();
                    
                    allFiles.forEach(file => {
                        if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                            const key = `${file.name}_${file.size}_${file.type}`;
                            if (!fileKeys.has(key)) {
                                fileKeys.add(key);
                                uniqueFiles.push(file);
                                console.log('添加文件:', file.name, file.type);
                            } else {
                                console.log('跳过重复文件:', file.name);
                            }
                        }
                    });
                    
                    console.log('去重后文件数量:', uniqueFiles.length);
                    
                    // 添加到DataTransfer
                    uniqueFiles.forEach(file => {
                        dt.items.add(file);
                    });
                    
                    fileInput.files = dt.files;
                    
                    // 触发change事件
                    const event = new Event('change', { bubbles: true });
                    fileInput.dispatchEvent(event);
                });
            }
        }); // 关闭DOMContentLoaded事件
        
        // 点击模态框外部关闭
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                closeCreatePostModal();
                closeQuickPostModal();
            }
        });
        
        // 打开媒体预览模态框
        function openMediaModal(filePath, type) {
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            
            if (type === 'image') {
                modalImage.src = filePath;
                modalImage.style.display = 'block';
                modalVideo.style.display = 'none';
            } else if (type === 'video') {
                modalVideo.src = filePath;
                modalVideo.style.display = 'block';
                modalImage.style.display = 'none';
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // 防止背景滚动
        }
        
        // 关闭媒体预览模态框
        function closeMediaModal() {
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            
            modal.style.display = 'none';
            modalImage.src = '';
            modalVideo.src = '';
            document.body.style.overflow = 'auto'; // 恢复背景滚动
        }
        
        
        // 点击模态框背景关闭
        const mediaModal = document.getElementById('mediaModal');
        if (mediaModal) {
            mediaModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMediaModal();
                }
            });
        }
        
        // ESC键关闭模态框
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMediaModal();
            }
        });
    </script>
    
    <!-- 媒体预览模态框 -->
    <div id="mediaModal" class="media-modal" style="display: none;">
        <span class="media-modal-close" onclick="closeMediaModal()">&times;</span>
        <div class="media-modal-content">
            <img id="modalImage" style="display: none;" alt="预览图片">
            <video id="modalVideo" style="display: none;" controls>
                您的浏览器不支持视频播放。
            </video>
        </div>
    </div>

</body>
</html>
