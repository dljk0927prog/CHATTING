<?php
// 聊天系统欢迎页面
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/session.php';
ensureSessionStarted();

define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('MODEL_PATH', APP_PATH . '/models');
define('CONTROLLER_PATH', APP_PATH . '/controllers');

// 自动加载类
spl_autoload_register(function ($class) {
    $paths = [
        MODEL_PATH . '/' . $class . '.php',
        CONTROLLER_PATH . '/' . $class . '.php',
        BASE_PATH . '/config/' . $class . '.php',
        BASE_PATH . '/lang/' . $class . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();

// 检查用户是否已登录
$isLoggedIn = isset($_SESSION['user_id']);

// 如果已登录，重定向到dashboard
if ($isLoggedIn) {
    header("Location: /Chat_System/dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('index_page_title'); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
</head>
<body class="page-with-footer">
    <div class="welcome-page-container">
        <div class="welcome-content">
            <div class="welcome-header">
                <h1>💬 <?php echo __('app_name'); ?></h1>
                <p><?php echo __('index_tagline'); ?></p>
            </div>

            <div class="welcome-main">
                <div class="action-section">
                    <h2><?php echo __('index_get_started'); ?></h2>
                    <p><?php echo __('index_description'); ?></p>
                    <div class="action-buttons">
                        <a href="/Chat_System/auth/login" class="btn btn-primary btn-large"><?php echo __('index_login_btn'); ?></a>
                        <a href="/Chat_System/auth/register" class="btn btn-secondary btn-large"><?php echo __('index_register_btn'); ?></a>
                    </div>
                    <div style="text-align: center; margin-top: 16px;">
                        <a href="/Chat_System/help.php" class="welcome-help-link"><?php echo __('nav_help'); ?></a>
                    </div>
                    <div style="text-align: center; margin-top: 24px;">
                        <?php include BASE_PATH . '/app/views/components/languageSwitcher.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* 欢迎页面专用样式 */
        .welcome-page-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            padding-bottom: 60px;
            box-sizing: border-box;
        }

        .welcome-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            overflow: visible;
            position: relative;
            z-index: 2;
        }

        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .welcome-header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .welcome-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .welcome-main {
            padding: 40px;
        }

        .action-section {
            text-align: center;
        }

        .action-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 2rem;
        }

        .action-section p {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 15px 30px;
            font-size: 1.1rem;
            min-width: 150px;
        }

        .welcome-help-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .welcome-help-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .welcome-page-container {
                padding: 10px;
                padding-bottom: 60px;
            }

            .welcome-content {
                border-radius: 15px;
            }

            .welcome-header {
                padding: 30px 20px;
            }

            .welcome-header h1 {
                font-size: 2.5rem;
            }

            .welcome-main {
                padding: 30px 20px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
    <?php $footerVariant = 'auth'; include BASE_PATH . '/app/views/components/site-footer.php'; ?>
</body>
</html>
