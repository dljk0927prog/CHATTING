<?php
// 个人资料页面 - 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: /CHATTING/auth/login");
    exit;
}

// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();

// 获取用户信息
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/models/User.php';

$userModel = new User();
$user = $userModel->getUserById($_SESSION['user_id']);

if (!$user) {
    header("Location: /CHATTING/auth/login");
    exit;
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
    <title><?php echo str_replace('{username}', htmlspecialchars($user['username']), __('profile_page_title')); ?></title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            background: #ffffff;
        }
        
        body {
            background: #ffffff !important;
        }
        
        .profile-card {
            background: linear-gradient(135deg, #f8f4ff 0%, #ffffff 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
        }
        
        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e8d5ff, #d1b3ff, #b894ff, #a075ff);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #f0e6ff 0%, #e8d5ff 100%);
            color: #4a3c5c;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .profile-header::before {
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
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 20px;
            border: 4px solid rgba(168, 85, 247, 0.2);
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.15);
            z-index: 2;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            border-color: rgba(168, 85, 247, 0.4);
        }
        
        .profile-avatar img {
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
        
        .profile-avatar:hover .avatar-overlay {
            opacity: 1;
        }
        
        .avatar-overlay span {
            color: white;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
        }
        
        .profile-name {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: bold;
            color: #4a3c5c;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }
        
        .profile-status {
            font-size: 1.1rem;
            color: #6b46c1;
            background: rgba(168, 85, 247, 0.1);
            padding: 8px 16px;
            border-radius: 25px;
            display: inline-block;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(168, 85, 247, 0.2);
            font-weight: 500;
            position: relative;
            z-index: 2;
        }
        
        .profile-content {
            padding: 40px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            color: #4a3c5c;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 700;
            padding-bottom: 12px;
            border-bottom: 3px solid #a855f7;
            position: relative;
        }
        
        .info-section h3::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #a855f7, #8b5cf6);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f4ff 100%);
            border-radius: 15px;
            border-left: 5px solid #a855f7;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .info-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.08), rgba(139, 92, 246, 0.08));
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.15);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #a855f7;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }
        
        .info-value {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-online {
            background: #d4edda;
            color: #155724;
        }
        
        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-away {
            background: #fff3cd;
            color: #856404;
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
        
        /* 头像上传模态框样式 */
        .avatar-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            backdrop-filter: blur(5px);
        }
        
        .avatar-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-modal h3 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
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
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }

        .edit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            border: none !important;
            padding: 6px 12px !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            margin-left: auto !important;
            position: relative !important;
            z-index: 10 !important;
            pointer-events: auto !important;
            display: inline-block !important;
        }

        .edit-btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3) !important;
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%) !important;
        }

        .info-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
            pointer-events: auto;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #4a3c5c;
            min-width: 80px;
        }

        .info-value {
            color: #666;
            flex: 1;
            margin: 0 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a3c5c;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 10px;
            }
            
            .profile-header {
                padding: 30px 20px;
            }
            
            .profile-content {
                padding: 30px 20px;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .back-btn {
                width: 40px;
                height: 40px;
                top: 15px;
                right: 15px;
            }
            
            .info-item {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .info-label {
                min-width: auto;
                margin-bottom: 5px;
            }
            
            .info-value {
                margin: 0;
                flex: none;
            }
            
            .edit-btn {
                margin-left: 0 !important;
                align-self: flex-end;
                min-width: 60px;
                min-height: 36px;
            }
            
            .avatar-modal-content {
                width: 95%;
                margin: 10px;
                padding: 20px;
            }
            
            .modal-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .modal-buttons .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .profile-container {
                padding: 5px;
            }
            
            .profile-header {
                padding: 20px 15px;
            }
            
            .profile-content {
                padding: 20px 15px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            
            .profile-name {
                font-size: 1.3rem;
            }
            
            .profile-status {
                font-size: 1rem;
                padding: 6px 12px;
            }
            
            .info-section h3 {
                font-size: 1.3rem;
            }
            
            .info-item {
                padding: 12px;
            }
            
            .info-label {
                font-size: 0.85rem;
            }
            
            .info-value {
                font-size: 1rem;
            }
            
            .edit-btn {
                font-size: 0.75rem !important;
                padding: 4px 8px !important;
                min-width: 50px;
                min-height: 32px;
            }
            
            .avatar-modal-content {
                padding: 15px;
            }
            
            .avatar-preview {
                width: 80px;
                height: 80px;
            }
            
            .file-input {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <a href="/CHATTING/dashboard" class="back-btn" title="<?php echo __('btn_back'); ?>"></a>
            <div class="profile-header">
                <div class="profile-avatar" onclick="showAvatarModal()">
                    <?php 
                    $avatarPath = BASE_PATH . '/public/uploads/avatars/' . $user['avatar'];
                    $avatarExists = !empty($user['avatar']) && file_exists($avatarPath);
                    ?>
                    <?php if ($avatarExists): ?>
                        <img src="/CHATTING/public/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo __('avatar_default'); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); align-items:center; justify-content:center; color:white; font-size:3rem; font-weight:bold; border-radius:50%;">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php else: ?>
                        <div style="width:100%; height:100%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); display:flex; align-items:center; justify-content:center; color:white; font-size:3rem; font-weight:bold; border-radius:50%;">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="avatar-overlay">
                        <span><?php echo __('profile_change_avatar'); ?></span>
                    </div>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="profile-status"><?php 
                if ($user['status'] === 'online') {
                    echo __('status_online');
                } elseif ($user['status'] === 'away') {
                    echo __('status_away');
                } else {
                    echo __('status_offline');
                }
                ?></div>
            </div>
            
            <div class="profile-content">
                <div class="info-section">
                    <h3><?php echo __('profile_basic_info'); ?></h3>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_username_label'); ?></span>
                        <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                        <button class="edit-btn" onclick="showEditModal('username', '<?php echo htmlspecialchars($user['username']); ?>')"><?php echo __('edit'); ?></button>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_email_label'); ?></span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        <button class="edit-btn" onclick="showEditModal('email', '<?php echo htmlspecialchars($user['email']); ?>')"><?php echo __('edit'); ?></button>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_status_label'); ?></span>
                        <span class="status-badge status-<?php echo $user['status']; ?>">
                            <?php 
                            if ($user['status'] === 'online') {
                                echo __('status_online');
                            } elseif ($user['status'] === 'away') {
                                echo __('status_away');
                            } else {
                                echo __('status_offline');
                            }
                            ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_registration_time'); ?></span>
                        <span class="info-value"><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3><?php echo __('profile_account_info'); ?></h3>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_password_label'); ?></span>
                        <span class="info-value">••••••••</span>
                        <button class="edit-btn" onclick="showEditModal('password', '')"><?php echo __('profile_change_password'); ?></button>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_user_id'); ?></span>
                        <span class="info-value">#<?php echo $user['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php echo __('profile_last_login'); ?></span>
                        <span class="info-value"><?php echo isset($user['last_login']) ? date('Y-m-d H:i', strtotime($user['last_login'])) : __('profile_unknown'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 头像上传模态框 -->
    <div id="avatarModal" class="avatar-modal">
        <div class="avatar-modal-content">
            <h3><?php echo __('profile_change_avatar'); ?></h3>
            <div class="avatar-preview" id="avatarPreview">
                <div class="default-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
            </div>
            <div class="file-input-wrapper">
                <div class="file-input">
                    <input type="file" id="avatarInput" accept="image/*" onchange="previewAvatar(this)">
                    <span><?php echo __('profile_click_select_image'); ?></span>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="hideAvatarModal()"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary" onclick="uploadAvatar()"><?php echo __('save'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- 编辑信息模态框 -->
    <div id="editModal" class="avatar-modal">
        <div class="avatar-modal-content">
            <h3 id="editModalTitle"><?php echo __('edit'); ?></h3>
            <form id="editForm">
                <div class="form-group">
                    <label class="form-label" id="editLabel"><?php echo __('profile_enter_new_value'); ?></label>
                    <input type="text" id="editInput" class="form-input" placeholder="<?php echo __('profile_enter_new_value'); ?>">
                    <div id="passwordFields" style="display: none;">
                        <input type="password" id="currentPassword" class="form-input" placeholder="<?php echo __('profile_current_password_placeholder'); ?>" style="margin-top: 10px;">
                        <input type="password" id="newPassword" class="form-input" placeholder="<?php echo __('profile_new_password_placeholder'); ?>" style="margin-top: 10px;">
                        <input type="password" id="confirmPassword" class="form-input" placeholder="<?php echo __('profile_confirm_password_placeholder'); ?>" style="margin-top: 10px;">
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="hideEditModal()"><?php echo __('cancel'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()"><?php echo __('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let selectedFile = null;
        
        
        function showAvatarModal() {
            document.getElementById('avatarModal').style.display = 'block';
        }
        
        function hideAvatarModal() {
            document.getElementById('avatarModal').style.display = 'none';
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
            preview.innerHTML = `<div class="default-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>`;
        }
        
        function uploadAvatar() {
            if (!selectedFile) {
                alert('<?php echo __('profile_please_select_image'); ?>');
                return;
            }
            
            const formData = new FormData();
            formData.append('avatar', selectedFile);
            
            fetch('/CHATTING/profile/uploadAvatar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo __('profile_avatar_updated_success'); ?>');
                    location.reload();
                } else {
                    alert('<?php echo __('profile_upload_failed'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('profile_upload_failed_retry'); ?>');
            });
        }
        
        // 点击模态框外部关闭
        document.getElementById('avatarModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAvatarModal();
            }
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideEditModal();
            }
        });

        let currentEditType = '';

        function showEditModal(type, currentValue) {
            currentEditType = type;
            const modal = document.getElementById('editModal');
            const title = document.getElementById('editModalTitle');
            const label = document.getElementById('editLabel');
            const input = document.getElementById('editInput');
            const passwordFields = document.getElementById('passwordFields');

            if (!modal) {
                alert('<?php echo __('profile_modal_not_found'); ?>');
                return;
            }

            // 重置表单
            if (input) input.value = '';
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            if (currentPassword) currentPassword.value = '';
            if (newPassword) newPassword.value = '';
            if (confirmPassword) confirmPassword.value = '';

            if (type === 'username') {
                title.textContent = '<?php echo __('profile_edit_username'); ?>';
                label.textContent = '<?php echo __('profile_new_username'); ?>';
                input.placeholder = '<?php echo __('profile_enter_new_username'); ?>';
                input.type = 'text';
                passwordFields.style.display = 'none';
                input.style.display = 'block';
                input.value = currentValue;
            } else if (type === 'email') {
                title.textContent = '<?php echo __('profile_edit_email'); ?>';
                label.textContent = '<?php echo __('profile_new_email'); ?>';
                input.placeholder = '<?php echo __('profile_enter_new_email'); ?>';
                input.type = 'email';
                passwordFields.style.display = 'none';
                input.style.display = 'block';
                input.value = currentValue;
            } else if (type === 'password') {
                title.textContent = '<?php echo __('profile_change_password'); ?>';
                label.textContent = '<?php echo __('profile_password_change'); ?>';
                input.style.display = 'none';
                passwordFields.style.display = 'block';
            }

            modal.style.display = 'block';
        }

        function hideEditModal() {
            document.getElementById('editModal').style.display = 'none';
            currentEditType = '';
        }

        function saveEdit() {
            if (currentEditType === 'password') {
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (!currentPassword || !newPassword || !confirmPassword) {
                    alert('<?php echo __('profile_fill_all_password_fields'); ?>');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert('<?php echo __('profile_password_mismatch'); ?>');
                    return;
                }

                if (newPassword.length < 6) {
                    alert('<?php echo __('profile_password_min_length'); ?>');
                    return;
                }

                updateProfile('password', {
                    current_password: currentPassword,
                    new_password: newPassword
                });
            } else {
                const newValue = document.getElementById('editInput').value.trim();
                
                if (!newValue) {
                    alert('<?php echo __('profile_enter_new_value_required'); ?>');
                    return;
                }

                if (currentEditType === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(newValue)) {
                        alert('<?php echo __('profile_email_invalid'); ?>');
                        return;
                    }
                }

                if (currentEditType === 'username') {
                    if (newValue.length < 3) {
                        alert('<?php echo __('profile_username_min_length'); ?>');
                        return;
                    }
                }

                updateProfile(currentEditType, newValue);
            }
        }

        function updateProfile(field, value) {
            const formData = new FormData();
            formData.append('field', field);
            
            if (field === 'password') {
                formData.append('current_password', value.current_password);
                formData.append('new_password', value.new_password);
            } else {
                formData.append('value', value);
            }

            fetch('/CHATTING/profile/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo __('profile_update_success'); ?>');
                    hideEditModal();
                    location.reload();
                } else {
                    alert('<?php echo __('profile_update_failed'); ?>: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('profile_update_failed_retry'); ?>');
            });
        }
    </script>
</body>
</html>
