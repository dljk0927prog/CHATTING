<?php
// blockedList.php - 封锁列表页面

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
    <title><?php echo __('blocked_list_title'); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .blocked-list-container {
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8f4ff 0%, #ffffff 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .blocked-list-container {
                margin: 0;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .page-icon {
                margin-right: 0;
                margin-bottom: 20px;
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .page-name {
                font-size: 1.8rem;
            }
            
            .page-description {
                font-size: 1rem;
                padding: 6px 12px;
            }
            
            .back-btn {
                width: 40px;
                height: 40px;
                top: 15px;
                right: 15px;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .blocked-user-item {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .user-info {
                width: 100%;
            }
            
            .user-avatar {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
            
            .user-details h4 {
                font-size: 1rem;
            }
            
            .user-details p {
                font-size: 0.8rem;
            }
            
            .blocked-actions {
                width: 100%;
                justify-content: flex-end;
                gap: 10px;
            }
            
            .btn {
                min-height: 44px;
                padding: 12px 20px;
                font-size: 16px;
            }
            
            .empty-state {
                padding: 60px 20px;
            }
            
            .empty-state-icon {
                font-size: 3rem;
            }
            
            .empty-state h3 {
                font-size: 1.2rem;
            }
            
            .empty-state p {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .header-section {
                padding: 20px 15px;
            }
            
            .page-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            .page-name {
                font-size: 1.5rem;
            }
            
            .content-section {
                padding: 20px 15px;
            }
            
            .blocked-user-item {
                padding: 12px;
            }
            
            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .user-details h4 {
                font-size: 0.95rem;
            }
            
            .user-details p {
                font-size: 0.75rem;
            }
            
            .blocked-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .blocked-actions .btn {
                width: 100%;
            }
            
            .empty-state {
                padding: 40px 15px;
            }
            
            .empty-state-icon {
                font-size: 2.5rem;
            }
            
            .empty-state h3 {
                font-size: 1.1rem;
            }
            
            .empty-state p {
                font-size: 0.85rem;
            }
        }
        
        .blocked-list-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e8d5ff, #d1b3ff, #b894ff, #a075ff);
        }
        
        .header-section {
            background: linear-gradient(135deg, #f0e6ff 0%, #e8d5ff 100%);
            color: #4a3c5c;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(168, 85, 247, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .page-header {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .page-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a3c5c;
            font-size: 2rem;
            font-weight: bold;
            margin-right: 25px;
            position: relative;
            overflow: hidden;
            border: 4px solid rgba(168, 85, 247, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.15);
        }
        
        .page-title {
            flex: 1;
        }
        
        .page-name {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4a3c5c;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .page-description {
            font-size: 1.1rem;
            color: #6b46c1;
            background: rgba(168, 85, 247, 0.1);
            padding: 8px 16px;
            border-radius: 25px;
            display: inline-block;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(168, 85, 247, 0.2);
            font-weight: 500;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.8);
            color: #6b46c1;
            border: 2px solid rgba(168, 85, 247, 0.3);
            padding: 0;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.4rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            position: absolute;
            top: 20px;
            right: 20px;
            width: 48px;
            height: 48px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.15);
        }
        
        .back-btn:hover {
            background: rgba(168, 85, 247, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.2);
        }
        
        .back-btn::before {
            content: '←';
            font-size: 1.4rem;
            line-height: 1;
        }
        
        .content-section {
            padding: 40px 30px;
        }
        
        .blocked-users-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .blocked-user-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f4ff 100%);
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid #ff9800;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .blocked-user-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.08), rgba(255, 193, 7, 0.08));
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .blocked-user-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.15);
        }
        
        .blocked-user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff9800 0%, #ffc107 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .blocked-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .blocked-user-info {
            flex: 1;
            position: relative;
            z-index: 2;
        }
        
        .blocked-user-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .blocked-user-status {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .blocked-time {
            font-size: 0.8rem;
            color: #999;
        }
        
        .unblock-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .unblock-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        }
        
        .unblock-btn:active {
            transform: translateY(0);
        }
        
        .empty-state {
            text-align: center;
            color: #95a5a6;
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 15px;
            border: 2px dashed #e9ecef;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.6;
        }
        
        .empty-state div:last-child {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .blocked-list-container {
                margin: 0;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .page-icon {
                margin-right: 0;
                margin-bottom: 20px;
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .page-name {
                font-size: 1.8rem;
            }
            
            .back-btn {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                align-self: flex-start;
                width: 40px;
                height: 40px;
            }
            
            .blocked-user-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .blocked-user-avatar {
                margin-right: 0;
            }
            
            .unblock-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .page-name {
                font-size: 1.5rem;
            }
            
            .page-description {
                font-size: 1rem;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="blocked-list-container">
        <!-- 返回按钮 -->
        <a href="/Chat_System/dashboard" class="back-btn" title="<?php echo __('blocked_list_back_dashboard'); ?>"></a>
        
        <!-- 页面头部信息 -->
        <div class="header-section">
            <div class="page-header">
                <div class="page-icon">🚫</div>
                <div class="page-title">
                    <div class="page-name">封锁列表</div>
                    <div class="page-description">管理被封锁的用户</div>
                </div>
            </div>
        </div>
        
        <!-- 内容区域 -->
        <div class="content-section">
            <?php if (empty($blockedUsers)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔓</div>
                    <div>暂无被封锁的用户</div>
                </div>
            <?php else: ?>
                <div class="blocked-users-list">
                    <?php foreach ($blockedUsers as $blockedUser): ?>
                        <div class="blocked-user-item">
                            <div class="blocked-user-avatar">
                                <?php 
                                $avatarValue = $blockedUser['avatar'] ?? null;
                                
                                if (empty($avatarValue) || $avatarValue === 'default_avatar.png' || $avatarValue === 'NULL') {
                                    echo strtoupper(substr($blockedUser['username'], 0, 1));
                                } else {
                                    $avatarPath = BASE_PATH . '/public/uploads/avatars/' . $avatarValue;
                                    if (file_exists($avatarPath)) {
                                        echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($avatarValue) . '" alt="' . __('blocked_avatar_alt') . '">';
                                    } else {
                                        echo strtoupper(substr($blockedUser['username'], 0, 1));
                                    }
                                }
                                ?>
                            </div>
                            <div class="blocked-user-info">
                                <div class="blocked-user-name"><?php echo htmlspecialchars($blockedUser['username']); ?></div>
                                <div class="blocked-user-status">
                                    <?php 
                                    if ($blockedUser['status'] === 'online') {
                                        echo '🟢 在线';
                                    } elseif ($blockedUser['status'] === 'away') {
                                        echo '🟡 离开';
                                    } else {
                                        echo '⚫ 离线';
                                    }
                                    ?>
                                </div>
                                <div class="blocked-time">封锁时间: <?php echo date('Y-m-d H:i:s', strtotime($blockedUser['blocked_at'])); ?></div>
                            </div>
                            <button class="unblock-btn" onclick="unblockUser(<?php echo $blockedUser['blocked_id']; ?>)">
                                解除封锁
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // 解除封锁功能
        function unblockUser(blockedId) {
            if (confirm('确定要解除对这个用户的封锁吗？')) {
                fetch('/Chat_System/chat/unblockFriend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `blocked_id=${blockedId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('已解除封锁');
                        // 刷新页面
                        location.reload();
                    } else {
                        alert('解除封锁失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('解除封锁失败，请重试');
                });
            }
        }
    </script>
    <?php $footerVariant = 'default'; include BASE_PATH . '/app/views/components/site-footer.php'; ?>
</body>
</html>
