<?php
// navbar.php - 可复用的侧边栏组件
// 需要传入的变量: $user, $rooms, $friends, $pendingRequests, $currentTab

// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();
?>

<style>
    /* 用户头像点击样式 */
    .user-avatar {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .user-avatar:hover {
        transform: scale(1.05);
    }
    
    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .friend-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    
    /* 用户下拉菜单样式 */
    .user-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 50%, #90caf9 100%);
        border: 1px solid #64b5f6;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(33, 150, 243, 0.2);
        z-index: 1000;
        margin-top: 8px;
        overflow: hidden;
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        animation: dropdownFadeIn 0.2s ease-out;
    }
    
    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #1565c0;
        font-weight: 600;
        font-size: 14px;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.6) 100%);
        color: #0d47a1;
        transform: translateX(2px);
        box-shadow: inset 0 2px 4px rgba(33, 150, 243, 0.1);
    }
    
    .dropdown-item:active {
        transform: translateX(1px) scale(0.98);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
    }
    
    .profile-item:hover {
        color: #1976d2;
        text-shadow: 0 1px 2px rgba(25, 118, 210, 0.3);
    }
    
    .favorites-item:hover {
        color: #9c27b0;
        text-shadow: 0 1px 2px rgba(156, 39, 176, 0.3);
    }
    
    .block-item:hover {
        color: #ff9800;
        text-shadow: 0 1px 2px rgba(255, 152, 0, 0.3);
    }
    
    .logout-item:hover {
        color: #d32f2f;
        text-shadow: 0 1px 2px rgba(211, 47, 47, 0.3);
    }
    
    .settings-item:hover {
        color: #7b1fa2;
        text-shadow: 0 1px 2px rgba(123, 31, 162, 0.3);
    }
    
    .dropdown-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(33, 150, 243, 0.3) 50%, transparent 100%);
        margin: 6px 0;
        border-radius: 1px;
    }
    
    /* 确保sidebar-header有相对定位 */
    .sidebar-header {
        position: relative;
    }
    
    /* 请求徽章样式 */
    .request-badge {
        background: #dc3545;
        color: white;
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 0.7rem;
        font-weight: bold;
        margin-left: 5px;
        min-width: 16px;
        text-align: center;
        display: inline-block;
    }
    
    /* 标签页内容样式 */
    .tab-content {
        display: none !important;
    }
    
    .tab-content.active {
        display: block !important;
    }
    
    /* 隐藏类 */
    .hidden {
        display: none !important;
    }
    
    .empty-state {
        text-align: center;
        color: #666;
        padding: 20px;
    }
    
    /* 移动端侧边栏切换按钮 */
    .sidebar-toggle-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1001;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        font-size: 18px;
    }

    .sidebar-toggle-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .sidebar-toggle-btn:active {
        transform: scale(0.95);
    }

    /* 移动端优化 */
    @media (max-width: 768px) {
        .sidebar-toggle-btn {
            display: flex;
        }

        .sidebar {
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            /* 添加触摸支持 */
            touch-action: pan-y;
            -webkit-overflow-scrolling: touch;
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        
        /* 触摸区域 */
        .sidebar-touch-area {
            position: absolute;
            top: 0;
            right: 0;
            width: 30px;
            height: 100%;
            z-index: 1002;
            cursor: pointer;
        }
        
        .sidebar-header {
            padding: 15px 20px;
        }
        
        .user-info {
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .user-details {
            text-align: center;
        }
        
        .user-name {
            font-size: 1rem;
        }
        
        .user-status {
            font-size: 0.8rem;
        }
        
        .user-dropdown {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 300px;
            border-radius: 15px;
        }
        
        .dropdown-item {
            padding: 16px 20px;
            font-size: 16px;
            min-height: 48px;
        }
        
        .sidebar-content {
            padding: 15px 20px;
        }
        
        .tab-buttons {
            flex-direction: column;
            gap: 8px;
        }
        
        .tab-button {
            padding: 12px 16px;
            font-size: 16px;
            min-height: 48px;
            border-radius: 8px;
        }
        
        .tab-content {
            padding: 15px 0;
        }
        
        .room-item, .friend-item, .group-item {
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
        }
        
        .room-avatar, .friend-avatar, .group-avatar {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .room-info h4, .friend-info h4, .group-info h4 {
            font-size: 1rem;
        }
        
        .room-info p, .friend-info p, .group-info p {
            font-size: 0.8rem;
        }
        
        .room-menu-btn, .group-settings-btn {
            min-width: 44px;
            min-height: 44px;
            padding: 8px;
        }
        
        .empty-state {
            padding: 30px 15px;
            font-size: 0.9rem;
        }
        
        .request-badge {
            font-size: 0.8rem;
            padding: 3px 8px;
            min-width: 20px;
        }
    }
    
    @media (max-width: 480px) {
        .sidebar-header {
            padding: 12px 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            font-size: 1.3rem;
        }
        
        .user-name {
            font-size: 0.9rem;
        }
        
        .user-status {
            font-size: 0.75rem;
        }
        
        .sidebar-content {
            padding: 12px 15px;
        }
        
        .tab-button {
            padding: 10px 14px;
            font-size: 15px;
            min-height: 44px;
        }
        
        .room-item, .friend-item, .group-item {
            padding: 10px 14px;
            margin-bottom: 6px;
        }
        
        .room-avatar, .friend-avatar, .group-avatar {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }
        
        .room-info h4, .friend-info h4, .group-info h4 {
            font-size: 0.9rem;
        }
        
        .room-info p, .friend-info p, .group-info p {
            font-size: 0.75rem;
        }
        
        .room-menu-btn, .group-settings-btn {
            min-width: 40px;
            min-height: 40px;
            padding: 6px;
        }
        
        .empty-state {
            padding: 25px 12px;
            font-size: 0.85rem;
        }
    }
    
    .empty-state-large {
        text-align: center;
        color: #666;
        padding: 50px 20px;
    }
    
    /* 添加好友按钮样式 */
    .add-friend-container {
        padding: 15px 10px;
        text-align: center;
    }
    
    .add-friend-btn {
        width: 100%;
        padding: 12px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    
    .add-friend-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    
    .add-friend-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    
    /* 群组相关样式 */
    .add-group-container {
        padding: 15px 10px;
        text-align: center;
    }
    
    .add-group-btn {
        width: 100%;
        padding: 12px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    
    .add-group-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    
    .add-group-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    
    .group-list {
        list-style: none;
    }
    
    .group-item {
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .group-item:hover {
        background: #e9ecef;
    }
    
    .group-item.active {
        background: #28a745;
        color: white;
    }
    
    .room-item.active {
        background: #e3f2fd;
        border-left: 3px solid #2196f3;
    }
    
    .room-item.active .room-name {
        color: #1976d2;
        font-weight: 600;
    }
    
    .group-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
    }
    
    .group-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .group-info {
        flex: 1;
        min-width: 0;
    }
    
    .friend-username {
        font-weight: 400;
        color: #666;
        font-size: 0.85rem;
        margin-left: 4px;
    }
    
    .group-name {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .group-members {
        font-size: 0.8rem;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .group-item.active .group-members {
        color: rgba(255, 255, 255, 0.8);
    }
    
    /* 群组房间标识样式 */
    .group-room {
        border-left: 3px solid #28a745;
    }
    
    .group-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        background: #28a745;
        color: white;
        border-radius: 50%;
        padding: 2px;
        font-size: 0.6rem;
        font-weight: bold;
        border: 2px solid white;
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        line-height: 1;
    }
    
    .group-icon {
        margin-right: 4px;
        font-size: 0.8rem;
    }
    
    .room-item.group-room .room-name {
        color: #28a745;
        font-weight: 600;
    }
    
    .room-item.group-room:hover .room-name {
        color: #218838;
    }
    
    /* 群组设置按钮样式 */
    .group-actions {
        margin-left: auto;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .group-item:hover .group-actions {
        opacity: 1;
    }
    
    .group-settings-btn {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
        color: #666;
    }
    
    .group-settings-btn:hover {
        background: #f0f0f0;
        color: #333;
    }
    
    /* 聊天项操作按钮样式 */
    .room-actions {
        margin-left: auto;
        opacity: 0;
        transition: opacity 0.3s ease;
        position: relative;
    }
    
    .room-item:hover .room-actions {
        opacity: 1;
    }
    
    .room-menu-btn {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
        color: #666;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .room-menu-btn:hover {
        background: #f0f0f0;
        color: #333;
    }
    
    /* 聊天下拉菜单样式 */
    .room-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        margin-top: 4px;
        min-width: 140px;
        overflow: hidden;
        animation: dropdownFadeIn 0.2s ease-out;
    }
    
    .room-dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #333;
        font-size: 14px;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }
    
    .room-dropdown-item:hover {
        background: #f8f9fa;
        color: #007bff;
    }
    
    .room-dropdown-item:active {
        background: #e9ecef;
    }
    
    .room-dropdown-item.pin-item:hover {
        color: #28a745;
    }
    
    .room-dropdown-item.delete-item:hover {
        color: #dc3545;
    }
    
    .room-dropdown-item.info-item:hover {
        color: #17a2b8;
    }
    
    .room-dropdown-divider {
        height: 1px;
        background: #e9ecef;
        margin: 4px 0;
    }
    
    .room-dropdown-item .icon {
        margin-right: 8px;
        font-size: 16px;
    }
    
    /* 置顶状态样式 */
    .room-item.pinned {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 3px solid #ffc107;
    }
    
    .room-item.pinned .room-name {
        color: #856404;
        font-weight: 600;
    }
    
    .pin-indicator {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 12px;
        opacity: 0.8;
    }
    
    .room-info {
        position: relative;
    }
    
    /* 论坛相关样式 */
    .forum-actions-container {
        padding: 15px 10px;
        display: flex;
        gap: 10px;
    }
    
    .create-forum-btn,
    .join-forum-btn {
        flex: 1;
        padding: 12px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    
    .join-forum-btn {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 2px 8px rgba(240, 147, 251, 0.2);
    }
    
    .create-forum-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    
    .join-forum-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(240, 147, 251, 0.3);
        background: linear-gradient(135deg, #e084f0 0%, #e5485c 100%);
    }
    
    .create-forum-btn:active,
    .join-forum-btn:active {
        transform: translateY(0);
    }
    
    .forum-list {
        list-style: none;
    }
    
    .forum-item {
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .forum-item:hover {
        background: #e9ecef;
    }
    
    .forum-item.active {
        background: #17a2b8;
        color: white;
    }
    
    .forum-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
    }
    
    .forum-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .forum-info {
        flex: 1;
        min-width: 0;
    }
    
    .forum-name {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .forum-description {
        font-size: 0.8rem;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }
    
    .forum-stats {
        font-size: 0.75rem;
        color: #999;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .forum-item.active .forum-description,
    .forum-item.active .forum-stats {
        color: rgba(255, 255, 255, 0.8);
    }
    
    /* 论坛操作按钮样式 */
    .forum-actions {
        margin-left: auto;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .forum-item:hover .forum-actions {
        opacity: 1;
    }
    
    .forum-settings-btn {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
        color: #666;
    }
    
    .forum-settings-btn:hover {
        background: #f0f0f0;
        color: #333;
    }
    
    .forum-item.active .forum-settings-btn {
        color: white;
    }
    
    .forum-item.active .forum-settings-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    /* 设置模态框样式 */
    .settings-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: none;
    }
    
    .settings-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .settings-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease-out;
    }
    
    .settings-modal-content {
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 20px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        animation: slideInUp 0.3s ease-out;
    }
    
    .settings-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .settings-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }
    
    .settings-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
        padding: 5px;
        border-radius: 50%;
        transition: all 0.2s ease;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .settings-modal-close:hover {
        background: #e9ecef;
        color: #495057;
        transform: scale(1.1);
    }
    
    .settings-section {
        margin-bottom: 25px;
    }
    
    .settings-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .settings-section-title::before {
        content: '';
        width: 4px;
        height: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    .language-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .language-option {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .language-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        transition: left 0.3s ease;
    }
    
    .language-option:hover::before {
        left: 0;
    }
    
    .language-option:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    }
    
    .language-option.active {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
    }
    
    .language-option.active::after {
        content: '✓';
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        color: #667eea;
        font-weight: bold;
        font-size: 18px;
    }
    
    .language-option.loading {
        opacity: 0.7;
        pointer-events: none;
    }
    
    .language-option.loading::after {
        content: '⏳';
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 16px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: translateY(-50%) rotate(0deg); }
        to { transform: translateY(-50%) rotate(360deg); }
    }
    
    .language-flag {
        width: 32px;
        height: 24px;
        margin-right: 15px;
        border-radius: 4px;
        object-fit: cover;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .language-info {
        flex: 1;
    }
    
    .language-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1rem;
        margin-bottom: 2px;
    }
    
    .language-native-name {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .settings-modal-footer {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .settings-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .settings-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .settings-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .settings-btn-secondary {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #e9ecef;
    }
    
    .settings-btn-secondary:hover {
        background: #e9ecef;
        color: #495057;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* 移动端优化 */
    @media (max-width: 768px) {
        .settings-modal-content {
            margin: 20px;
            padding: 20px;
            max-height: 90vh;
        }
        
        .settings-modal-title {
            font-size: 1.3rem;
        }
        
        .language-option {
            padding: 12px 15px;
        }
        
        .language-flag {
            width: 28px;
            height: 21px;
            margin-right: 12px;
        }
        
        .language-name {
            font-size: 0.95rem;
        }
        
        .language-native-name {
            font-size: 0.8rem;
        }
        
        .settings-modal-footer {
            flex-direction: column;
        }
        
        .settings-btn {
            width: 100%;
            padding: 14px;
        }
    }
</style>

<!-- 移动端侧边栏切换按钮 -->
<div class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebar()" title="打开导航栏">
    <i class="fas fa-bars"></i>
</div>

<!-- 侧边栏 -->
<div class="sidebar" id="sidebar">
    
    <!-- 触摸区域 -->
    <div class="sidebar-touch-area" id="sidebarTouchArea"></div>
    
    <div class="sidebar-header">
        <div class="user-info">
            <div class="user-avatar" onclick="toggleUserMenu()">
                <?php if (!empty($user['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $user['avatar'])): ?>
                    <img src="/Chat_System/public/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo __('avatar_default'); ?>">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="user-details">
                <h3><?php echo htmlspecialchars($user['username'] ?? __('unknown_user', '未知用户')); ?></h3>
                <p><?php echo ucfirst($user['status'] ?? 'offline'); ?></p>
            </div>
        </div>
        
        <!-- 用户下拉菜单 -->
        <div class="user-dropdown hidden" id="userDropdown">
            <div class="dropdown-item profile-item" onclick="goToProfile()">
                <?php echo __('nav_profile'); ?>
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item favorites-item" onclick="goToFavorites()">
                <?php echo __('nav_favorites'); ?>
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item settings-item" onclick="showSettingsModal()">
                <?php echo __('nav_settings'); ?>
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item block-item" onclick="goToBlockedList()">
                <?php echo __('nav_blocked_users'); ?>
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item logout-item" onclick="logout()">
                <?php echo __('nav_logout'); ?>
            </div>
        </div>
    </div>
    
    <div class="sidebar-tabs">
        <button class="tab-button <?php echo ($currentTab === 'chats') ? 'active' : ''; ?>" data-tab="chats"><?php echo __('nav_chats'); ?></button>
        <button class="tab-button <?php echo ($currentTab === 'friends') ? 'active' : ''; ?>" data-tab="friends" title="<?php echo __('nav_friends'); ?>"><?php echo __('nav_friends_short', '好友'); ?></button>
        <button class="tab-button <?php echo ($currentTab === 'groups') ? 'active' : ''; ?>" data-tab="groups" title="<?php echo __('nav_groups'); ?>"><?php echo __('nav_groups_short', '群组'); ?></button>
        <button class="tab-button <?php echo ($currentTab === 'forums') ? 'active' : ''; ?>" data-tab="forums" title="<?php echo __('nav_forums'); ?>"><?php echo __('nav_forums_short', '论坛'); ?></button>
        <button class="tab-button <?php echo ($currentTab === 'requests') ? 'active' : ''; ?>" data-tab="requests" title="<?php echo __('friends_requests'); ?>">
            <?php echo __('friends_requests_short', '请求'); ?>
            <?php if (!empty($pendingRequests) && count($pendingRequests) > 0): ?>
                <span class="request-badge"><?php echo count($pendingRequests); ?></span>
            <?php endif; ?>
        </button>
    </div>
    
    <div class="sidebar-content">
        <!-- 聊天列表 -->
        <div class="tab-content <?php echo ($currentTab === 'chats') ? 'active' : ''; ?>" id="chats-tab">
            <div class="search-box">
                <input type="text" placeholder="<?php echo __('search'); ?>..." id="chat-search">
            </div>
            <ul class="room-list" id="room-list">
                <?php if (empty($rooms)): ?>
                    <li class="empty-state">
                        <?php echo __('chat_no_messages', '暂无聊天记录'); ?>
                    </li>
                <?php else: ?>
                    <?php foreach ($rooms as $roomItem): ?>
                        <li class="room-item <?php echo $roomItem['type'] === 'group' ? 'group-room' : ''; ?> <?php echo !empty($roomItem['pinned']) ? 'pinned' : ''; ?> <?php echo (isset($currentRoomId) && $currentRoomId == $roomItem['id']) ? 'active' : ''; ?>" data-room-id="<?php echo $roomItem['id']; ?>" data-room-type="<?php echo $roomItem['type']; ?>">
                            <div class="room-avatar">
                                <?php 
                                // 显示聊天对象的头像
                                $roomAvatar = $roomItem['avatar'] ?? null;
                                if (!empty($roomAvatar) && $roomAvatar !== 'default_avatar.png' && $roomAvatar !== 'group_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $roomAvatar)) {
                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($roomAvatar) . '" alt="' . __('avatar_default') . '">';
                                } else {
                                    echo strtoupper(substr($roomItem['display_name'], 0, 1));
                                }
                                ?>
                                <?php if ($roomItem['type'] === 'group'): ?>
                                    <div class="group-indicator" data-group-text="<?php echo __('chat_group_short'); ?>"><?php echo mb_substr(__('chat_group_short'), 0, 1); ?></div>
                                <?php elseif ($roomItem['status'] === 'online'): ?>
                                    <div class="status-indicator status-online"></div>
                                <?php endif; ?>
                            </div>
                            <div class="room-info">
                                <div class="room-name">
                                    <?php if ($roomItem['type'] === 'group'): ?>
                                        <span class="group-icon">👥</span>
                                    <?php endif; ?>
                                    <?php 
                                    // 对于私聊，如果有备注则显示【备注（用户名）】格式
                                    if ($roomItem['type'] === 'private' && !empty($roomItem['nickname'])) {
                                        echo '' . htmlspecialchars($roomItem['nickname']) . '（' . htmlspecialchars($roomItem['display_name']) . '）';
                                    } else {
                                        echo htmlspecialchars($roomItem['display_name']);
                                    }
                                    ?>
                                </div>
                                <div class="room-last-message">
                                    <?php echo htmlspecialchars($roomItem['last_message'] ?? __('chat_no_message', '暂无消息')); ?>
                                </div>
                                <?php if (!empty($roomItem['pinned'])): ?>
                                    <div class="pin-indicator" title="<?php echo __('chat_pinned'); ?>">📌</div>
                                <?php endif; ?>
                            </div>
                            <div class="room-actions">
                                <button class="room-menu-btn" onclick="toggleRoomMenu(event, <?php echo $roomItem['id']; ?>, '<?php echo $roomItem['type']; ?>')" title="<?php echo __('more_actions'); ?>">
                                    ⋮
                                </button>
                                <div class="room-dropdown hidden" id="room-dropdown-<?php echo $roomItem['id']; ?>">
                                    <div class="room-dropdown-item pin-item" onclick="pinRoom(<?php echo $roomItem['id']; ?>)">
                                        <span class="icon">📌</span>
                                        <span class="pin-text"><?php echo !empty($roomItem['pinned']) ? __('chat_unpin', '取消置顶') : __('chat_pin', '置顶'); ?></span>
                                    </div>
                                    <div class="room-dropdown-divider"></div>
                                    <div class="room-dropdown-item delete-item" onclick="deleteRoom(<?php echo $roomItem['id']; ?>, '<?php echo $roomItem['type']; ?>')">
                                        <span class="icon">🗑️</span>
                                        <?php echo __('chat_delete', '删除聊天'); ?>
                                    </div>
                                    <div class="room-dropdown-divider"></div>
                                    <a href="/Chat_System/chat/roomDetails?id=<?php echo $roomItem['id']; ?>" class="room-dropdown-item info-item" onclick="event.stopPropagation();">
                                        <span class="icon">ℹ️</span>
                                        <?php echo __('chat_details', '详细资料'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php if ($roomItem['unread_count'] > 0): ?>
                                <div class="unread-badge"><?php echo $roomItem['unread_count']; ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- 好友列表 -->
        <div class="tab-content <?php echo ($currentTab === 'friends') ? 'active' : ''; ?>" id="friends-tab">
            <div class="search-box">
                <input type="text" placeholder="<?php echo __('search'); ?> <?php echo __('nav_friends'); ?>..." id="friend-search">
            </div>
            <div class="add-friend-container">
                <button class="add-friend-btn" onclick="showAddFriendModal()">
                    <?php echo __('friends_add'); ?>
                </button>
            </div>
            <ul class="friend-list" id="friend-list">
                <?php if (empty($friends)): ?>
                    <li class="empty-state">
                        <?php echo __('friends_no_friends'); ?>
                    </li>
                <?php else: ?>
                    <?php foreach ($friends as $friend): ?>
                        <li class="friend-item" data-friend-id="<?php echo $friend['id']; ?>">
                            <div class="friend-avatar">
                                <?php 
                                // 检查头像
                                $avatarValue = $friend['avatar'] ?? null;
                                
                                // 如果avatar为空或为默认值，显示首字母
                                if (empty($avatarValue) || $avatarValue === 'default_avatar.png' || $avatarValue === 'NULL') {
                                    echo strtoupper(substr($friend['username'], 0, 1));
                                } else {
                                    // 检查文件是否存在
                                    $avatarPath = BASE_PATH . '/public/uploads/avatars/' . $avatarValue;
                                    if (file_exists($avatarPath)) {
                                        echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($avatarValue) . '" alt="' . __('avatar_default') . '">';
                                    } else {
                                        echo strtoupper(substr($friend['username'], 0, 1));
                                    }
                                }
                                ?>
                                <div class="status-indicator status-<?php echo $friend['status']; ?>"></div>
                            </div>
                            <div class="friend-info">
                                <div class="friend-name">
                                    <?php 
                                    // 显示备注名称，如果没有备注则显示用户名
                                    if (!empty($friend['nickname'])) {
                                        echo '【' . htmlspecialchars($friend['nickname']) . '（' . htmlspecialchars($friend['username']) . '）】';
                                    } else {
                                        echo htmlspecialchars($friend['username']);
                                    }
                                    ?>
                                </div>
                                <div class="friend-status">
                                    <?php 
                                    if ($friend['status'] === 'online') {
                                        echo __('chat_online');
                                    } elseif ($friend['status'] === 'away') {
                                        echo __('chat_away', '离开');
                                    } else {
                                        echo __('chat_offline');
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- 群组列表 -->
        <div class="tab-content <?php echo ($currentTab === 'groups') ? 'active' : ''; ?>" id="groups-tab">
            <div class="search-box">
                <input type="text" placeholder="<?php echo __('search'); ?> <?php echo __('nav_groups'); ?>..." id="group-search">
            </div>
            <div class="add-group-container">
                <button class="add-group-btn" onclick="showCreateGroupModal()">
                    <?php echo __('chat_create_group'); ?>
                </button>
            </div>
            <ul class="group-list" id="group-list">
                <?php if (empty($groups)): ?>
                    <li class="empty-state">
                        <?php echo __('chat_no_groups', '暂无群组'); ?>
                    </li>
                <?php else: ?>
                    <?php foreach ($groups as $groupItem): ?>
                        <li class="group-item <?php echo (isset($currentGroupId) && $currentGroupId == $groupItem['id']) ? 'active' : ''; ?>" data-group-id="<?php echo $groupItem['id']; ?>">
                            <div class="group-avatar">
                                <?php 
                                // 显示群组头像
                                $groupAvatar = $groupItem['avatar'] ?? null;
                                if (!empty($groupAvatar) && $groupAvatar !== 'default_group_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $groupAvatar)) {
                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($groupAvatar) . '" alt="' . __('avatar_group') . '">';
                                } else {
                                    echo strtoupper(substr($groupItem['name'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="group-info">
                                <div class="group-name"><?php echo htmlspecialchars($groupItem['name']); ?></div>
                                <div class="group-members">
                                    <?php echo $groupItem['member_count']; ?> <?php echo __('chat_group_members'); ?>
                                </div>
                            </div>
                            <div class="group-actions">
                                <button class="group-settings-btn" onclick="goToGroupSettings(<?php echo $groupItem['id']; ?>)" title="<?php echo __('chat_group_settings'); ?>">
                                    ⚙️
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- 论坛列表 -->
        <div class="tab-content <?php echo ($currentTab === 'forums') ? 'active' : ''; ?>" id="forums-tab">
            <div class="search-box">
                <input type="text" placeholder="<?php echo __('search'); ?> <?php echo __('nav_forums'); ?>..." id="forum-search">
            </div>
            <div class="forum-actions-container">
                <button class="create-forum-btn" onclick="showCreateForumModal()">
                    <?php echo __('forum_create', '创建论坛'); ?>
                </button>
                <button class="join-forum-btn" onclick="showJoinForumModal()">
                    <?php echo __('forum_join', '加入论坛'); ?>
                </button>
            </div>
            <ul class="forum-list" id="forum-list">
                <?php if (empty($forums ?? [])): ?>
                    <li class="empty-state">
                        <?php echo __('forum_no_forums', '暂无论坛'); ?>
                    </li>
                <?php else: ?>
                    <?php foreach ($forums as $forum): ?>
                        <li class="forum-item <?php echo (isset($currentForumId) && $currentForumId == $forum['id']) ? 'active' : ''; ?>" data-forum-id="<?php echo $forum['id']; ?>">
                            <div class="forum-avatar">
                                <?php 
                                // 显示论坛头像
                                $forumAvatar = $forum['avatar'] ?? null;
                                if (!empty($forumAvatar) && $forumAvatar !== 'default_forum_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $forumAvatar)) {
                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($forumAvatar) . '" alt="' . __('avatar_forum') . '">';
                                } else {
                                    echo strtoupper(substr($forum['name'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="forum-info">
                                <div class="forum-name"><?php echo htmlspecialchars($forum['name']); ?></div>
                                <div class="forum-description">
                                    <?php echo htmlspecialchars($forum['description'] ?? __('forum_no_description', '暂无描述')); ?>
                                </div>
                                <div class="forum-stats">
                                    <?php echo $forum['member_count'] ?? 0; ?> <?php echo __('forum_members', '成员'); ?> · <?php echo $forum['post_count'] ?? 0; ?> <?php echo __('forum_posts', '帖子'); ?>
                                </div>
                            </div>
                            <div class="forum-actions">
                                <button class="forum-settings-btn" onclick="event.stopPropagation(); goToForumSettings(<?php echo $forum['id']; ?>)" title="<?php echo __('forum_forum_settings'); ?>">
                                    ⚙️
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- 好友请求 -->
        <div class="tab-content <?php echo ($currentTab === 'requests') ? 'active' : ''; ?>" id="requests-tab">
            <div id="requests-list">
                <?php 
                // 检查是否有待处理的请求
                $hasFriendRequests = !empty($pendingRequests);
                $hasForumInvites = !empty($forumInvites ?? []);
                
                if (!$hasFriendRequests && !$hasForumInvites): ?>
                    <div class="empty-state">
                        <?php echo __('no_pending_requests', '暂无待处理的请求'); ?>
                    </div>
                <?php else: ?>
                    <!-- 好友请求 -->
                    <?php if ($hasFriendRequests): ?>
                        <div class="request-section">
                            <h4 style="margin: 0 0 15px 0; color: #333; font-size: 14px; font-weight: 600;"><?php echo __('friends_requests'); ?></h4>
                            <?php foreach ($pendingRequests as $request): ?>
                                <div class="friend-request" data-request-id="<?php echo $request['id']; ?>">
                                    <div class="request-info">
                                        <div class="friend-avatar">
                                            <?php if (!empty($request['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $request['avatar'])): ?>
                                                <img src="/Chat_System/public/uploads/avatars/<?php echo htmlspecialchars($request['avatar']); ?>" alt="<?php echo __('avatar_default'); ?>">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($request['username'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="friend-info">
                                            <div class="friend-name"><?php echo htmlspecialchars($request['username']); ?></div>
                                            <div class="friend-status"><?php echo __('friends_request_message', '请求添加您为好友'); ?></div>
                                        </div>
                                    </div>
                                    <div class="request-actions">
                                        <button class="btn btn-sm btn-success" onclick="handleFriendRequest(<?php echo $request['id']; ?>, 'accept')">
                                            <?php echo __('friends_accept'); ?>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="handleFriendRequest(<?php echo $request['id']; ?>, 'reject')">
                                            <?php echo __('friends_reject'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- 论坛邀请 -->
                    <?php if ($hasForumInvites): ?>
                        <div class="request-section" style="margin-top: 20px;">
                            <h4 style="margin: 0 0 15px 0; color: #333; font-size: 14px; font-weight: 600;"><?php echo __('forum_invites', '论坛邀请'); ?></h4>
                            <?php foreach ($forumInvites as $invite): ?>
                                <div class="forum-invite" data-invite-id="<?php echo $invite['id']; ?>">
                                    <div class="request-info">
                                        <div class="friend-avatar">
                                            <?php if (!empty($invite['inviter_avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $invite['inviter_avatar'])): ?>
                                                <img src="/Chat_System/public/uploads/avatars/<?php echo htmlspecialchars($invite['inviter_avatar']); ?>" alt="<?php echo __('avatar_default'); ?>">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($invite['inviter_username'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="friend-info">
                                            <div class="friend-name"><?php echo htmlspecialchars($invite['inviter_username']); ?></div>
                                            <div class="friend-status"><?php echo __('forum_invite_message'); ?> "<?php echo htmlspecialchars($invite['forum_name']); ?>"</div>
                                            <?php if (!empty($invite['message'])): ?>
                                                <div class="invite-message" style="font-size: 12px; color: #666; margin-top: 2px;">
                                                    "<?php echo htmlspecialchars($invite['message']); ?>"
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="request-actions">
                                        <button class="btn btn-sm btn-success" onclick="handleForumInvite(<?php echo $invite['id']; ?>, 'accept')">
                                            <?php echo __('friends_accept'); ?>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="handleForumInvite(<?php echo $invite['id']; ?>, 'reject')">
                                            <?php echo __('friends_reject'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php $footerVariant = 'sidebar'; include BASE_PATH . '/app/views/components/site-footer.php'; ?>
</div>

<!-- 设置模态框 -->
<div class="settings-modal" id="settingsModal">
    <div class="settings-modal-overlay" onclick="closeSettingsModal()"></div>
    <div class="settings-modal-content">
        <div class="settings-modal-header">
            <h2 class="settings-modal-title"><?php echo __('nav_settings'); ?></h2>
            <button class="settings-modal-close" onclick="closeSettingsModal()">&times;</button>
        </div>
        
        <div class="settings-section">
            <h3 class="settings-section-title"><?php echo __('language'); ?></h3>
            <div class="language-options">
                <div class="language-option <?php echo $lang->getCurrentLanguage() === 'zh' ? 'active' : ''; ?>" data-lang="zh" onclick="selectLanguage('zh')">
                    <img class="language-flag" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAzMiAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjI0IiBmaWxsPSIjRkYwMDAwIi8+CjxwYXRoIGQ9Ik0xNiA0TDE4IDhIMjJMMTkgMTBMMjAgMTRMMTYgMTJMMTIgMTRMMTMgMTBMMTAgOEgxNEwxNiA0WiIgZmlsbD0iI0ZGRkYwMCIvPgo8L3N2Zz4K" alt="<?php echo __('flag_china'); ?>">
                    <div class="language-info">
                        <div class="language-name"><?php echo __('language_chinese'); ?></div>
                        <div class="language-native-name">简体中文</div>
                    </div>
                </div>
                
                <div class="language-option <?php echo $lang->getCurrentLanguage() === 'en' ? 'active' : ''; ?>" data-lang="en" onclick="selectLanguage('en')">
                    <img class="language-flag" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAzMiAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjI0IiBmaWxsPSIjMDAwMDgwIi8+CjxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjRkZGRkZGIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iMCIgeT0iMTAiIHdpZHRoPSIzMiIgaGVpZ2h0PSI0IiBmaWxsPSIjRkZGRkZGIi8+CjxyZWN0IHg9IjEwIiB5PSIwIiB3aWR0aD0iNCIgaGVpZ2h0PSIyNCIgZmlsbD0iI0ZGRkZGRiIvPgo8L3N2Zz4K" alt="<?php echo __('flag_usa'); ?>">
                    <div class="language-info">
                        <div class="language-name"><?php echo __('language_english'); ?></div>
                        <div class="language-native-name">English</div>
                    </div>
                </div>
                
                <div class="language-option <?php echo $lang->getCurrentLanguage() === 'ms' ? 'active' : ''; ?>" data-lang="ms" onclick="selectLanguage('ms')">
                    <img class="language-flag" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAzMiAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjI0IiBmaWxsPSIjRkYwMDAwIi8+CjxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSI4IiBmaWxsPSIjMDAwMDAwIi8+CjxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSI4IiB5PSI4IiBmaWxsPSIjRkZGRkZGIi8+CjxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSI4IiB5PSIxNiIgZmlsbD0iIzAwMDAwMCIvPgo8L3N2Zz4K" alt="<?php echo __('flag_malaysia'); ?>">
                    <div class="language-info">
                        <div class="language-name"><?php echo __('language_malay'); ?></div>
                        <div class="language-native-name">Bahasa Melayu</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="settings-modal-footer">
            <button class="settings-btn settings-btn-secondary" onclick="closeSettingsModal()"><?php echo __('cancel'); ?></button>
        </div>
    </div>
</div>

<script>
// navbar.js - 侧边栏功能脚本
(function() {
    'use strict';
    
    // 全局变量
    let currentTab = '<?php echo $currentTab; ?>';
    let searchTimeout;
    
    // 标签页切换
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.dataset.tab;
            switchTab(tab);
        });
    });
    
    function switchTab(tab) {
        // 更新按钮状态
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
        
        // 更新内容
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(`${tab}-tab`).classList.add('active');
        
        currentTab = tab;
    }
    
    function goToVideoCall() {
        window.location.href = '/Chat_System/chat/videoCall';
    }
    
    // 聊天房间点击
    document.querySelectorAll('.room-item').forEach(item => {
        item.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            const roomType = this.dataset.roomType;
            
            // 根据房间类型决定跳转页面
            if (roomType === 'group') {
                window.location.href = `/Chat_System/chat/group?id=${roomId}`;
            } else {
                window.location.href = `/Chat_System/chat/room?id=${roomId}`;
            }
        });
    });
    
    // 好友点击
    document.querySelectorAll('.friend-item').forEach(item => {
        item.addEventListener('click', function() {
            const friendId = this.dataset.friendId;
            window.location.href = `/Chat_System/chat/startChat?friend_id=${friendId}`;
        });
    });
    
    // 群组点击
    document.querySelectorAll('.group-item').forEach(item => {
        item.addEventListener('click', function() {
            const groupId = this.dataset.groupId;
            
            // 移除所有群组项的active状态
            document.querySelectorAll('.group-item').forEach(groupItem => {
                groupItem.classList.remove('active');
            });
            
            // 为当前点击的群组添加active状态
            this.classList.add('active');
            
            window.location.href = `/Chat_System/chat/group?id=${groupId}`;
        });
    });
    
    // 论坛点击
    document.querySelectorAll('.forum-item').forEach(item => {
        item.addEventListener('click', function(event) {
            // 如果点击的是设置按钮，不处理
            if (event.target.classList.contains('forum-settings-btn')) {
                return;
            }
            
            const forumId = this.dataset.forumId;
            console.log('Forum clicked, ID:', forumId);
            
            // 移除所有论坛项的active状态
            document.querySelectorAll('.forum-item').forEach(forumItem => {
                forumItem.classList.remove('active');
            });
            
            // 为当前点击的论坛添加active状态
            this.classList.add('active');
            
            const targetUrl = `/Chat_System/forum/view?id=${forumId}`;
            console.log('Navigating to:', targetUrl);
            window.location.href = targetUrl;
        });
    });
    
    // 处理好友请求
    function handleFriendRequest(friendId, action) {
        fetch('/Chat_System/chat/handleFriendRequest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `friend_id=${friendId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 移除请求项
                document.querySelector(`[data-request-id="${friendId}"]`).remove();
                
                // 检查是否还有请求
                const requestsList = document.getElementById('requests-list');
                const remainingRequests = requestsList.querySelectorAll('.friend-request').length;
                
                if (remainingRequests === 0) {
                    requestsList.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;"><?php echo __('no_pending_requests', '暂无待处理的请求'); ?></div>';
                }
                
                // 更新请求徽章
                updateRequestBadge(remainingRequests);
                
                alert(data.message);
            } else {
                alert('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>');
        });
    }
    
    // 更新请求徽章
    function updateRequestBadge(count) {
        const requestTab = document.querySelector('[data-tab="requests"]');
        const existingBadge = requestTab.querySelector('.request-badge');
        
        if (count > 0) {
            if (existingBadge) {
                existingBadge.textContent = count;
            } else {
                requestTab.innerHTML = `<?php echo __('friends_requests'); ?> <span class="request-badge">${count}</span>`;
            }
        } else {
            if (existingBadge) {
                existingBadge.remove();
            }
            requestTab.innerHTML = '<?php echo __('friends_requests'); ?>';
        }
    }
    
    // 处理论坛邀请
    function handleForumInvite(inviteId, action) {
        fetch('/Chat_System/forum/handleInvite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `invite_id=${inviteId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 移除邀请项
                document.querySelector(`[data-invite-id="${inviteId}"]`).remove();
                
                // 检查是否还有请求
                const requestsList = document.getElementById('requests-list');
                const remainingFriendRequests = requestsList.querySelectorAll('.friend-request').length;
                const remainingForumInvites = requestsList.querySelectorAll('.forum-invite').length;
                const totalRequests = remainingFriendRequests + remainingForumInvites;
                
                if (totalRequests === 0) {
                    requestsList.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;"><?php echo __('no_pending_requests', '暂无待处理的请求'); ?></div>';
                }
                
                // 更新请求徽章
                updateRequestBadge(totalRequests);
                
                alert(data.message);
                
                // 如果是接受邀请，可以跳转到论坛
                if (action === 'accept' && data.forum_id) {
                    window.location.href = `/Chat_System/forum/view?id=${data.forum_id}`;
                }
            } else {
                alert('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>');
        });
    }
    
    // 切换用户下拉菜单
    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }
    
    // 点击页面其他地方关闭下拉菜单
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const avatar = document.querySelector('.user-avatar');
        
        if (!avatar.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
    
    // 跳转到个人资料页面
    function goToProfile() {
        window.location.href = '/Chat_System/profile';
    }
    
    // 跳转到收藏页面
    function goToFavorites() {
        window.location.href = '/Chat_System/favorites';
    }
    
    // 跳转到封锁列表页面
    function goToBlockedList() {
        window.location.href = '/Chat_System/blocked';
    }
    
    // 登出功能
    function logout() {
        if (confirm('<?php echo __('confirm_logout', '确定要退出登录吗？'); ?>')) {
            window.location.href = '/Chat_System/auth/logout';
        }
    }
    
    // 移动端侧边栏切换
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        
        sidebar.classList.toggle('open');
        
        // 切换按钮的显示/隐藏
        if (sidebar.classList.contains('open')) {
            toggleBtn.style.display = 'none';
        } else {
            toggleBtn.style.display = 'flex';
        }
    }
    
    // 关闭侧边栏
    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        
        sidebar.classList.remove('open');
        toggleBtn.style.display = 'flex';
    }
    
    // 触摸手势处理
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    let isSwipeGesture = false;
    
    // 添加触摸事件监听器
    function initTouchGestures() {
        const sidebar = document.getElementById('sidebar');
        const touchArea = document.getElementById('sidebarTouchArea');
        
        if (!sidebar || !touchArea) return;
        
        // 触摸开始
        touchArea.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            isSwipeGesture = false;
        }, { passive: true });
        
        // 触摸移动
        touchArea.addEventListener('touchmove', function(e) {
            if (!touchStartX || !touchStartY) return;
            
            touchEndX = e.touches[0].clientX;
            touchEndY = e.touches[0].clientY;
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            // 判断是否为水平滑动
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 10) {
                isSwipeGesture = true;
                
            }
        }, { passive: true });
        
        // 触摸结束
        touchArea.addEventListener('touchend', function(e) {
            if (!isSwipeGesture || !touchStartX || !touchStartY) {
                resetTouchState();
                return;
            }
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            // 向左滑动超过50px则关闭侧边栏
            if (deltaX < -50 && Math.abs(deltaX) > Math.abs(deltaY)) {
                closeSidebar();
            }
            
            resetTouchState();
        }, { passive: true });
        
        // 整个侧边栏的触摸事件（用于检测向右滑动打开）
        sidebar.addEventListener('touchstart', function(e) {
            if (sidebar.classList.contains('open')) return;
            
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        sidebar.addEventListener('touchmove', function(e) {
            if (sidebar.classList.contains('open')) return;
            
            touchEndX = e.touches[0].clientX;
            touchEndY = e.touches[0].clientY;
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            // 向右滑动打开侧边栏
            if (deltaX > 50 && Math.abs(deltaX) > Math.abs(deltaY)) {
                sidebar.classList.add('open');
            }
        }, { passive: true });
    }
    
    // 重置触摸状态
    function resetTouchState() {
        touchStartX = 0;
        touchStartY = 0;
        touchEndX = 0;
        touchEndY = 0;
        isSwipeGesture = false;
        
    }
    
    // 点击侧边栏外部关闭
    function initClickOutside() {
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggleBtn');
            
            if (sidebar && sidebar.classList.contains('open')) {
                // 如果点击的不是侧边栏内部且不是切换按钮
                if (!sidebar.contains(e.target) && (!sidebarToggle || !sidebarToggle.contains(e.target))) {
                    closeSidebar();
                }
            }
        });
    }
    
    // 页面加载时初始化标签页状态
    document.addEventListener('DOMContentLoaded', function() {
        // 确保正确的标签页是激活状态
        switchTab(currentTab);
        
        // 初始化请求徽章
        initializeRequestBadge();
        
        // 初始化触摸手势
        initTouchGestures();
        
        // 初始化点击外部关闭
        initClickOutside();
    });
    
    // 初始化请求徽章
    function initializeRequestBadge() {
        const requestsList = document.getElementById('requests-list');
        const friendRequests = requestsList.querySelectorAll('.friend-request');
        const forumInvites = requestsList.querySelectorAll('.forum-invite');
        const totalCount = friendRequests.length + forumInvites.length;
        updateRequestBadge(totalCount);
    }
    
    // 显示添加好友弹窗
    function showAddFriendModal() {
        const username = prompt('<?php echo __('enter_friend_username', '请输入要添加的好友用户名:'); ?>');
        if (username && username.trim()) {
            addFriend(username.trim());
        }
    }
    
    // 添加好友功能
    function addFriend(username) {
        fetch('/Chat_System/chat/addFriend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php echo __('friend_request_sent', '好友请求已发送！'); ?>');
            } else {
                alert('<?php echo __('add_failed', '添加失败'); ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo __('add_failed_retry', '添加失败，请重试'); ?>');
        });
    }
    
    // 显示创建群组弹窗
    function showCreateGroupModal() {
        const groupName = prompt('<?php echo __('enter_group_name', '请输入群组名称:'); ?>');
        if (groupName && groupName.trim()) {
            createGroup(groupName.trim());
        }
    }
    
    // 创建群组功能
    function createGroup(groupName) {
        fetch('/Chat_System/chat/createGroup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `name=${encodeURIComponent(groupName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php echo __('group_created_success', '群组创建成功！'); ?>');
                // 可以在这里刷新群组列表
                location.reload();
            } else {
                alert('<?php echo __('create_failed', '创建失败'); ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo __('create_failed_retry', '创建失败，请重试'); ?>');
        });
    }
    
    // 跳转到群组设置
    function goToGroupSettings(groupId) {
        window.location.href = `/Chat_System/chat/groupSettings?id=${groupId}`;
    }
    
    // 显示创建论坛弹窗
    function showCreateForumModal() {
        const forumName = prompt('<?php echo __('enter_forum_name', '请输入论坛名称:'); ?>');
        if (forumName && forumName.trim()) {
            const forumDescription = prompt('<?php echo __('enter_forum_description', '请输入论坛描述 (可选):'); ?>');
            createForum(forumName.trim(), forumDescription ? forumDescription.trim() : '');
        }
    }
    
    // 创建论坛功能
    function createForum(forumName, forumDescription) {
        fetch('/Chat_System/forum/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `name=${encodeURIComponent(forumName)}&description=${encodeURIComponent(forumDescription)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php echo __('forum_created_success', '论坛创建成功！'); ?>');
                // 可以在这里刷新论坛列表
                location.reload();
            } else {
                alert('<?php echo __('create_failed', '创建失败'); ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo __('create_failed_retry', '创建失败，请重试'); ?>');
        });
    }
    
    // 显示加入论坛弹窗
    function showJoinForumModal() {
        window.location.href = '/Chat_System/list_forum';
    }
    
    // 加入论坛功能
    function joinForum(forumId) {
        fetch('/Chat_System/forum/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `forum_id=${encodeURIComponent(forumId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php echo __('forum_join_success', '成功加入论坛！'); ?>');
                // 可以在这里刷新论坛列表
                location.reload();
            } else {
                alert('<?php echo __('join_failed', '加入失败'); ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo __('join_failed_retry', '加入失败，请重试'); ?>');
        });
    }
    
    // 跳转到论坛设置
    function goToForumSettings(forumId) {
        window.location.href = `/Chat_System/forum/settings?id=${forumId}`;
    }
    
    // 切换房间菜单
    function toggleRoomMenu(event, roomId, roomType) {
        event.stopPropagation();
        
        // 关闭所有其他下拉菜单
        document.querySelectorAll('.room-dropdown').forEach(dropdown => {
            if (dropdown.id !== `room-dropdown-${roomId}`) {
                dropdown.classList.add('hidden');
            }
        });
        
        // 切换当前下拉菜单
        const dropdown = document.getElementById(`room-dropdown-${roomId}`);
        dropdown.classList.toggle('hidden');
    }
    
    // 置顶房间
    function pinRoom(roomId) {
        fetch('/Chat_System/chat/pinRoom', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `room_id=${roomId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 显示成功消息
                showNotification(data.message, 'success');
                
                // 更新UI显示置顶状态
                updateRoomPinStatus(roomId, data.pinned);
                
                // 重新排序聊天列表
                sortRoomList();
            } else {
                showNotification('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>', 'error');
        });
        
        // 关闭下拉菜单
        document.getElementById(`room-dropdown-${roomId}`).classList.add('hidden');
    }
    
    // 更新房间置顶状态显示
    function updateRoomPinStatus(roomId, isPinned) {
        const roomItem = document.querySelector(`[data-room-id="${roomId}"]`);
        if (roomItem) {
            if (isPinned) {
                roomItem.classList.add('pinned');
                // 添加置顶标识
                if (!roomItem.querySelector('.pin-indicator')) {
                    const pinIndicator = document.createElement('div');
                    pinIndicator.className = 'pin-indicator';
                    pinIndicator.innerHTML = '📌';
                    pinIndicator.title = '<?php echo __('chat_pinned', '已置顶'); ?>';
                    roomItem.querySelector('.room-info').appendChild(pinIndicator);
                }
            } else {
                roomItem.classList.remove('pinned');
                const pinIndicator = roomItem.querySelector('.pin-indicator');
                if (pinIndicator) {
                    pinIndicator.remove();
                }
            }
            
            // 更新下拉菜单中的文本
            const dropdown = document.getElementById(`room-dropdown-${roomId}`);
            if (dropdown) {
                const pinText = dropdown.querySelector('.pin-text');
                if (pinText) {
                    pinText.textContent = isPinned ? '<?php echo __('chat_unpin', '取消置顶'); ?>' : '<?php echo __('chat_pin', '置顶'); ?>';
                }
            }
        }
    }
    
    // 重新排序聊天列表
    function sortRoomList() {
        const roomList = document.getElementById('room-list');
        const roomItems = Array.from(roomList.querySelectorAll('.room-item'));
        
        // 按置顶状态和时间排序
        roomItems.sort((a, b) => {
            const aPinned = a.classList.contains('pinned');
            const bPinned = b.classList.contains('pinned');
            
            if (aPinned && !bPinned) return -1;
            if (!aPinned && bPinned) return 1;
            
            // 如果置顶状态相同，按时间排序（这里简化处理）
            return 0;
        });
        
        // 重新排列DOM元素
        roomItems.forEach(item => roomList.appendChild(item));
    }
    
    // 显示通知消息（如果chat-common.js中没有定义）
    if (typeof window.showNotification === 'undefined') {
        window.showNotification = function(message, type = 'info') {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // 添加样式
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
            max-width: 300px;
            word-wrap: break-word;
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
                notification.style.backgroundColor = '#007bff';
        }
        
        // 添加动画样式
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
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
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
        };
    } // 结束showNotification函数定义检查
    
    // 删除房间（不删除好友）
    function deleteRoom(roomId, roomType) {
        const confirmMessage = roomType === 'group' ? '<?php echo __('confirm_delete_group_chat', '确定要删除这个群组聊天吗？'); ?>' : '<?php echo __('confirm_delete_chat', '确定要删除这个聊天吗？'); ?>';
        
        if (confirm(confirmMessage)) {
            fetch('/Chat_System/chat/deleteRoom', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `room_id=${roomId}&room_type=${roomType}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo __('chat_deleted', '聊天已删除'); ?>');
                    // 移除聊天项
                    const roomItem = document.querySelector(`[data-room-id="${roomId}"]`);
                    if (roomItem) {
                        roomItem.remove();
                    }
                } else {
                    alert('<?php echo __('delete_failed', '删除失败'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('delete_failed_retry', '删除失败，请重试'); ?>');
            });
        }
        
        // 关闭下拉菜单
        document.getElementById(`room-dropdown-${roomId}`).classList.add('hidden');
    }
    
    // 显示房间详细信息
    function showRoomInfo(roomId, roomType) {
        console.log('showRoomInfo called with roomId:', roomId, 'roomType:', roomType);
        
        if (roomType === 'group') {
            // 跳转到群组设置页面
            console.log('Navigating to group settings');
            window.location.href = `/Chat_System/chat/groupSettings?id=${roomId}`;
        } else {
            // 跳转到详细资料页面
            console.log('Navigating to room details');
            window.location.href = `/Chat_System/chat/roomDetails?id=${roomId}`;
        }
        
        // 关闭下拉菜单
        const dropdown = document.getElementById(`room-dropdown-${roomId}`);
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }
    
    // 显示房间信息模态框
    function showRoomInfoModal(roomInfo) {
        const modal = document.createElement('div');
        modal.className = 'room-info-modal';
        modal.innerHTML = `
            <div class="modal-overlay" onclick="closeRoomInfoModal()">
                <div class="modal-content" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <h3><?php echo __('chat_details', '聊天详情'); ?></h3>
                        <button class="close-btn" onclick="closeRoomInfoModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="info-item">
                            <label><?php echo __('chat_object', '聊天对象'); ?>:</label>
                            <span>${roomInfo.display_name}</span>
                        </div>
                        <div class="info-item">
                            <label><?php echo __('chat_type', '聊天类型'); ?>:</label>
                            <span>${roomInfo.type === 'group' ? '<?php echo __('group_chat', '群组'); ?>' : '<?php echo __('private_chat', '私聊'); ?>'}</span>
                        </div>
                        <div class="info-item">
                            <label><?php echo __('created_time', '创建时间'); ?>:</label>
                            <span>${roomInfo.created_at}</span>
                        </div>
                        <div class="info-item">
                            <label><?php echo __('last_message', '最后消息'); ?>:</label>
                            <span>${roomInfo.last_message || '<?php echo __('no_message', '暂无消息'); ?>'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // 添加模态框样式
        const style = document.createElement('style');
        style.textContent = `
            .room-info-modal {
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
            }
            .modal-content {
                background: white;
                border-radius: 12px;
                padding: 20px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .close-btn {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #666;
            }
            .info-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                padding: 8px 0;
            }
            .info-item label {
                font-weight: 600;
                color: #333;
            }
            .info-item span {
                color: #666;
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(modal);
    }
    
    // 关闭房间信息模态框
    function closeRoomInfoModal() {
        const modal = document.querySelector('.room-info-modal');
        if (modal) {
            modal.remove();
        }
    }
    
    // 点击页面其他地方关闭所有下拉菜单
    document.addEventListener('click', function(event) {
        // 关闭用户下拉菜单
        const dropdown = document.getElementById('userDropdown');
        const avatar = document.querySelector('.user-avatar');
        
        if (!avatar.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
        
        // 关闭所有房间下拉菜单
        document.querySelectorAll('.room-dropdown').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    });
    
    // 设置模态框相关功能
    let selectedLanguage = '<?php echo $lang->getCurrentLanguage(); ?>';
    
    // 显示设置模态框
    function showSettingsModal() {
        const modal = document.getElementById('settingsModal');
        modal.classList.add('show');
        
        // 关闭用户下拉菜单
        document.getElementById('userDropdown').classList.add('hidden');
        
        // 防止背景滚动
        document.body.style.overflow = 'hidden';
    }
    
    // 关闭设置模态框
    function closeSettingsModal() {
        const modal = document.getElementById('settingsModal');
        modal.classList.remove('show');
        
        // 恢复背景滚动
        document.body.style.overflow = '';
        
        // 重置选择状态
        resetLanguageSelection();
    }
    
    // 选择语言并自动保存
    function selectLanguage(langCode) {
        // 如果选择的是当前语言，直接返回
        if (langCode === '<?php echo $lang->getCurrentLanguage(); ?>') {
            return;
        }
        
        // 移除所有active状态
        document.querySelectorAll('.language-option').forEach(option => {
            option.classList.remove('active');
        });
        
        // 为选中的语言添加active状态
        document.querySelector(`[data-lang="${langCode}"]`).classList.add('active');
        
        // 更新选中的语言
        selectedLanguage = langCode;
        
        // 自动保存语言设置
        applyLanguageSettings(langCode);
    }
    
    // 重置语言选择状态
    function resetLanguageSelection() {
        const currentLang = '<?php echo $lang->getCurrentLanguage(); ?>';
        selectedLanguage = currentLang;
        
        // 移除所有active状态
        document.querySelectorAll('.language-option').forEach(option => {
            option.classList.remove('active');
        });
        
        // 为当前语言添加active状态
        document.querySelector(`[data-lang="${currentLang}"]`).classList.add('active');
    }
    
    // 应用语言设置
    function applyLanguageSettings(langCode = null) {
        const targetLang = langCode || selectedLanguage;
        
        // 显示加载状态
        const languageOption = document.querySelector(`[data-lang="${targetLang}"]`);
        if (languageOption) {
            languageOption.classList.add('loading');
        }
        
        // 发送AJAX请求切换语言
        fetch('/Chat_System/language/switch?lang=' + targetLang, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 切换成功，显示成功消息
                showNotification('<?php echo __('language_switch_success', '语言切换成功'); ?>', 'success');
                
                // 延迟刷新页面以显示新语言
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                // 切换失败，恢复选项状态
                if (languageOption) {
                    languageOption.classList.remove('loading');
                }
                showNotification('<?php echo __('language_switch_failed', '语言切换失败'); ?>', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (languageOption) {
                languageOption.classList.remove('loading');
            }
            showNotification('<?php echo __('network_error', '网络错误'); ?>', 'error');
        });
    }
    
    // 键盘支持
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('settingsModal');
            if (modal.classList.contains('show')) {
                closeSettingsModal();
            }
        }
    });
    
    // 将函数暴露到全局作用域
    window.handleFriendRequest = handleFriendRequest;
    window.handleForumInvite = handleForumInvite;
    window.toggleUserMenu = toggleUserMenu;
    window.goToProfile = goToProfile;
    window.goToFavorites = goToFavorites;
    window.goToBlockedList = goToBlockedList;
    window.logout = logout;
    window.toggleSidebar = toggleSidebar;
    window.closeSidebar = closeSidebar;
    window.switchTab = switchTab;
    window.showAddFriendModal = showAddFriendModal;
    window.showCreateGroupModal = showCreateGroupModal;
    window.goToGroupSettings = goToGroupSettings;
    window.showCreateForumModal = showCreateForumModal;
    window.showJoinForumModal = showJoinForumModal;
    window.goToForumSettings = goToForumSettings;
    window.toggleRoomMenu = toggleRoomMenu;
    window.pinRoom = pinRoom;
    window.deleteRoom = deleteRoom;
    window.showRoomInfo = showRoomInfo;
    window.closeRoomInfoModal = closeRoomInfoModal;
    window.showSettingsModal = showSettingsModal;
    window.closeSettingsModal = closeSettingsModal;
    window.selectLanguage = selectLanguage;
    window.applyLanguageSettings = applyLanguageSettings;
    
})();

// 确保 showRoomInfo 函数在全局作用域中可用
window.showRoomInfo = function(roomId, roomType) {
    console.log('showRoomInfo called with roomId:', roomId, 'roomType:', roomType);
    
    if (roomType === 'group') {
        // 跳转到群组设置页面
        console.log('Navigating to group settings');
        window.location.href = `/Chat_System/chat/groupSettings?id=${roomId}`;
    } else {
        // 跳转到详细资料页面
        console.log('Navigating to room details');
        window.location.href = `/Chat_System/chat/roomDetails?id=${roomId}`;
    }
    
    // 关闭下拉菜单
    const dropdown = document.getElementById(`room-dropdown-${roomId}`);
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
};
</script>

<script>
window.sidebarI18n = <?php echo json_encode([
    'online' => __('chat_online'),
    'offline' => __('chat_offline'),
    'away' => __('chat_away'),
    'noMessage' => __('chat_no_message'),
], JSON_UNESCAPED_UNICODE); ?>;

function formatSidebarStatus(status) {
    const i18n = window.sidebarI18n || {};
    if (status === 'online') return i18n.online || 'Online';
    if (status === 'away') return i18n.away || 'Away';
    return i18n.offline || 'Offline';
}

function updateSidebarRoomList(rooms) {
    const roomList = document.getElementById('room-list');
    if (!roomList || !Array.isArray(rooms)) return;

    const emptyState = roomList.querySelector('.empty-state');
    if (rooms.length === 0) return;
    if (emptyState) emptyState.remove();

    const itemMap = new Map();
    roomList.querySelectorAll('.room-item').forEach(function(li) {
        itemMap.set(li.getAttribute('data-room-id'), li);
    });

    const activeId = roomList.querySelector('.room-item.active')?.getAttribute('data-room-id');
    const fragment = document.createDocumentFragment();

    rooms.forEach(function(room) {
        const id = String(room.id);
        const li = itemMap.get(id);
        if (!li) return;

        const lastMsg = li.querySelector('.room-last-message');
        if (lastMsg) {
            lastMsg.textContent = room.last_message || (window.sidebarI18n?.noMessage || '');
        }

        if (room.type === 'private') {
            const avatar = li.querySelector('.room-avatar');
            if (avatar) {
                let indicator = avatar.querySelector('.status-indicator');
                if (room.status === 'online' || room.status === 'away') {
                    if (!indicator) {
                        indicator = document.createElement('div');
                        avatar.appendChild(indicator);
                    }
                    indicator.className = 'status-indicator status-' + room.status;
                } else if (indicator) {
                    indicator.remove();
                }
            }
        }

        let badge = li.querySelector('.unread-badge');
        const unread = parseInt(room.unread_count, 10) || 0;
        if (unread > 0) {
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'unread-badge';
                li.appendChild(badge);
            }
            badge.textContent = unread;
        } else if (badge) {
            badge.remove();
        }

        li.classList.toggle('pinned', !!room.pinned);
        li.classList.toggle('active', activeId === id);
        fragment.appendChild(li);
    });

    if (fragment.childNodes.length > 0) {
        roomList.innerHTML = '';
        roomList.appendChild(fragment);
    }
}

function updateSidebarFriendList(friends) {
    const friendList = document.getElementById('friend-list');
    if (!friendList || !Array.isArray(friends)) return;

    friends.forEach(function(friend) {
        const li = friendList.querySelector('.friend-item[data-friend-id="' + friend.id + '"]');
        if (!li) return;

        const indicator = li.querySelector('.status-indicator');
        if (indicator) {
            indicator.className = 'status-indicator status-' + (friend.status || 'offline');
        }

        const statusEl = li.querySelector('.friend-status');
        if (statusEl) {
            statusEl.textContent = formatSidebarStatus(friend.status || 'offline');
        }
    });
}

function updateChatHeaderStatus(rooms) {
    const roomId = window.currentRoomId || window.currentChatRoomId;
    if (!roomId || !Array.isArray(rooms)) return;

    const room = rooms.find(function(r) { return String(r.id) === String(roomId); });
    if (!room || room.type !== 'private') return;

    const chatStatus = document.querySelector('.chat-status');
    if (chatStatus) {
        chatStatus.textContent = formatSidebarStatus(room.status || 'offline');
    }
}

function refreshSidebar() {
    const activeRoom = window.currentRoomId || window.currentChatRoomId;
    let url = '/Chat_System/dashboard/getSidebarData';
    if (activeRoom) {
        url += '?active_room_id=' + encodeURIComponent(activeRoom);
    }
    fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (!data.success) return;

        const userStatusEl = document.querySelector('.sidebar-header .user-details p');
        if (userStatusEl && data.user) {
            userStatusEl.textContent = formatSidebarStatus(data.user.status || 'offline');
        }

        updateSidebarRoomList(data.rooms);
        updateSidebarFriendList(data.friends);
        updateChatHeaderStatus(data.rooms);
    })
    .catch(function(err) {
        console.error('refreshSidebar failed:', err);
    });
}

window.refreshSidebar = refreshSidebar;

document.addEventListener('DOMContentLoaded', function() {
    refreshSidebar();
    setInterval(refreshSidebar, 2000);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) refreshSidebar();
    });
    window.addEventListener('focus', refreshSidebar);
});
</script>
