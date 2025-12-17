<?php
// 聊天系统欢迎页面
session_start();

// 定义基础路径
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('MODEL_PATH', APP_PATH . '/models');
define('CONTROLLER_PATH', APP_PATH . '/controllers');

// 自动加载类
spl_autoload_register(function ($class) {
    $paths = [
        MODEL_PATH . '/' . $class . '.php',
        CONTROLLER_PATH . '/' . $class . '.php',
        BASE_PATH . '/config/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// 检查用户是否已登录
$isLoggedIn = isset($_SESSION['user_id']);

// 如果已登录，重定向到dashboard
if ($isLoggedIn) {
    header("Location: /CHATTING/dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>欢迎使用聊天系统</title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
</head>
<body>
    <div class="welcome-page-container">
        <div class="welcome-content">
            <div class="welcome-header">
                <h1>💬 聊天系统</h1>
                <p>现代化的实时聊天平台</p>
            </div>
            
            <div class="welcome-main">
                <div class="action-section">
                    <h2>开始使用</h2>
                    <p>立即注册账户或登录现有账户，开始您的聊天之旅</p>
                    <div class="action-buttons">
                        <a href="/CHATTING/auth/login" class="btn btn-primary btn-large">登录账户</a>
                        <a href="/CHATTING/auth/register" class="btn btn-secondary btn-large">注册新账户</a>
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
        }
        
        .welcome-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
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
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .welcome-page-container {
                padding: 10px;
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
</body>
</html>