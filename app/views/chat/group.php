<?php
// 群组聊天页面
$currentTab = 'chats'; // 设置当前标签页

// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();

// 获取群组信息
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/models/Chat.php';

$chatModel = new Chat();

// 获取当前群组信息
$groupId = $_GET['id'] ?? null;
if (!$groupId) {
    header("Location: /CHATTING/dashboard");
    exit;
}

$group = $chatModel->getGroupInfo($groupId, $_SESSION['user_id']);
if (!$group) {
    // 记录调试信息
    error_log("用户 {$_SESSION['user_id']} 尝试访问群组 $groupId 但无权限");
    header("Location: /CHATTING/dashboard");
    exit;
}

// 添加调试信息
error_log("群组信息获取成功: ID={$group['id']}, 名称={$group['name']}, 类型={$group['type']}");

// 强制输出调试信息到页面
echo "<!-- 调试信息: 群组ID={$group['id']}, 名称={$group['name']} -->";


// 检查房间类型，如果是私聊，重定向到私聊页面
if ($group['type'] === 'private') {
    header("Location: /CHATTING/chat/room?id=" . $groupId);
    exit;
}

// 获取群组消息
$messages = $chatModel->getRoomMessages($groupId, $_SESSION['user_id']);

// 标记消息为已读
$chatModel->markRoomMessagesAsRead($groupId, $_SESSION['user_id']);

