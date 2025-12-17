<?php
// 检查会话状态
if (!isset($_SESSION['user_id'])) {
    header("Location: /CHATTING/auth/login");
    exit;
}

// 检查必要的变量是否存在
if (!isset($group) || !isset($members)) {
    error_log("groupSettings.php: Missing required variables - group or members not set");
    header("Location: /CHATTING/dashboard");
    exit;
}

// 检查权限
$isOwner = isset($group['created_by']) && $group['created_by'] == $_SESSION['user_id'];
$isAdmin = false;
foreach ($members as $member) {
    if ($member['id'] == $_SESSION['user_id'] && $member['role'] === 'admin') {
        $isAdmin = true;
        break;
    }
}
$canManage = $isOwner || $isAdmin;
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
    <title><?php echo str_replace('{name}', htmlspecialchars($group['name']), __('page_title_group_settings')); ?></title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
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
            
            .group-title {
                font-size: 1.5rem;
            }
            
            .permission-badge {
                padding: 8px 12px;
                font-size: 0.85rem;
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
        
        /* 群组管理按钮样式 */
        .btn-group {
            display: flex !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
        }
        
        .btn-group .btn {
            flex: 1 !important;
            min-width: 120px !important;
        }
            
            .member-list {
                gap: 12px;
            }
            
            .member-item {
                padding: 15px;
            }
            
            .member-avatar {
                width: 40px;
                height: 40px;
            }
            
            .member-info h4 {
                font-size: 1rem;
            }
            
            .member-info p {
                font-size: 0.8rem;
            }
            
            .member-actions {
                gap: 8px;
            }
            
            .member-actions .btn {
                padding: 8px 12px;
                font-size: 0.8rem;
                min-height: 36px;
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
            
            .group-title {
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
            
            .member-item {
                padding: 12px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .member-info {
                width: 100%;
            }
            
            .member-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .member-actions .btn {
                flex: 1;
                max-width: 120px;
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

        .group-title {
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

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
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
            border-radius: 50%;
            background: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

        .kick-member-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .kick-member-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .kick-icon {
            color: white;
            font-size: 20px;
        }


        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
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

        .avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }

        .group-name-display {
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

        .group-name-text {
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

        .edit-name-btn:active {
            transform: translateY(0);
        }

        .current-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 3rem;
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
            color: #333;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
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

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .modal .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }

        .modal .btn-secondary:hover {
            background: #e9ecef;
        }

        .kick-members-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }

        .kick-member-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 12px;
        }

        .kick-member-item:last-child {
            border-bottom: none;
        }

        .kick-member-item:hover {
            background: #f3f4f6;
        }

        .kick-member-avatar {
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

        .kick-member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .kick-member-info {
            flex: 1;
        }

        .kick-member-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .kick-member-role {
            font-size: 12px;
            color: #64748b;
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

        .invite-friend-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }


        .input-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .input-group .form-input {
            flex: 1;
        }

        .readonly-info {
            padding: 12px 16px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            color: #6b7280;
            font-size: 16px;
        }

        .status-online {
            color: #10b981;
        }

        .status-away {
            color: #f59e0b;
        }

        .status-offline {
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-container {
                padding: 16px;
            }
            
            .member-actions {
                flex-direction: column;
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

        /* 头像区域美化 */
        .avatar-section .form-label {
            margin-bottom: 0;
            font-size: 1.1rem;
            color: #374151;
        }

        .avatar-section .current-avatar {
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .avatar-section .current-avatar:hover {
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.25);
        }

        /* 响应式优化 */
        @media (max-width: 768px) {
            .avatar-section {
                padding: 15px;
            }
            
            .current-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .group-name-display {
                min-width: 150px;
                padding: 10px 16px;
            }
            
            .group-name-text {
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
            
            .member-role-indicator {
                width: 16px;
                height: 16px;
                font-size: 10px;
            }
            
            .member-status-indicator {
                width: 10px;
                height: 10px;
            }
            
            .invite-member-btn {
                width: 50px;
                height: 50px;
            }
            
            .invite-icon {
                font-size: 20px;
            }
            
            .kick-member-btn {
                width: 50px;
                height: 50px;
            }
            
            .kick-icon {
                font-size: 16px;
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
        }
    </style>
</head>
<body>
    <div class="chat-page-container">
        <div class="chat-container">
            <!-- 引入侧边栏组件 -->
            <?php include __DIR__ . '/../components/navbar.php'; ?>
            
            <!-- 设置区域 -->
            <div class="chat-area">
                <div class="mobile-header">
                    <button class="menu-button" onclick="toggleSidebar()">☰</button>
                    <h2><?php echo __('group_settings'); ?></h2>
                </div>
                
                <div class="settings-container">
                    <!-- 页面头部 -->
                    <div class="settings-header">
                        <div class="header-top">
                            <a href="/CHATTING/chat/group?id=<?php echo $group['id']; ?>" class="back-btn">
                                <span>←</span>
                                <span><?php echo __('group_back_to_group'); ?></span>
                            </a>
                            <div class="permission-badge <?php echo $isOwner ? 'permission-owner' : ($isAdmin ? 'permission-admin' : 'permission-member'); ?>">
                                <?php if ($isOwner): ?>
                                    👑 <?php echo __('group_owner'); ?>
                                <?php elseif ($isAdmin): ?>
                                    ⚡ <?php echo __('group_admin'); ?>
                                <?php else: ?>
                                    👤 <?php echo __('group_member'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <h1 class="group-title"><?php echo htmlspecialchars($group['name']); ?></h1>
                        <div style="font-size: 14px; color: #64748b; margin-top: 8px;">
                            <?php echo __('group_id_label', '群组ID'); ?>: <?php echo $group['id']; ?> | <?php echo __('member_count_label', '成员数'); ?>: <?php echo count($members); ?> | 
                            <?php echo __('created_time_label', '创建时间'); ?>: <?php echo date('Y-m-d H:i', strtotime($group['created_at'])); ?>
                        </div>
                    </div>

                    <div class="settings-grid">
                        <!-- 基本信息 -->
                        <div class="settings-card">
                            <h3 class="card-title"><?php echo __('basic_info', '基本信息'); ?></h3>
                            
                            <!-- 群组头像和名称 -->
                            <div class="form-group">
                                <label class="form-label"><?php echo __('group_info', '群组信息'); ?></label>
                                <div class="avatar-section">
                                    <div class="current-avatar" onclick="showAvatarModal()">
                                        <?php 
                                        // 显示群组头像 - 参考其他页面的逻辑
                                        if (!empty($group['avatar']) && $group['avatar'] !== 'default_group_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $group['avatar'])) {
                                            // 添加时间戳避免缓存问题
                                            $timestamp = filemtime(BASE_PATH . '/public/uploads/avatars/' . $group['avatar']);
                                            echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($group['avatar']) . '?t=' . $timestamp . '" alt="' . __('avatar_group') . '">';
                                        } else {
                                            echo strtoupper(substr($group['name'], 0, 1));
                                        }
                                        ?>
                                        <div class="avatar-overlay">
                                            <span><?php echo __('change_avatar', '更换头像'); ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- 群组名称显示 -->
                                    <div class="group-name-display">
                                        <span class="group-name-text"><?php echo htmlspecialchars($group['name']); ?></span>
                                        <?php if ($canManage): ?>
                                            <button class="edit-name-btn" onclick="showGroupNameModal()" title="<?php echo __('group_edit_name'); ?>">
                                                ✏️
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 成员管理 -->
                        <div class="settings-card">
                            <h3 class="card-title"><?php echo __('member_management', '成员管理'); ?></h3>
                            
                            <!-- 成员头像网格 -->
                            <div class="form-group">
                                <label class="form-label"><?php echo __('group_members', '群组成员'); ?> (<?php echo count($members); ?>)</label>
                                <div class="members-avatar-grid">
                                    <?php foreach ($members as $member): ?>
                                        <div class="member-avatar-item" data-member-id="<?php echo $member['id']; ?>" 
                                             title="<?php echo htmlspecialchars($member['username']); ?> - <?php echo $member['is_creator'] ? __('group_owner') : ($member['role'] === 'admin' ? __('group_admin') : __('group_member')); ?>">
                                            <div class="member-avatar-small">
                                                <?php 
                                                if (!empty($member['avatar']) && $member['avatar'] !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $member['avatar'])) {
                                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($member['avatar']) . '" alt="' . __('avatar_default') . '">';
                                                } else {
                                                    echo strtoupper(substr($member['username'], 0, 1));
                                                }
                                                ?>
                                            </div>
                                            <div class="member-role-indicator">
                                                <?php if ($member['is_creator']): ?>
                                                    <span class="role-icon role-creator">👑</span>
                                                <?php elseif ($member['role'] === 'admin'): ?>
                                                    <span class="role-icon role-admin">⚡</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="member-status-indicator status-<?php echo $member['status']; ?>"></div>
                                            
                                            <!-- 成员操作菜单 -->
                                            <?php if ($canManage && !$member['is_creator'] && $member['id'] != $_SESSION['user_id']): ?>
                                                <div class="member-actions-menu">
                                                    <?php if ($member['role'] === 'member'): ?>
                                                        <button class="action-btn promote-btn" onclick="promoteToAdmin(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')" title="<?php echo __('set_admin'); ?>">
                                                            ⚡
                                                        </button>
                                                    <?php elseif ($member['role'] === 'admin'): ?>
                                                        <button class="action-btn demote-btn" onclick="demoteToMember(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')" title="<?php echo __('remove_admin'); ?>">
                                                            👤
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="action-btn remove-btn" onclick="removeMember(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')" title="<?php echo __('kick_member'); ?>">
                                                        🚫
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- 邀请成员按钮 -->
                                    <?php if ($canManage): ?>
                                        <div class="invite-member-btn" onclick="showInviteMemberModal()" title="<?php echo __('invite_member'); ?>">
                                            <div class="invite-icon">➕</div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- 踢人按钮 -->
                                    <?php if ($isOwner): ?>
                                        <div class="kick-member-btn" onclick="showKickMemberModal()" title="<?php echo __('kick_member_short'); ?>">
                                            <div class="kick-icon">🚫</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- 群组管理操作 -->
                            <?php if ($isOwner): ?>
                                <div class="form-group">
                                    <div class="btn-group" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                        <button class="btn btn-warning btn-sm" onclick="clearGroupChatHistory()">
                                            🗑️ <?php echo __('clear_group_chat_history', '清空群聊记录'); ?>
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="deleteGroup()">
                                            🗑️ <?php echo __('disband_group', '解散群组'); ?>
                                        </button>
                                    </div>
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
            <h3 class="modal-title"><?php echo __('change_group_avatar', '更换群组头像'); ?></h3>
            <div class="avatar-preview" id="avatarPreview">
                <div class="default-avatar">
                    <?php echo strtoupper(substr($group['name'], 0, 1)); ?>
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
    
    <!-- 群组名称修改模态框 -->
    <div id="groupNameModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><?php echo __('edit_group_name', '修改群组名称'); ?></h3>
            <div class="form-group">
                <label class="form-label"><?php echo __('new_group_name', '新群组名称'); ?></label>
                <input type="text" id="newGroupName" class="form-input" 
                       value="<?php echo htmlspecialchars($group['name']); ?>" 
                       maxlength="50" 
                       placeholder="<?php echo __('group_name_placeholder'); ?>">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <?php echo __('max_50_characters', '最多50个字符'); ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideGroupNameModal()"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary" onclick="updateGroupName()"><?php echo __('save'); ?></button>
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
                       placeholder="<?php echo __('search_friend_placeholder'); ?>" onkeyup="searchFriends()">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <?php echo __('select_friends_to_invite', '从您的好友列表中选择要邀请的成员'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo __('friends_list', '好友列表'); ?></label>
                <div class="friends-list" id="friendsList">
                    <?php 
                    // 获取已经是群组成员的好友ID
                    $memberIds = array_column($members, 'id');
                    foreach ($friends as $friend): 
                        if (!in_array($friend['id'], $memberIds)): // 只显示不是群组成员的好友
                    ?>
                        <div class="friend-item" data-friend-id="<?php echo $friend['id']; ?>" data-friend-username="<?php echo htmlspecialchars($friend['username']); ?>">
                            <div class="friend-avatar">
                                <?php 
                                if (!empty($friend['avatar']) && $friend['avatar'] !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $friend['avatar'])) {
                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($friend['avatar']) . '" alt="' . __('avatar_default') . '">';
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
    
    <!-- 踢出成员模态框 -->
    <div id="kickMemberModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><?php echo __('kick_member', '踢出成员'); ?></h3>
            <div class="form-group">
                <label class="form-label"><?php echo __('select_member_to_kick', '选择要踢出的成员'); ?></label>
                <div class="kick-members-list">
                    <?php foreach ($members as $member): ?>
                        <?php if (!$member['is_creator'] && $member['id'] != $_SESSION['user_id']): ?>
                            <div class="kick-member-item" onclick="selectMemberToKick(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['username']); ?>')">
                                <div class="kick-member-avatar">
                                    <?php 
                                    if (!empty($member['avatar']) && $member['avatar'] !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $member['avatar'])) {
                                        echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($member['avatar']) . '" alt="' . __('avatar_default') . '">';
                                    } else {
                                        echo strtoupper(substr($member['username'], 0, 1));
                                    }
                                    ?>
                                </div>
                                <div class="kick-member-info">
                                    <div class="kick-member-name"><?php echo htmlspecialchars($member['username']); ?></div>
                                    <div class="kick-member-role">
                                        <?php if ($member['role'] === 'admin'): ?>
                                            <?php echo __('admin'); ?>
                                        <?php else: ?>
                                            <?php echo __('member'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideKickMemberModal()"><?php echo __('cancel'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
        const groupId = <?php echo $group['id']; ?>;
        const userId = <?php echo $_SESSION['user_id']; ?>;
        const canManage = <?php echo $canManage ? 'true' : 'false'; ?>;
        
        // 显示群组名称修改弹窗
        function showGroupNameModal() {
            const modal = document.getElementById('groupNameModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.getElementById('newGroupName').focus();
            document.getElementById('newGroupName').select();
        }
        
        // 隐藏群组名称修改弹窗
        function hideGroupNameModal() {
            const modal = document.getElementById('groupNameModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // 更新群组名称
        function updateGroupName() {
            const newName = document.getElementById('newGroupName').value.trim();
            if (!newName) {
                alert('<?php echo __('group_name_cannot_be_empty', '群组名称不能为空'); ?>');
                return;
            }
            
            if (newName.length > 50) {
                alert('<?php echo __('group_name_too_long', '群组名称不能超过50个字符'); ?>');
                return;
            }
            
            fetch('/CHATTING/chat/updateGroupName', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `group_id=${groupId}&name=${encodeURIComponent(newName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ <?php echo __('group_name_updated_success', '群组名称更新成功'); ?>');
                    document.querySelector('.group-title').textContent = newName;
                    // 更新显示的名称
                    document.querySelector('.group-name-text').textContent = newName;
                    hideGroupNameModal();
                } else {
                    alert('❌ <?php echo __('update_failed', '更新失败'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ <?php echo __('update_failed_retry', '更新失败，请重试'); ?>');
            });
        }
        
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
        
        // 邀请好友
        function inviteFriend(friendId, username) {
            if (confirm(`<?php echo __('confirm_invite_member', '确定要邀请'); ?> ${username} <?php echo __('to_group', '加入群组吗？'); ?>`)) {
                // 禁用邀请按钮防止重复点击
                const inviteBtn = document.querySelector(`button[onclick*="inviteFriend(${friendId}"]`);
                let originalText = '<?php echo __('invite', '邀请'); ?>';
                if (inviteBtn) {
                    originalText = inviteBtn.textContent;
                    inviteBtn.disabled = true;
                    inviteBtn.textContent = '<?php echo __('inviting', '邀请中...'); ?>';
                }
                
                fetch('/CHATTING/chat/addGroupMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `group_id=${groupId}&username=${encodeURIComponent(username)}`
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
        
        // 显示踢人弹窗
        function showKickMemberModal() {
            const modal = document.getElementById('kickMemberModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }
        
        // 隐藏踢人弹窗
        function hideKickMemberModal() {
            const modal = document.getElementById('kickMemberModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // 选择要踢出的成员
        function selectMemberToKick(memberId, username) {
            if (confirm(`<?php echo __('confirm_kick_member', '确定要将'); ?> ${username} <?php echo __('from_group', '踢出群组吗？'); ?>`)) {
                fetch('/CHATTING/chat/removeGroupMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `group_id=${groupId}&member_id=${memberId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络请求失败');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ 成员已踢出');
                        hideKickMemberModal();
                        // 延迟刷新页面，确保用户看到成功消息
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('❌ 踢出失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ 踢出失败，请重试');
                });
            }
        }
        
        
        // 移除成员
        function removeMember(memberId, username) {
            if (confirm(`确定要将 ${username} 踢出群组吗？`)) {
                fetch('/CHATTING/chat/removeGroupMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `group_id=${groupId}&member_id=${memberId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络请求失败');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ 成员已移除');
                        // 延迟刷新页面，确保用户看到成功消息
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('❌ 移除失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ 移除失败，请重试');
                });
            }
        }
        
        // 提升为管理员
        function promoteToAdmin(memberId, username) {
            if (confirm(`确定要将 ${username} 设为管理员吗？`)) {
                fetch('/CHATTING/chat/promoteToAdmin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `group_id=${groupId}&member_id=${memberId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络请求失败');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ 已设为管理员');
                        // 延迟刷新页面，确保用户看到成功消息
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('❌ 操作失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ 操作失败，请重试');
                });
            }
        }
        
        // 取消管理员
        function demoteToMember(memberId, username) {
            if (confirm(`确定要取消 ${username} 的管理员权限吗？`)) {
                fetch('/CHATTING/chat/demoteToMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `group_id=${groupId}&member_id=${memberId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络请求失败');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ 已取消管理员权限');
                        // 延迟刷新页面，确保用户看到成功消息
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('❌ 操作失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ 操作失败，请重试');
                });
            }
        }
        
        // 清空群聊记录
        function clearGroupChatHistory() {
            const groupName = '<?php echo htmlspecialchars($group['name']); ?>';
            if (confirm(`确定要清空群组 "${groupName}" 的所有聊天记录吗？此操作不可恢复！`)) {
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = '清空中...';
                button.disabled = true;
                
                console.log('开始清空群聊记录...');
                console.log('群组ID:', groupId);
                
                fetch('/CHATTING/chat/clearHistory', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `room_id=${groupId}&room_type=group`
                })
                .then(response => {
                    console.log('响应状态:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('响应数据:', data);
                    if (data.success) {
                        alert('✅ 群聊记录已清空');
                        // 可以选择跳转回群组页面或刷新当前页面
                        setTimeout(() => {
                            window.location.href = `/CHATTING/chat/group?id=${groupId}`;
                        }, 1000);
                    } else {
                        alert('❌ 清空失败: ' + data.message);
                        button.textContent = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('清空群聊记录失败:', error);
                    alert('❌ 清空失败，请重试');
                    button.textContent = originalText;
                    button.disabled = false;
                });
            }
        }
        
        // 解散群组
        function deleteGroup() {
            const groupName = '<?php echo htmlspecialchars($group['name']); ?>';
            if (confirm(`确定要解散群组 "${groupName}" 吗？`)) {
                fetch('/CHATTING/chat/deleteGroup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `group_id=${groupId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ 群组已解散');
                        window.location.href = '/CHATTING/dashboard';
                    } else {
                        alert('❌ 解散失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ 解散失败，请重试');
                });
            }
        }
        
        let selectedFile = null;
        
        // 头像上传
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
                    preview.innerHTML = `<img src="${e.target.result}" alt="<?php echo __('preview_avatar_alt'); ?>">`;
                };
                reader.readAsDataURL(file);
            }
        }
        
        function resetPreview() {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<div class="default-avatar"><?php echo strtoupper(substr($group['name'], 0, 1)); ?></div>`;
        }
        
        function uploadAvatar() {
            if (!selectedFile) {
                alert('请先选择一张图片');
                return;
            }
            
            const formData = new FormData();
            formData.append('avatar', selectedFile);
            formData.append('group_id', groupId);
            
            fetch('/CHATTING/chat/updateGroupAvatar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('头像更新成功！');
                    location.reload();
                } else {
                    alert('上传失败: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('上传失败，请重试');
            });
        }
        
        // 点击模态框外部关闭
        document.getElementById('avatarModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAvatarModal();
            }
        });
        
        document.getElementById('groupNameModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideGroupNameModal();
            }
        });
        
        document.getElementById('inviteMemberModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideInviteMemberModal();
            }
        });
        
        document.getElementById('kickMemberModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideKickMemberModal();
            }
        });
        
        // 回车键保存群组名称
        document.getElementById('newGroupName').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                updateGroupName();
            }
        });
        
    </script>
</body>
</html>