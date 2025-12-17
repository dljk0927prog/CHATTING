<?php
// 格式化文件大小函数已移至 Language.php 中
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
    <title><?php echo str_replace(['{title}', '{forum_name}'], [htmlspecialchars($post['title']), htmlspecialchars($post['forum_name'])], __('post_view_title')); ?></title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
    <style>
        .post-container {
            height: 100%;
            padding: 20px;
            overflow-y: auto;
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }
        
        /* 响应式设计 */
        @media (min-width: 1200px) {
            .post-container {
                max-width: 1400px;
                padding: 30px;
            }
        }
        
        @media (min-width: 992px) and (max-width: 1199px) {
            .post-container {
                max-width: 100%;
                padding: 25px;
            }
        }
        
        @media (max-width: 991px) {
            .post-container {
                padding: 15px;
            }
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            .post-container {
                padding: 10px;
            }
            
            .post-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .post-title {
                font-size: 1.3rem;
                line-height: 1.4;
            }
            
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .post-author {
                font-size: 0.9rem;
            }
            
            .post-date {
                font-size: 0.8rem;
            }
            
            .post-content {
                padding: 20px;
                font-size: 0.95rem;
                line-height: 1.6;
            }
            
            .post-actions {
                padding: 20px;
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 14px 20px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-group .btn {
                width: 100%;
            }
            
            .file-list {
                gap: 10px;
            }
            
            .file-item {
                padding: 12px;
            }
            
            .file-icon {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }
            
            .file-info h4 {
                font-size: 0.9rem;
            }
            
            .file-info p {
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .post-container {
                padding: 5px;
            }
            
            .post-header {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .post-title {
                font-size: 1.2rem;
            }
            
            .post-author {
                font-size: 0.85rem;
            }
            
            .post-date {
                font-size: 0.75rem;
            }
            
            .post-content {
                padding: 15px;
                font-size: 0.9rem;
            }
            
            .post-actions {
                padding: 15px;
            }
            
            .btn {
                min-height: 46px;
                font-size: 16px;
                padding: 12px 16px;
            }
            
            .file-item {
                padding: 10px;
            }
            
            .file-icon {
                width: 25px;
                height: 25px;
                font-size: 0.9rem;
            }
            
            .file-info h4 {
                font-size: 0.85rem;
            }
            
            .file-info p {
                font-size: 0.7rem;
            }
        }
        }
        
        @media (max-width: 768px) {
            .post-container {
                padding: 10px;
            }
        }
        
        .post-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .post-header {
                padding: 20px;
                margin-bottom: 15px;
                border-radius: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .post-header {
                padding: 15px;
                margin-bottom: 10px;
            }
        }
        
        .post-title-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 20px;
        }
        
        .post-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            line-height: 1.3;
            word-wrap: break-word;
            flex: 1;
            margin: 0;
        }
        
        .post-management {
            position: relative;
            flex-shrink: 0;
        }
        
        .management-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #495057;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .management-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            transform: translateY(-1px);
        }
        
        .management-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 160px;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }
        
        .management-dropdown.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #495057;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
        }
        
        .dropdown-item.danger {
            color: #dc3545;
        }
        
        .dropdown-item.danger:hover {
            background: #f8d7da;
        }
        
        @media (max-width: 768px) {
            .post-title-container {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }
            
            .post-title {
                font-size: 1.5rem;
                margin-bottom: 0;
            }
            
            .post-management {
                align-self: flex-end;
            }
            
            .management-btn {
                padding: 10px 15px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .post-title-container {
                gap: 12px;
            }
            
            .post-title {
                font-size: 1.3rem;
            }
            
            .management-btn {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
            
            .management-dropdown {
                min-width: 140px;
            }
        }
        
        .post-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .post-meta {
                gap: 10px;
                padding-bottom: 12px;
                margin-bottom: 12px;
            }
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .author-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .author-info {
            display: flex;
            flex-direction: column;
        }
        
        .author-name {
            font-weight: 600;
            color: #333;
        }
        
        .post-time {
            font-size: 0.85rem;
            color: #666;
        }
        
        .post-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.85rem;
            color: #666;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .post-stats {
                gap: 10px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .post-stats {
                gap: 8px;
                font-size: 0.75rem;
            }
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .post-content a {
            color: #667eea;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: all 0.3s ease;
        }
        
        .post-content a:hover {
            color: #764ba2;
            border-bottom-color: #764ba2;
        }
        
        @media (max-width: 768px) {
            .post-content {
                font-size: 1rem;
                line-height: 1.6;
            }
        }
        
        @media (max-width: 480px) {
            .post-content {
                font-size: 0.95rem;
                line-height: 1.5;
            }
        }
        
        /* 媒体文件显示样式 */
        .post-media {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .post-media h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .media-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .media-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 8px;
                margin-bottom: 12px;
            }
        }
        
        .media-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .media-thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }
        
        .media-thumbnail:hover {
            opacity: 0.9;
        }
        
        .media-video {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .media-file {
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
        }
        
        .file-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .file-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            word-break: break-word;
        }
        
        .file-size {
            font-size: 0.85rem;
            color: #666;
        }
        
        .media-info {
            padding: 10px;
            background: #f8f9fa;
        }
        
        .media-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 3px;
            word-break: break-word;
        }
        
        .media-size {
            font-size: 0.8rem;
            color: #666;
        }
        
        /* 媒体预览模态框 */
        .media-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }
        
        .media-modal-content {
            position: relative;
            margin: auto;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        @media (max-width: 768px) {
            .media-modal-content {
                width: 95%;
                padding: 15px;
                max-width: none;
            }
        }
        
        @media (max-width: 480px) {
            .media-modal-content {
                width: 98%;
                padding: 10px;
            }
        }
        
        .media-modal img,
        .media-modal video {
            width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .media-modal-close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: #fff;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10001;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        
        .media-modal-close:hover {
            background: rgba(0,0,0,0.8);
        }
        
        @media (max-width: 768px) {
            .media-modal-close {
                top: 5px;
                right: 15px;
                font-size: 30px;
                width: 45px;
                height: 45px;
            }
        }
        
        @media (max-width: 480px) {
            .media-modal-close {
                top: 5px;
                right: 10px;
                font-size: 25px;
                width: 40px;
                height: 40px;
            }
        }
        
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .post-action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .post-actions {
                flex-direction: column;
                align-items: stretch;
                margin-top: 15px;
                padding-top: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .post-actions {
                margin-top: 12px;
                padding-top: 12px;
            }
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-align: center;
            justify-content: center;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .back-btn {
                padding: 12px 20px;
                font-size: 1rem;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .back-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
        
        .replies-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .replies-container {
                border-radius: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .replies-container {
                border-radius: 6px;
            }
        }
        
        .replies-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .replies-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
        }
        
        .replies-count {
            color: #666;
            font-size: 0.9rem;
        }
        
        .reply-form {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .reply-form {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .reply-form {
                padding: 12px;
            }
        }
        
        .reply-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        
        @media (max-width: 768px) {
            .reply-form textarea {
                padding: 10px;
                font-size: 16px; /* 防止iOS缩放 */
            }
        }
        
        @media (max-width: 480px) {
            .reply-form textarea {
                padding: 8px;
                min-height: 70px;
            }
        }
        
        .reply-form textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .reply-form-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .replies-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .reply-item {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .reply-item {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .reply-item {
                padding: 12px;
            }
        }
        
        .reply-item:hover {
            background: #f8f9fa;
        }
        
        .reply-item:last-child {
            border-bottom: none;
        }
        
        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .reply-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .reply-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .reply-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .reply-info {
            display: flex;
            flex-direction: column;
        }
        
        .reply-name {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .reply-time {
            font-size: 0.8rem;
            color: #666;
        }
        
        .reply-actions {
            display: flex;
            gap: 10px;
        }
        
        .reply-btn {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .reply-btn:hover {
            background: #f0f0f0;
        }
        
        .reply-content {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
            margin-left: 40px;
        }
        
        .reply-content a {
            color: #667eea;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: all 0.3s ease;
        }
        
        .reply-content a:hover {
            color: #764ba2;
            border-bottom-color: #764ba2;
        }
        
        @media (max-width: 768px) {
            .reply-content {
                font-size: 0.9rem;
                line-height: 1.5;
                margin-left: 35px;
            }
        }
        
        @media (max-width: 480px) {
            .reply-content {
                font-size: 0.85rem;
                margin-left: 30px;
            }
        }
        
        .reply-to {
            background: #e9ecef;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
            margin-left: 40px;
            font-size: 0.85rem;
            color: #666;
            border-left: 3px solid #667eea;
        }
        
        .reply-to-author {
            font-weight: 600;
            color: #333;
        }
        
        .empty-replies {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-replies-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .reply-form-reply {
            margin-top: 15px;
            padding: 15px;
            background: #f0f8ff;
            border-radius: 6px;
            border-left: 3px solid #667eea;
            display: none;
        }
        
        @media (max-width: 768px) {
            .reply-form-reply {
                margin-top: 12px;
                padding: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .reply-form-reply {
                margin-top: 10px;
                padding: 10px;
            }
        }
        
        .reply-form-reply.active {
            display: block;
        }
        
        /* 通用响应式优化 */
        @media (max-width: 768px) {
            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
            
            .btn-primary {
                width: 100%;
                margin-top: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
        }
        
        /* 确保文字在小屏幕上可读 */
        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                font-size: 13px;
            }
        }
        
        /* 优化触摸体验 */
        @media (max-width: 768px) {
            button, .btn, .reply-btn {
                min-height: 44px; /* iOS推荐的最小触摸目标 */
                min-width: 44px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- 引入侧边栏组件 -->
        <?php include __DIR__ . '/../components/navbar.php'; ?>
        
        <!-- 帖子内容区域 -->
        <div class="chat-area">
            <div class="post-container">
                <!-- 帖子头部 -->
                <div class="post-header">
                    <div class="post-title-container">
                        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <?php if ($post['author_id'] == $_SESSION['user_id']): ?>
                            <div class="post-management">
                                <button class="management-btn" onclick="toggleManagementMenu()">
                                    ⚙️ 管理帖子
                                </button>
                                <div class="management-dropdown" id="managementDropdown">
                                    <a href="/CHATTING/forum/editPost?id=<?php echo $post['id']; ?>" class="dropdown-item">
                                        ✏️ 编辑帖子
                                    </a>
                                    <button class="dropdown-item danger" onclick="deletePost(<?php echo $post['id']; ?>)">
                                        🗑️ 删除帖子
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-meta">
                        <div class="post-author">
                            <div class="author-avatar">
                                <?php 
                                $authorAvatar = $post['avatar'] ?? null;
                                if (!empty($authorAvatar) && $authorAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $authorAvatar)) {
                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($authorAvatar) . '" alt="头像">';
                                } else {
                                    echo strtoupper(substr($post['username'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="author-info">
                                <div class="author-name"><?php echo htmlspecialchars($post['username']); ?></div>
                                <div class="post-time"><?php echo date('Y-m-d H:i:s', strtotime($post['created_at'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="post-stats">
                            <div class="stat">
                                <span>👁️</span>
                                <span><?php echo $post['view_count']; ?> 浏览</span>
                            </div>
                            <div class="stat">
                                <span>💬</span>
                                <span><?php echo count($replies); ?> 回复</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    
                    <!-- 媒体文件显示 -->
                    <?php if (!empty($post['media_files'])): ?>
                    <div class="post-media">
                        <h3>附件</h3>
                        <div class="media-grid">
                            <?php foreach ($post['media_files'] as $media): ?>
                                <?php 
                                $filePath = '/CHATTING/public/uploads/files/' . $media['filename'];
                                $isImage = in_array($media['file_type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                $isVideo = in_array($media['file_type'], ['video/mp4', 'video/webm', 'video/quicktime']);
                                ?>
                                <div class="media-item">
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo $filePath; ?>" 
                                             alt="<?php echo htmlspecialchars($media['original_name']); ?>"
                                             onclick="openMediaModal('<?php echo $filePath; ?>', 'image')"
                                             class="media-thumbnail">
                                    <?php elseif ($isVideo): ?>
                                        <video controls class="media-video" preload="metadata">
                                            <source src="<?php echo $filePath; ?>" type="<?php echo $media['file_type']; ?>">
                                            您的浏览器不支持视频播放。
                                        </video>
                                    <?php else: ?>
                                        <div class="media-file">
                                            <div class="file-icon">📄</div>
                                            <div class="file-name"><?php echo htmlspecialchars($media['original_name']); ?></div>
                                            <div class="file-size"><?php echo formatFileSize($media['file_size']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="media-info">
                                        <div class="media-name"><?php echo htmlspecialchars($media['original_name']); ?></div>
                                        <div class="media-size"><?php echo formatFileSize($media['file_size']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="post-actions">
                        <a href="/CHATTING/forum/view?id=<?php echo $post['forum_id']; ?>" class="back-btn">
                            ← 返回论坛
                        </a>
                        <div class="post-action-buttons">
                            <button class="btn btn-primary" onclick="scrollToReplyForm()">
                                💬 回复帖子
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 回复区域 -->
                <div class="replies-container">
                    <div class="replies-header">
                        <h2 class="replies-title">回复</h2>
                        <span class="replies-count">共 <?php echo count($replies); ?> 条回复</span>
                    </div>
                    
                    <!-- 回复表单 -->
                    <div class="reply-form">
                        <form id="replyForm">
                            <input type="hidden" id="postId" value="<?php echo $post['id']; ?>">
                            <textarea id="replyContent" placeholder="写下你的回复..." required></textarea>
                            <div class="reply-form-actions">
                                <button type="submit" class="btn btn-primary">发表回复</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- 回复列表 -->
                    <?php if (empty($replies)): ?>
                        <div class="empty-replies">
                            <div class="empty-replies-icon">💬</div>
                            <h3>暂无回复</h3>
                            <p>成为第一个回复的人吧！</p>
                        </div>
                    <?php else: ?>
                        <ul class="replies-list">
                            <?php foreach ($replies as $reply): ?>
                                <li class="reply-item" id="reply-<?php echo $reply['id']; ?>">
                                    <div class="reply-header">
                                        <div class="reply-author">
                                            <div class="reply-avatar">
                                                <?php 
                                                $replyAvatar = $reply['avatar'] ?? null;
                                                if (!empty($replyAvatar) && $replyAvatar !== 'default_avatar.png' && file_exists(BASE_PATH . '/public/uploads/avatars/' . $replyAvatar)) {
                                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($replyAvatar) . '" alt="头像">';
                                                } else {
                                                    echo strtoupper(substr($reply['username'], 0, 1));
                                                }
                                                ?>
                                            </div>
                                            <div class="reply-info">
                                                <div class="reply-name"><?php echo htmlspecialchars($reply['username']); ?></div>
                                                <div class="reply-time"><?php echo date('Y-m-d H:i', strtotime($reply['created_at'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="reply-actions">
                                            <button class="reply-btn" onclick="replyToReply(<?php echo $reply['id']; ?>, '<?php echo htmlspecialchars($reply['username']); ?>')">
                                                回复
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <?php if ($reply['reply_to_id']): ?>
                                        <div class="reply-to">
                                            回复 <span class="reply-to-author">@<?php echo htmlspecialchars($reply['reply_to_username']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="reply-content">
                                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                                    </div>
                                    
                                    <!-- 嵌套回复表单 -->
                                    <div class="reply-form-reply" id="reply-form-<?php echo $reply['id']; ?>">
                                        <form class="nested-reply-form">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="reply_to" value="<?php echo $reply['id']; ?>">
                                            <textarea name="content" placeholder="回复 @<?php echo htmlspecialchars($reply['username']); ?>..." required></textarea>
                                            <div class="reply-form-actions">
                                                <button type="button" class="btn btn-secondary" onclick="cancelReply(<?php echo $reply['id']; ?>)">取消</button>
                                                <button type="submit" class="btn btn-primary">回复</button>
                                            </div>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 将文本中的URL转换为可点击链接
        function linkifyText(element) {
            const text = element.innerHTML;
            // URL正则表达式，匹配http、https、www开头的链接
            const urlRegex = /(https?:\/\/[^\s]+|www\.[^\s]+)/gi;
            const linkedText = text.replace(urlRegex, function(url) {
                let href = url;
                // 如果URL以www开头，添加https://
                if (url.toLowerCase().startsWith('www.')) {
                    href = 'https://' + url;
                }
                return '<a href="' + href + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
            });
            element.innerHTML = linkedText;
        }
        
        // 页面加载完成后处理所有内容
        document.addEventListener('DOMContentLoaded', function() {
            // 处理帖子内容中的链接
            const postContent = document.querySelector('.post-content');
            if (postContent) {
                linkifyText(postContent);
            }
            
            // 处理所有回复内容中的链接
            const replyContents = document.querySelectorAll('.reply-content');
            replyContents.forEach(function(replyContent) {
                linkifyText(replyContent);
            });
        });
        
        // 滚动到回复表单
        function scrollToReplyForm() {
            document.getElementById('replyContent').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('replyContent').focus();
        }
        
        // 回复帖子
        document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = document.getElementById('replyContent').value.trim();
            if (!content) {
                alert('请输入回复内容');
                return;
            }
            
            const formData = new FormData();
            formData.append('post_id', document.getElementById('postId').value);
            formData.append('content', content);
            
            fetch('/CHATTING/forum/replyPost', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('回复成功！');
                    location.reload();
                } else {
                    alert('回复失败: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('回复失败，请重试');
            });
        });
        
        // 回复某条回复
        function replyToReply(replyId, username) {
            // 隐藏其他回复表单
            document.querySelectorAll('.reply-form-reply').forEach(form => {
                form.classList.remove('active');
            });
            
            // 显示当前回复表单
            const replyForm = document.getElementById('reply-form-' + replyId);
            replyForm.classList.add('active');
            
            // 滚动到表单
            replyForm.scrollIntoView({ behavior: 'smooth' });
            
            // 聚焦到文本框
            const textarea = replyForm.querySelector('textarea');
            textarea.focus();
            textarea.value = '@' + username + ' ';
        }
        
        // 取消回复
        function cancelReply(replyId) {
            document.getElementById('reply-form-' + replyId).classList.remove('active');
        }
        
        // 嵌套回复表单提交
        document.querySelectorAll('.nested-reply-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const content = formData.get('content').trim();
                
                if (!content) {
                    alert('请输入回复内容');
                    return;
                }
                
                fetch('/CHATTING/forum/replyPost', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('回复成功！');
                        location.reload();
                    } else {
                        alert('回复失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('回复失败，请重试');
                });
            });
        });
        
        // 自动调整文本框高度
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });
        
        // 切换管理菜单显示状态
        function toggleManagementMenu() {
            const dropdown = document.getElementById('managementDropdown');
            dropdown.classList.toggle('show');
        }
        
        // 删除帖子
        function deletePost(postId) {
            if (confirm('确定要删除这个帖子吗？删除后将无法恢复！')) {
                const formData = new FormData();
                formData.append('post_id', postId);
                
                fetch('/CHATTING/forum/deletePost', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('帖子删除成功！');
                        window.location.href = '/CHATTING/forum/view?id=<?php echo $post['forum_id']; ?>';
                    } else {
                        alert('删除失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('删除失败，请重试');
                });
            }
        }
        
        // 点击页面其他地方关闭下拉菜单
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('managementDropdown');
            const btn = document.querySelector('.management-btn');
            
            if (dropdown && btn && !btn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
    
    <!-- 媒体预览模态框 -->
    <div id="mediaModal" class="media-modal">
        <span class="media-modal-close" onclick="closeMediaModal()">&times;</span>
        <div class="media-modal-content">
            <img id="modalImage" style="display: none;" alt="预览图片">
            <video id="modalVideo" style="display: none;" controls>
                您的浏览器不支持视频播放。
            </video>
        </div>
    </div>
    
    <script>
        // 打开媒体预览模态框
        function openMediaModal(filePath, type) {
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            
            if (type === 'image') {
                modalImage.src = filePath;
                modalImage.style.display = 'block';
                modalVideo.style.display = 'none';
            } else if (type === 'video') {
                modalVideo.src = filePath;
                modalVideo.style.display = 'block';
                modalImage.style.display = 'none';
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // 防止背景滚动
        }
        
        // 关闭媒体预览模态框
        function closeMediaModal() {
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            
            modal.style.display = 'none';
            modalImage.src = '';
            modalVideo.src = '';
            document.body.style.overflow = 'auto'; // 恢复背景滚动
        }
        
        // 点击模态框背景关闭
        document.getElementById('mediaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMediaModal();
            }
        });
        
        // ESC键关闭模态框
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMediaModal();
            }
        });
    </script>
</body>
</html>
