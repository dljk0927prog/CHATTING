<?php
// 保护论坛变量不被意外修改
$originalForum = $forum;
$originalForumId = $forum['id'];
$originalForumName = $forum['name'];
error_log("Forum Settings - 保护变量: ID=" . $originalForum['id'] . ", 名称=" . $originalForum['name'] . ", 头像=" . ($originalForum['avatar'] ?? 'NULL'));
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
    <title><?php echo str_replace('{name}', htmlspecialchars($forum['name']), __('forum_settings_title')); ?></title>
    <!-- 防缓存头部 -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/Chat_System/public/css/style.css?v=<?php echo time(); ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #334155;
        }

        .settings-container {
            width: 100%;
            padding: 20px;
            margin: 0;
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            .settings-container {
                padding: 10px;
            }
            
            .settings-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .header-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .back-btn {
                padding: 10px 16px;
                font-size: 0.9rem;
                min-height: 44px;
            }
            
            .forum-title {
                font-size: 1.5rem;
            }
            
            .settings-grid {
                gap: 20px;
            }
            
            .settings-card {
                padding: 20px;
            }
            
            .card-title {
                font-size: 1.2rem;
                margin-bottom: 15px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                font-size: 0.9rem;
                margin-bottom: 8px;
            }
            
            .form-input, .form-textarea {
                min-height: 44px;
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .form-textarea {
                min-height: 100px;
            }
            
            .btn {
                min-height: 44px;
                padding: 12px 20px;
                font-size: 16px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-group .btn {
                width: 100%;
            }
            
            .avatar-section {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            
            .current-avatar {
                width: 120px;
                height: 120px;
            }
            
            .avatar-actions {
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }
            
            .avatar-actions .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .settings-container {
                padding: 5px;
            }
            
            .settings-header {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .forum-title {
                font-size: 1.3rem;
            }
            
            .settings-card {
                padding: 15px;
            }
            
            .card-title {
                font-size: 1.1rem;
                margin-bottom: 12px;
            }
            
            .form-input, .form-textarea {
                min-height: 42px;
                font-size: 16px;
                padding: 10px 12px;
            }
            
            .btn {
                min-height: 42px;
                padding: 10px 16px;
                font-size: 16px;
            }
            
            .current-avatar {
                width: 100px;
                height: 100px;
            }
        }

        .settings-header {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            width: 100%;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #475569;
        }

        .back-btn:hover {
            background: #e2e8f0;
            transform: translateX(-2px);
        }

        .forum-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .permission-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .permission-owner {
            background: #dcfce7;
            color: #166534;
        }

        .permission-admin {
            background: #fef3c7;
            color: #92400e;
        }

        .permission-member {
            background: #f1f5f9;
            color: #475569;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            width: 100%;
        }

        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #3b82f6;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.2s;
            resize: vertical;
            min-height: 100px;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.2s;
            background: white;
        }

        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .members-avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 16px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .member-avatar-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .member-avatar-item:hover {
            transform: translateY(-2px);
        }

        .member-avatar-small {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            overflow: hidden;
            position: relative;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .member-avatar-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .member-role-indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            background: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .role-icon.role-creator {
            color: #166534;
        }

        .role-icon.role-admin {
            color: #92400e;
        }

        .member-status-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .member-status-indicator.status-online {
            background: #10b981;
        }

        .member-status-indicator.status-away {
            background: #f59e0b;
        }

        .member-status-indicator.status-offline {
            background: #6b7280;
        }

        .member-actions-menu {
            position: absolute;
            top: -10px;
            left: -10px;
            display: none;
            gap: 4px;
            background: white;
            padding: 4px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10;
        }

        .member-avatar-item:hover .member-actions-menu {
            display: flex;
        }

        .action-btn {
            width: 24px;
            height: 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .promote-btn {
            background: #f59e0b;
            color: white;
        }

        .promote-btn:hover {
            background: #d97706;
        }

        .demote-btn {
            background: #6b7280;
            color: white;
        }

        .demote-btn:hover {
            background: #4b5563;
        }

        .remove-btn {
            background: #ef4444;
            color: white;
        }

        .remove-btn:hover {
            background: #dc2626;
        }

        .invite-member-btn {
            width: 60px;
            height: 60px;
            min-width: 60px;
            min-height: 60px;
            border-radius: 50%;
            background: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            aspect-ratio: 1;
        }

        .invite-member-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .invite-icon {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .member-action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
            margin-left: 30px;
        }

        .remove-member-btn {
            width: 60px;
            height: 60px;
            min-width: 60px;
            min-height: 60px;
            border-radius: 50%;
            background: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            aspect-ratio: 1;
        }

        .remove-member-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .remove-icon {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .friends-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }

        .friend-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            gap: 12px;
        }

        .friend-item:last-child {
            border-bottom: none;
        }

        .friend-item:hover {
            background: #f3f4f6;
        }

        .friend-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            overflow: hidden;
        }

        .friend-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .friend-info {
            flex: 1;
        }

        .friend-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .friend-status {
            font-size: 12px;
            color: #6b7280;
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
            background: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: auto !important;
            height: auto !important;
        }

        .friend-status.status-online {
            color: #10b981 !important;
            background: none !important;
        }

        .friend-status.status-away {
            color: #f59e0b !important;
            background: none !important;
        }

        .friend-status.status-offline {
            color: #6b7280 !important;
            background: none !important;
        }

        .invite-friend-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .invite-friend-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .remove-member-btn-modal {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .remove-member-btn-modal:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .invite-friend-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .role-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .role-creator {
            background: #dcfce7;
            color: #166534;
        }

        .role-admin {
            background: #fef3c7;
            color: #92400e;
        }

        .role-member {
            background: #f1f5f9;
            color: #475569;
        }

        .requests-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .request-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 12px;
            background: #fef3c7;
            transition: all 0.2s ease;
        }

        .request-item:hover {
            background: #fde68a;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .request-actions {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #374151;
        }

        .action-section {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .action-section h4 {
            color: #ea580c;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .action-section p {
            color: #ea580c;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .creator-info {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .creator-info h4 {
            color: #92400e;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .creator-info p {
            color: #92400e;
            margin-bottom: 0;
            line-height: 1.6;
        }

        .danger-zone {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .danger-zone h4 {
            color: #dc2626;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .danger-zone p {
            color: #dc2626;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .danger-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .danger-info {
            background: rgba(220, 38, 38, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .danger-info p {
            margin-bottom: 8px;
            font-size: 14px;
            color: #991b1b;
        }

        .danger-info p:last-child {
            margin-bottom: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .current-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 2.5rem;
            overflow: hidden;
            border: 4px solid rgba(102, 126, 234, 0.3);
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .current-avatar:hover {
            transform: scale(1.05);
            border-color: rgba(102, 126, 234, 0.6);
        }

        .current-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 50%;
        }

        .current-avatar:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-overlay span {
            color: white;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
        }

        .forum-name-display {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            justify-content: center;
        }

        .forum-name-text {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
            flex: 1;
        }

        .edit-name-btn {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }

        .edit-name-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            backdrop-filter: blur(5px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translate(-50%, -50%) scale(1);
        }

        .modal-title {
            margin-bottom: 20px;
            color: #1e293b;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .modal .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .modal .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .modal .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .modal .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }

        .modal .btn-secondary:hover {
            background: #e9ecef;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 3px solid #e0e0e0;
            overflow: hidden;
            position: relative;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-preview .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        .file-input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-container {
                padding: 16px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }

            .current-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }

            .forum-name-display {
                min-width: 150px;
                padding: 10px 16px;
            }

            .forum-name-text {
                font-size: 16px;
            }

            .edit-name-btn {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .members-avatar-grid {
                grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
                gap: 12px;
                padding: 16px;
            }
            
            .member-avatar-small {
                width: 50px;
                height: 50px;
                font-size: 16px;
            }

            .member-avatar {
                width: 35px;
                height: 35px;
                font-size: 14px;
                margin-right: 10px;
            }
            
            .member-role-indicator {
                width: 16px;
                height: 16px;
                font-size: 10px;
            }
            
            .member-status-indicator {
                width: 10px;
                height: 10px;
            }
            
            .member-action-buttons {
                gap: 8px;
                flex-wrap: nowrap;
                margin-left: 20px;
            }

            .invite-member-btn {
                width: 50px;
                height: 50px;
                min-width: 50px;
                min-height: 50px;
                flex-shrink: 0;
                aspect-ratio: 1;
            }
            
            .remove-member-btn {
                width: 50px;
                height: 50px;
                min-width: 50px;
                min-height: 50px;
                flex-shrink: 0;
                aspect-ratio: 1;
            }
            
            .invite-icon, .remove-icon {
                font-size: 20px;
            }
            
            .member-actions-menu {
                top: -8px;
                left: -8px;
            }
            
            .action-btn {
                width: 20px;
                height: 20px;
                font-size: 10px;
            }

            .action-section,
            .danger-zone,
            .creator-info {
                padding: 15px;
            }
        }

        /* 确保内容区域充分利用空间 */
        .chat-area {
            width: 100%;
            min-height: 100vh;
            overflow-y: auto;
        }

        .settings-container {
            min-height: 100vh;
            padding-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="chat-page-container">
        <div class="chat-container">
            <!-- 引入侧边栏组件 -->
            <?php 
            // 在包含navbar之前检查变量状态
            error_log("Forum Settings - 包含navbar前: ID=" . $forum['id'] . ", 名称=" . $forum['name']);
            include __DIR__ . '/../components/navbar.php';
            // 在包含navbar之后检查变量状态
            error_log("Forum Settings - 包含navbar后: ID=" . $forum['id'] . ", 名称=" . $forum['name']);
            
            // 如果变量被修改，恢复原始值
            if ($forum['id'] != $originalForumId || $forum['name'] != $originalForumName) {
                error_log("Forum Settings - 检测到变量被修改，恢复原始值");
                error_log("Forum Settings - 恢复前: ID=" . $forum['id'] . ", 名称=" . $forum['name'] . ", 头像=" . ($forum['avatar'] ?? 'NULL'));
                $forum = $originalForum;
                error_log("Forum Settings - 恢复后: ID=" . $forum['id'] . ", 名称=" . $forum['name'] . ", 头像=" . ($forum['avatar'] ?? 'NULL'));
            }
            
            // 添加调试信息
            error_log("Forum Settings - 页面继续执行，准备显示头像");
            ?>
            
            <!-- 设置区域 -->
            <div class="chat-area">
                <div class="settings-container">
                    <!-- 页面头部 -->
                    <div class="settings-header">
                        <div class="header-top">
                            <a href="/Chat_System/forum/view?id=<?php echo $forum['id']; ?>" class="back-btn">
                                <span>←</span>
                                <span><?php echo __('back_to_forum', '返回论坛'); ?></span>
                            </a>
                            <div class="permission-badge <?php echo ($userRole === 'creator') ? 'permission-owner' : (($userRole === 'admin') ? 'permission-admin' : 'permission-member'); ?>">
                                <?php if ($userRole === 'creator'): ?>
                                    👑 <?php echo __('forum_creator', '创建者'); ?>
                                <?php elseif ($userRole === 'admin'): ?>
                                    ⚡ <?php echo __('forum_admin', '管理员'); ?>
                                <?php else: ?>
                                    👤 <?php echo __('forum_member', '成员'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <h1 class="forum-title"><?php echo htmlspecialchars($forum['name']); ?> <?php echo __('settings', '设置'); ?></h1>
                        <div style="font-size: 14px; color: #64748b; margin-top: 8px;">
                            <?php echo __('forum_id_label', '论坛ID'); ?>: <?php echo $forum['id']; ?> | <?php echo __('member_count_label', '成员数'); ?>: <?php echo count($members); ?> | 
                            <?php echo __('created_time_label', '创建时间'); ?>: <?php echo date('Y-m-d H:i', strtotime($forum['created_at'])); ?>
                        </div>
                    </div>

                    <div class="settings-grid">
                        <!-- 基本信息 -->
                        <div class="settings-card">
                            <h3 class="card-title"><?php echo __('basic_info', '基本信息'); ?></h3>
                            
                            <!-- 论坛头像和名称 -->
                            <div class="form-group">
                                <label class="form-label"><?php echo __('forum_info', '论坛信息'); ?></label>
                                <div class="avatar-section">
                                    <div class="current-avatar" onclick="showAvatarModal()">
                                        <?php 
                                        // 显示论坛头像
                                        error_log("Avatar Debug - 头像字段: " . ($forum['avatar'] ?? 'NULL'));
                                        error_log("Avatar Debug - 文件路径: " . __DIR__ . '/public/uploads/avatars/' . ($forum['avatar'] ?? 'NULL'));
                                        error_log("Avatar Debug - 文件存在: " . (file_exists(__DIR__ . '/public/uploads/avatars/' . ($forum['avatar'] ?? '')) ? 'true' : 'false'));
                                        
                                        if (!empty($forum['avatar']) && $forum['avatar'] !== 'default_forum_avatar.png' && file_exists(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $forum['avatar'])) {
                                            $timestamp = filemtime(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $forum['avatar']);
                                            echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($forum['avatar']) . '?t=' . $timestamp . '" alt="论坛头像">';
                                            error_log("Avatar Debug - 显示头像图片: " . $forum['avatar']);
                                        } else {
                                            echo strtoupper(substr($forum['name'], 0, 1));
                                            error_log("Avatar Debug - 显示默认字母: " . strtoupper(substr($forum['name'], 0, 1)));
                                        }
                                        ?>
                                        <div class="avatar-overlay">
                                            <span><?php echo __('change_avatar', '更换头像'); ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- 论坛名称显示 -->
                                    <div class="forum-name-display">
                                        <span class="forum-name-text"><?php echo htmlspecialchars($forum['name']); ?></span>
                                        <?php if ($userRole === 'creator' || $userRole === 'admin'): ?>
                                            <button class="edit-name-btn" onclick="showForumNameModal()" title="<?php echo __('edit_forum_name', '修改论坛名称'); ?>">
                                                ✏️
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 论坛设置表单 -->
                            <form id="forumSettingsForm">
                                <input type="hidden" id="forumId" value="<?php echo $forum['id']; ?>">
                                
                                <div class="form-group">
                                    <label class="form-label" for="forumName"><?php echo __('forum_name', '论坛名称'); ?></label>
                                    <input type="text" id="forumName" class="form-input" value="<?php echo htmlspecialchars($forum['name']); ?>" maxlength="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="forumDescription"><?php echo __('forum_description', '论坛描述'); ?></label>
                                    <textarea id="forumDescription" class="form-textarea" rows="4"><?php echo htmlspecialchars($forum['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="maxMembers"><?php echo __('max_members', '最大成员数'); ?></label>
                                        <input type="number" id="maxMembers" class="form-input" value="<?php echo $forum['max_members'] ?? 1000; ?>" min="1" max="10000">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="isPublic"><?php echo __('forum_type', '论坛类型'); ?></label>
                                        <select id="isPublic" class="form-select">
                                            <option value="1" <?php echo ($forum['is_public'] ?? true) ? 'selected' : ''; ?>><?php echo __('public_forum', '公开论坛'); ?></option>
                                            <option value="0" <?php echo !($forum['is_public'] ?? true) ? 'selected' : ''; ?>><?php echo __('private_forum', '私有论坛'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><?php echo __('save_settings', '保存设置'); ?></button>
                                    <button type="button" class="btn btn-secondary" onclick="location.reload()"><?php echo __('reset', '重置'); ?></button>
                                </div>
                            </form>
                        </div>

                        <!-- 成员管理 -->
                        <div class="settings-card">
                            <h3 class="card-title"><?php echo __('member_management', '成员管理'); ?></h3>
                            
                            <!-- 成员头像网格 -->
                            <div class="form-group">
                                <label class="form-label"><?php echo __('forum_members', '论坛成员'); ?> (<?php echo count($members); ?>)</label>
                                <div class="members-avatar-grid">
                                    <?php foreach ($members as $member): ?>
                                        <div class="member-avatar-item" data-member-id="<?php echo $member['user_id']; ?>" 
                                             title="<?php echo htmlspecialchars($member['username']); ?> - <?php echo $member['role'] === 'creator' ? __('forum_creator', '创建者') : ($member['role'] === 'admin' ? __('forum_admin', '管理员') : __('forum_member', '成员')); ?>">
                                            <div class="member-avatar-small">
                                                <?php 
                                                $memberAvatar = $member['avatar'] ?? null;
                                                if (!empty($memberAvatar) && $memberAvatar !== 'default_avatar.png' && file_exists(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $memberAvatar)) {
                                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($memberAvatar) . '" alt="头像">';
                                                } else {
                                                    echo strtoupper(substr($member['username'], 0, 1));
                                                }
                                                ?>
                                            </div>
                                            <div class="member-role-indicator">
                                                <?php if ($member['role'] === 'creator'): ?>
                                                    <span class="role-icon role-creator">👑</span>
                                                <?php elseif ($member['role'] === 'admin'): ?>
                                                    <span class="role-icon role-admin">⚡</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="member-status-indicator status-<?php echo $member['status'] ?? 'offline'; ?>"></div>
                                            
                                            <!-- 成员操作菜单 -->
                                            <?php if (($userRole === 'creator' || $userRole === 'admin') && $member['role'] !== 'creator' && $member['user_id'] != $_SESSION['user_id']): ?>
                                                <div class="member-actions-menu">
                                                    <?php if ($member['role'] === 'member'): ?>
                                                        <button class="action-btn promote-btn" onclick="promoteToAdmin(<?php echo $member['user_id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')" title="<?php echo __('set_admin', '设为管理员'); ?>">
                                                            ⚡
                                                        </button>
                                                    <?php elseif ($member['role'] === 'admin' && $userRole === 'creator'): ?>
                                                        <button class="action-btn demote-btn" onclick="demoteFromAdmin(<?php echo $member['user_id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')" title="<?php echo __('remove_admin', '取消管理员'); ?>">
                                                            👤
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="action-btn remove-btn" onclick="removeMember(<?php echo $member['user_id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')" title="<?php echo __('remove_member', '移除成员'); ?>">
                                                        🚫
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- 成员操作按钮 -->
                                    <?php if ($userRole === 'creator' || $userRole === 'admin'): ?>
                                        <div class="member-action-buttons">
                                            <div class="invite-member-btn" onclick="showInviteMemberModal()" title="<?php echo __('invite_member', '邀请成员'); ?>">
                                                <div class="invite-icon">➕</div>
                                            </div>
                                            <div class="remove-member-btn" onclick="showRemoveMemberModal()" title="<?php echo __('remove_member', '移除成员'); ?>">
                                                <div class="remove-icon">➖</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 加入请求 -->
                        <div class="settings-card">
                            <h3 class="card-title"><?php echo __('join_requests', '加入请求'); ?></h3>
                            
                            <?php if (empty($pendingRequests)): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">📝</div>
                                    <h3><?php echo __('no_pending_requests', '暂无待处理的请求'); ?></h3>
                                    <p><?php echo __('all_requests_processed', '所有加入请求都已处理完毕'); ?></p>
                                </div>
                            <?php else: ?>
                                <ul class="requests-list">
                                    <?php foreach ($pendingRequests as $request): ?>
                                        <li class="request-item">
                                            <div class="member-avatar">
                                                <?php 
                                                $requestAvatar = $request['avatar'] ?? null;
                                                if (!empty($requestAvatar) && $requestAvatar !== 'default_avatar.png' && file_exists(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $requestAvatar)) {
                                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($requestAvatar) . '" alt="头像">';
                                                } else {
                                                    echo strtoupper(substr($request['username'], 0, 1));
                                                }
                                                ?>
                                            </div>
                                            <div class="member-info">
                                                <div class="member-name"><?php echo htmlspecialchars($request['username']); ?></div>
                                                <div class="member-role">
                                                    <?php echo __('request_time', '申请时间'); ?>: <?php echo date('Y-m-d H:i', strtotime($request['requested_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="request-actions">
                                                <button class="btn btn-sm btn-primary" onclick="handleJoinRequest(<?php echo $request['id']; ?>, 'approved')"><?php echo __('approve', '同意'); ?></button>
                                                <button class="btn btn-sm btn-danger" onclick="handleJoinRequest(<?php echo $request['id']; ?>, 'rejected')"><?php echo __('reject', '拒绝'); ?></button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 论坛操作 -->
                        <div class="settings-card">
                            <h3 class="card-title"><?php echo __('forum_operations', '论坛操作'); ?></h3>
                            
                            <!-- 退出论坛（非创建者显示） -->
                            <?php if ($userRole !== 'creator'): ?>
                            <div class="action-section">
                                <h4>🚪 <?php echo __('leave_forum', '退出论坛'); ?></h4>
                                <p><?php echo __('leave_forum_description', '离开此论坛，您将无法再访问论坛内容。'); ?></p>
                                <button class="btn btn-warning" onclick="leaveForum()"><?php echo __('leave_forum', '退出论坛'); ?></button>
                            </div>
                            <?php endif; ?>
                            
                            <!-- 创建者专用操作 -->
                            <?php if ($userRole === 'creator'): ?>
                                <!-- 创建者说明 -->
                                <div class="creator-info">
                                    <h4>👑 <?php echo __('creator_permissions', '创建者权限'); ?></h4>
                                    <p><?php echo __('creator_permissions_description', '作为论坛创建者，您拥有特殊的管理权限。创建者不能退出论坛，只能删除论坛。'); ?></p>
                                </div>
                                
                                <div class="danger-zone">
                                    <h4>⚠️ <?php echo __('delete_forum', '删除论坛'); ?></h4>
                                    <p><?php echo __('delete_forum_description', '删除论坛将永久移除所有数据，包括帖子、回复和成员信息。此操作不可撤销。'); ?></p>
                                    <div class="danger-buttons">
                                        <button class="btn btn-danger" onclick="deleteForum()">🗑️ <?php echo __('delete_forum', '删除论坛'); ?></button>
                                    </div>
                                    <div class="danger-info">
                                        <p><strong><?php echo __('delete_operations_include', '删除操作包括'); ?></strong>：</p>
                                        <p>• <?php echo __('delete_forum_and_data', '完全删除论坛和所有数据'); ?></p>
                                        <p>• <?php echo __('delete_posts_replies_attachments', '删除所有帖子、回复和附件'); ?></p>
                                        <p>• <?php echo __('remove_all_member_relationships', '移除所有成员关系'); ?></p>
                                        <p>• <?php echo __('clear_all_join_requests', '清除所有加入请求'); ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">🔒</div>
                                    <h3><?php echo __('insufficient_permissions', '权限不足'); ?></h3>
                                    <p><?php echo __('only_creator_can_perform_dangerous_operations', '只有论坛创建者才能执行危险操作'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 头像上传模态框 -->
    <div id="avatarModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><?php echo __('change_forum_avatar', '更换论坛头像'); ?></h3>
            <div class="avatar-preview" id="avatarPreview">
                <div class="default-avatar">
                    <?php echo strtoupper(substr($forum['name'], 0, 1)); ?>
                </div>
            </div>
            <div class="file-input-wrapper">
                <div class="file-input">
                    <input type="file" id="avatarInput" accept="image/*" onchange="previewAvatar(this)">
                    <span><?php echo __('click_to_select_image', '点击选择图片或拖拽到此处'); ?></span>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideAvatarModal()"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary" onclick="uploadAvatar()"><?php echo __('save'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- 论坛名称修改模态框 -->
    <div id="forumNameModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><?php echo __('edit_forum_name', '修改论坛名称'); ?></h3>
            <div class="form-group">
                <label class="form-label"><?php echo __('new_forum_name', '新论坛名称'); ?></label>
                <input type="text" id="newForumName" class="form-input" 
                       value="<?php echo htmlspecialchars($forum['name']); ?>" 
                       maxlength="100" 
                       placeholder="<?php echo __('enter_forum_name', '请输入论坛名称'); ?>">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <?php echo __('max_100_characters', '最多100个字符'); ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideForumNameModal()"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary" onclick="updateForumName()"><?php echo __('save'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- 邀请成员模态框 -->
    <div id="inviteMemberModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><?php echo __('invite_member', '邀请成员'); ?></h3>
            <div class="form-group">
                <label class="form-label"><?php echo __('search_friends', '搜索好友'); ?></label>
                <input type="text" id="friendSearchInput" class="form-input"
                       placeholder="<?php echo __('search_friend_username', '搜索好友用户名'); ?>" onkeyup="searchFriends()">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <?php echo __('select_friends_to_invite', '从您的好友列表中选择要邀请的成员'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo __('friends_list', '好友列表'); ?></label>
                <div class="friends-list" id="friendsList">
                    <?php 
                    // 获取已经是论坛成员的好友ID
                    $memberIds = array_column($members, 'user_id');
                    foreach ($friends as $friend): 
                        if (!in_array($friend['id'], $memberIds)): // 只显示不是论坛成员的好友
                    ?>
                        <div class="friend-item" data-friend-id="<?php echo $friend['id']; ?>" data-friend-username="<?php echo htmlspecialchars($friend['username']); ?>">
                            <div class="friend-avatar">
                                <?php 
                                if (!empty($friend['avatar']) && $friend['avatar'] !== 'default_avatar.png' && file_exists(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $friend['avatar'])) {
                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($friend['avatar']) . '" alt="头像">';
                                } else {
                                    echo strtoupper(substr($friend['username'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="friend-info">
                                <div class="friend-name"><?php echo htmlspecialchars($friend['username']); ?></div>
                                <div class="friend-status status-<?php echo $friend['status']; ?>">
                                    <?php 
                                    if ($friend['status'] === 'online') {
                                        echo '🟢 ' . __('online');
                                    } elseif ($friend['status'] === 'away') {
                                        echo '🟡 ' . __('away');
                                    } else {
                                        echo '⚫ ' . __('offline');
                                    }
                                    ?>
                                </div>
                            </div>
                            <button class="invite-friend-btn" onclick="inviteFriend(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['username']); ?>')">
                                <?php echo __('invite', '邀请'); ?>
                            </button>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideInviteMemberModal()"><?php echo __('cancel'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- 移除成员模态框 -->
    <div id="removeMemberModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><?php echo __('remove_member', '移除成员'); ?></h3>
            <div class="form-group">
                <label class="form-label"><?php echo __('search_members', '搜索成员'); ?></label>
                <input type="text" id="memberSearchInput" class="form-input"
                       placeholder="<?php echo __('search_member_username', '搜索成员用户名'); ?>" onkeyup="searchMembers()">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <?php echo __('select_member_to_remove', '从论坛成员列表中选择要移除的成员'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo __('members_list', '成员列表'); ?></label>
                <div class="friends-list" id="membersList">
                    <?php 
                    foreach ($members as $member): 
                        if ($member['role'] !== 'creator' && $member['user_id'] != $_SESSION['user_id']): // 不能移除创建者和自己
                    ?>
                        <div class="member-item" data-member-id="<?php echo $member['user_id']; ?>" data-member-username="<?php echo htmlspecialchars($member['username']); ?>">
                            <div class="friend-avatar">
                                <?php 
                                if (!empty($member['avatar']) && $member['avatar'] !== 'default_avatar.png' && file_exists(dirname(__DIR__, 3) . '/public/uploads/avatars/' . $member['avatar'])) {
                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($member['avatar']) . '" alt="头像">';
                                } else {
                                    echo strtoupper(substr($member['username'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="friend-info">
                                <div class="friend-name"><?php echo htmlspecialchars($member['username']); ?></div>
                                <div class="friend-status">
                                    <?php 
                                    if ($member['role'] === 'admin') {
                                        echo '⚡ ' . __('admin');
                                    } else {
                                        echo '👤 ' . __('member');
                                    }
                                    ?>
                                </div>
                            </div>
                            <button class="remove-member-btn-modal" onclick="removeMember(<?php echo $member['user_id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')">
                                <?php echo __('remove', '移除'); ?>
                            </button>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideRemoveMemberModal()"><?php echo __('cancel'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
        const forumId = <?php echo $forum['id']; ?>;
        const userId = <?php echo $_SESSION['user_id']; ?>;
        const userRole = '<?php echo $userRole; ?>';
        
        let selectedFile = null;
        
        // 头像上传相关函数
        function showAvatarModal() {
            const modal = document.getElementById('avatarModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }
        
        function hideAvatarModal() {
            const modal = document.getElementById('avatarModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
            document.getElementById('avatarInput').value = '';
            selectedFile = null;
            resetPreview();
        }
        
        function previewAvatar(input) {
            const file = input.files[0];
            if (file) {
                selectedFile = file;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="预览头像">`;
                };
                reader.readAsDataURL(file);
            }
        }
        
        function resetPreview() {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<div class="default-avatar"><?php echo strtoupper(substr($forum['name'], 0, 1)); ?></div>`;
        }
        
        function uploadAvatar() {
            if (!selectedFile) {
                alert('<?php echo __('please_select_image', '请先选择一张图片'); ?>');
                return;
            }
            
            const formData = new FormData();
            formData.append('avatar', selectedFile);
            formData.append('forum_id', forumId);
            
            fetch('/Chat_System/forum/updateForumAvatar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo __('avatar_updated_success', '头像更新成功！'); ?>');
                    location.reload();
                } else {
                    alert('<?php echo __('upload_failed', '上传失败'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('upload_failed_retry', '上传失败，请重试'); ?>');
            });
        }
        
        // 显示论坛名称修改弹窗
        function showForumNameModal() {
            const modal = document.getElementById('forumNameModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.getElementById('newForumName').focus();
            document.getElementById('newForumName').select();
        }
        
        // 隐藏论坛名称修改弹窗
        function hideForumNameModal() {
            const modal = document.getElementById('forumNameModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // 更新论坛名称
        function updateForumName() {
            const newName = document.getElementById('newForumName').value.trim();
            if (!newName) {
                alert('<?php echo __('forum_name_cannot_be_empty', '论坛名称不能为空'); ?>');
                return;
            }
            
            if (newName.length > 100) {
                alert('<?php echo __('forum_name_too_long', '论坛名称不能超过100个字符'); ?>');
                return;
            }
            
            fetch('/Chat_System/forum/updateSettings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `forum_id=${forumId}&name=${encodeURIComponent(newName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ <?php echo __('forum_name_updated_success', '论坛名称更新成功'); ?>');
                    document.querySelector('.forum-title').textContent = newName + ' <?php echo __('settings', '设置'); ?>';
                    document.querySelector('.forum-name-text').textContent = newName;
                    hideForumNameModal();
                } else {
                    alert('❌ <?php echo __('update_failed', '更新失败'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ <?php echo __('update_failed_retry', '更新失败，请重试'); ?>');
            });
        }
        
        // 保存论坛设置
        document.getElementById('forumSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                forum_id: document.getElementById('forumId').value,
                name: document.getElementById('forumName').value,
                description: document.getElementById('forumDescription').value,
                max_members: document.getElementById('maxMembers').value,
                is_public: document.getElementById('isPublic').value
            };
            
            fetch('/Chat_System/forum/updateSettings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: Object.keys(formData).map(key => `${key}=${encodeURIComponent(formData[key])}`).join('&')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ <?php echo __('settings_saved_success', '设置保存成功！'); ?>');
                } else {
                    alert('❌ <?php echo __('save_failed', '保存失败'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ <?php echo __('save_failed_retry', '保存失败，请重试'); ?>');
            });
        });
        
        // 处理加入请求
        function handleJoinRequest(requestId, action) {
            fetch('/Chat_System/forum/handleJoinRequest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `request_id=${requestId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>');
            });
        }
        
        // 提升为管理员
        function promoteToAdmin(userId) {
            if (confirm('<?php echo __('confirm_set_admin', '确定要将此成员设为管理员吗？'); ?>')) {
                fetch('/Chat_System/forum/updateMemberRole', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&role=admin`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo __('set_as_admin', '已设为管理员'); ?>');
                        location.reload();
                    } else {
                        alert('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>');
                });
            }
        }
        
        // 取消管理员
        function demoteFromAdmin(userId) {
            if (confirm('<?php echo __('confirm_remove_admin', '确定要取消此成员的管理员权限吗？'); ?>')) {
                fetch('/Chat_System/forum/updateMemberRole', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&role=member`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo __('admin_permission_removed', '已取消管理员权限'); ?>');
                        location.reload();
                    } else {
                        alert('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>');
                });
            }
        }
        
        // 移除成员
        function removeMember(userId) {
            if (confirm('<?php echo __('confirm_remove_member', '确定要移除此成员吗？'); ?>')) {
                fetch('/Chat_System/forum/removeMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo __('member_removed', '成员已移除'); ?>');
                        location.reload();
                    } else {
                        alert('<?php echo __('operation_failed', '操作失败'); ?>: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo __('operation_failed_retry', '操作失败，请重试'); ?>');
                });
            }
        }
        
        // 退出论坛
        function leaveForum() {
            if (confirm('<?php echo __('confirm_leave_forum', '确定要退出此论坛吗？退出后您将无法再访问论坛内容。'); ?>')) {
                fetch('/Chat_System/forum/leaveForum', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `forum_id=${forumId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ <?php echo __('leave_forum_success', '已成功退出论坛'); ?>');
                        window.location.href = '/Chat_System/list_forum';
                    } else {
                        alert('❌ <?php echo __('leave_forum_failed', '退出失败'); ?>: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ <?php echo __('leave_forum_failed_retry', '退出失败，请重试'); ?>');
                });
            }
        }

        // 删除论坛
        function deleteForum() {
            const forumName = prompt('<?php echo __('enter_forum_name_to_confirm_delete', '请输入论坛名称以确认删除'); ?>:');
            if (forumName === '<?php echo $forum['name']; ?>') {
                if (confirm('⚠️ <?php echo __('confirm_delete_forum', '确定要删除此论坛吗？'); ?>\n\n<?php echo __('delete_operations_will', '此操作将'); ?>：\n• <?php echo __('permanently_delete_all_posts', '永久删除所有帖子'); ?>\n• <?php echo __('permanently_delete_all_replies', '永久删除所有回复'); ?>\n• <?php echo __('permanently_delete_all_attachments', '永久删除所有附件文件'); ?>\n• <?php echo __('permanently_delete_all_member_info', '永久删除所有成员信息'); ?>\n• <?php echo __('permanently_delete_all_join_requests', '永久删除所有加入请求'); ?>\n• <?php echo __('permanently_delete_forum_data', '永久删除论坛数据'); ?>\n\n<?php echo __('operation_cannot_be_undone', '此操作不可撤销！'); ?>')) {
                    fetch('/Chat_System/forum/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `forum_id=<?php echo $forum['id']; ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ <?php echo __('forum_deleted', '论坛已删除'); ?>');
                            window.location.href = '/Chat_System/list_forum';
                        } else {
                            alert('❌ <?php echo __('delete_failed', '删除失败'); ?>: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ <?php echo __('delete_failed_retry', '删除失败，请重试'); ?>');
                    });
                }
            } else if (forumName !== null) {
                alert('❌ <?php echo __('forum_name_mismatch', '论坛名称不匹配'); ?>');
            }
        }
        
        // 点击模态框外部关闭
        document.getElementById('avatarModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAvatarModal();
            }
        });
        
        document.getElementById('forumNameModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideForumNameModal();
            }
        });
        
        document.getElementById('inviteMemberModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideInviteMemberModal();
            }
        });

        document.getElementById('removeMemberModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRemoveMemberModal();
            }
        });
        
        // 回车键保存论坛名称
        document.getElementById('newForumName').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                updateForumName();
            }
        });
        
        
        // 显示邀请成员弹窗
        function showInviteMemberModal() {
            const modal = document.getElementById('inviteMemberModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.getElementById('friendSearchInput').focus();
        }
        
        // 隐藏邀请成员弹窗
        function hideInviteMemberModal() {
            const modal = document.getElementById('inviteMemberModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
            
            const searchInput = document.getElementById('friendSearchInput');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // 重置好友列表显示
            searchFriends();
        }

        // 显示移除成员弹窗
        function showRemoveMemberModal() {
            const modal = document.getElementById('removeMemberModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.getElementById('memberSearchInput').focus();
        }
        
        // 隐藏移除成员弹窗
        function hideRemoveMemberModal() {
            const modal = document.getElementById('removeMemberModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
            
            const searchInput = document.getElementById('memberSearchInput');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // 重置成员列表显示
            searchMembers();
        }
        
        // 搜索好友
        function searchFriends() {
            const searchInput = document.getElementById('friendSearchInput');
            if (!searchInput) return;
            
            const searchTerm = searchInput.value.toLowerCase();
            const friendItems = document.querySelectorAll('.friend-item');
            
            friendItems.forEach(item => {
                const usernameAttr = item.getAttribute('data-friend-username');
                if (usernameAttr) {
                    const username = usernameAttr.toLowerCase();
                    if (username.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        }

        // 搜索成员
        function searchMembers() {
            const searchInput = document.getElementById('memberSearchInput');
            if (!searchInput) return;
            
            const searchTerm = searchInput.value.toLowerCase();
            const memberItems = document.querySelectorAll('.member-item');
            
            memberItems.forEach(item => {
                const usernameAttr = item.getAttribute('data-member-username');
                if (usernameAttr) {
                    const username = usernameAttr.toLowerCase();
                    if (username.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        }
        
        // 邀请好友
        function inviteFriend(friendId, username) {
            if (confirm(`<?php echo __('confirm_invite_to_forum', '确定要邀请'); ?> ${username} <?php echo __('to_forum', '加入论坛吗？'); ?>`)) {
                // 禁用邀请按钮防止重复点击
                const inviteBtn = document.querySelector(`button[onclick*="inviteFriend(${friendId}"]`);
                let originalText = '<?php echo __('invite', '邀请'); ?>';
                if (inviteBtn) {
                    originalText = inviteBtn.textContent;
                    inviteBtn.disabled = true;
                    inviteBtn.textContent = '<?php echo __('inviting', '邀请中...'); ?>';
                }
                
                fetch('/Chat_System/forum/inviteMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `forum_id=${forumId}&username=${encodeURIComponent(username)}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ <?php echo __('member_invited_success', '成员邀请成功'); ?>');
                        hideInviteMemberModal();
                        // 延迟刷新页面，确保用户看到成功消息
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('❌ <?php echo __('invite_failed', '邀请失败'); ?>: ' + data.message);
                        // 恢复按钮状态
                        if (inviteBtn) {
                            inviteBtn.disabled = false;
                            inviteBtn.textContent = originalText;
                        }
                    }
                })
                .catch(error => {
                    console.error('<?php echo __('invite_failed', '邀请失败'); ?>:', error);
                    alert('❌ <?php echo __('invite_failed', '邀请失败'); ?>: ' + error.message);
                    // 恢复按钮状态
                    if (inviteBtn) {
                        inviteBtn.disabled = false;
                        inviteBtn.textContent = originalText;
                    }
                });
            }
        }
    </script>
</body>
</html>