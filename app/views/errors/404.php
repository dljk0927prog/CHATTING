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
    <title><?php echo __('error_404_title'); ?></title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
    <style>
        /* 404页面移动端优化 */
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
            
            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 14px;
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
            
            .btn {
                min-height: 46px;
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>404</h1>
                <p>页面未找到</p>
            </div>
            
            <div style="text-align: center; padding: 20px;">
                <p style="color: #666; margin-bottom: 30px;">
                    抱歉，您访问的页面不存在或已被删除。
                </p>
                
                <a href="/CHATTING/chat" class="btn btn-primary">返回首页</a>
            </div>
        </div>
    </div>
</body>
</html>