// 获取群组成员列表
$members = $chatModel->getGroupMembers($groupId);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo htmlspecialchars($group['name']); ?> - <?php echo __('page_title_chat'); ?></title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* 群组聊天页面样式 */
        .chat-page-container {
            display: flex;
            height: 100vh;
            background: #f8f9fa;
        }
        
        .chat-container {
            display: flex;
            flex: 1;
            height: 100vh;
            overflow: hidden;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: white;
        }
        
        .mobile-header {
            display: none;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-bottom: 1px solid #e1e5e9;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .menu-button {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
            margin-right: 15px;
        }
        
        .chat-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: white;
            border-bottom: 1px solid #e1e5e9;
            position: relative;
        }
        
        .room-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .room-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .chat-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        
        .chat-status {
            font-size: 0.85rem;
            color: #666;
        }
        
        .call-buttons {
            display: flex;
            gap: 8px;
        }
        
        .call-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .call-btn.voice {
            background: #2ed573;
            color: white;
        }
        
        .call-btn.video {
            background: #00bfff;
            color: white;
        }
        
        .call-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .settings-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: #f8f9fa;
            color: #666;
        }
        
        .settings-btn:hover {
            background: #667eea;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        /* 调试样式 - 确保按钮可见 */
        .settings-btn {
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #f8f9fa !important;
            border: 2px solid #667eea !important;
        }
        
        .settings-btn i {
            font-size: 20px !important;
            color: #6c757d !important;
            display: block !important;
            visibility: visible !important;
            border-radius: 50% !important;
            padding: 2px !important;
            background: rgba(102, 126, 234, 0.1) !important;
            width: 24px !important;
            height: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .call-btn {
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        
        .call-btn i {
            font-size: 16px !important;
            display: block !important;
            visibility: visible !important;
        }
        
        .call-buttons {
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message {
            display: flex;
            margin-bottom: 15px;
            animation: fadeInUp 0.3s ease;
        }
        
        .message.own {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
            flex-shrink: 0;
            margin: 0 10px;
        }
        
        .message-content {
            max-width: 70%;
            background: white;
            padding: 12px 16px;
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .message.own .message-content {
            background: #667eea;
            color: white;
        }
        
        .message-sender {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 4px;
            font-weight: 600;
        }
        
        .message.own .message-sender {
            color: rgba(255,255,255,0.8);
        }
        
        .message-text {
            line-height: 1.4;
            word-wrap: break-word;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #999;
            margin-top: 4px;
            text-align: right;
        }
        
        .message.own .message-time {
            color: rgba(255,255,255,0.7);
        }
        
        .message-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e1e5e9;
        }
        
        .message-form {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            min-height: 40px;
            max-height: 100px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
            font-family: inherit;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }
        
        .send-button {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .send-button:hover {
            background: #5a6fd8;
            transform: scale(1.05);
        }
        
        .send-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .file-upload-btn, .voice-record-btn {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .file-upload-btn:hover, .voice-record-btn:hover {
            background: #e9ecef;
            border-color: #667eea;
            color: #667eea;
        }
        
        .voice-record-btn.recording {
            background: #ff4757;
            color: white;
            border-color: #ff4757;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* 文件上传相关样式 - 参考room.php */
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
        
        /* 图片和文件消息样式 */
        .file-message {
            margin: 8px 0;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
        }
        
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
            transition: transform 0.2s ease;
            display: block;
            object-fit: cover;
        }
        
        .message-image:hover {
            transform: scale(1.02);
        }
        
        .video-message {
            max-width: 300px;
            background: transparent;
            border: none;
        }
        
        .message-video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .document-message {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            gap: 12px;
        }
        
        .file-icon {
            font-size: 24px;
            color: #667eea;
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        
        .file-type {
            font-size: 12px;
            color: #666;
        }
        
        .download-btn {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        
        .download-btn:hover {
            background: #5a6fd8;
        }
        
        /* 多文件消息网格样式 */
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
        
        .collage-thumbnail:hover {
            transform: scale(1.05);
        }
        
        .more-item {
            position: relative;
        }
        
        .more-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .more-dots {
            font-size: 24px;
            margin-bottom: 4px;
        }
        
        .more-text {
            font-size: 12px;
        }
        
        .files-info {
            padding: 8px 12px;
            background: #f8f9fa;
            border-top: 1px solid #e1e5e9;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        /* 语音消息样式 */
        .voice-message {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            max-width: 300px;
        }
        
        .voice-player {
            flex: 1;
            height: 32px;
        }
        
        .voice-duration {
            font-size: 12px;
            color: #666;
            white-space: nowrap;
        }
        
        /* 撤回消息样式 */
        .recalled-message {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 8px;
            font-style: italic;
            color: #666;
            max-width: 200px;
        }
        
        .recall-icon {
            font-size: 16px;
        }
        
        .recall-text {
            font-size: 12px;
        }
        
        /* 图片预览模态框样式 */
        .image-preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .image-preview-modal.hidden {
            display: none;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 90%;
            max-height: 90%;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .image-preview-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .image-preview-main {
            text-align: center;
        }
        
        .preview-main-image,
        .preview-main-video {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .image-preview-thumbnails {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .thumbnail-item {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s ease;
        }
        
        .thumbnail-item:hover {
            border-color: #667eea;
        }
        
        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        
        /* 消息容器相对定位 */
        .message {
            position: relative;
        }
        
        /* 置顶消息区域 - 固定在聊天头部下方 */
        .pinned-messages-container {
            position: sticky;
            top: 0;
            z-index: 100;
            background: white;
            border-bottom: 1px solid #e1e5e9;
            padding: 10px 20px;
            margin-bottom: 10px;
        }
        
        .pinned-messages-container .pinned-message {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #fdcb6e;
            border-radius: 12px;
            margin: 8px 0;
            padding: 12px;
            position: relative;
        }
        
        .pinned-messages-container .pinned-message::before {
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
        
        .pinned-messages-container .pinned-message .message-content {
            background: transparent;
            box-shadow: none;
        }
        
        /* 置顶消息中的照片尺寸限制 */
        .pinned-messages-container .image-message {
            max-width: 200px;
        }
        
        .pinned-messages-container .message-image {
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
        }
        
        .pinned-messages-container .image-collage {
            max-width: 200px;
        }
        
        .pinned-messages-container .collage-item {
            aspect-ratio: 1;
        }
        
        .pinned-messages-container .collage-thumbnail {
            max-height: 75px;
        }
        
        /* 普通消息容器 */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 20px;
        }
        
        /* 移动端置顶消息样式优化 */
        @media (max-width: 768px) {
            .pinned-messages-container {
                padding: 8px 15px;
                margin-bottom: 8px;
            }
            
            .pinned-messages-container .pinned-message {
                padding: 10px;
                margin: 6px 0;
            }
            
            .pinned-messages-container .pinned-message::before {
                font-size: 10px;
                padding: 1px 6px;
                top: -6px;
                left: 10px;
            }
            
            .messages-container {
                padding: 0 15px;
            }
        }
        
        @media (max-width: 480px) {
            .pinned-messages-container {
                padding: 6px 12px;
                margin-bottom: 6px;
            }
            
            .pinned-messages-container .pinned-message {
                padding: 8px;
                margin: 4px 0;
            }
            
            .messages-container {
                padding: 0 12px;
            }
        }
        
        /* 修改消息弹窗样式 */
        .edit-message-modal {
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
        
        .edit-message-modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .edit-modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .edit-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .edit-modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .edit-modal-close {
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
            transition: background 0.2s ease;
        }
        
        .edit-modal-close:hover {
            background: #f0f0f0;
        }
        
        .edit-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            margin-bottom: 20px;
        }
        
        .edit-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .edit-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .edit-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .edit-btn-cancel {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e0e0e0;
        }
        
        .edit-btn-cancel:hover {
            background: #e9ecef;
        }
        
        .edit-btn-save {
            background: #667eea;
            color: white;
        }
        
        .edit-btn-save:hover {
            background: #5a6fd8;
        }
        
        .edit-btn-save:disabled {
            background: #ccc;
            cursor: not-allowed;
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
            transition: background 0.2s ease;
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
        
        .recipients-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
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

        /* @功能样式 */
        .mention-modal {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            margin-bottom: 8px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border: 1px solid #e1e5e9;
            z-index: 1000;
            max-height: 300px;
            overflow: hidden;
            animation: slideUp 0.3s ease-out;
        }

        .mention-modal.hidden {
            display: none;
        }

        .mention-modal-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .mention-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #e1e5e9;
        }

        .mention-modal-title {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .mention-modal-close {
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
            border-radius: 50%;
            transition: background 0.2s ease;
        }

        .mention-modal-close:hover {
            background: #e9ecef;
        }

        .mention-search-box {
            padding: 12px 16px;
            border-bottom: 1px solid #e1e5e9;
        }

        .mention-search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .mention-search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        .mention-members-list {
            flex: 1;
            overflow-y: auto;
            max-height: 200px;
        }

        .mention-member-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .mention-member-item:hover {
            background: #f8f9ff;
        }

        .mention-member-item.selected {
            background: #e3f2fd;
        }

        .mention-member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-right: 12px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .mention-member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .mention-member-info {
            flex: 1;
        }

        .mention-member-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
        }

        .mention-member-status {
            font-size: 12px;
            color: #666;
        }

        .mention-member-status.online {
            color: #2ed573;
        }

        .mention-member-status.offline {
            color: #999;
        }

        /* @消息显示样式 */
        .message-text .mention {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 0 2px;
        }

        .message-text .mention:hover {
            background: #bbdefb;
        }

        /* 移动端@功能优化 */
        @media (max-width: 768px) {
            .mention-modal {
                max-height: 250px;
            }
            
            .mention-members-list {
                max-height: 150px;
            }
            
            .mention-member-item {
                padding: 10px 12px;
            }
            
            .mention-member-avatar {
                width: 28px;
                height: 28px;
                font-size: 12px;
                margin-right: 10px;
            }
        }

        /* 移动端优化 */
        @media (max-width: 768px) {
            .mobile-header {
                display: flex;
            }
            
            .chat-header {
                padding: 15px;
            }
            
            .room-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .chat-title {
                font-size: 1rem;
            }
            
            .chat-status {
                font-size: 0.8rem;
            }
            
            .call-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .settings-btn {
                width: 35px !important;
                height: 35px !important;
                font-size: 14px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .settings-btn i {
                font-size: 18px !important;
                border-radius: 50% !important;
                padding: 2px !important;
                background: rgba(102, 126, 234, 0.1) !important;
                width: 20px !important;
                height: 20px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .messages-container {
                padding: 15px;
            }
            
            .message-content {
                max-width: 85%;
            }
            
            .message-avatar {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }
            
            .message-input-container {
                padding: 15px;
            }
            
            .send-button, .file-upload-btn, .voice-record-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            /* 移动端图片样式优化 */
            .image-message, .video-message, .multiple-files-message {
                max-width: 180px;
            }
            
            .message-image, .message-video {
                max-width: 100%;
                height: auto;
            }
            
            .image-collage {
                grid-template-columns: repeat(2, 1fr);
                gap: 1px;
            }
            
            .collage-item {
                aspect-ratio: 1;
            }
            
            .voice-message {
                max-width: 250px;
                padding: 10px;
            }
            
            .voice-player {
                height: 28px;
            }
            
        }
        
        @media (max-width: 480px) {
            .mobile-header {
                padding: 12px 15px;
            }
            
            .chat-header {
                padding: 12px;
            }
            
            .room-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
            
            .chat-title {
                font-size: 0.9rem;
            }
            
            .chat-status {
                font-size: 0.75rem;
            }
            
            .call-btn {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
            
            .settings-btn {
                width: 32px !important;
                height: 32px !important;
                font-size: 12px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .settings-btn i {
                font-size: 16px !important;
                border-radius: 50% !important;
                padding: 2px !important;
                background: rgba(102, 126, 234, 0.1) !important;
                width: 18px !important;
                height: 18px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .messages-container {
                padding: 12px;
            }
            
            .message-content {
                max-width: 90%;
                padding: 10px 12px;
            }
            
            .message-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.7rem;
            }
            
            .message-input-container {
                padding: 12px;
            }
            
            .send-button, .file-upload-btn, .voice-record-btn {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
            
            /* 小屏幕图片样式优化 */
            .image-message, .video-message, .multiple-files-message {
                max-width: 150px;
            }
            
            .voice-message {
                max-width: 200px;
                padding: 8px;
            }
            
            .voice-player {
                height: 24px;
            }
            
            .image-preview-modal .modal-content {
                max-width: 95%;
                max-height: 95%;
            }
            
            .preview-main-image,
            .preview-main-video {
                max-height: 50vh;
            }
            
            .thumbnail-item {
                width: 50px;
                height: 50px;
            }
            
        }
    </style>
</head>
<body>
    <div class="chat-page-container">
        <div class="chat-container">
            <!-- 引入侧边栏组件 -->
            <?php 
            // 传递当前群组ID给navbar组件
            $currentRoomId = $currentGroupId; // 也传递给聊天列表，让群组在聊天标签页中高亮
            include __DIR__ . '/../components/navbar.php'; 
            ?>
            
            <!-- 聊天区域 -->
            <div class="chat-area">
                <div class="mobile-header">
                    <button class="menu-button" onclick="toggleSidebar()">☰</button>
                    <h2><?php echo htmlspecialchars($group['name']); ?></h2>
                </div>
                
                <div class="chat-header">
                    <div class="room-avatar">
                        <?php 
                        // 显示群组头像
                        if (!empty($group['avatar']) && $group['avatar'] !== 'default_group_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $group['avatar'])) {
                            // 添加时间戳避免缓存问题
                            $timestamp = filemtime(BASE_PATH . '/public/uploads/avatars/' . $group['avatar']);
                            echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($group['avatar']) . '?t=' . $timestamp . '" alt="' . __('avatar_group') . '">';
                        } else {
                            echo strtoupper(substr($group['name'], 0, 1));
                        }
                        ?>
                    </div>
                    <div style="flex: 1;">
                        <div class="chat-title">
                            <span class="group-icon">👥</span>
                            <?php echo htmlspecialchars($group['name']); ?>
                        </div>
                        <div class="chat-status">
                            <?php echo $group['member_count']; ?> <?php echo __('chat_group_members'); ?>
                        </div>
                    </div>
                    
                    <!-- 右侧按钮区域 -->
                    <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                        <!-- 视频通话按钮 -->
                        <div class="call-buttons" style="display: flex !important; gap: 8px !important;">
                            <button class="call-btn voice" id="voiceCallBtn" title="<?php echo __('voice_call'); ?>" onclick="startGroupVoiceCall()" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; border: none !important; background: #2ed573 !important; color: white !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; transition: all 0.3s ease !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important; position: relative !important; z-index: 10 !important;">
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="call-btn video" id="videoCallBtn" title="<?php echo __('video_call'); ?>" onclick="startGroupVideoCall()" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; border: none !important; background: #00bfff !important; color: white !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; transition: all 0.3s ease !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important; position: relative !important; z-index: 10 !important;">
                                <i class="fas fa-video"></i>
                            </button>
                        </div>
                        
                        <!-- 群组设置按钮 - 移到最右边 -->
                        <button class="settings-btn" id="groupSettingsBtn" title="<?php echo __('group_settings', '群组设置'); ?>" onclick="openGroupSettings()" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; border: 2px solid #667eea !important; background: #f8f9fa !important; color: #6c757d !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; transition: all 0.3s ease !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important; position: relative !important; z-index: 10 !important; visibility: visible !important; opacity: 1 !important;">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
                
                <!-- 置顶消息区域 - 固定在聊天头部下方 -->
                <?php if (!empty($messages)): ?>
                    <?php 
                    $pinnedMessages = array_filter($messages, function($msg) { return !empty($msg['is_pinned']); });
                    if (!empty($pinnedMessages)): ?>
                        <div class="pinned-messages-container">
                            <?php foreach ($pinnedMessages as $message): ?>
                            <div class="pinned-message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'own' : ''; ?>" 
                                 data-message-id="<?php echo $message['id']; ?>"
                                 data-message-type="<?php echo $message['message_type']; ?>"
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
                                        echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($messageAvatar) . '" alt="' . __('message_avatar_alt') . '">';
                                    } else {
                                        echo strtoupper(substr($message['username'], 0, 1));
                                    }
                                    ?>
                                </div>
                                <div class="message-content">
                                    <?php if ($message['sender_id'] != $_SESSION['user_id']): ?>
                                        <div class="message-sender"><?php echo htmlspecialchars($message['username']); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // 显示引用消息 - 使用与room.php相同的格式
                                    if (!empty($message['quoted_content'])): ?>
                                        <div class="quoted-message">
                                            <div class="quoted-header">
                                                <span class="quoted-label">引用</span>
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
                                    // 重写语音消息渲染逻辑
                                    if ($message['message_type'] === 'voice' && !empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">[撤回消息]</span>
                                        </div>
                                    <?php 
                                    elseif ($message['message_type'] === 'voice'): 
                                        // 语音消息 - 强制显示音频播放器
                                    ?>
                                        <div class="voice-message">
                                            <audio controls class="voice-player">
                                                <source src="/CHATTING/<?php echo htmlspecialchars($message['file_path']); ?>" type="audio/webm">
                                                您的浏览器不支持音频播放。
                                            </audio>
                                        </div>
                                    <?php 
                                    elseif (!empty($message['file_path']) && !empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">[撤回消息]</span>
                                        </div>
                                    <?php 
                                    elseif (!empty($message['file_path'])): 
                                        // 文件消息处理
                                        $fileName = basename($message['file_path']);
                                        $fileUrl = htmlspecialchars($message['file_path']);
                                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        
                                        if (strpos($message['file_path'], ',') !== false) {
                                            // 多文件消息
                                            $files = explode(',', $message['file_path']);
                                            $fileCount = count($files);
                                    ?>
                                            <div class="file-message multiple-files-message">
                                                <div class="image-collage">
                                                    <?php
                                                    $displayCount = min(4, $fileCount);
                                                    for ($i = 0; $i < $displayCount; $i++):
                                                        $fileUrl = htmlspecialchars($files[$i]);
                                                        $fileExtension = strtolower(pathinfo($files[$i], PATHINFO_EXTENSION));
                                                    ?>
                                                        <div class="collage-item">
                                                            <?php if (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'])): ?>
                                                                <video class="collage-thumbnail" controls>
                                                                    <source src="<?php echo $fileUrl; ?>" type="video/<?php echo $fileExtension; ?>">
                                                                </video>
                                                            <?php else: ?>
                                                                <img src="<?php echo $fileUrl; ?>" alt="<?php echo __('message_image_alt'); ?>" class="collage-thumbnail">
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endfor; ?>
                                                    
                                                    <?php if ($fileCount > 4): ?>
                                                        <div class="more-files">
                                                            <span>+<?php echo $fileCount - 4; ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="file-count"><?php echo $fileCount; ?>个文件</div>
                                            </div>
                                        <?php } else {
                                            // 单文件消息
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
                                                    <video class="message-video" controls>
                                                        <source src="<?php echo $fileUrl; ?>" type="video/<?php echo $fileExtension; ?>">
                                                    </video>
                                                <?php elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                                    <img src="<?php echo $fileUrl; ?>" alt="<?php echo __('message_image_alt'); ?>" class="message-image">
                                                <?php else: ?>
                                                    <!-- 文档文件显示 -->
                                                    <div class="document-message">
                                                        <div class="file-icon">📄</div>
                                                        <div class="file-details">
                                                            <div class="file-name"><?php echo htmlspecialchars($fileName); ?></div>
                                                            <div class="file-size"><?php echo $fileExtension; ?> 文件</div>
                                                        </div>
                                                        <a href="<?php echo $fileUrl; ?>" download class="download-btn">下载</a>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="file-name"><?php echo htmlspecialchars($message['content']); ?></div>
                                            </div>
                                        <?php } ?>
                                    <?php 
                                    elseif (!empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">[撤回消息]</span>
                                        </div>
                                    <?php 
                                    else: 
                                        // 普通文本消息 - 处理@功能
                                        $processedContent = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', htmlspecialchars($message['content']));
                                    ?>
                                        <div class="message-text"><?php echo nl2br($processedContent); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="message-time"><?php echo date('H:i', strtotime($message['created_at'])); ?></div>
                                    
                                    <!-- 消息气泡栏 -->
                                    <?php 
                                    // 为置顶消息传递特殊参数
                                    $isPinnedMessage = true;
                                    include __DIR__ . '/../components/message-bubble-bar.php'; 
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- 普通消息容器 -->
                <div class="messages-container" id="messages-container">
                    <?php if (empty($messages)): ?>
                        <div style="text-align: center; color: #666; padding: 50px 20px;">
                            <div style="font-size: 3rem; margin-bottom: 20px;">💬</div>
                            <h3>开始群组聊天</h3>
                            <p>发送第一条消息开始与群组成员交流</p>
                        </div>
                    <?php else: ?>
                        <!-- 普通消息区域 -->
                        <?php 
                        $normalMessages = array_filter($messages, function($msg) { return empty($msg['is_pinned']); });
                        foreach ($normalMessages as $message): ?>
                            <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'own' : ''; ?>" 
                                 data-message-id="<?php echo $message['id']; ?>"
                                 data-message-type="<?php echo $message['message_type']; ?>"
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
                                        echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($messageAvatar) . '" alt="' . __('message_avatar_alt') . '">';
                                    } else {
                                        echo strtoupper(substr($message['username'], 0, 1));
                                    }
                                    ?>
                                </div>
                                <div class="message-content">
                                    <?php if ($message['sender_id'] != $_SESSION['user_id']): ?>
                                        <div class="message-sender"><?php echo htmlspecialchars($message['username']); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // 显示引用消息 - 使用与room.php相同的格式
                                    if (!empty($message['quoted_content'])): ?>
                                        <div class="quoted-message">
                                            <div class="quoted-header">
                                                <span class="quoted-label">引用</span>
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
                                    // 重写语音消息渲染逻辑
                                    if ($message['message_type'] === 'voice' && !empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">[撤回消息]</span>
                                        </div>
                                    <?php 
                                    elseif ($message['message_type'] === 'voice'): 
                                        // 语音消息 - 强制显示音频播放器
                                    ?>
                                        <div class="voice-message">
                                            <audio controls class="voice-player">
                                                <source src="/CHATTING/<?php echo htmlspecialchars($message['file_path']); ?>" type="audio/webm">
                                                您的浏览器不支持音频播放。
                                            </audio>
                                            <div class="voice-duration">语音消息</div>
                                        </div>
                                    <?php 
                                    elseif (!empty($message['file_path']) && !empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">[撤回消息]</span>
                                        </div>
                                    <?php 
                                    elseif (!empty($message['file_path'])): 
                                        // 其他文件消息 - 使用与room.php相同的逻辑
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
                                                                    <div class="file-type"><?php echo strtoupper($fileExtension); ?> 文件</div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" download class="download-btn">下载</a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!in_array($fileExtension, ['zip', 'rar', '7z', 'tar', 'gz', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'])): ?>
                                                        <div class="file-name"><?php echo htmlspecialchars($message['content']); ?></div>
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
                                                    <div class="files-info"><?php echo $fileCount; ?> 个文件</div>
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
                                                            <div class="file-name"><?php echo htmlspecialchars($fileName ?: '文件'); ?></div>
                                                            <?php if ($fileExtension): ?>
                                                                <div class="file-type"><?php echo strtoupper($fileExtension); ?> 文件</div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" download class="download-btn">下载</a>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="file-name"><?php echo htmlspecialchars($message['content']); ?></div>
                                            </div>
                                        <?php } ?>
                                    <?php 
                                    elseif (!empty($message['is_recalled'])): 
                                    ?>
                                        <div class="recalled-message">
                                            <span class="recall-icon">↩️</span>
                                            <span class="recall-text">[撤回消息]</span>
                                        </div>
                                    <?php 
                                    else: 
                                        // 普通文本消息 - 处理@功能
                                        $processedContent = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', htmlspecialchars($message['content']));
                                    ?>
                                        <div class="message-text"><?php echo nl2br($processedContent); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="message-time"><?php echo date('H:i', strtotime($message['created_at'])); ?></div>
                                    
                                    <!-- 消息气泡栏 -->
                                    <?php include __DIR__ . '/../components/message-bubble-bar.php'; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- 消息输入区域 -->
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
                            <span class="preview-title">文件预览</span>
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
                            <span class="preview-title">语音预览</span>
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
                    
                    <!-- @成员选择弹窗 -->
                    <div class="mention-modal hidden" id="mentionModal">
                        <div class="mention-modal-content">
                            <div class="mention-modal-header">
                                <span class="mention-modal-title">选择@成员</span>
                                <button class="mention-modal-close" onclick="hideMentionModal()">&times;</button>
                            </div>
                            <div class="mention-search-box">
                                <input type="text" id="mentionSearch" placeholder="搜索成员..." class="mention-search-input">
                            </div>
                            <div class="mention-members-list" id="mentionMembersList">
                                <!-- 成员列表将通过JavaScript动态生成 -->
                            </div>
                        </div>
                    </div>
                    
                    <form class="message-form" id="message-form">
                        <div class="input-row">
                            <button type="button" class="file-upload-btn" id="fileUploadBtn" title="<?php echo __('upload_file'); ?>" onclick="showFileUploadModal()">
                                📎
                            </button>
                            <button type="button" class="voice-record-btn" id="voiceRecordBtn" title="<?php echo __('voice_message'); ?>" 
                                    onmousedown="startVoiceRecording(event)" onmouseup="stopVoiceRecording(event)" onmouseleave="stopVoiceRecording(event)">
                                🎤
                            </button>
                            <textarea class="message-input" name="content" id="message-input" placeholder="<?php echo __('chat_type_message'); ?>" rows="1"></textarea>
                            <button type="submit" class="send-button" id="sendButton">
                                ➤
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 隐藏的文件输入 -->
    <input type="file" id="fileInput" style="display: none;" onchange="handleFileSelect(this)" multiple>
    
    <!-- 修改消息模态框 -->
    <div class="edit-message-modal" id="editMessageModal">
        <div class="edit-modal-content">
            <div class="edit-modal-header">
                <div class="edit-modal-title">修改消息</div>
                <button class="edit-modal-close" onclick="hideEditModal()">&times;</button>
            </div>
            <textarea class="edit-textarea" id="editTextarea" placeholder="请输入修改后的消息内容..."></textarea>
            <div class="edit-modal-actions">
                <button class="edit-btn edit-btn-cancel" onclick="hideEditModal()">取消</button>
                <button class="edit-btn edit-btn-save" id="editSaveBtn" onclick="saveEditMessage()">保存</button>
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
    
    <!-- 图片预览模态框 -->
    <div class="image-preview-modal hidden" id="imagePreviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>图片预览</h3>
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
    
    <!-- 引入聊天通用功能 -->
    <script src="/CHATTING/public/js/chat-common.js?v=2025012723"></script>
    
    <!-- 提供好友和群组数据给JavaScript -->
    <script>
        // 将PHP数据传递给JavaScript
        window.friendsData = <?php echo json_encode($friends); ?>;
        window.groupsData = <?php echo json_encode($groups); ?>;
        
        // 当前聊天信息
        window.currentChat = {
            type: 'group',
            id: <?php echo $group['id']; ?>,
            name: '<?php echo addslashes($group['name']); ?>'
        };
        
        // 调试信息
        console.log('Page loaded - friendsData:', window.friendsData);
        console.log('Page loaded - groupsData:', window.groupsData);
        console.log('Page loaded - currentChat:', window.currentChat);
    </script>
    
    <script>
        // 消息气泡栏功能现在在chat-common.js中统一处理
        
        // 确保函数存在，如果不存在则定义空函数
        if (typeof showMessageBubble === 'undefined') {
            window.showMessageBubble = function(messageElement) {
                console.log('showMessageBubble fallback function called');
                clearTimeout(messageHoverTimeout);
                messageHoverTimeout = setTimeout(() => {
                    const bubbleBar = messageElement.querySelector('.message-bubble-bar');
                    if (bubbleBar) {
                        bubbleBar.classList.add('show');
                    }
                }, 500);
            };
        }
        
        if (typeof hideMessageBubble === 'undefined') {
            window.hideMessageBubble = function(messageElement) {
                console.log('hideMessageBubble fallback function called');
                clearTimeout(messageHoverTimeout);
                setTimeout(() => {
                    const bubbleBar = messageElement.querySelector('.message-bubble-bar');
                    if (bubbleBar && !bubbleBar.matches(':hover')) {
                        bubbleBar.classList.remove('show');
                    }
                }, 1000);
            };
        }
        
        if (typeof preventContextMenu === 'undefined') {
            window.preventContextMenu = function(event) {
                event.preventDefault();
                return false;
            };
        }
        
        if (typeof handleMessageTouchStart === 'undefined') {
            window.handleMessageTouchStart = function(event, messageElement) {
                // 空函数，避免错误
            };
        }
        
        if (typeof handleMessageTouchEnd === 'undefined') {
            window.handleMessageTouchEnd = function(event, messageElement) {
                // 空函数，避免错误
            };
        }
        
        if (typeof handleMessageTouchMove === 'undefined') {
            window.handleMessageTouchMove = function(event, messageElement) {
                // 空函数，避免错误
            };
        }
    </script>
    
    <script>
        // 群组聊天页面JavaScript
        const roomId = <?php echo $currentGroupId; ?>;
        
        // 引用消息相关变量（确保全局变量存在）
        window.quotedMessageId = window.quotedMessageId || null;
        
        // @功能相关变量
        let mentionModal = null;
        let mentionSearchInput = null;
        let mentionMembersList = null;
        let mentionMembers = [];
        let currentMentionStart = -1;
        let currentMentionEnd = -1;
        let selectedMentionIndex = -1;
        
        // 引用消息功能
        function quoteMessage(messageId) {
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
            const senderElement = messageElement.querySelector('.message-sender');
            if (senderElement) {
                messageSender = senderElement.textContent || senderElement.innerText || '';
            } else {
                // 从data属性获取
                messageSender = messageElement.getAttribute('data-sender-name') || '未知用户';
            }
            
            // 判断消息类型
            const voiceMessage = messageElement.querySelector('.voice-message');
            const fileMessage = messageElement.querySelector('.file-message');
            const recalledMessage = messageElement.querySelector('.recalled-message');
            
            if (recalledMessage) {
                messageType = '[撤回消息]';
                messageContent = '撤回消息';
            } else if (voiceMessage) {
                messageType = '[语音消息]';
                messageContent = '语音消息';
            } else if (fileMessage) {
                const fileInfo = messageElement.querySelector('.files-info');
                if (fileInfo) {
                    messageType = '[文件]';
                    messageContent = fileInfo.textContent || '文件消息';
                } else {
                    messageType = '[文件]';
                    messageContent = '文件消息';
                }
            } else {
                messageType = '';
            }
            
            // 设置引用的消息ID
            window.quotedMessageId = messageId;
            
            // 显示引用指示器
            showQuoteIndicator(messageSender, messageContent);
            
            // 聚焦到输入框
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.focus();
            }
            
            showNotification('已引用消息', 'success');
        }
        
        // 清除引用
        function clearQuote() {
            window.quotedMessageId = null;
            const indicator = document.getElementById('quoteIndicator');
            if (indicator) {
                indicator.remove();
            }
            showNotification('已取消引用', 'info');
        }
        
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
                        fetch(`/CHATTING/chat/getQuotedMessageId?message_id=${messageId}`)
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
        
        // 动态添加新消息到聊天列表
        function addNewMessageToChat(messageData) {
            console.log('添加新消息到聊天:', messageData);
            
            const messagesContainer = document.getElementById('messages-container');
            if (!messagesContainer) {
                console.error('找不到消息容器');
                return;
            }
            
            // 创建新消息元素
            const messageElement = createMessageElement(messageData);
            if (messageElement) {
                messagesContainer.appendChild(messageElement);
                
                // 为新消息添加事件监听器
                addMessageEventListeners(messageElement);
                
                // 添加引用点击事件
                addQuoteClickHandlers();
            }
        }
        
        // 创建消息元素
        function createMessageElement(messageData) {
            const isOwnMessage = messageData.sender_id == <?php echo $_SESSION['user_id']; ?>;
            const messageTime = new Date(messageData.created_at).toLocaleTimeString('zh-CN', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            let messageContent = '';
            
            // 根据消息类型生成内容
            if (messageData.message_type === 'voice') {
                messageContent = `
                    <div class="voice-message">
                        <audio controls class="voice-player">
                            <source src="/CHATTING/${messageData.file_path}" type="audio/webm">
                            您的浏览器不支持音频播放。
                        </audio>
                        <div class="voice-duration">语音消息</div>
                    </div>
                `;
            } else if (messageData.file_path) {
                // 文件消息处理
                const fileData = JSON.parse(messageData.file_path);
                if (fileData && fileData.urls && fileData.urls.length > 0) {
                    const fileUrl = fileData.urls[0];
                    const fileName = fileData.names ? fileData.names[0] : '';
                    const fileExtension = fileName ? fileName.split('.').pop().toLowerCase() : '';
                    
                    if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(fileExtension)) {
                        messageContent = `
                            <div class="file-message video-message">
                                <video controls class="message-video">
                                    <source src="${fileUrl}" type="video/${fileExtension}">
                                </video>
                                <div class="file-name">${messageData.content}</div>
                            </div>
                        `;
                    } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                        messageContent = `
                            <div class="file-message image-message">
                                <img src="${fileUrl}" alt="图片" class="message-image">
                                <div class="file-name">${messageData.content}</div>
                            </div>
                        `;
                    } else {
                        messageContent = `
                            <div class="file-message document-message">
                                <div class="document-message">
                                    <div class="file-icon">📄</div>
                                    <div class="file-details">
                                        <div class="file-name">${fileName}</div>
                                        <div class="file-type">${fileExtension.toUpperCase()} 文件</div>
                                    </div>
                                    <a href="${fileUrl}" download class="download-btn">下载</a>
                                </div>
                                <div class="file-name">${messageData.content}</div>
                            </div>
                        `;
                    }
                }
            } else {
                // 文本消息 - 处理@功能
                const processedContent = processMentionMessage(messageData.content);
                messageContent = `<div class="message-text">${processedContent.replace(/\n/g, '<br>')}</div>`;
            }
            
            // 处理引用消息 - 使用与room.php相同的格式
            let quotedContent = '';
            if (messageData.quoted_content) {
                const quotedSenderName = messageData.quoted_username || '未知用户';
                const quotedText = messageData.quoted_content;
                const quotedType = messageData.quoted_type || 'text';
                
                quotedContent = `
                    <div class="quoted-message">
                        <div class="quoted-header">
                            <span class="quoted-label">引用</span>
                            <span class="quoted-sender">${quotedSenderName}</span>
                        </div>
                        <div class="quoted-content">
                            ${quotedType === 'text' ? 
                                `<div class="quoted-text">${quotedText}</div>` : 
                                `<div class="quoted-file">📎 ${quotedText}</div>`
                            }
                        </div>
                    </div>
                `;
            }
            
            const messageElement = document.createElement('div');
            messageElement.className = `message ${isOwnMessage ? 'own' : ''}`;
            messageElement.setAttribute('data-message-id', messageData.id);
            messageElement.setAttribute('data-message-type', messageData.message_type);
            messageElement.setAttribute('data-sender-id', messageData.sender_id);
            messageElement.setAttribute('data-sender-name', messageData.username);
            messageElement.setAttribute('data-created-at', messageData.created_at);
            messageElement.setAttribute('data-message-hover', 'true');
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    ${messageData.avatar && messageData.avatar !== 'default_avatar.png' ? 
                        `<img src="/CHATTING/public/uploads/avatars/${messageData.avatar}" alt="头像">` : 
                        messageData.username.charAt(0).toUpperCase()}
                </div>
                <div class="message-content">
                    ${!isOwnMessage ? `<div class="message-sender">${messageData.username}</div>` : ''}
                    ${quotedContent}
                    ${messageContent}
                    <div class="message-time">${messageTime}</div>
                </div>
            `;
            
            // 添加消息气泡栏
            const bubbleBar = document.createElement('div');
            bubbleBar.className = 'message-bubble-bar';
            bubbleBar.id = `bubble-${messageData.id}`;
            bubbleBar.setAttribute('onmouseenter', 'keepBubbleVisible(this)');
            bubbleBar.setAttribute('onmouseleave', 'hideBubbleOnLeave(this)');
            
            // 生成气泡栏内容
            let bubbleContent = '';
            
            if (isOwnMessage) {
                // 撤回/删除按钮
                bubbleContent += `
                    <button class="bubble-btn" 
                            onclick="${messageData.message_type === 'text' ? 'recallMessage' : 'deleteMessage'}(${messageData.id})"
                            title="${messageData.message_type === 'text' ? '撤回消息' : '删除消息'}">
                        ${messageData.message_type === 'text' ? '↩️' : '🗑️'}
                        <div class="bubble-tooltip">${messageData.message_type === 'text' ? '撤回' : '删除'}</div>
                    </button>
                `;
                
                // 修改按钮（仅文本消息）
                if (messageData.message_type === 'text' && !messageData.file_path) {
                    bubbleContent += `
                        <button class="bubble-btn" 
                                onclick="editMessage(${messageData.id}, '${messageData.content.replace(/'/g, "\\'")}')"
                                title="修改消息">
                            ✏️
                            <div class="bubble-tooltip">修改</div>
                        </button>
                    `;
                }
            }
            
            // 收藏按钮
            bubbleContent += `
                <button class="bubble-btn" 
                        onclick="toggleFavorite(${messageData.id})"
                        title="收藏消息">
                    ⭐
                    <div class="bubble-tooltip">收藏</div>
                </button>
            `;
            
            // 置顶按钮
            bubbleContent += `
                <button class="bubble-btn" 
                        onclick="togglePin(${messageData.id})"
                        title="置顶消息">
                    📌
                    <div class="bubble-tooltip">置顶</div>
                </button>
            `;
            
            // 引用按钮
            bubbleContent += `
                <button class="bubble-btn" 
                        onclick="quoteMessage(${messageData.id})"
                        title="引用消息">
                    💬
                    <div class="bubble-tooltip">引用</div>
                </button>
            `;
            
            // 转发按钮
            bubbleContent += `
                <button class="bubble-btn" 
                        onclick="forwardMessage(${messageData.id})"
                        title="转发消息">
                    📤
                    <div class="bubble-tooltip">转发</div>
                </button>
            `;
            
            bubbleBar.innerHTML = bubbleContent;
            messageElement.appendChild(bubbleBar);
            
            return messageElement;
        }
        
        // 为消息元素添加事件监听器
        function addMessageEventListeners(messageElement) {
            // 添加鼠标悬停事件
            messageElement.addEventListener('mouseenter', function() {
                showMessageBubble(this);
            });
            
            messageElement.addEventListener('mouseleave', function() {
                hideMessageBubble(this);
            });
            
            // 添加右键菜单阻止
            messageElement.addEventListener('contextmenu', function(e) {
                preventContextMenu(e);
            });
            
            // 添加触摸事件
            messageElement.addEventListener('touchstart', function(e) {
                handleMessageTouchStart(e, this);
            });
            
            messageElement.addEventListener('touchend', function(e) {
                handleMessageTouchEnd(e, this);
            });
            
            messageElement.addEventListener('touchmove', function(e) {
                handleMessageTouchMove(e, this);
            });
        }
        
        // 确保toggleSidebar函数可用
        function toggleSidebar() {
            console.log('toggleSidebar called');
            
            // 优先使用navbar.js中的函数
            if (typeof window.toggleSidebar === 'function' && window.toggleSidebar !== toggleSidebar) {
                console.log('Using navbar toggleSidebar function');
                window.toggleSidebar();
                return;
            }
            
            // 备用方案：直接操作DOM
            console.log('Using fallback toggleSidebar function');
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            
            if (sidebar) {
                sidebar.classList.toggle('open');
                console.log('Sidebar toggled, open:', sidebar.classList.contains('open'));
                
                if (toggleBtn) {
                    if (sidebar.classList.contains('open')) {
                        toggleBtn.style.display = 'none';
                    } else {
                        toggleBtn.style.display = 'flex';
                    }
                }
            } else {
                console.error('Sidebar element not found');
            }
        }
        
        // @功能相关函数
        function initMentionFeature() {
            mentionModal = document.getElementById('mentionModal');
            mentionSearchInput = document.getElementById('mentionSearch');
            mentionMembersList = document.getElementById('mentionMembersList');
            
            // 获取群组成员数据
            loadGroupMembers();
            
            // 绑定输入框事件
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.addEventListener('input', handleMentionInput);
                messageInput.addEventListener('keydown', handleMentionKeydown);
            }
            
            // 绑定搜索框事件
            if (mentionSearchInput) {
                mentionSearchInput.addEventListener('input', filterMentionMembers);
            }
        }
        
        function loadGroupMembers() {
            // 从PHP传递的成员数据中获取
            const members = <?php echo json_encode($members); ?>;
            mentionMembers = members.map(member => ({
                id: member.id,
                username: member.username,
                avatar: member.avatar || 'default_avatar.png',
                status: member.status || 'offline'
            }));
            
            console.log('加载群组成员:', mentionMembers);
        }
        
        function handleMentionInput(event) {
            const input = event.target;
            const value = input.value;
            const cursorPos = input.selectionStart;
            
            // 检查是否输入了@
            const atIndex = value.lastIndexOf('@', cursorPos - 1);
            
            if (atIndex !== -1) {
                // 检查@后面是否有空格，如果有空格则关闭弹窗
                const afterAt = value.substring(atIndex + 1, cursorPos);
                if (afterAt.includes(' ')) {
                    hideMentionModal();
                    return;
                }
                
                // 显示@弹窗
                currentMentionStart = atIndex;
                currentMentionEnd = cursorPos;
                showMentionModal();
                
                // 根据@后的内容过滤成员
                const searchTerm = afterAt.toLowerCase();
                filterMentionMembersByTerm(searchTerm);
            } else {
                hideMentionModal();
            }
        }
        
        function handleMentionKeydown(event) {
            if (!mentionModal || mentionModal.classList.contains('hidden')) {
                return;
            }
            
            const key = event.key;
            
            if (key === 'ArrowDown') {
                event.preventDefault();
                selectNextMentionMember();
            } else if (key === 'ArrowUp') {
                event.preventDefault();
                selectPreviousMentionMember();
            } else if (key === 'Enter') {
                event.preventDefault();
                selectCurrentMentionMember();
            } else if (key === 'Escape') {
                event.preventDefault();
                hideMentionModal();
            }
        }
        
        function showMentionModal() {
            if (mentionModal) {
                mentionModal.classList.remove('hidden');
                renderMentionMembers();
                selectedMentionIndex = 0;
                updateMentionSelection();
            }
        }
        
        function hideMentionModal() {
            if (mentionModal) {
                mentionModal.classList.add('hidden');
                selectedMentionIndex = -1;
            }
        }
        
        function renderMentionMembers() {
            if (!mentionMembersList) return;
            
            mentionMembersList.innerHTML = '';
            
            mentionMembers.forEach((member, index) => {
                const memberItem = document.createElement('div');
                memberItem.className = 'mention-member-item';
                memberItem.dataset.index = index;
                
                memberItem.innerHTML = `
                    <div class="mention-member-avatar">
                        ${member.avatar && member.avatar !== 'default_avatar.png' ? 
                            `<img src="/CHATTING/public/uploads/avatars/${member.avatar}" alt="头像">` : 
                            member.username.charAt(0).toUpperCase()}
                    </div>
                    <div class="mention-member-info">
                        <div class="mention-member-name">${member.username}</div>
                        <div class="mention-member-status ${member.status}">${member.status === 'online' ? '在线' : '离线'}</div>
                    </div>
                `;
                
                memberItem.addEventListener('click', () => selectMentionMember(index));
                mentionMembersList.appendChild(memberItem);
            });
        }
        
        function filterMentionMembers() {
            const searchTerm = mentionSearchInput.value.toLowerCase();
            filterMentionMembersByTerm(searchTerm);
        }
        
        function filterMentionMembersByTerm(searchTerm) {
            const memberItems = mentionMembersList.querySelectorAll('.mention-member-item');
            
            memberItems.forEach((item, index) => {
                const member = mentionMembers[index];
                const matches = member.username.toLowerCase().includes(searchTerm);
                
                if (matches) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // 重置选择
            selectedMentionIndex = 0;
            updateMentionSelection();
        }
        
        function selectNextMentionMember() {
            const visibleItems = Array.from(mentionMembersList.querySelectorAll('.mention-member-item:not([style*="display: none"])'));
            if (visibleItems.length === 0) return;
            
            selectedMentionIndex = (selectedMentionIndex + 1) % visibleItems.length;
            updateMentionSelection();
        }
        
        function selectPreviousMentionMember() {
            const visibleItems = Array.from(mentionMembersList.querySelectorAll('.mention-member-item:not([style*="display: none"])'));
            if (visibleItems.length === 0) return;
            
            selectedMentionIndex = selectedMentionIndex <= 0 ? visibleItems.length - 1 : selectedMentionIndex - 1;
            updateMentionSelection();
        }
        
        function updateMentionSelection() {
            const memberItems = mentionMembersList.querySelectorAll('.mention-member-item');
            memberItems.forEach((item, index) => {
                if (index === selectedMentionIndex) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            });
        }
        
        function selectCurrentMentionMember() {
            const visibleItems = Array.from(mentionMembersList.querySelectorAll('.mention-member-item:not([style*="display: none"])'));
            if (visibleItems.length === 0 || selectedMentionIndex < 0) return;
            
            const selectedItem = visibleItems[selectedMentionIndex];
            const memberIndex = parseInt(selectedItem.dataset.index);
            selectMentionMember(memberIndex);
        }
        
        function selectMentionMember(memberIndex) {
            const member = mentionMembers[memberIndex];
            if (!member) return;
            
            const input = document.getElementById('message-input');
            const value = input.value;
            
            // 替换@后的内容为选中的成员名
            const beforeAt = value.substring(0, currentMentionStart);
            const afterMention = value.substring(currentMentionEnd);
            const newValue = beforeAt + '@' + member.username + ' ' + afterMention;
            
            input.value = newValue;
            
            // 设置光标位置
            const newCursorPos = beforeAt.length + member.username.length + 2; // +2 for @ and space
            input.setSelectionRange(newCursorPos, newCursorPos);
            
            // 隐藏弹窗
            hideMentionModal();
            
            // 聚焦到输入框
            input.focus();
        }
        
        function processMentionMessage(content) {
            // 将@用户名转换为HTML格式
            return content.replace(/@(\w+)/g, '<span class="mention">@$1</span>');
        }
        
        // 将引用功能函数暴露到全局作用域
        window.quoteMessage = quoteMessage;
        window.clearQuote = clearQuote;
        window.scrollToQuotedMessage = scrollToQuotedMessage;
        window.addQuoteClickHandlers = addQuoteClickHandlers;
        
        // 将@功能函数暴露到全局作用域
        window.showMentionModal = showMentionModal;
        window.hideMentionModal = hideMentionModal;
        
        // 页面加载完成后确保函数可用
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Group chat loaded, toggleSidebar available:', typeof toggleSidebar);
            
            // 初始化@功能
            initMentionFeature();
            
            // 为引用消息添加点击事件
            addQuoteClickHandlers();
            
            // 如果navbar.js中的函数还没有加载，等待一下再尝试
            setTimeout(function() {
                if (typeof window.toggleSidebar === 'function' && window.toggleSidebar !== toggleSidebar) {
                    console.log('Navbar toggleSidebar function is now available');
                }
            }, 100);
            
            // 添加点击外部区域关闭文件类型选择菜单的功能
            document.addEventListener('click', function(event) {
                const fileTypeCards = document.getElementById('fileTypeCards');
                const fileUploadBtn = document.getElementById('fileUploadBtn');
                
                if (fileTypeCards && !fileTypeCards.classList.contains('hidden')) {
                    // 如果点击的不是文件类型选择菜单和文件上传按钮，则关闭菜单
                    if (!fileTypeCards.contains(event.target) && !fileUploadBtn.contains(event.target)) {
                        fileTypeCards.classList.add('hidden');
                    }
                }
            });
        });
        
        // 发送消息
        document.getElementById('message-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = document.getElementById('message-input').value.trim();
            const hasFile = selectedFiles.length > 0;
            const hasVoice = recordedAudioBlob !== null;
            
            // 如果没有内容、文件或语音，不发送
            if (!content && !hasFile && !hasVoice) return;
            
            const sendButton = document.getElementById('sendButton');
            const originalText = sendButton.textContent;
            sendButton.textContent = '<?php echo __('sending', '发送中...'); ?>';
            sendButton.disabled = true;
            
            // 如果有语音，发送语音消息；如果有文件，发送文件；否则发送文本消息
            if (hasVoice) {
                sendGroupVoiceMessage(recordedAudioBlob, roomId);
            } else if (hasFile) {
                sendGroupFileWithMessage(content, roomId);
            } else {
                sendGroupTextMessage(content, roomId);
            }
            
            // 重置按钮状态
            setTimeout(() => {
                sendButton.textContent = originalText;
                sendButton.disabled = false;
            }, 1000);
        });
        
        // 群组聊天专用消息发送函数（发送成功后刷新页面）
        function sendGroupTextMessage(content, roomId) {
            console.log('Sending group text message:', content, 'to room:', roomId);
            
            // 构建请求体
            let body = `room_id=${roomId}&content=${encodeURIComponent(content)}`;
            if (window.quotedMessageId) {
                body += `&quoted_message_id=${window.quotedMessageId}`;
            }
            
            fetch('/CHATTING/chat/sendMessage', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: body
            })
            .then(response => {
                console.log('Response status:', response.status);
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
                console.log('Response data:', data);
                if (data.success) {
                    // 发送成功后清空输入框并刷新页面
                    document.getElementById('message-input').value = '';
                    
                    // 清除引用状态
                    if (window.quotedMessageId) {
                        clearQuote();
                    }
                    
                    showNotification('消息发送成功', 'success');
                    
                    // 动态添加新消息到消息列表
                    addNewMessageToChat(data.message);
                    
                    // 滚动到底部
                    setTimeout(forceScrollToBottom, 100);
                } else {
                    console.error('消息发送失败:', data.message);
                    showNotification('发送失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('发送消息失败:', error);
                showNotification('发送失败: ' + error.message, 'error');
            });
        }
        
        function sendGroupFileWithMessage(content, roomId) {
            const formData = new FormData();
            
            // 添加所有文件
            selectedFiles.forEach((file, index) => {
                formData.append(`files[]`, file);
            });
            
            formData.append('room_id', roomId);
            formData.append('file_type', currentFileType);
            formData.append('file_count', selectedFiles.length);
            
            fetch('/CHATTING/chat/sendMultipleFiles', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: formData
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
                    showNotification('文件发送成功', 'success');
                    
                    // 清空文件选择
                    selectedFiles = [];
                    document.getElementById('message-input').value = '';
                    
                    // 动态添加新消息到聊天列表
                    if (data.message) {
                        addNewMessageToChat(data.message);
                    }
                    
                    // 滚动到底部
                    setTimeout(forceScrollToBottom, 100);
                } else {
                    showNotification('文件发送失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('文件发送失败:', error);
                showNotification('文件发送失败: ' + error.message, 'error');
            });
        }
        
        function sendGroupVoiceMessage(audioBlob, roomId) {
            const formData = new FormData();
            
            // 创建文件对象
            const audioFile = new File([audioBlob], 'voice_message.webm', { type: 'audio/webm' });
            formData.append('voice_file', audioFile);
            formData.append('room_id', roomId);
            
            // 发送语音消息
            fetch('/CHATTING/chat/sendVoiceMessage', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: formData
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
                    showNotification('语音消息发送成功', 'success');
                    
                    // 移除预览
                    recordedAudioBlob = null;
                    document.getElementById('message-input').value = '';
                    
                    // 动态添加新消息到聊天列表
                    if (data.message) {
                        addNewMessageToChat(data.message);
                    }
                    
                    // 滚动到底部
                    setTimeout(forceScrollToBottom, 100);
                } else {
                    showNotification('语音消息发送失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('语音消息发送失败:', error);
                showNotification('语音消息发送失败: ' + error.message, 'error');
            });
        }
        
        // 文件类型选择功能已在chat-common.js中定义，这里不需要重复定义
        
        // 文件处理函数已在chat-common.js中定义，这里不需要重复定义
        
        // 语音处理函数已在chat-common.js中定义，这里不需要重复定义
        
        
        // 输入框自动调整高度
        const textarea = document.getElementById('message-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        
        // 回车发送消息（Shift+Enter换行）
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('message-form').dispatchEvent(new Event('submit'));
            }
        });
        
        // 文件上传功能 - 已通过HTML onclick="showFileTypeCards()"处理
        // document.getElementById('fileUploadBtn').addEventListener('click', function() {
        //     document.getElementById('fileInput').click();
        // });
        
        // 文件输入事件处理已在chat-common.js中定义，这里不需要重复定义
        
        // 语音录制功能已在chat-common.js中定义，这里不需要重复定义
        
        // 通知功能已在chat-common.js中定义，这里不需要重复定义
        
        
        // 消息气泡栏相关变量
        let currentEditingMessageId = null;
        let currentForwardingMessageId = null;
        let selectedRecipients = [];
        
        // 这些函数已在message-bubble-bar.php中定义，无需重复定义
        
        // 开始群组语音通话
        async function startGroupVoiceCall() {
            console.log('群组语音通话按钮被点击');
            
            try {
                // 生成通话ID
                const callId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const roomId = '<?php echo $groupId; ?>';
                const callType = 'voice';
                const fromUserId = '<?php echo $_SESSION['user_id']; ?>';
                const fromUsername = '<?php echo htmlspecialchars($_SESSION['username']); ?>';
                
                console.log('群组通话参数:', { callId, roomId, callType, fromUserId, fromUsername });
                
                // 发送群组通话邀请
                const response = await fetch('/CHATTING/call/sendGroupCallInvitation', {
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
                        targetUserId: null // 群组通话不需要指定特定用户
                    })
                });
                
                const result = await response.json();
                console.log('群组通话邀请响应:', result);
                
                if (result.success) {
                    console.log('群组通话邀请发送成功:', result);
                    showNotification('正在呼叫群组成员...', 'success');
                    
                    // 跳转到视频通话页面
                    const videoCallUrl = `/CHATTING/chat/videoCall?roomId=${roomId}&callType=${callType}&fromUserId=${fromUserId}&fromUsername=${encodeURIComponent(fromUsername)}&isIncoming=false&callId=${callId}&isGroup=true`;
                    window.location.href = videoCallUrl;
                } else {
                    throw new Error(result.message || '发送群组通话邀请失败');
                }
                
            } catch (error) {
                console.error('发起群组语音通话失败:', error);
                showNotification('发起群组语音通话失败: ' + error.message, 'error');
            }
        }
        
        // 开始群组视频通话
        async function startGroupVideoCall() {
            console.log('群组视频通话按钮被点击');
            
            try {
                // 生成通话ID
                const callId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const roomId = '<?php echo $groupId; ?>';
                const callType = 'video';
                const fromUserId = '<?php echo $_SESSION['user_id']; ?>';
                const fromUsername = '<?php echo htmlspecialchars($_SESSION['username']); ?>';
                
                console.log('群组通话参数:', { callId, roomId, callType, fromUserId, fromUsername });
                
                // 发送群组通话邀请
                const response = await fetch('/CHATTING/call/sendGroupCallInvitation', {
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
                        targetUserId: null // 群组通话不需要指定特定用户
                    })
                });
                
                const result = await response.json();
                console.log('群组通话邀请响应:', result);
                
                if (result.success) {
                    console.log('群组通话邀请发送成功:', result);
                    showNotification('正在呼叫群组成员...', 'success');
                    
                    // 跳转到视频通话页面
                    const videoCallUrl = `/CHATTING/chat/videoCall?roomId=${roomId}&callType=${callType}&fromUserId=${fromUserId}&fromUsername=${encodeURIComponent(fromUsername)}&isIncoming=false&callId=${callId}&isGroup=true`;
                    window.location.href = videoCallUrl;
                } else {
                    throw new Error(result.message || '发送群组通话邀请失败');
                }
                
            } catch (error) {
                console.error('发起群组视频通话失败:', error);
                showNotification('发起群组视频通话失败: ' + error.message, 'error');
            }
        }
        
        // 打开群组设置页面
        function openGroupSettings() {
            const groupId = <?php echo $group['id']; ?>;
            window.location.href = `/CHATTING/chat/groupSettings?id=${groupId}`;
        }
        
        // 调试函数 - 检查按钮是否存在
        function debugSettingsButton() {
            const button = document.getElementById('groupSettingsBtn');
            console.log('Settings button element:', button);
            if (button) {
                console.log('Button found!');
                console.log('Button display:', window.getComputedStyle(button).display);
                console.log('Button visibility:', window.getComputedStyle(button).visibility);
                console.log('Button opacity:', window.getComputedStyle(button).opacity);
                console.log('Button position:', window.getComputedStyle(button).position);
                console.log('Button z-index:', window.getComputedStyle(button).zIndex);
            } else {
                console.log('Settings button NOT found!');
            }
        }
        
        // 页面加载后检查按钮
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(debugSettingsButton, 1000);
        });
        
        // 这些函数已在message-bubble-bar.php中定义，无需重复定义
        
        
        
        
        
        // 增强的滚动到底部函数
        function forceScrollToBottom() {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                // 使用多种方法确保滚动到底部
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // 使用 requestAnimationFrame 确保在下一帧执行
                requestAnimationFrame(() => {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
                
                // 再次确保滚动到底部
                setTimeout(() => {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }, 50);
            }
        }
        
        // 页面加载完成后滚动到底部
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化聊天通用功能
            if (typeof initChatCommon === 'function') {
                initChatCommon();
            }
            
            // 确保页面完全加载后再滚动到底部
            setTimeout(function() {
                forceScrollToBottom();
            }, 200);
        });
        
        // 页面完全加载后再次确保滚动到底部
        window.addEventListener('load', function() {
            setTimeout(forceScrollToBottom, 300);
        });
        
        // 监听图片和视频加载完成事件，确保内容加载后滚动到底部
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img, video');
            images.forEach(media => {
                media.addEventListener('load', function() {
                    setTimeout(forceScrollToBottom, 100);
                });
            });
        });
    </script>
</body>
</html>
