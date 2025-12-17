<?php
// 头像上传处理 - 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => __('avatar_upload_not_logged_in')]);
    exit;
}

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 0); // 不显示错误，避免影响JSON输出

// 检查是否有文件上传
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => __('avatar_upload_no_file')]);
    exit;
}

$file = $_FILES['avatar'];

// 检查文件类型
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => __('avatar_upload_invalid_type')]);
    exit;
}

// 检查文件大小 (最大10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => __('avatar_upload_too_large')]);
    exit;
}

// 生成唯一文件名
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
$uploadPath = BASE_PATH . '/public/uploads/avatars/' . $fileName;

// 确保目录存在
$uploadDir = BASE_PATH . '/public/uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 移动上传的文件
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    try {
        // 更新数据库中的头像信息
        require_once BASE_PATH . '/config/Database.php';
        require_once BASE_PATH . '/app/models/User.php';
        
        $userModel = new User();
        $result = $userModel->updateAvatar($_SESSION['user_id'], $fileName);
        
        if ($result) {
            // 删除旧头像文件
            $user = $userModel->getUserById($_SESSION['user_id']);
            if (!empty($user['avatar']) && $user['avatar'] !== $fileName) {
                $oldAvatarPath = BASE_PATH . '/public/uploads/avatars/' . $user['avatar'];
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
            
            echo json_encode(['success' => true, 'message' => '头像更新成功', 'filename' => $fileName]);
        } else {
            // 如果数据库更新失败，删除已上传的文件
            unlink($uploadPath);
            echo json_encode(['success' => false, 'message' => '数据库更新失败']);
        }
    } catch (Exception $e) {
        // 如果出现异常，删除已上传的文件
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        echo json_encode(['success' => false, 'message' => '处理失败: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => '文件保存失败，请检查目录权限']);
}
?>
