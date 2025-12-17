<?php
// 个人资料更新处理 - 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => __('profile_update_not_logged_in')]);
    exit;
}

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 0); // 不显示错误，避免影响JSON输出

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => __('profile_update_method_error')]);
    exit;
}

// 获取字段和值
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// 验证字段
$allowedFields = ['username', 'email', 'password'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => __('profile_update_invalid_field')]);
    exit;
}

try {
    require_once BASE_PATH . '/config/Database.php';
    require_once BASE_PATH . '/app/models/User.php';
    
    $userModel = new User();
    $userId = $_SESSION['user_id'];
    
    // 获取当前用户信息（包含密码用于验证）
    $currentUser = $userModel->getUserByIdWithPassword($userId);
    if (!$currentUser) {
        echo json_encode(['success' => false, 'message' => __('profile_update_user_not_found')]);
        exit;
    }
    
    $result = false;
    $message = '';
    
    switch ($field) {
        case 'username':
            // 验证用户名
            if (strlen($value) < 3) {
                echo json_encode(['success' => false, 'message' => '用户名长度至少3位']);
                exit;
            }
            
            if (strlen($value) > 20) {
                echo json_encode(['success' => false, 'message' => '用户名长度不能超过20位']);
                exit;
            }
            
            // 检查用户名是否已存在
            if ($userModel->isUsernameExists($value, $userId)) {
                echo json_encode(['success' => false, 'message' => '用户名已存在']);
                exit;
            }
            
            $result = $userModel->updateUsername($userId, $value);
            $message = '用户名更新成功';
            break;
            
        case 'email':
            // 验证邮箱格式
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
                exit;
            }
            
            // 检查邮箱是否已存在
            if ($userModel->isEmailExists($value, $userId)) {
                echo json_encode(['success' => false, 'message' => '邮箱已存在']);
                exit;
            }
            
            $result = $userModel->updateEmail($userId, $value);
            $message = '邮箱更新成功';
            break;
            
        case 'password':
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            
            // 验证当前密码
            if (!password_verify($currentPassword, $currentUser['password'])) {
                echo json_encode(['success' => false, 'message' => '当前密码不正确']);
                exit;
            }
            
            // 验证新密码
            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'message' => '新密码长度至少6位']);
                exit;
            }
            
            if (strlen($newPassword) > 50) {
                echo json_encode(['success' => false, 'message' => '新密码长度不能超过50位']);
                exit;
            }
            
            $result = $userModel->updatePassword($userId, $newPassword);
            $message = '密码更新成功';
            break;
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => '更新失败，请重试']);
    }
    
} catch (Exception $e) {
    error_log('Profile update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '服务器错误，请稍后重试']);
}
?>
