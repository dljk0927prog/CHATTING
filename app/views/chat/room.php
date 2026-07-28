<?php
// 首先检查房间类型，避免不必要的数据库查询
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/models/Chat.php';

$chatModel = new Chat();

// 获取当前聊天室信息
$roomId = $_GET['id'] ?? null;
if (!$roomId) {
    header("Location: /Chat_System/dashboard");
    exit;
}

$room = $chatModel->getRoomInfo($roomId, $_SESSION['user_id']);
if (!$room) {
    header("Location: /Chat_System/dashboard");
    exit;
}



// 检查房间类型，如果是群组，重定向到群组页面
if ($room['type'] === 'group') {
    header("Location: /Chat_System/chat/group?id=" . $roomId);
    exit;
}

// 为navbar组件准备数据
$currentTab = 'chats';
require_once BASE_PATH . '/app/models/User.php';

$userModel = new User();
$user = $userModel->getUserById($_SESSION['user_id']);
$rooms = $chatModel->getUserRooms($_SESSION['user_id']);
$friends = $userModel->getFriends($_SESSION['user_id']);
$pendingRequests = $userModel->getPendingRequests($_SESSION['user_id']);

// 获取房间消息
$messages = $chatModel->getRoomMessages($roomId, $_SESSION['user_id']);

