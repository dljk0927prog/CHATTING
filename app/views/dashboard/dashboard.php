<?php
// dashboard视图文件 - 数据由DashboardController提供
$currentTab = 'chats'; // 设置当前标签页

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
    <title><?php echo __('page_title_chat'); ?> - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <style>
        /* 仪表板页面移动端优化 */
        @media (max-width: 768px) {
            .mobile-header {
                padding: 15px 20px;
            }
            
            .mobile-header h2 {
                font-size: 1.2rem;
            }
            
            .menu-button {
                min-width: 44px;
                min-height: 44px;
                font-size: 1.2rem;
            }
            
            .welcome-screen {
                padding: 30px 15px;
            }
            
            .welcome-screen h2 {
                font-size: 1.5rem;
            }
            
            .welcome-screen p {
                font-size: 1rem;
                margin: 15px 0;
            }
            
            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 14px 20px;
            }
            
            #addFriendModal {
                padding: 20px;
            }
            
            #addFriendModal h3 {
                font-size: 1.2rem;
                margin-bottom: 15px;
            }
            
            #searchUserInput {
                min-height: 44px;
                font-size: 16px;
                padding: 12px 15px;
            }
            
            #searchResults {
                max-height: 150px;
            }
            
            .search-result-item {
                padding: 12px 15px;
                min-height: 44px;
            }
            
            .search-result-item h4 {
                font-size: 1rem;
            }
            
            .search-result-item p {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .mobile-header {
                padding: 12px 15px;
            }
            
            .mobile-header h2 {
                font-size: 1.1rem;
            }
            
            .menu-button {
                min-width: 40px;
                min-height: 40px;
                font-size: 1.1rem;
            }
            
            .welcome-screen {
                padding: 25px 12px;
            }
            
            .welcome-screen h2 {
                font-size: 1.3rem;
            }
            
            .welcome-screen p {
                font-size: 0.9rem;
                margin: 12px 0;
            }
            
            .btn {
                min-height: 46px;
                font-size: 16px;
                padding: 12px 16px;
            }
            
            #addFriendModal {
                padding: 15px;
            }
            
            #addFriendModal h3 {
                font-size: 1.1rem;
                margin-bottom: 12px;
            }
            
            #searchUserInput {
                min-height: 42px;
                font-size: 16px;
                padding: 10px 12px;
            }
            
            .search-result-item {
                padding: 10px 12px;
                min-height: 42px;
            }
            
            .search-result-item h4 {
                font-size: 0.95rem;
            }
            
            .search-result-item p {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="chat-page-container">
        <div class="chat-container">
            <!-- 引入侧边栏组件 -->
            <?php include __DIR__ . '/../components/navbar.php'; ?>
            
            <!-- 聊天区域 -->
            <div class="chat-area">
                <div class="mobile-header">
                    <button class="menu-button" onclick="toggleSidebar()">☰</button>
                    <h2>聊天</h2>
                </div>
                
                <div class="welcome-screen">
                    <div style="text-align: center; padding: 50px 20px; color: #666;">
                        <h2><?php echo __('dashboard_welcome_title'); ?></h2>
                        <p><?php echo __('dashboard_welcome_subtitle'); ?></p>
                        <button class="btn btn-primary" onclick="showAddFriendModal()" style="margin-top: 20px;">
                            <?php echo __('dashboard_add_friend'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 添加好友模态框 -->
    <div id="addFriendModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 400px;">
            <h3 style="margin-bottom: 20px;"><?php echo __('dashboard_add_friend'); ?></h3>
            <div class="form-group">
                <input type="text" id="searchUserInput" placeholder="<?php echo __('dashboard_search_users'); ?>..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div id="searchResults" style="max-height: 200px; overflow-y: auto; margin-bottom: 20px;"></div>
            <div style="text-align: right;">
                <button class="btn btn-secondary" onclick="hideAddFriendModal()" style="margin-right: 10px;"><?php echo __('cancel'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
        // dashboard页面特定的JavaScript
        let searchTimeout;
        
        // 确保toggleSidebar函数可用（备用方案）
        function toggleSidebar() {
            console.log('toggleSidebar called'); // 调试日志
            
            // 优先使用navbar.js中的函数
            if (typeof window.toggleSidebar === 'function' && window.toggleSidebar !== toggleSidebar) {
                console.log('Using navbar toggleSidebar function');
                window.toggleSidebar();
                return;
            }
            
            // 备用方案：直接操作DOM
            console.log('Using fallback toggleSidebar function');
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            
            if (sidebar) {
                sidebar.classList.toggle('open');
                console.log('Sidebar toggled, open:', sidebar.classList.contains('open'));
                
                // 如果有切换按钮，控制其显示
                if (toggleBtn) {
                    if (sidebar.classList.contains('open')) {
                        toggleBtn.style.display = 'none';
                    } else {
                        toggleBtn.style.display = 'flex';
                    }
                }
            } else {
                console.error('Sidebar element not found');
            }
        }
        
        // 页面加载完成后确保函数可用
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard loaded, toggleSidebar available:', typeof toggleSidebar);
            
            // 如果navbar.js中的函数还没有加载，等待一下再尝试
            setTimeout(function() {
                if (typeof window.toggleSidebar === 'function' && window.toggleSidebar !== toggleSidebar) {
                    console.log('Navbar toggleSidebar function is now available');
                }
            }, 100);
        });
        
        // 显示添加好友模态框
        function showAddFriendModal() {
            document.getElementById('addFriendModal').style.display = 'block';
            document.getElementById('searchUserInput').focus();
        }
        
        // 隐藏添加好友模态框
        function hideAddFriendModal() {
            document.getElementById('addFriendModal').style.display = 'none';
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('searchUserInput').value = '';
        }
        
        // 搜索用户
        document.getElementById('searchUserInput').addEventListener('input', function() {
            const keyword = this.value.trim();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (keyword.length >= 2) {
                    searchUsers(keyword);
                } else {
                    document.getElementById('searchResults').innerHTML = '';
                }
            }, 300);
        });
        
        function searchUsers(keyword) {
            fetch(`/Chat_System/chat/searchUsers?q=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    const resultsContainer = document.getElementById('searchResults');
                    
                    if (data.users.length === 0) {
                        resultsContainer.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;"><?php echo __('dashboard_no_users_found'); ?></div>';
                        return;
                    }
                    
                     resultsContainer.innerHTML = data.users.map(user => `
                         <div class="search-result-item" style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 8px; transition: all 0.2s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#667eea';" onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#eee';">
                             <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px; flex-shrink: 0; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                 ${user.avatar ? `<img src="/Chat_System/public/uploads/avatars/${user.avatar}" alt="头像" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">` : user.username.charAt(0).toUpperCase()}
                             </div>
                             <div style="flex: 1; min-width: 0; margin-right: 8px;">
                                 <div style="font-weight: bold; font-size: 13px; margin-bottom: 2px; color: #333;">${user.username}</div>
                                 <div style="font-size: 0.75rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${user.email}</div>
                             </div>
                             <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); sendFriendRequest(${user.id})" style="flex-shrink: 0; padding: 4px 8px !important; font-size: 10px !important; min-width: 45px !important; width: auto !important; transition: all 0.2s ease;"><?php echo __('dashboard_send_friend_request'); ?></button>
                         </div>
                     `).join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('searchResults').innerHTML = '<div style="text-align: center; color: #dc3545; padding: 20px;"><?php echo __('dashboard_search_failed'); ?></div>';
                });
        }
        
        // 发送好友请求
        function sendFriendRequest(friendId) {
            fetch('/Chat_System/chat/sendFriendRequest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `friend_id=${friendId}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    hideAddFriendModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('dashboard_request_failed_retry'); ?>');
            });
        }
        
        // 点击模态框外部关闭
        document.getElementById('addFriendModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddFriendModal();
            }
        });
    </script>
</body>
</html>
