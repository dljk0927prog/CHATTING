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
    <title><?php echo __('auth_register_title'); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <style>
        /* 注册页面移动端优化 */
        @media (max-width: 768px) {
            .auth-container {
                padding: 10px;
            }
            
            .auth-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .auth-header h1 {
                font-size: 1.8rem;
            }
            
            .auth-header p {
                font-size: 0.85rem;
            }
            
            .form-group input {
                min-height: 48px;
                font-size: 16px;
                padding: 14px 15px;
            }
            
            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 14px;
            }
            
            .auth-links {
                margin-top: 25px;
            }
            
            .auth-links a {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 5px;
            }
            
            .auth-card {
                padding: 25px 15px;
                margin: 5px;
            }
            
            .auth-header h1 {
                font-size: 1.6rem;
            }
            
            .auth-header p {
                font-size: 0.8rem;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .form-group label {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }
            
            .form-group input {
                min-height: 46px;
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .btn {
                min-height: 46px;
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body class="page-with-footer">
    <div class="auth-container" style="padding-bottom: 60px; box-sizing: border-box;">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?php echo __('auth_create_account', '创建账户'); ?></h1>
                <p><?php echo __('auth_register_subtitle'); ?></p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($errors) && is_array($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form method="POST" action="/Chat_System/auth/registerProcess">
                <div class="form-group">
                    <label for="username"><?php echo __('auth_username'); ?></label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           minlength="3" maxlength="50">
                    <small style="color: #666; font-size: 0.8rem;"><?php echo __('auth_username_min_length', '至少3个字符'); ?></small>
                </div>
                
                <div class="form-group">
                    <label for="email"><?php echo __('auth_email'); ?></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo __('auth_password'); ?></label>
                    <input type="password" id="password" name="password" required 
                           minlength="6">
                    <small style="color: #666; font-size: 0.8rem;"><?php echo __('auth_password_min_length', '至少6个字符'); ?></small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><?php echo __('auth_confirm_password'); ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo __('auth_register'); ?></button>
            </form>
            
            <div class="auth-links">
                <p><?php echo __('auth_has_account'); ?> <a href="/Chat_System/auth/login"><?php echo __('auth_login_here'); ?></a></p>
            </div>
            
            <!-- 语言切换器 -->
            <div style="text-align: center; margin-top: 20px;">
                <?php include BASE_PATH . '/app/views/components/languageSwitcher.php'; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 表单验证
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // 检查用户名
            if (username.length < 3) {
                e.preventDefault();
                alert('<?php echo __('auth_username_min_length_error', '用户名至少需要3个字符'); ?>');
                return false;
            }
            
            // 检查邮箱格式
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('<?php echo __('auth_email_invalid', '请输入有效的邮箱地址'); ?>');
                return false;
            }
            
            // 检查密码
            if (password.length < 6) {
                e.preventDefault();
                alert('<?php echo __('auth_password_min_length_error', '密码至少需要6个字符'); ?>');
                return false;
            }
            
            // 检查密码确认
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('<?php echo __('auth_password_mismatch', '两次输入的密码不一致'); ?>');
                return false;
            }
        });
        
        // 实时密码确认验证
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#e1e5e9';
            }
        });
    </script>
    <?php $footerVariant = 'auth'; include BASE_PATH . '/app/views/components/site-footer.php'; ?>
</body>
</html>