// 标记消息为已读
$chatModel->markRoomMessagesAsRead($roomId, $_SESSION['user_id']);
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
    <title><?php echo str_replace('{name}', htmlspecialchars($room['display_name']), __('chat_page_title')); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <link rel="stylesheet" href="/Chat_System/public/css/message-bubble-bar.css?v=1">
    <link rel="stylesheet" href="/Chat_System/public/css/media-preview.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* 聊天头部样式 */
        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .chat-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .call-buttons {
            display: flex;
            gap: 8px;
        }
        
        .call-btn {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            border: none !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            position: relative !important;
            z-index: 10 !important;
        }
        
        .call-btn.voice {
            background: #2ed573 !important;
            color: white !important;
        }
        
        .call-btn.video {
            background: #00bfff !important;
            color: white !important;
        }
        
        .call-btn:hover {
            transform: scale(1.1) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* 调试样式 - 确保按钮可见 */
        .call-btn {
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        
        .call-buttons {
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* 通话邀请样式 */
        .call-invitation-sent,
        .call-invitation-received {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }
        
        .invitation-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            animation: slideUp 0.3s ease;
        }
        
        .invitation-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bfff, #9d00ff);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: white;
            animation: pulse 2s infinite;
        }
        
        .caller-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2ed573, #00bfff);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: white;
            animation: pulse 2s infinite;
        }
        
        .invitation-text h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.2rem;
        }
        
        .invitation-text p {
            margin: 0 0 20px 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .invitation-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-accept,
        .btn-reject,
        .btn-cancel {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            min-width: 100px;
            justify-content: center;
        }
        
        .btn-accept {
            background: #2ed573;
            color: white;
        }
        
        .btn-accept:hover {
            background: #26c765;
            transform: scale(1.05);
        }
        
        .btn-reject,
        .btn-cancel {
            background: #ff4757;
            color: white;
        }
        
        .btn-reject:hover,
        .btn-cancel:hover {
            background: #ff3742;
            transform: scale(1.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .call-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .menu-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: #f8f9fa;
            color: #6c757d;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .menu-btn:hover {
            background: #e9ecef;
            transform: scale(1.05);
        }

        /* 文件上传相关样式 - 参考group.php */
        .message-input-container {
            position: relative;
        }
        
        .input-row {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        
        .file-upload-btn {
            background: #10b981;
            border: none;
            color: white;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
            flex-shrink: 0;
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .file-upload-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .voice-record-btn {
            background: #ef4444;
            border: none;
            color: white;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
            flex-shrink: 0;
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }
        
        .voice-record-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        .voice-record-btn.recording {
            background: #dc2626;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .message-input {
            flex: 1;
            min-width: 0;
        }
        
        /* 文件类型选择卡片 */
        .file-type-cards {
            position: absolute;
            bottom: 100%;
            left: 0;
            margin-bottom: 8px;
            display: flex;
            gap: 12px;
            background: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border: 1px solid #e1e5e9;
            z-index: 1000;
            animation: slideUp 0.3s ease-out;
        }
        
        .file-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 60px;
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
        }
        
        .file-card:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .card-icon {
            font-size: 24px;
            margin-bottom: 4px;
        }
        
        .card-text {
            font-size: 12px;
            font-weight: 600;
        }
        
        /* 文件预览区域样式 */
        .file-preview-area {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
            animation: slideDown 0.3s ease-out;
        }
        
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .preview-title {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .remove-preview-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-preview-btn:hover {
            color: #dc3545;
        }
        
        .preview-content {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 16px;
        }
        
        .file-preview-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
        }
        
        .file-item-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .file-item-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .preview-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-name-small {
            font-size: 10px;
            text-align: center;
            padding: 4px;
            word-break: break-all;
        }
        
        .preview-info {
            padding: 12px 16px;
            background: #f8f9fa;
            border-top: 1px solid #e1e5e9;
        }
        
        .file-count {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 14px;
        }
        
        .file-size {
            font-size: 12px;
            color: #666;
        }
        
        /* 添加更多按钮 */
        .add-more-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            background: rgba(102, 126, 234, 0.8);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
        }
        
        .add-more-btn:hover {
            background: rgba(102, 126, 234, 1);
            transform: scale(1.1);
        }
        
        .plus-icon {
            font-weight: bold;
        }
        
        /* 语音预览区域样式 */
        .voice-preview-area {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
            animation: slideDown 0.3s ease-out;
        }
        
        .voice-preview-content {
            padding: 16px;
            text-align: center;
        }
        
        .voice-preview-player {
            width: 100%;
            max-width: 300px;
            margin-bottom: 8px;
        }
        
        .voice-preview-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #666;
        }
        
        .voice-duration-text {
            font-weight: 600;
        }
        
        .voice-size-text {
            color: #999;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* 消息内容容器需要相对定位以便气泡栏绝对定位 */
        .message-content {
            position: relative;
        }

        /* 私聊房间页面移动端优化 */
        @media (max-width: 768px) {
            .chat-header {
                padding: 15px;
            }
            
            .chat-title {
                font-size: 1rem;
            }
            
            .chat-status {
                font-size: 0.75rem;
            }
            
            .call-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .menu-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .messages-container {
                padding: 15px;
            }
            
            .message {
                margin-bottom: 12px;
            }
            
            .message-content {
                max-width: 80%;
                padding: 10px 12px;
            }
            
            .message-text {
                font-size: 0.9rem;
                line-height: 1.4;
            }
            
            .message-time {
                font-size: 0.7rem;
                margin-top: 4px;
            }
            
            .message-input-container {
                padding: 15px;
            }
            
            .message-input-form {
                gap: 8px;
            }
            
            .message-input {
                min-height: 44px;
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .send-button {
                min-width: 44px;
                min-height: 44px;
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            
            .message-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
            
            .typing-indicator {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            
            .message-actions {
                gap: 8px;
                padding: 6px 12px;
                top: calc(100% + 6px);
            }
            
            .action-btn {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .chat-header {
                padding: 12px;
            }
            
            .chat-title {
                font-size: 0.95rem;
            }
            
            .chat-status {
                font-size: 0.7rem;
            }
            
            .messages-container {
                padding: 12px;
            }
            
            .message-content {
                max-width: 85%;
                padding: 8px 10px;
            }
            
            .message-text {
                font-size: 0.85rem;
            }
            
            .message-input-container {
                padding: 12px;
            }
            
            .message-input {
                min-height: 42px;
                font-size: 16px;
                padding: 10px 12px;
            }
            
            .send-button {
                min-width: 42px;
                min-height: 42px;
                padding: 10px 14px;
                font-size: 0.85rem;
            }
            
            .message-avatar {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }
            
            .typing-indicator {
                padding: 6px 10px;
                font-size: 0.75rem;
            }
            
            .message-actions {
                gap: 6px;
                padding: 4px 8px;
                top: calc(100% + 4px);
            }
            
            .action-btn {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
        }
        
        /* 置顶消息区域样式 */
        .pinned-message {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #fdcb6e;
            border-radius: 12px;
            margin: 8px 0;
            padding: 12px;
            position: relative;
        }
        
        .pinned-message::before {
            content: '📌 置顶消息';
            position: absolute;
            top: -8px;
            left: 12px;
            background: #fdcb6e;
            color: #2d3436;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            z-index: 10;
        }
        
        .pinned-message .message-content {
            background: transparent;
            box-shadow: none;
        }
        
        /* 置顶消息中的照片尺寸限制 */
        .pinned-message .image-message {
            max-width: 200px;
        }
        
        .pinned-message .message-image {
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
        }
        
        .pinned-message .image-collage {
            max-width: 200px;
        }
        
        .pinned-message .collage-item {
            aspect-ratio: 1;
        }
        
        .pinned-message .collage-thumbnail {
            max-height: 75px;
        }
        
        /* 视频消息样式 */
        .video-message {
            max-width: 200px;
            background: transparent;
            border: none;
        }
        
        .message-video {
            max-width: 100%;
            max-height: 200px;
            height: auto;
            border-radius: 8px;
            cursor: pointer;
            object-fit: cover;
        }
        
        /* 图片消息样式 */
        .image-message {
            max-width: 200px;
            background: transparent;
            border: none;
        }
        
        .message-image {
            max-width: 100%;
            max-height: 200px;
            height: auto;
            border-radius: 8px;
            cursor: pointer;
            object-fit: cover;
        }
        
        /* 多文件消息样式 */
        .multiple-files-message {
            max-width: 200px;
            background: transparent;
            border: none;
        }
        
        .image-collage {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .collage-item {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
            background: #f0f0f0;
        }
        
        .collage-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        /* 移动端视频样式优化 */
        @media (max-width: 768px) {
            .video-message, .image-message, .multiple-files-message {
                max-width: 180px;
            }
            
            .message-video, .message-image {
                max-width: 100%;
                height: auto;
            }
        }
        
        @media (max-width: 480px) {
            .video-message, .image-message, .multiple-files-message {
                max-width: 150px;
            }
        }
        
        /* 转发模态框样式 */
        .forward-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .forward-modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .forward-modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .forward-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .forward-modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .forward-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }
        
        .forward-modal-close:hover {
            background: #f0f0f0;
        }
        
        .forward-preview {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .forward-preview-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .forward-preview-content {
            color: #666;
            font-size: 14px;
        }
        
        .forward-recipients {
            margin-bottom: 20px;
        }
        
        .forward-recipients-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }
        
        .recipient-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .recipient-item:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .recipient-item.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .recipient-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
            font-size: 14px;
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .recipient-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .recipient-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        
        .recipients-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .recipients-list .recipient-item {
            margin-bottom: 8px;
        }
        
        .message-preview {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #667eea;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .forward-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .forward-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .forward-btn-cancel {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e0e0e0;
        }
        
        .forward-btn-cancel:hover {
            background: #e9ecef;
        }
        
        .forward-btn-send {
            background: #667eea;
            color: white;
        }
        
        .forward-btn-send:hover {
            background: #5a6fd8;
        }
        
        .forward-btn-send:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="chat-page-container">
        <div class="chat-container">
            <!-- 引入侧边栏组件 -->
            <?php 
            // 传递当前房间ID给navbar组件
            $currentRoomId = $roomId;
            include __DIR__ . '/../components/navbar.php'; 
            ?>
            
            <!-- 聊天区域 -->
            <div class="chat-area">
                <div class="mobile-header">
                    <button class="menu-button" onclick="toggleSidebar()">☰</button>
                    <h2><?php echo htmlspecialchars($room['display_name']); ?></h2>
                </div>
                
                
                
                <div class="chat-header">
                    <div class="chat-header-left">
                        <div class="room-avatar">
                            <?php 
                            
                            // 参考 navbar.php 的头像显示逻辑
                            $roomAvatar = $room['avatar'] ?? null;
                            
                            if (!empty($roomAvatar) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $roomAvatar)) {
                                echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($roomAvatar) . '" alt="' . __('avatar_default') . '">';
                            } else {
                                echo strtoupper(substr($room['display_name'], 0, 1));
                            }
                            ?>
                        </div>
                        <div>
                            <div class="chat-title"><?php echo htmlspecialchars($room['display_name']); ?></div>
                            <div class="chat-status">
                                <?php 
                                // 使用从数据库获取的真实状态
                                if (isset($room['partner_status'])) {
                                    if ($room['partner_status'] === 'online') {
                                        echo '在线';
                                    } elseif ($room['partner_status'] === 'away') {
                                        echo '离开';
                                    } else {
                                        echo '离线';
                                    }
                                } else {
                                    // 如果没有partner_status字段，使用默认值
                                    echo '离线';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-header-right" style="display: flex !important; align-items: center !important; gap: 10px !important;">
                        <!-- 视频通话按钮 -->
                        <div class="call-buttons" style="display: flex !important; gap: 8px !important;">
                            <button class="call-btn voice" id="voiceCallBtn" title="<?php echo __('voice_call'); ?>" onclick="startVoiceCall(<?php echo $room['partner_id'] ?? $room['id']; ?>)" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; border: none !important; background: #2ed573 !important; color: white !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; transition: all 0.3s ease !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important; position: relative !important; z-index: 10 !important;">
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="call-btn video" id="videoCallBtn" title="<?php echo __('video_call'); ?>" onclick="startVideoCall(<?php echo $room['partner_id'] ?? $room['id']; ?>)" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; border: none !important; background: #00bfff !important; color: white !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; transition: all 0.3s ease !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important; position: relative !important; z-index: 10 !important;">
                                <i class="fas fa-video"></i>
                            </button>
                        </div>
                        
                        <!-- Menu按钮 -->
                        <a href="/Chat_System/chat/roomDetails?id=<?php echo $room['id']; ?>" class="menu-btn" title="<?php echo __('menu'); ?>" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; border: none !important; background: #f8f9fa !important; color: #6c757d !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; transition: all 0.3s ease !important; text-decoration: none !important;">
                            <i class="fas fa-ellipsis-v"></i>
                        </a>
                    </div>
                </div>
                
                <!-- 置顶消息区域 - 固定在屏幕顶部 -->
                <?php if (!empty($messages)): ?>
                    <?php 
                    $pinnedMessages = array_filter($messages, function($msg) { return !empty($msg['is_pinned']); });
                    foreach ($pinnedMessages as $message): ?>
                        <div class="pinned-message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'own' : ''; ?>" 
                             data-message-id="<?php echo $message['id']; ?>"
                             data-sender-id="<?php echo $message['sender_id']; ?>"
                             data-sender-name="<?php echo htmlspecialchars($message['username']); ?>"
                             data-created-at="<?php echo $message['created_at']; ?>"
                             data-message-hover="true"
                             onmouseenter="showMessageBubble(this)" 
                             onmouseleave="hideMessageBubble(this)"
                             oncontextmenu="preventContextMenu(event)"
                             ontouchstart="handleMessageTouchStart(event, this)"
                             ontouchend="handleMessageTouchEnd(event, this)"
                             ontouchmove="handleMessageTouchMove(event, this)">
                            <div class="message-avatar">
                                <?php 
                                $messageAvatar = $message['avatar'] ?? null;
                                if (!empty($messageAvatar) && $messageAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $messageAvatar)) {
                                    echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($messageAvatar) . '" alt="' . __('message_avatar_alt') . '">';
                                } else {
                                    echo strtoupper(substr($message['username'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="message-content">
                                <?php if (!empty($message['quoted_content'])): ?>
                                    <div class="quoted-message">
                                        <div class="quoted-header">
                                            <span class="quoted-label"><?php echo __('quote_label'); ?></span>
                                            <span class="quoted-sender"><?php echo htmlspecialchars($message['quoted_username']); ?></span>
                                        </div>
                                        <div class="quoted-content">
                                            <?php if ($message['quoted_type'] === 'text'): ?>
                                                <div class="quoted-text"><?php echo htmlspecialchars($message['quoted_content']); ?></div>
                                            <?php else: ?>
                                                <div class="quoted-file">📎 <?php echo htmlspecialchars($message['quoted_content']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php 
                                // 重写置顶消息的语音消息渲染逻辑
                                if ($message['message_type'] === 'voice' && !empty($message['is_recalled'])): 
                                ?>
                                    <div class="recalled-message">
                                        <span class="recall-icon">↩️</span>
                                        <span class="recall-text">${chatT('message_recalled_label')}</span>
                                    </div>
                                <?php 
                                elseif ($message['message_type'] === 'voice'): 
                                    // 语音消息 - 强制显示音频播放器
                                ?>
                                    <div class="voice-message">
                                        <audio controls class="voice-player">
                                            <source src="/Chat_System/<?php echo htmlspecialchars($message['file_path']); ?>" type="audio/webm">
                                            <?php echo __('audio_not_supported'); ?>
                                        </audio>
                                        <div class="voice-duration"><?php echo __('voice_message'); ?></div>
                                    </div>
                                <?php 
                                elseif (!empty($message['file_path']) && !empty($message['is_recalled'])): 
                                ?>
                                    <div class="recalled-message">
                                        <span class="recall-icon">↩️</span>
                                        <span class="recall-text">${chatT('message_recalled_label')}</span>
                                    </div>
                                <?php 
                                elseif (!empty($message['file_path'])): 
                                    // 其他文件消息 - 使用与group.php相同的逻辑
                                    $fileData = json_decode($message['file_path'], true);
                                    if ($fileData && isset($fileData['urls']) && !empty($fileData['urls'])) {
                                        // JSON格式文件消息
                                        $fileUrls = $fileData['urls'];
                                        $fileNames = $fileData['names'] ?? [];
                                        $fileCount = $fileData['count'] ?? count($fileUrls);
                                        
                                        if ($fileCount === 1) {
                                            // 单文件消息 - 显示为大播放器
                                            $fileUrl = $fileUrls[0];
                                            $fileName = $fileNames[0] ?? '';
                                            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                ?>
                                            <div class="file-message <?php 
                                                if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])) {
                                                    echo 'video-message';
                                                } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                                                    echo 'image-message';
                                                } else {
                                                    echo 'document-message';
                                                }
                                            ?>">
                                                <?php if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])): ?>
                                                    <video controls class="message-video">
                                                        <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                    </video>
                                                <?php elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                                    <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="message-image">
                                                <?php else: ?>
                                                    <!-- 文档文件显示 -->
                                                    <div class="document-message">
                                                        <div class="file-icon">📄</div>
                                                        <div class="file-details">
                                                            <div class="file-name"><?php echo htmlspecialchars($fileName); ?></div>
                                                            <?php if ($fileExtension): ?>
                                                                <div class="file-type"><?php echo str_replace('{ext}', strtoupper($fileExtension), __('file_type_with_ext')); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" download class="download-btn"><?php echo __('download'); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else {
                                            // 多文件消息 - 显示为拼图模式
                                ?>
                                            <div class="file-message multiple-files-message">
                                                <div class="image-collage">
                                                    <?php
                                                    $displayCount = min($fileCount, 4);
                                                    $hasMore = $fileCount > 4;
                                                    
                                                    for ($i = 0; $i < $displayCount; $i++) {
                                                        $fileUrl = $fileUrls[$i];
                                                        $fileName = $fileNames[$i] ?? '';
                                                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                        $isVideo = in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv']);
                                                        $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                                        
                                                        if ($i === 3 && $hasMore) {
                                                            // 最后一张且有多余图片时显示"more"
                                                    ?>
                                                            <div class="collage-item more-item">
                                                                <div class="more-overlay">
                                                                    <div class="more-dots">⋯</div>
                                                                    <div class="more-text">more</div>
                                                                </div>
                                                                <?php if ($isVideo): ?>
                                                                    <video class="collage-thumbnail" muted>
                                                                        <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                                    </video>
                                                                <?php else: ?>
                                                                    <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="collage-thumbnail">
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php } else { ?>
                                                            <div class="collage-item">
                                                                <?php if ($isVideo): ?>
                                                                    <video class="collage-thumbnail" muted>
                                                                        <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                                    </video>
                                                                <?php else: ?>
                                                                    <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="collage-thumbnail">
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php }
                                                    }
                                                    ?>
                                                </div>
                                                <div class="files-info"><?php echo str_replace('{count}', $fileCount, __('message_files_count')); ?></div>
                                            </div>
                                        <?php }
                                    } else {
                                        // 单文件消息（兼容旧格式）
                                        $fileUrl = $message['file_path'];
                                        $fileName = '';
                                        $fileExtension = '';
                                        
                                        // 尝试从URL中提取文件名
                                        $pathParts = explode('/', $fileUrl);
                                        if (count($pathParts) > 0) {
                                            $fileName = end($pathParts);
                                            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        }
                                    ?>
                                        <div class="file-message <?php 
                                            if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])) {
                                                echo 'video-message';
                                            } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                                                echo 'image-message';
                                            } else {
                                                echo 'document-message';
                                            }
                                        ?>">
                                            <?php if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])): ?>
                                                <video controls class="message-video">
                                                    <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                </video>
                                            <?php elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                                <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="message-image">
                                            <?php else: ?>
                                                <!-- 文档文件显示 -->
                                                <div class="document-message">
                                                    <div class="file-icon">📄</div>
                                                    <div class="file-details">
                                                        <div class="file-name"><?php echo htmlspecialchars($fileName ?: __('file')); ?></div>
                                                        <?php if ($fileExtension): ?>
                                                            <div class="file-type"><?php echo strtoupper($fileExtension); ?> 文件</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="<?php echo htmlspecialchars($fileUrl); ?>" download class="download-btn">下载</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php } ?>
                                <?php 
                                elseif (!empty($message['is_recalled'])): 
                                ?>
                                    <div class="recalled-message">
                                        <span class="recall-icon">↩️</span>
                                        <span class="recall-text">${chatT('message_recalled_label')}</span>
                                    </div>
                                <?php 
                                else: 
                                    // 普通文本消息
                                ?>
                                    <div class="message-text"><?php echo nl2br(htmlspecialchars($message['content'])); ?></div>
                                <?php endif; ?>

                                <div class="message-time">
                                    <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                    <?php if (!empty($message['is_edited'])): ?>
                                        <span class="edited-indicator">(已编辑)</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (empty($message['is_recalled'])): ?>
                                    <?php 
                                    // 为置顶消息传递特殊参数
                                    $isPinnedMessage = true;
                                    include __DIR__ . '/../components/message-bubble-bar.php'; 
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="messages-container" id="messages-container">
                    <?php if (empty($messages)): ?>
                        <div style="text-align: center; color: #666; padding: 50px 20px;">
                            <h3>开始对话</h3>
                            <p>发送第一条消息开始聊天</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        // 只显示普通消息（非置顶消息）
                        $normalMessages = array_filter($messages, function($msg) { return empty($msg['is_pinned']); });
                        
                        // 显示普通消息
                        foreach ($normalMessages as $message): ?>
                            <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'own' : ''; ?>" 
                                 data-message-id="<?php echo $message['id']; ?>"
                                 data-sender-id="<?php echo $message['sender_id']; ?>"
                                 data-sender-name="<?php echo htmlspecialchars($message['username']); ?>"
                                 data-created-at="<?php echo $message['created_at']; ?>"
                                 data-message-hover="true"
                                 onmouseenter="showMessageBubble(this)" 
                                 onmouseleave="hideMessageBubble(this)"
                                 oncontextmenu="preventContextMenu(event)"
                                 ontouchstart="handleMessageTouchStart(event, this)"
                                 ontouchend="handleMessageTouchEnd(event, this)"
                                 ontouchmove="handleMessageTouchMove(event, this)">
                                <div class="message-avatar">
                                    <?php 
                                    $messageAvatar = $message['avatar'] ?? null;
                                    if (!empty($messageAvatar) && $messageAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $messageAvatar)) {
                                        echo '<img src="/Chat_System/public/uploads/avatars/' . htmlspecialchars($messageAvatar) . '" alt="' . __('message_avatar_alt') . '">';
                                    } else {
                                        echo strtoupper(substr($message['username'], 0, 1));
                                    }
                                    ?>
                                </div>
                                <div class="message-content">
                                    <?php 
                                    // 重写语音消息渲染逻辑
                                    if ($message['message_type'] === 'voice' && !empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">${chatT('message_recalled_label')}</span>
                                        </div>
                                    <?php 
                                    elseif ($message['message_type'] === 'voice'): 
                                        // 语音消息 - 强制显示音频播放器
                                    ?>
                                        <div class="voice-message">
                                        <audio controls class="voice-player">
                                            <source src="/Chat_System/<?php echo htmlspecialchars($message['file_path']); ?>" type="audio/webm">
                                            <?php echo __('audio_not_supported'); ?>
                                        </audio>
                                            <div class="voice-duration"><?php echo __('voice_message'); ?></div>
                                        </div>
                                    <?php 
                                    elseif (!empty($message['file_path']) && !empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">${chatT('message_recalled_label')}</span>
                                        </div>
                                    <?php 
                                    elseif (!empty($message['file_path'])): 
                                        // 其他文件消息 - 使用与group.php相同的逻辑
                                        $fileData = json_decode($message['file_path'], true);
                                        if ($fileData && isset($fileData['urls']) && !empty($fileData['urls'])) {
                                            // JSON格式文件消息
                                            $fileUrls = $fileData['urls'];
                                            $fileNames = $fileData['names'] ?? [];
                                            $fileCount = $fileData['count'] ?? count($fileUrls);
                                            
                                            if ($fileCount === 1) {
                                                // 单文件消息 - 显示为大播放器
                                                $fileUrl = $fileUrls[0];
                                                $fileName = $fileNames[0] ?? '';
                                                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                    ?>
                                                <div class="file-message <?php 
                                                    if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])) {
                                                        echo 'video-message';
                                                    } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                                                        echo 'image-message';
                                                    } else {
                                                        echo 'document-message';
                                                    }
                                                ?>">
                                                    <?php 
                                                    // 检查文件是否存在
                                                    $actualFilePath = str_replace('/Chat_System/public/uploads/', BASE_PATH . '/public/uploads/', $fileUrl);
                                                    $fileExists = file_exists($actualFilePath);
                                                    ?>
                                                    <?php if (!$fileExists): ?>
                                                        <!-- 文件不存在时显示占位符 -->
                                                        <div class="file-message file-not-found">
                                                            <div class="file-placeholder">
                                                                <div class="file-icon">📁</div>
                                                                <div class="file-details">
                                                                    <div class="file-name">文件已删除或不存在</div>
                                                                    <div class="file-type"><?php echo str_replace('{ext}', strtoupper($fileExtension), __('file_type_with_ext')); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php elseif (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])): ?>
                                                        <video controls class="message-video">
                                                            <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                        </video>
                                                    <?php elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                                        <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="message-image">
                                                    <?php else: ?>
                                                        <!-- 文档文件显示 -->
                                                        <div class="document-message">
                                                            <div class="file-icon">📄</div>
                                                            <div class="file-details">
                                                                <div class="file-name"><?php echo htmlspecialchars($fileName); ?></div>
                                                                <?php if ($fileExtension): ?>
                                                                    <div class="file-type"><?php echo str_replace('{ext}', strtoupper($fileExtension), __('file_type_with_ext')); ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" download class="download-btn"><?php echo __('download'); ?></a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php } else {
                                                // 多文件消息 - 显示为拼图模式
                                    ?>
                                                <div class="file-message multiple-files-message">
                                                    <div class="image-collage">
                                                        <?php
                                                        $displayCount = min($fileCount, 4);
                                                        $hasMore = $fileCount > 4;
                                                        
                                                        for ($i = 0; $i < $displayCount; $i++) {
                                                            $fileUrl = $fileUrls[$i];
                                                            $fileName = $fileNames[$i] ?? '';
                                                            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                            $isVideo = in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv']);
                                                            $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                                            
                                                            if ($i === 3 && $hasMore) {
                                                                // 最后一张且有多余图片时显示"more"
                                                        ?>
                                                                <div class="collage-item more-item">
                                                                    <div class="more-overlay">
                                                                        <div class="more-dots">⋯</div>
                                                                        <div class="more-text">more</div>
                                                                    </div>
                                                                    <?php if ($isVideo): ?>
                                                                        <video class="collage-thumbnail" muted>
                                                                            <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                                        </video>
                                                                    <?php else: ?>
                                                                        <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="collage-thumbnail">
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php } else { ?>
                                                                <div class="collage-item">
                                                                    <?php if ($isVideo): ?>
                                                                        <video class="collage-thumbnail" muted>
                                                                            <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                                        </video>
                                                                    <?php else: ?>
                                                                        <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="collage-thumbnail">
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="files-info"><?php echo str_replace('{count}', $fileCount, __('message_files_count')); ?></div>
                                                </div>
                                            <?php }
                                        } else {
                                            // 单文件消息（兼容旧格式）
                                            $fileUrl = $message['file_path'];
                                            $fileName = '';
                                            $fileExtension = '';
                                            
                                            // 尝试从URL中提取文件名
                                            $pathParts = explode('/', $fileUrl);
                                            if (count($pathParts) > 0) {
                                                $fileName = end($pathParts);
                                                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                            }
                                        ?>
                                            <div class="file-message <?php 
                                                if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])) {
                                                    echo 'video-message';
                                                } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                                                    echo 'image-message';
                                                } else {
                                                    echo 'document-message';
                                                }
                                            ?>">
                                                <?php if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])): ?>
                                                    <video controls class="message-video">
                                                        <source src="<?php echo htmlspecialchars($fileUrl); ?>" type="video/<?php echo $fileExtension; ?>">
                                                    </video>
                                                <?php elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                                    <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="<?php echo __('message_image_alt'); ?>" class="message-image">
                                                <?php else: ?>
                                                    <!-- 文档文件显示 -->
                                                    <div class="document-message">
                                                        <div class="file-icon">📄</div>
                                                        <div class="file-details">
                                                            <div class="file-name"><?php echo htmlspecialchars($fileName ?: __('file')); ?></div>
                                                            <?php if ($fileExtension): ?>
                                                                <div class="file-type"><?php echo str_replace('{ext}', strtoupper($fileExtension), __('file_type_with_ext')); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" download class="download-btn"><?php echo __('download'); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php } ?>
                                    <?php 
                                    elseif (!empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">${chatT('message_recalled_label')}</span>
                                        </div>
                                    <?php 
                                    else: 
                                        // 普通文本消息
                                    ?>
                                        <div class="message-text"><?php echo nl2br(htmlspecialchars($message['content'])); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="message-time">
                                        <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                    </div>
                                    
                                    <?php if (empty($message['is_recalled'])): ?>
                                        <?php include __DIR__ . '/../components/message-bubble-bar.php'; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- 分享弹窗 -->
                <div class="share-modal hidden" id="shareModal">
                    <div class="share-modal-content">
                        <div class="share-header">
                            <h3>分享消息</h3>
                            <button class="share-close" onclick="closeShareModal()">×</button>
                        </div>
                        <div class="share-search">
                            <input type="text" id="shareSearchInput" placeholder="<?php echo __('search_chat'); ?>" onkeyup="filterShareContacts()">
                        </div>
                        <div class="share-contacts" id="shareContacts">
                            <!-- 联系人列表将在这里动态加载 -->
                        </div>
                        <div class="share-actions">
                            <button class="share-cancel-btn" onclick="closeShareModal()">取消</button>
                            <button class="share-confirm-btn" onclick="confirmShare()" disabled>分享</button>
                        </div>
                    </div>
                </div>
                
                <!-- 引用消息显示区域 -->
                <div class="quote-container hidden" id="quoteContainer">
                    <div class="quote-content">
                        <div class="quote-header">
                            <span class="quote-label"><?php echo __('quote_message'); ?></span>
                            <button class="quote-close" onclick="clearQuote()">×</button>
                        </div>
                        <div class="quote-message" id="quoteMessageContent">
                            <!-- 引用消息内容将在这里显示 -->
                        </div>
                    </div>
                </div>
                
                <div class="message-input-container">
                    <!-- 文件类型选择卡片 -->
                    <div class="file-type-cards hidden" id="fileTypeCards">
                        <div class="file-card" onclick="selectFileType('image')">
                            <div class="card-icon">📷</div>
                            <div class="card-text"><?php echo __('image'); ?></div>
                        </div>
                        <div class="file-card" onclick="selectFileType('video')">
                            <div class="card-icon">🎥</div>
                            <div class="card-text"><?php echo __('video'); ?></div>
                        </div>
                        <div class="file-card" onclick="selectFileType('file')">
                            <div class="card-icon">📄</div>
                            <div class="card-text"><?php echo __('file'); ?></div>
                        </div>
                    </div>
                    
                    <!-- 文件预览区域 -->
                    <div class="file-preview-area hidden" id="filePreviewArea">
                        <div class="preview-header">
                            <span class="preview-title"><?php echo __('file_preview'); ?></span>
                            <button class="remove-preview-btn" onclick="removeFilePreview()">×</button>
                        </div>
                        <div class="preview-content" id="previewContent"></div>
                        <div class="preview-info" id="previewInfo"></div>
                        <div class="add-more-btn" onclick="addMoreFiles()" title="<?php echo __('add_more_files'); ?>">
                            <span class="plus-icon">+</span>
                        </div>
                    </div>
                    
                    <!-- 语音预览区域 -->
                    <div class="voice-preview-area hidden" id="voicePreviewArea">
                        <div class="preview-header">
                            <span class="preview-title"><?php echo __('voice_preview'); ?></span>
                            <button class="remove-preview-btn" onclick="removeVoicePreview()">×</button>
                        </div>
                        <div class="voice-preview-content" id="voicePreviewContent">
                            <audio controls class="voice-preview-player" id="voicePreviewPlayer">
                                <source src="" type="audio/webm">
                            </audio>
                            <div class="voice-preview-info">
                                <div class="voice-duration-text" id="voiceDurationText">0:00</div>
                                <div class="voice-size-text" id="voiceSizeText">0 KB</div>
                            </div>
                        </div>
                    </div>
                    
                    <form class="message-input-form" id="message-form">
                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                        <div class="input-row">
                            <button type="button" class="file-upload-btn" onclick="showFileUploadModal()" title="<?php echo __('send_file'); ?>">
                                📎
                            </button>
                            <button type="button" class="voice-record-btn" id="voiceRecordBtn" title="<?php echo __('voice_record'); ?>" onmousedown="startVoiceRecording(event)" onmouseup="stopVoiceRecording(event)" ontouchstart="startVoiceRecording(event)" ontouchend="stopVoiceRecording(event)">
                                🎤
                            </button>
                            <textarea class="message-input" id="message-input" name="content" placeholder="<?php echo __('message_input_placeholder'); ?>" rows="1"></textarea>
                            <button type="submit" class="send-button" id="send-button"><?php echo __('chat_send_message'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 隐藏的文件输入 -->
    <input type="file" id="fileInput" style="display: none;" onchange="handleFileSelect(this)" multiple>
    
    <!-- 图片预览模态框 -->
    <div class="image-preview-modal hidden" id="imagePreviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo __('image_preview_title'); ?></h3>
                <button class="close-btn" onclick="hideImagePreview()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="image-preview-container">
                    <div class="image-preview-main">
                        <img id="previewImage" src="" alt="<?php echo __('preview_image_alt'); ?>" class="preview-main-image">
                        <video id="previewVideo" controls class="preview-main-video" style="display: none;">
                            <source src="" type="video/mp4">
                        </video>
                    </div>
                    <div class="image-preview-thumbnails" id="previewThumbnails">
                        <!-- 缩略图将通过JavaScript动态生成 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 转发消息模态框 -->
    <div class="forward-modal" id="forwardModal">
        <div class="forward-modal-content">
            <div class="forward-modal-header">
                <div class="forward-modal-title">转发消息</div>
                <button class="forward-modal-close" onclick="hideForwardModal()">&times;</button>
            </div>
            <div class="forward-preview" id="forwardPreview">
                <div class="forward-preview-title">消息预览</div>
                <div class="forward-preview-content" id="forwardPreviewContent"></div>
            </div>
            <div class="forward-recipients">
                <div class="forward-recipients-title">选择接收者</div>
                <div class="recipients-list" id="recipientsList">
                    <!-- 接收者列表将通过JavaScript动态生成 -->
                </div>
            </div>
            <div class="forward-modal-actions">
                <button class="forward-btn forward-btn-cancel" onclick="hideForwardModal()">取消</button>
                <button class="forward-btn forward-btn-send" id="forwardSendBtn" onclick="sendForwardMessage()">发送</button>
            </div>
        </div>
    </div>
    
    <!-- 聊天 i18n + 通用功能 -->
    <?php include BASE_PATH . '/app/views/components/chat-i18n.php'; ?>
    <script src="/Chat_System/public/js/chat-common.js?v=2025012728"></script>
    
    <!-- 提供好友和群组数据给JavaScript -->
    <script>
        // 将PHP数据传递给JavaScript
        window.friendsData = <?php echo json_encode($friends); ?>;
        window.groupsData = <?php echo json_encode($groups); ?>;
        
        window.currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
        window.currentRoomId = <?php echo $room['id']; ?>;
        
        // 当前聊天信息
        window.currentChat = {
            type: 'private',
            id: <?php echo $room['id']; ?>,
            name: '<?php echo addslashes($room['display_name']); ?>'
        };
        
        // 调试信息
        console.log('Page loaded - friendsData:', window.friendsData);
        console.log('Page loaded - groupsData:', window.groupsData);
        console.log('Page loaded - currentChat:', window.currentChat);
    </script>
    <!-- 通话系统模块已简化，使用新的实现 -->
    <script>
        // 全局变量和函数定义 - 必须在最前面
        let hoverTimer = null;
        // touchStartTime 和 touchTimer 已在 chat-common.js 中定义
        
        // 消息操作按钮功能现在在chat-common.js中统一处理
        
        // 鼠标进入气泡栏时保持显示 - 全局函数
        window.keepBubbleVisible = function(bubbleElement) {
            bubbleElement.classList.add('show');
        };
        
        // 鼠标离开气泡栏时延迟隐藏 - 全局函数
        window.hideBubbleOnLeave = function(bubbleElement) {
            setTimeout(() => {
                if (!bubbleElement.matches(':hover')) {
                    bubbleElement.classList.remove('show');
                }
            }, 500);
        };
        
        // 触摸处理函数已在 chat-common.js 中定义
        
        // 通话系统已简化，不再需要复杂的CallSystem检查
        console.log('通话系统已简化，使用新的实现');
        
        // 简化的通话系统变量
        let isInCall = false;
        
        // 打开房间详情页面 - 直接定义在全局作用域
        window.openRoomDetails = function() {
            const roomId = <?php echo $room['id']; ?>;
            window.location.href = `/Chat_System/chat/roomDetails?id=${roomId}`;
        };
        // 暴露其他函数到全局作用域
        window.startVoiceCall = startVoiceCall;
        window.startVideoCall = startVideoCall;
        
        // 发起语音通话
        async function startVoiceCall(targetUserId) {
            console.log('语音通话按钮被点击，目标用户ID:', targetUserId);
            
            try {
                // 生成通话ID
                const callId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const roomId = '<?php echo $roomId; ?>';
                const callType = 'voice';
                const fromUserId = '<?php echo $_SESSION['user_id']; ?>';
                const fromUsername = '<?php echo htmlspecialchars($user['username']); ?>';
                
                // 发送通话邀请
                const response = await fetch('/Chat_System/call/sendCallInvitation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: callId,
                        type: callType,
                        roomId: roomId,
                        callerId: fromUserId,
                        callerName: fromUsername,
                        targetUserId: targetUserId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('通话邀请发送成功:', result);
                    showNotification('正在呼叫对方...', 'success');
                    
                    // 跳转到视频通话页面
                    const videoCallUrl = `/Chat_System/chat/videoCall?roomId=${roomId}&callType=${callType}&fromUserId=${fromUserId}&fromUsername=${encodeURIComponent(fromUsername)}&isIncoming=false&callId=${callId}`;
                    window.location.href = videoCallUrl;
                } else {
                    throw new Error(result.message || '发送通话邀请失败');
                }
                
            } catch (error) {
                console.error('发起语音通话失败:', error);
                showNotification('发起语音通话失败: ' + error.message, 'error');
            }
        }
        
        // 发起视频通话
        async function startVideoCall(targetUserId) {
            console.log('视频通话按钮被点击，目标用户ID:', targetUserId);
            
            try {
                // 生成通话ID
                const callId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const roomId = '<?php echo $roomId; ?>';
                const callType = 'video';
                const fromUserId = '<?php echo $_SESSION['user_id']; ?>';
                const fromUsername = '<?php echo htmlspecialchars($user['username']); ?>';
                
                // 发送通话邀请
                const response = await fetch('/Chat_System/call/sendCallInvitation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: callId,
                        type: callType,
                        roomId: roomId,
                        callerId: fromUserId,
                        callerName: fromUsername,
                        targetUserId: targetUserId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('通话邀请发送成功:', result);
                    showNotification('正在呼叫对方...', 'success');
                    
                    // 跳转到视频通话页面
                    const videoCallUrl = `/Chat_System/chat/videoCall?roomId=${roomId}&callType=${callType}&fromUserId=${fromUserId}&fromUsername=${encodeURIComponent(fromUsername)}&isIncoming=false&callId=${callId}`;
                    window.location.href = videoCallUrl;
                } else {
                    throw new Error(result.message || '发送通话邀请失败');
                }
                
            } catch (error) {
                console.error('发起视频通话失败:', error);
                showNotification('发起视频通话失败: ' + error.message, 'error');
            }
        }
        
        // 切换麦克风
        function toggleMic() {
            console.log('切换麦克风');
            // 简化的麦克风切换功能
        }

        // 切换视频
        function toggleVideo() {
            console.log('切换视频');
            // 简化的视频切换功能
        }

        // 挂断通话
        function hangup() {
            console.log('挂断通话');
            isInCall = false;
            hideCallPopup();
        }
        
        
        // 处理文件加载错误
        document.addEventListener('DOMContentLoaded', function() {
            // 为所有图片添加错误处理
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    console.warn('图片加载失败:', this.src);
                    this.style.display = 'none';
                    // 可以显示占位符
                    const placeholder = document.createElement('div');
                    placeholder.className = 'file-placeholder';
                    placeholder.innerHTML = '📁 文件已删除或不存在';
                    placeholder.style.cssText = 'padding: 10px; background: #f0f0f0; border-radius: 4px; color: #666; text-align: center;';
                    this.parentNode.insertBefore(placeholder, this);
                });
            });
            
            // 为所有视频添加错误处理
            const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.addEventListener('error', function() {
                    console.warn('视频加载失败:', this.src);
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = 'file-placeholder';
                    placeholder.innerHTML = '🎥 视频文件已删除或不存在';
                    placeholder.style.cssText = 'padding: 10px; background: #f0f0f0; border-radius: 4px; color: #666; text-align: center;';
                    this.parentNode.insertBefore(placeholder, this);
                });
            });
            
            // 为所有音频添加错误处理
            const audios = document.querySelectorAll('audio');
            audios.forEach(audio => {
                audio.addEventListener('error', function() {
                    console.warn('音频加载失败:', this.src);
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = 'file-placeholder';
                    placeholder.innerHTML = '🎵 音频文件已删除或不存在';
                    placeholder.style.cssText = 'padding: 10px; background: #f0f0f0; border-radius: 4px; color: #666; text-align: center;';
                    this.parentNode.insertBefore(placeholder, this);
                });
            });
        });
    </script>
    <script>
        // 直接从URL获取roomId，确保正确性
        const roomId = <?php echo json_encode($_GET['id'] ?? null); ?>;
        const userId = <?php echo $_SESSION['user_id']; ?>;
        const username = <?php echo json_encode($user['username']); ?>;
        
        
        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', function() {
            console.log('页面加载完成');
            
        // 检查引用功能是否加载
        console.log('引用功能状态检查:');
        console.log('- quoteMessage:', typeof window.quoteMessage);
        console.log('- clearQuote:', typeof window.clearQuote);
        
        // 为引用消息添加点击事件
        addQuoteClickHandlers();
        
        // 定义引用功能函数（确保立即可用）
        window.quoteMessage = function(messageId) {
            console.log('引用消息:', messageId);
            
            // 获取消息元素
            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
            if (!messageElement) {
                console.error('找不到消息元素:', messageId);
                showNotification('找不到要引用的消息', 'error');
                return;
            }
            
            // 获取消息内容
            let messageContent = '';
            let messageSender = '';
            let messageType = '';
            
            // 尝试获取消息文本内容
            const textElement = messageElement.querySelector('.message-text');
            if (textElement) {
                messageContent = textElement.textContent || textElement.innerText || '';
            } else {
                // 如果没有找到文本元素，尝试获取其他内容
                const contentElement = messageElement.querySelector('.message-content');
                if (contentElement) {
                    messageContent = contentElement.textContent || contentElement.innerText || '';
                }
            }
            
            // 获取发送者信息
            const senderElement = messageElement.querySelector('.message-sender') || 
                                 messageElement.querySelector('.sender-name') ||
                                 messageElement.querySelector('.username');
            if (senderElement) {
                messageSender = senderElement.textContent || senderElement.innerText || '';
            } else {
                // 从数据属性获取
                const senderId = messageElement.getAttribute('data-sender-id');
                const senderName = messageElement.getAttribute('data-sender-name');
                if (senderName) {
                    messageSender = senderName;
                } else if (senderId) {
                    messageSender = '用户' + senderId;
                } else {
                    messageSender = '未知用户';
                }
            }
            
            // 检查消息类型
            const fileMessage = messageElement.querySelector('.file-message');
            const voiceMessage = messageElement.querySelector('.voice-message');
            const recalledMessage = messageElement.querySelector('.recalled-message');
            
            if (recalledMessage) {
                messageType = chatT('message_recalled_label');
                messageContent = chatT('message_recalled_desc');
            } else if (voiceMessage) {
                messageType = chatT('voice_message');
                messageContent = chatT('voice_message');
            } else if (fileMessage) {
                const fileInfo = messageElement.querySelector('.files-info');
                if (fileInfo) {
                    messageType = `[${fileInfo.textContent}]`;
                } else {
                    messageType = chatT('file');
                }
                messageContent = chatT('file_message_text');
            }
            
            // 设置引用的消息ID
            window.quotedMessageId = messageId;
            
            // 构建引用内容（仅用于显示）
            const quotedContent = `> ${messageSender}: ${messageType}${messageContent}`;
            
            // 将引用内容添加到输入框
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                // 如果输入框已有内容，在末尾添加引用
                const currentContent = messageInput.value.trim();
                if (currentContent) {
                    messageInput.value = currentContent + '\n\n' + quotedContent;
                } else {
                    messageInput.value = quotedContent + '\n\n';
                }
                
                // 聚焦到输入框
                messageInput.focus();
                
                // 将光标移动到引用内容之后
                const cursorPosition = messageInput.value.length;
                messageInput.setSelectionRange(cursorPosition, cursorPosition);
                
                showNotification('已引用消息', 'success');
                
                // 显示引用指示器
                showQuoteIndicator(messageSender, messageContent);
            } else {
                console.error('找不到消息输入框');
                showNotification('无法引用消息，找不到输入框', 'error');
            }
        };
        
        window.clearQuote = function() {
            window.quotedMessageId = null;
            const indicator = document.getElementById('quoteIndicator');
            if (indicator) {
                indicator.remove();
            }
            showNotification('已取消引用', 'info');
        };
        
        // 显示引用指示器
        function showQuoteIndicator(sender, content) {
            // 查找或创建引用指示器
            let indicator = document.getElementById('quoteIndicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'quoteIndicator';
                indicator.className = 'quote-indicator';
                indicator.innerHTML = `
                    <div class="quote-indicator-content">
                        <span class="quote-indicator-label">引用</span>
                        <span class="quote-indicator-text">${sender}: ${content.substring(0, 50)}${content.length > 50 ? '...' : ''}</span>
                        <button class="quote-indicator-close" onclick="clearQuote()">×</button>
                    </div>
                `;
                
                // 插入到消息输入框上方
                const messageInputContainer = document.querySelector('.message-input-container');
                if (messageInputContainer) {
                    messageInputContainer.insertBefore(indicator, messageInputContainer.firstChild);
                }
            } else {
                // 更新现有指示器
                const textElement = indicator.querySelector('.quote-indicator-text');
                if (textElement) {
                    textElement.textContent = `${sender}: ${content.substring(0, 50)}${content.length > 50 ? '...' : ''}`;
                }
            }
        }
        
        // 点击引用消息定位到原消息
        function scrollToQuotedMessage(quotedMessageId) {
            console.log('定位到引用消息:', quotedMessageId);
            
            // 查找被引用的消息
            const quotedMessage = document.querySelector(`[data-message-id="${quotedMessageId}"]`);
            if (!quotedMessage) {
                console.error('找不到被引用的消息:', quotedMessageId);
                showNotification('找不到被引用的消息', 'error');
                return;
            }
            
            // 滚动到消息位置
            quotedMessage.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            // 添加高亮效果
            quotedMessage.classList.add('highlighted');
            
            // 3秒后移除高亮效果
            setTimeout(() => {
                quotedMessage.classList.remove('highlighted');
            }, 3000);
            
            showNotification('已定位到引用消息', 'success');
        }
        
        // 为引用消息添加点击事件
        function addQuoteClickHandlers() {
            // 为所有引用消息添加点击事件
            const quotedMessages = document.querySelectorAll('.quoted-message');
            quotedMessages.forEach(quotedMsg => {
                // 查找包含此引用消息的原始消息
                const parentMessage = quotedMsg.closest('.message');
                if (parentMessage) {
                    const messageId = parentMessage.getAttribute('data-message-id');
                    if (messageId) {
                        // 从数据库获取引用关系
                        fetch(`/Chat_System/chat/getQuotedMessageId?message_id=${messageId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.quoted_message_id) {
                                    // 添加点击事件
                                    quotedMsg.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        scrollToQuotedMessage(data.quoted_message_id);
                                    });
                                    
                                    // 添加视觉提示
                                    quotedMsg.style.cursor = 'pointer';
                                    quotedMsg.title = '点击定位到原消息';
                                }
                            })
                            .catch(error => {
                                console.error('获取引用关系失败:', error);
                            });
                    }
                }
            });
        }
        window.addQuoteClickHandlers = addQuoteClickHandlers;
        
        
        // 发送文本消息（支持引用）
        function sendTextMessage(content, roomId) {
            // 构建请求体
            let body = `room_id=${roomId}&content=${encodeURIComponent(content)}`;
            if (window.quotedMessageId) {
                body += `&quoted_message_id=${window.quotedMessageId}`;
            }
            
            fetch('/Chat_System/chat/sendMessage', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: body
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('服务器返回的不是有效的JSON:', text);
                        throw new Error('服务器返回了无效的响应格式');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    // 发送成功后清空输入框
                    document.getElementById('message-input').value = '';
                    
                    // 清除引用状态
                    if (window.quotedMessageId) {
                        clearQuote();
                    }
                    
                    // 立即将服务器返回的消息追加到列表（与群组逻辑一致，无需刷新）
                    if (data.message && typeof addNewMessageToChat === 'function') {
                        addNewMessageToChat(data.message);
                        setTimeout(scrollToBottom, 100);
                    }
                    refreshMessagesArea(true);
                } else {
                    showNotification('发送失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('发送消息失败:', error);
                showNotification('发送失败: ' + error.message, 'error');
            });
        }
            
            // 消息发送功能
            const messageForm = document.getElementById('message-form');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            
            // 处理表单提交
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const content = messageInput.value.trim();
                const hasFile = selectedFiles.length > 0;
                const hasVoice = recordedAudioBlob !== null;
                
                // 如果没有内容、文件或语音，不发送
                if (!content && !hasFile && !hasVoice) return;
                
                // 禁用发送按钮
                sendButton.disabled = true;
                sendButton.textContent = chatT('sending');
                
                // 如果有语音，发送语音消息；如果有文件，发送文件；否则发送文本消息
                const roomId = <?php echo $room['id']; ?>;
                if (hasVoice) {
                    sendVoiceMessage(recordedAudioBlob, roomId);
                } else if (hasFile) {
                    sendFileWithMessage(content, roomId);
                } else {
                    sendTextMessage(content, roomId);
                }
                
                // 重置按钮状态
                setTimeout(() => {
                    sendButton.disabled = false;
                    sendButton.textContent = chatT('chat_send_message');
                }, 1000);
            });
            
            // 回车发送消息（Shift+Enter换行）
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });
            
            // 输入框自动调整高度
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
            
            // 移动端输入框焦点处理
            messageInput.addEventListener('focus', function() {
                // 延迟滚动，确保键盘弹出后再滚动
                setTimeout(() => {
                    scrollToBottom();
                }, 300);
            });
            
            // 页面加载完成后滚动到底部
            setTimeout(scrollToBottom, 100);
            
            // 立即检查一次通话邀请，然后每 1 秒轮询（保证邀请尽快显示）
            checkCallInvitations();
            setInterval(checkCallInvitations, 1000);
            
            // 每 1 秒轮询同步消息（含撤回等状态变更）
            setInterval(function() { refreshMessagesArea(false); }, 1000);
            
            // 页面重新可见或侧边栏切换回来时立即同步
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) refreshMessagesArea(false);
            });
            window.addEventListener('focus', function() { refreshMessagesArea(false); });
        });
        
        // 添加消息到界面
        function addMessageToUI(content, isOwn) {
            const messagesContainer = document.getElementById('messages-container');
            const messageElement = document.createElement('div');
            messageElement.className = `message ${isOwn ? 'own' : ''}`;
            
            const currentTime = new Date().toLocaleTimeString('zh-CN', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            // 获取用户头像信息
            const currentUserAvatar = '<?php echo !empty($user['avatar']) && $user['avatar'] !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $user['avatar']) ? "/Chat_System/public/uploads/avatars/" . $user['avatar'] : ""; ?>';
            const currentUsername = '<?php echo htmlspecialchars($user['username']); ?>';
            const roomDisplayName = '<?php echo htmlspecialchars($room['display_name']); ?>';
            const roomAvatar = '<?php echo !empty($room['avatar']) && $room['avatar'] !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $room['avatar']) ? "/Chat_System/public/uploads/avatars/" . $room['avatar'] : ""; ?>';
            
            // 生成头像HTML
            let avatarHtml = '';
            if (isOwn) {
                if (currentUserAvatar) {
                    avatarHtml = `<img src="${currentUserAvatar}" alt="${currentUsername}的头像">`;
                } else {
                    avatarHtml = currentUsername.charAt(0).toUpperCase();
                }
            } else {
                if (roomAvatar) {
                    avatarHtml = `<img src="${roomAvatar}" alt="${roomDisplayName}的头像">`;
                } else {
                    avatarHtml = roomDisplayName.charAt(0).toUpperCase();
                }
            }
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    ${avatarHtml}
                </div>
                <div class="message-content">
                    <div class="message-text">${content.replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${currentTime}</div>
                </div>
            `;
            
            messagesContainer.appendChild(messageElement);
        }
        
        // 滚动到底部
        function scrollToBottom() {
            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // 刷新消息区域（同步新消息与状态变更）
        function refreshMessagesArea(forceScroll) {
            syncRoomMessages(<?php echo $room['id']; ?>, createMessageElement, { forceScroll: !!forceScroll });
        }
        
        // 将一条新消息立即追加到聊天列表（参考群组逻辑，发送后立即可见）
        function addNewMessageToChat(messageData) {
            if (!messageData || !messageData.id) return;
            const messagesContainer = document.getElementById('messages-container');
            if (!messagesContainer) return;
            // 若 API 返回的是 quoted_sender_name，统一为 quoted_username 供后续扩展使用
            if (messageData.quoted_sender_name !== undefined && messageData.quoted_username === undefined) {
                messageData.quoted_username = messageData.quoted_sender_name;
            }
            const messageElement = createMessageElement(messageData);
            if (messageElement) {
                messageElement.setAttribute('data-msg-sig', getMessageSignature(messageData));
                messagesContainer.appendChild(messageElement);
                if (typeof enhanceMediaElements === 'function') {
                    enhanceMediaElements(messageElement);
                }
                if (typeof window.addQuoteClickHandlers === 'function') {
                    window.addQuoteClickHandlers();
                }
            }
        }
        
        // 创建消息元素
        function createMessageElement(message) {
            const messageElement = document.createElement('div');
            const isOwn = message.sender_id == <?php echo $_SESSION['user_id']; ?>;
            messageElement.className = `message ${isOwn ? 'own' : ''}`;
            messageElement.setAttribute('data-message-id', String(message.id));
            messageElement.setAttribute('data-msg-sig', getMessageSignature(message));
            
            // 生成头像HTML
            let avatarHtml = '';
            if (message.avatar && message.avatar !== 'default_avatar.png') {
                avatarHtml = `<img src="/Chat_System/public/uploads/avatars/${message.avatar}" alt="${message.username}${chatT('avatar_alt_suffix')}">`;
            } else {
                avatarHtml = message.username.charAt(0).toUpperCase();
            }
            
            // 格式化时间
            const messageTime = new Date(message.created_at).toLocaleTimeString('zh-CN', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            // 处理不同类型的消息内容
            let messageContent = '';
            
            // 处理语音消息
            if (message.message_type === 'voice' && !message.is_recalled) {
                messageContent = `
                    <div class="voice-message">
                        <audio controls class="voice-player">
                            <source src="/Chat_System/${message.file_path}" type="audio/webm">
                            ${chatT('audio_not_supported')}
                        </audio>
                        <div class="voice-duration">${chatT('voice_message')}</div>
                    </div>
                `;
            }
            // 处理文件消息
            else if (message.file_path && !message.is_recalled) {
                let fileData = null;
                try {
                    fileData = JSON.parse(message.file_path);
                } catch (e) {
                    // 如果JSON解析失败，说明是旧格式的单文件路径
                    fileData = null;
                }
                
                if (fileData && fileData.urls && fileData.urls.length > 0) {
                    // JSON格式文件消息
                    const fileUrls = fileData.urls;
                    const fileNames = fileData.names || [];
                    const fileCount = fileData.count || fileUrls.length;
                    
                    if (fileCount === 1) {
                        // 单文件消息
                        const fileUrl = fileUrls[0];
                        const fileName = fileNames[0] || '';
                        const fileExtension = fileName.split('.').pop().toLowerCase();
                        
                        if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(fileExtension)) {
                            // 视频文件
                            messageContent = `
                                <div class="file-message video-message">
                                    <video controls class="message-video">
                                        <source src="${fileUrl}" type="video/${fileExtension}">
                                    </video>
                                </div>
                            `;
                        } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                            // 图片文件
                            messageContent = `
                                <div class="file-message image-message">
                                    <img src="${fileUrl}" alt="${chatT('message_image_alt')}" class="message-image">
                                </div>
                            `;
                        } else {
                            // 其他文件
                            messageContent = `
                                <div class="file-message document-message">
                                    <div class="document-message">
                                        <div class="file-icon">📄</div>
                                        <div class="file-details">
                                            <div class="file-name">${fileName}</div>
                                            <div class="file-type">${chatT('file_type_with_ext', { ext: fileExtension.toUpperCase() })}</div>
                                        </div>
                                        <a href="${fileUrl}" download class="download-btn">${chatT('download')}</a>
                                    </div>
                                    <div class="file-name">${message.content}</div>
                                </div>
                            `;
                        }
                    } else {
                        // 多文件消息
                        let collageHtml = '<div class="image-collage">';
                        const displayCount = Math.min(fileCount, 4);
                        const hasMore = fileCount > 4;
                        
                        for (let i = 0; i < displayCount; i++) {
                            const fileUrl = fileUrls[i];
                            const fileName = fileNames[i] || '';
                            const fileExtension = fileName.split('.').pop().toLowerCase();
                            const isVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(fileExtension);
                            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension);
                            
                            if (i === 3 && hasMore) {
                                collageHtml += `
                                    <div class="collage-item more-item">
                                        <div class="more-overlay">
                                            <div class="more-dots">⋯</div>
                                            <div class="more-text">more</div>
                                        </div>
                                        ${isVideo ? 
                                            `<video class="collage-thumbnail" muted><source src="${fileUrl}" type="video/${fileExtension}"></video>` :
                                            `<img src="${fileUrl}" alt="图片" class="collage-thumbnail">`
                                        }
                                    </div>
                                `;
                            } else {
                                collageHtml += `
                                    <div class="collage-item">
                                        ${isVideo ? 
                                            `<video class="collage-thumbnail" muted><source src="${fileUrl}" type="video/${fileExtension}"></video>` :
                                            `<img src="${fileUrl}" alt="图片" class="collage-thumbnail">`
                                        }
                                    </div>
                                `;
                            }
                        }
                        collageHtml += '</div>';
                        
                        messageContent = `
                            <div class="file-message multiple-files-message">
                                ${collageHtml}
                                <div class="files-info">${chatT('message_files_count', { count: fileCount })}</div>
                            </div>
                        `;
                    }
                } else {
                    // 单文件消息（兼容旧格式）
                    const fileUrl = message.file_path;
                    const fileName = fileUrl.split('/').pop();
                    const fileExtension = fileName.split('.').pop().toLowerCase();
                    
                    if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(fileExtension)) {
                        messageContent = `
                            <div class="file-message video-message">
                                <video controls class="message-video">
                                    <source src="${fileUrl}" type="video/${fileExtension}">
                                </video>
                            </div>
                        `;
                    } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                        messageContent = `
                            <div class="file-message image-message">
                                <img src="${fileUrl}" alt="图片" class="message-image">
                            </div>
                        `;
                    } else {
                        messageContent = `
                            <div class="file-message document-message">
                                <div class="document-message">
                                    <div class="file-icon">📄</div>
                                    <div class="file-details">
                                        <div class="file-name">${fileName || chatT('file')}</div>
                                        <div class="file-type">${chatT('file_type_with_ext', { ext: fileExtension.toUpperCase() })}</div>
                                    </div>
                                    <a href="${fileUrl}" download class="download-btn">${chatT('download')}</a>
                                </div>
                                <div class="file-name">${message.content}</div>
                            </div>
                        `;
                    }
                }
            }
            // 处理撤回消息
            else if (message.is_recalled) {
                messageContent = `
                    <div class="recalled-message">
                        <span class="recall-icon">↩️</span>
                        <span class="recall-text">${chatT('message_recalled_label')}</span>
                    </div>
                `;
            }
            // 普通文本消息
            else {
                messageContent = `<div class="message-text">${message.content.replace(/\n/g, '<br>')}</div>`;
            }
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    ${avatarHtml}
                </div>
                <div class="message-content">
                    ${messageContent}
                    <div class="message-time">${messageTime}</div>
                </div>
            `;
            
            if (window.currentUserId) {
                attachMessageBubbleBar(messageElement, message, window.currentUserId);
            }
            
            return messageElement;
        }
        
        // 初始化聊天通用功能
        initChatCommon();
        stampExistingMessageSignatures(<?php echo (int)$room['id']; ?>);
        
        // 检查通话邀请
        function checkCallInvitations() {
            const roomId = <?php echo $roomId; ?>;
            const userId = <?php echo $_SESSION['user_id']; ?>;
            
            fetch(`/Chat_System/chat/getCallInvitations?roomId=${roomId}&userId=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.invitations && data.invitations.length > 0) {
                        // 处理通话邀请
                        data.invitations.forEach(invitation => {
                            if (invitation.status === 'inviting' && invitation.target_user_id == userId) {
                                showCallInvitation(invitation);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('检查通话邀请失败:', error);
                });
        }
        
        // 显示通话邀请弹窗
        function showCallInvitation(invitation) {
            // 检查是否已经显示过这个邀请
            if (document.getElementById('call-invitation-modal')) {
                return;
            }
            
            const modal = document.createElement('div');
            modal.id = 'call-invitation-modal';
            modal.className = 'call-invitation-sent';
            modal.innerHTML = `
                <div class="invitation-content">
                    <div class="caller-avatar">
                        <i class="fas fa-${invitation.call_type === 'voice' ? 'phone' : 'video'}"></i>
                    </div>
                    <div class="invitation-text">
                        <h3>${invitation.caller_name} 邀请您进行${invitation.call_type === 'voice' ? '语音' : '视频'}通话</h3>
                        <p>点击接受开始通话，或点击拒绝取消邀请</p>
                    </div>
                    <div class="invitation-actions">
                        <button class="btn-accept" onclick="acceptCallInvitation('${invitation.id}')">
                            <i class="fas fa-check"></i> 接受
                        </button>
                        <button class="btn-reject" onclick="rejectCallInvitation('${invitation.id}')">
                            <i class="fas fa-times"></i> 拒绝
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        // 接受通话邀请
        function acceptCallInvitation(callId) {
            fetch('/Chat_System/chat/acceptCallInvitation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ callId: callId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 移除邀请弹窗
                    const modal = document.getElementById('call-invitation-modal');
                    if (modal) {
                        modal.remove();
                    }
                    
                    // 跳转到视频通话页面
                    const roomId = <?php echo $roomId; ?>;
                    const callType = data.callData?.type || 'voice';
                    const fromUserId = data.callData?.callerId || '';
                    const fromUsername = data.callData?.callerName || '';
                    
                    const videoCallUrl = `/Chat_System/chat/videoCall?roomId=${roomId}&callType=${callType}&fromUserId=${fromUserId}&fromUsername=${encodeURIComponent(fromUsername)}&isIncoming=true&callId=${callId}`;
                    window.location.href = videoCallUrl;
                } else {
                    showNotification('接受通话失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('接受通话邀请失败:', error);
                showNotification('接受通话邀请失败', 'error');
            });
        }
        
        // 拒绝通话邀请
        function rejectCallInvitation(callId) {
            fetch('/Chat_System/chat/rejectCallInvitation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ callId: callId })
            })
            .then(response => response.json())
            .then(data => {
                // 移除邀请弹窗
                const modal = document.getElementById('call-invitation-modal');
                if (modal) {
                    modal.remove();
                }
                
                if (data.success) {
                    showNotification('已拒绝通话邀请', 'success');
                } else {
                    showNotification('拒绝通话失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('拒绝通话邀请失败:', error);
                showNotification('拒绝通话邀请失败', 'error');
            });
        }
        
    </script>
    
    <style>
        /* 引用消息样式 */
        .quote-container {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 12px;
        }
        
        .quote-header {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .quote-content {
            font-size: 14px;
            color: #495057;
        }
        
        /* 文件不存在样式 */
        .file-not-found {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .file-placeholder {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .file-placeholder-small {
            font-size: 24px;
            color: #adb5bd;
        }
        
        .file-placeholder .file-icon {
            font-size: 48px;
            color: #adb5bd;
        }
        
        .file-placeholder .file-details {
            text-align: left;
        }
        
        .file-placeholder .file-name {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 4px;
        }
        
        .file-placeholder .file-type {
            font-size: 12px;
            color: #adb5bd;
        }
    </style>
</body>
</html>