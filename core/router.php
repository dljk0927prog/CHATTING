<?php
// 聊天系统路由处理文件
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/session.php';
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

// 路由处理
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// 移除项目根路径
$projectPath = '/Chat_System';
if (strpos($path, $projectPath) === 0) {
    $path = substr($path, strlen($projectPath));
}

// 移除开头的斜杠
$path = ltrim($path, '/');

// 特殊路由处理
if (empty($path)) {
    // 首页直接显示欢迎页面
    include BASE_PATH . '/index.php';
    exit;
}

// chat路由重定向到dashboard
if ($path === 'chat') {
    header("Location: /Chat_System/dashboard");
    exit;
}

    // chat/roomDetails路由处理
    if ($path === 'chat/roomDetails') {
        $path = 'chat/roomDetails';
    }
    
    // chat/videoCall路由处理
    if ($path === 'chat/videoCall') {
        $path = 'chat/videoCall';
    }

// dashboard路由处理
if ($path === 'dashboard') {
    $path = 'dashboard/index';
}

// profile路由处理
if ($path === 'profile') {
    include VIEW_PATH . '/profile.php';
    exit;
}

// profile头像上传路由处理
if ($path === 'profile/uploadAvatar') {
    include VIEW_PATH . '/profile/uploadAvatar.php';
    exit;
}

// profile更新路由处理
if ($path === 'profile/update') {
    include VIEW_PATH . '/profile/update.php';
    exit;
}

    // blocked路由处理
    if ($path === 'blocked') {
        $path = 'chat/blockedList';
    }
    
    // favorites路由处理
    if ($path === 'favorites') {
        $path = 'chat/favorites';
    }
    
    // favorites/getFavoriteData路由处理
    if ($path === 'favorites/getFavoriteData') {
        $path = 'chat/getFavoriteData';
    }
    
    // chat/getGroupMembers路由处理
    if ($path === 'chat/getGroupMembers') {
        $path = 'chat/getGroupMembers';
    }
    
    // language路由处理
    if (strpos($path, 'language/') === 0) {
        $path = $path; // 保持原路径，让下面的通用路由处理
    }
    
    // list_forum路由处理
    if ($path === 'list_forum') {
        include VIEW_PATH . '/list_forum.php';
        exit;
    }
    
    // forum路由处理
    if (strpos($path, 'forum/') === 0) {
        $path = $path; // 保持原路径，让下面的通用路由处理
    }
    
    // 通话邀请路由处理
    if (strpos($path, 'chat/sendCallInvitation') === 0) {
        $path = 'callInvitation/sendCallInvitation';
    }
    if (strpos($path, 'call/sendCallInvitation') === 0) {
        $path = 'callInvitation/sendCallInvitation';
    }
    if (strpos($path, 'call/sendGroupCallInvitation') === 0) {
        $path = 'callInvitation/sendGroupCallInvitation';
    }
    if (strpos($path, 'chat/getCallInvitations') === 0) {
        $path = 'chat/getCallInvitations';
    }
    if (strpos($path, 'chat/getCallStatus') === 0) {
        $path = 'callInvitation/getCallStatus';
    }
    if (strpos($path, 'chat/acceptCallInvitation') === 0) {
        $path = 'callInvitation/acceptCallInvitation';
    }
    if (strpos($path, 'chat/rejectCallInvitation') === 0) {
        $path = 'callInvitation/rejectCallInvitation';
    }
    if (strpos($path, 'chat/cancelCallInvitation') === 0) {
        $path = 'callInvitation/cancelCallInvitation';
    }
    // 通话信令 API（WebRTC offer/answer/ice/end）
    if (strpos($path, 'callSignal/') === 0) {
        $path = $path;
    }
    
    // 消息操作API路由处理
    if (strpos($path, 'chat/recallMessage') === 0) {
        $path = 'chat/recallMessage';
    }
    if (strpos($path, 'chat/deleteMessage') === 0) {
        $path = 'chat/deleteMessage';
    }
    if (strpos($path, 'chat/editMessage') === 0) {
        $path = 'chat/editMessage';
    }
    if (strpos($path, 'chat/toggleFavorite') === 0) {
        $path = 'chat/toggleFavorite';
    }
    if (strpos($path, 'chat/togglePin') === 0) {
        $path = 'chat/togglePin';
    }
    if (strpos($path, 'chat/pinMessage') === 0) {
        $path = 'chat/pinMessage';
    }
    if (strpos($path, 'chat/favoriteMessage') === 0) {
        $path = 'chat/favoriteMessage';
    }
    if (strpos($path, 'chat/getRecipients') === 0) {
        $path = 'chat/getRecipients';
    }
    if (strpos($path, 'chat/forwardMessage') === 0) {
        $path = 'chat/forwardMessage';
    }
    if (strpos($path, 'chat/clearHistory') === 0) {
        $path = 'chat/clearHistory';
    }
    
    if (strpos($path, 'chat/getMessageById') === 0) {
        $path = 'chat/getMessageById';
    }
    if (strpos($path, 'chat/getRoomMessages') === 0) {
        $path = 'chat/getRoomMessages';
    }
    if (strpos($path, 'chat/getTurnCredentials') === 0) {
        $path = 'chat/getTurnCredentials';
    }
$segments = explode('/', $path);
$controller = ucfirst($segments[0]) . 'Controller';
$action = isset($segments[1]) ? $segments[1] : 'index';

// 检查控制器文件是否存在
$controllerFile = CONTROLLER_PATH . '/' . $controller . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    include VIEW_PATH . '/errors/404.php';
    exit;
}

// 包含控制器文件
require_once $controllerFile;

// 检查控制器类是否存在
if (!class_exists($controller)) {
    http_response_code(404);
    include VIEW_PATH . '/errors/404.php';
    exit;
}

// 实例化控制器并调用方法
$controllerInstance = new $controller();
if (method_exists($controllerInstance, $action)) {
    $controllerInstance->$action();
} else {
    http_response_code(404);
    include VIEW_PATH . '/errors/404.php';
}
?>
