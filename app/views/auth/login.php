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
    <title><?php echo __('auth_login_title'); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <style>
        /* 登录页面移动端优化 */
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
                <h1><?php echo __('auth_welcome_back', '欢迎回来'); ?></h1>
                <p><?php echo __('auth_login_subtitle'); ?></p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/Chat_System/auth/loginProcess">
                <div class="form-group">
                    <label for="username"><?php echo __('auth_username_or_email', '用户名或邮箱'); ?></label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo __('auth_password'); ?></label>
                    <input type="password" id="password" name="password" required
                           value="<?php echo htmlspecialchars($_POST['password'] ?? 'password'); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo __('auth_login'); ?></button>
            </form>
            
            <div class="auth-links">
                <p><?php echo __('auth_no_account'); ?> <a href="/Chat_System/auth/register"><?php echo __('auth_register_here'); ?></a></p>
                <p style="margin-top: 12px;"><a href="/Chat_System/help.php"><?php echo __('nav_help'); ?></a></p>
            </div>
            
            <!-- 语言切换器 -->
            <div style="text-align: center; margin-top: 20px;">
                <?php include BASE_PATH . '/app/views/components/languageSwitcher.php'; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 简单的表单验证
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('<?php echo __('fill_all_fields', '请填写所有字段'); ?>');
                return false;
            }
        });
    </script>
    <?php $footerVariant = 'auth'; include BASE_PATH . '/app/views/components/site-footer.php'; ?>
</body>
</html>
