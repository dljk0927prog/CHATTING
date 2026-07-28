<?php
// 收藏页面 - 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: /Chat_System/auth/login");
    exit;
}

// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();

// 获取用户信息
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Favorites.php';

$userModel = new User();
$favoritesModel = new Favorites();
$user = $userModel->getUserById($_SESSION['user_id']);

if (!$user) {
    header("Location: /Chat_System/auth/login");
    exit;
}

// 获取收藏列表
$type = $_GET['type'] ?? null;
$favorites = $favoritesModel->getUserFavorites($_SESSION['user_id'], $type);
$stats = $favoritesModel->getFavoritesStats($_SESSION['user_id']);

// 文件大小格式化函数已移至 Language.php 中
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?php echo str_replace('{username}', htmlspecialchars($user['username']), __('favorites_page_title')); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .favorites-container {
            max-width: 1200px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8f4ff 0%, #ffffff 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        .favorites-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e8d5ff, #d1b3ff, #b894ff, #a075ff);
        }
        
        .header-section {
            background: linear-gradient(135deg, #f0e6ff 0%, #e8d5ff 100%);
            color: #4a3c5c;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(168, 85, 247, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .page-header {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .page-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a3c5c;
            font-size: 2rem;
            font-weight: bold;
            margin-right: 25px;
            position: relative;
            overflow: hidden;
            border: 4px solid rgba(168, 85, 247, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.15);
        }
        
        .page-title {
            flex: 1;
        }
        
        .page-name {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4a3c5c;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .page-description {
            font-size: 1.1rem;
            color: #6b46c1;
            background: rgba(168, 85, 247, 0.1);
            padding: 8px 16px;
            border-radius: 25px;
            display: inline-block;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(168, 85, 247, 0.2);
            font-weight: 500;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.8);
            color: #6b46c1;
            border: 2px solid rgba(168, 85, 247, 0.3);
            padding: 0;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.4rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            position: absolute;
            top: 20px;
            right: 20px;
            width: 48px;
            height: 48px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.15);
        }
        
        .back-btn:hover {
            background: rgba(168, 85, 247, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.2);
        }
        
        .back-btn::before {
            content: '←';
            font-size: 1.4rem;
            line-height: 1;
        }
        
        .content-section {
            padding: 40px 30px;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f4ff 100%);
            padding: 20px;
            border-radius: 15px;
            border-left: 5px solid #9c27b0;
            box-shadow: 0 4px 12px rgba(156, 39, 176, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(156, 39, 176, 0.08), rgba(142, 36, 170, 0.08));
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(156, 39, 176, 0.15);
        }
        
        .stat-label {
            font-weight: 600;
            color: #9c27b0;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }
        
        .stat-value {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }
        
        .filters-section {
            margin-bottom: 30px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            background: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .filter-tab:hover {
            border-color: #9c27b0;
            color: #9c27b0;
            transform: translateY(-2px);
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #9c27b0 0%, #7b1fa2 100%);
            border-color: #9c27b0;
            color: white;
            box-shadow: 0 4px 12px rgba(156, 39, 176, 0.3);
        }
        
        .search-box {
            position: relative;
            max-width: 400px;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #9c27b0;
            box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.2rem;
        }
        
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .favorite-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f4ff 100%);
            border-radius: 15px;
            padding: 20px;
            border-left: 5px solid #9c27b0;
            box-shadow: 0 4px 12px rgba(156, 39, 176, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .favorite-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(156, 39, 176, 0.08), rgba(142, 36, 170, 0.08));
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .favorite-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(156, 39, 176, 0.15);
        }
        
        .favorite-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        
        .favorite-type-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #9c27b0 0%, #7b1fa2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .favorite-title {
            flex: 1;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .favorite-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .view-btn {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }
        
        .view-btn:hover {
            background: #2196f3;
            color: white;
        }
        
        .delete-btn {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
        
        .delete-btn:hover {
            background: #f44336;
            color: white;
        }
        
        .favorite-content {
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        
        .favorite-preview {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        /* 媒体预览样式 */
        .media-preview {
            width: 100%;
            height: 200px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .media-preview img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
            border-radius: 8px;
        }
        
        .media-preview img:hover {
            transform: scale(1.05);
        }
        
        .media-preview video {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .media-preview .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .media-preview .video-overlay:hover {
            background: rgba(0, 0, 0, 0.5);
        }
        
        .media-preview .file-icon {
            font-size: 4rem;
            color: #9c27b0;
            opacity: 0.7;
        }
        
        .media-preview .file-info {
            text-align: center;
            color: #666;
        }
        
        .media-preview .file-info .file-name {
            font-weight: 600;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        .media-preview .file-info .file-size {
            font-size: 0.8rem;
            color: #999;
        }
        
        /* 媒体模态框样式 */
        .media-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
        }
        
        .media-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .media-modal-content img,
        .media-modal-content video {
            width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .media-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        
        .media-modal-close:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        
        .favorite-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #999;
            position: relative;
            z-index: 2;
        }
        
        .favorite-tags {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .tag {
            background: rgba(156, 39, 176, 0.1);
            color: #9c27b0;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            color: #95a5a6;
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 15px;
            border: 2px dashed #e9ecef;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.6;
        }
        
        .empty-state div:last-child {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        /* 图片预览模态框样式 */
        .image-preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }
        
        .image-preview-modal.hidden {
            display: none;
        }
        
        .image-preview-modal .modal-content {
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .image-preview-modal .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .image-preview-modal .modal-header h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .image-preview-modal .close-btn {
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
            transition: background 0.3s ease;
        }
        
        .image-preview-modal .close-btn:hover {
            background: #e9ecef;
        }
        
        .image-preview-container {
            display: flex;
            flex-direction: column;
            height: 70vh;
        }
        
        .image-preview-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            padding: 20px;
        }
        
        .preview-main-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .preview-main-video {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .image-preview-thumbnails {
            display: flex;
            gap: 8px;
            padding: 16px;
            background: white;
            border-top: 1px solid #e1e5e9;
            overflow-x: auto;
        }
        
        .thumbnail-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: border-color 0.3s ease;
            flex-shrink: 0;
        }
        
        .thumbnail-item:hover {
            border-color: #9c27b0;
        }
        
        .thumbnail-item.active {
            border-color: #9c27b0;
        }
        
        .thumbnail-media {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .thumbnail-play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 1.5rem;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .favorites-container {
                margin: 0;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .page-icon {
                margin-right: 0;
                margin-bottom: 20px;
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .page-name {
                font-size: 1.8rem;
            }
            
            .back-btn {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                align-self: flex-start;
                width: 40px;
                height: 40px;
            }
            
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .favorites-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .favorite-item {
                padding: 15px;
            }
            
            .favorite-header {
                margin-bottom: 12px;
            }
            
            .favorite-type-icon {
                width: 35px;
                height: 35px;
                font-size: 1.1rem;
            }
            
            .favorite-title {
                font-size: 1rem;
            }
            
            .favorite-actions {
                gap: 8px;
            }
            
            .action-btn {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
            
            .media-preview {
                height: 150px;
                margin-bottom: 12px;
            }
            
            .favorite-preview {
                font-size: 0.85rem;
                max-height: 50px;
            }
            
            .filter-tabs {
                justify-content: center;
                gap: 8px;
            }
            
            .filter-tab {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .search-input {
                font-size: 16px;
                padding: 12px 20px 12px 45px;
            }
            
            .search-icon {
                left: 12px;
                font-size: 1.1rem;
            }
            
            .empty-state {
                padding: 60px 15px;
            }
            
            .empty-state-icon {
                font-size: 3rem;
            }
            
            .empty-state div:last-child {
                font-size: 1rem;
            }
            
            /* 图片预览模态框移动端优化 */
            .image-preview-modal .modal-content {
                width: 95%;
                margin: 10px;
            }
            
            .image-preview-container {
                height: 60vh;
            }
            
            .image-preview-main {
                padding: 15px;
            }
            
            .image-preview-thumbnails {
                padding: 12px;
                gap: 6px;
            }
            
            .thumbnail-item {
                width: 60px;
                height: 60px;
            }
            
            .thumbnail-play-icon {
                width: 24px;
                height: 24px;
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .header-section {
                padding: 20px 15px;
            }
            
            .content-section {
                padding: 20px 15px;
            }
            
            .page-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            .page-name {
                font-size: 1.5rem;
            }
            
            .page-description {
                font-size: 1rem;
                padding: 6px 12px;
            }
            
            .back-btn {
                width: 36px;
                height: 36px;
            }
            
            .stats-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .stat-value {
                font-size: 1.3rem;
            }
            
            .favorite-item {
                padding: 12px;
            }
            
            .favorite-type-icon {
                width: 32px;
                height: 32px;
                font-size: 1rem;
            }
            
            .favorite-title {
                font-size: 0.95rem;
            }
            
            .action-btn {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
            
            .media-preview {
                height: 120px;
                margin-bottom: 10px;
            }
            
            .favorite-preview {
                font-size: 0.8rem;
                max-height: 40px;
            }
            
            .filter-tabs {
                gap: 6px;
            }
            
            .filter-tab {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .search-input {
                padding: 10px 18px 10px 40px;
                font-size: 16px;
            }
            
            .search-icon {
                left: 10px;
                font-size: 1rem;
            }
            
            .empty-state {
                padding: 40px 10px;
            }
            
            .empty-state-icon {
                font-size: 2.5rem;
            }
            
            .empty-state div:last-child {
                font-size: 0.95rem;
            }
            
            /* 图片预览模态框小屏幕优化 */
            .image-preview-modal .modal-content {
                width: 98%;
                margin: 5px;
            }
            
            .image-preview-container {
                height: 50vh;
            }
            
            .image-preview-main {
                padding: 10px;
            }
            
            .image-preview-thumbnails {
                padding: 8px;
                gap: 4px;
            }
            
            .thumbnail-item {
                width: 50px;
                height: 50px;
            }
            
            .thumbnail-play-icon {
                width: 20px;
                height: 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="favorites-container">
        <!-- 返回按钮 -->
        <a href="/Chat_System/dashboard" class="back-btn" title="<?php echo __('btn_back'); ?>"></a>
        
        <!-- 页面头部信息 -->
        <div class="header-section">
            <div class="page-header">
                <div class="page-icon">⭐</div>
                <div class="page-title">
                    <div class="page-name"><?php echo __('favorites_title'); ?></div>
                    <div class="page-description"><?php echo __('favorites_stats'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- 内容区域 -->
        <div class="content-section">
            <!-- 统计信息 -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-label"><?php echo __('favorites_total'); ?></div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?php echo __('favorites_filter_videos'); ?></div>
                    <div class="stat-value"><?php echo $stats['by_type']['video'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?php echo __('favorites_filter_images'); ?></div>
                    <div class="stat-value"><?php echo $stats['by_type']['image'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?php echo __('favorites_filter_files'); ?></div>
                    <div class="stat-value"><?php echo $stats['by_type']['file'] ?? 0; ?></div>
                </div>
            </div>
            
            <!-- 筛选和搜索 -->
            <div class="filters-section">
                <div class="filter-tabs">
                    <div class="filter-tab <?php echo !$type ? 'active' : ''; ?>" onclick="filterFavorites('')">
                        <?php echo __('favorites_filter_all'); ?>
                    </div>
                    <div class="filter-tab <?php echo $type === 'video' ? 'active' : ''; ?>" onclick="filterFavorites('video')">
                        <?php echo __('favorites_filter_videos'); ?>
                    </div>
                    <div class="filter-tab <?php echo $type === 'image' ? 'active' : ''; ?>" onclick="filterFavorites('image')">
                        <?php echo __('favorites_filter_images'); ?>
                    </div>
                    <div class="filter-tab <?php echo $type === 'audio' ? 'active' : ''; ?>" onclick="filterFavorites('audio')">
                        <?php echo __('favorites_filter_voices'); ?>
                    </div>
                    <div class="filter-tab <?php echo $type === 'url' ? 'active' : ''; ?>" onclick="filterFavorites('url')">
                        <?php echo __('favorites_filter_files'); ?>
                    </div>
                    <div class="filter-tab <?php echo $type === 'file' ? 'active' : ''; ?>" onclick="filterFavorites('file')">
                        <?php echo __('favorites_filter_files'); ?>
                    </div>
                    <div class="filter-tab <?php echo $type === 'text' ? 'active' : ''; ?>" onclick="filterFavorites('text')">
                        <?php echo __('favorites_filter_files'); ?>
                    </div>
                </div>
                
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" placeholder="<?php echo __('search'); ?>..." id="searchInput">
                </div>
            </div>
            
            <!-- 收藏列表 -->
            <?php if (empty($favorites)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <div>暂无收藏内容</div>
                </div>
            <?php else: ?>
                <div class="favorites-grid">
                    <?php foreach ($favorites as $favorite): ?>
                        <div class="favorite-item">
                            <div class="favorite-header">
                                <div class="favorite-type-icon">
                                    <?php
                                    $typeIcons = [
                                        'video' => '🎥',
                                        'image' => '🖼️',
                                        'audio' => '🎵',
                                        'url' => '🔗',
                                        'file' => '📄',
                                        'text' => '📝'
                                    ];
                                    echo $typeIcons[$favorite['type']] ?? '⭐';
                                    ?>
                                </div>
                                <div class="favorite-title"><?php echo htmlspecialchars($favorite['title']); ?></div>
                                <div class="favorite-actions">
                                    <button class="action-btn view-btn" onclick="viewFavorite(<?php echo $favorite['id']; ?>)" title="查看">
                                        👁️
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteFavorite(<?php echo $favorite['id']; ?>)" title="删除">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                            <div class="favorite-content">
                                <?php if (!empty($favorite['file_path']) && in_array($favorite['type'], ['image', 'video'])): ?>
                                    <!-- 媒体预览 -->
                                    <div class="media-preview">
                                        <?php 
                                        // 检查是否有多个文件
                                        $metadata = $favorite['metadata'] ? json_decode($favorite['metadata'], true) : null;
                                        $hasMultipleFiles = $metadata && isset($metadata['files']) && count($metadata['files']['urls']) > 1;
                                        
                                        if ($hasMultipleFiles) {
                                            // 多文件显示
                                            $files = $metadata['files'];
                                            $fileUrls = $files['urls'];
                                            $fileNames = $files['names'];
                                            $displayCount = min(4, count($fileUrls)); // 最多显示4个
                                            
                                            echo '<div class="multi-file-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; height: 100%;">';
                                            
                                            for ($i = 0; $i < $displayCount; $i++) {
                                                $filePath = $fileUrls[$i];
                                                if (strpos($filePath, '/Chat_System/') !== 0) {
                                                    $filePath = '/Chat_System/public/uploads/files/' . basename($filePath);
                                                }
                                                
                                                if ($i === 3 && count($fileUrls) > 4) {
                                                    // 显示"更多"占位符
                                                    echo '<div class="more-files" style="background: rgba(0,0,0,0.7); color: white; display: flex; align-items: center; justify-content: center; border-radius: 5px; font-size: 0.8rem;">+' . (count($fileUrls) - 3) . '</div>';
                                                } else {
                                                    echo '<div class="file-item" style="position: relative; border-radius: 5px; overflow: hidden;">';
                                                    echo '<img src="' . htmlspecialchars($filePath) . '" alt="' . htmlspecialchars($fileNames[$i] ?? '') . '" style="width: 100%; height: 100%; object-fit: cover;" onclick="showFavoritesImagePreview(' . $favorite['id'] . ', ' . $i . ')">';
                                                    echo '</div>';
                                                }
                                            }
                                            
                                            echo '</div>';
                                        } else {
                                            // 单文件显示
                                            $filePath = $favorite['file_path'];
                                            if (strpos($filePath, '/Chat_System/') !== 0) {
                                                $filePath = '/Chat_System/public/uploads/files/' . basename($filePath);
                                            }
                                            
                                            if ($favorite['type'] === 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($filePath); ?>" 
                                                     alt="<?php echo htmlspecialchars($favorite['title']); ?>"
                                                     onclick="showSingleImagePreview('<?php echo htmlspecialchars($filePath); ?>', '<?php echo htmlspecialchars($favorite['title']); ?>')"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                     onload="this.style.display='block'; this.nextElementSibling.style.display='none';">
                                                <div class="media-error" style="display: none; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #999;">
                                                    <div style="font-size: 2rem; margin-bottom: 10px;">🖼️</div>
                                                    <div>图片加载失败</div>
                                                    <div style="font-size: 0.8rem; margin-top: 5px;"><?php echo htmlspecialchars($favorite['title']); ?></div>
                                                </div>
                                            <?php elseif ($favorite['type'] === 'video'): ?>
                                                <video controls preload="metadata" 
                                                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                       onloadeddata="this.style.display='block'; this.nextElementSibling.style.display='none';">
                                                    <source src="<?php echo htmlspecialchars($filePath); ?>" type="video/mp4">
                                                    您的浏览器不支持视频播放。
                                                </video>
                                                <div class="media-error" style="display: none; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #999;">
                                                    <div style="font-size: 2rem; margin-bottom: 10px;">🎥</div>
                                                    <div>视频加载失败</div>
                                                    <div style="font-size: 0.8rem; margin-top: 5px;"><?php echo htmlspecialchars($favorite['title']); ?></div>
                                                </div>
                                            <?php endif;
                                        }
                                        ?>
                                    </div>
                                <?php elseif (!empty($favorite['file_path']) && $favorite['type'] === 'file'): ?>
                                    <!-- 文件预览 -->
                                    <div class="media-preview">
                                        <div class="file-info">
                                            <div class="file-icon">📄</div>
                                            <div class="file-name"><?php echo htmlspecialchars($favorite['title']); ?></div>
                                            <?php if (!empty($favorite['file_size'])): ?>
                                                <div class="file-size"><?php echo formatFileSize($favorite['file_size']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- 文本预览 -->
                                    <div class="favorite-preview">
                                        <?php echo htmlspecialchars(substr($favorite['content'] ?? '', 0, 150)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="favorite-meta">
                                <div class="favorite-tags">
                                    <?php if (!empty($favorite['tags'])): ?>
                                        <?php foreach (explode(',', $favorite['tags']) as $tag): ?>
                                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div><?php echo date('Y-m-d', strtotime($favorite['created_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 媒体模态框 -->
    <div id="mediaModal" class="media-modal">
        <div class="media-modal-content">
            <span class="media-modal-close" onclick="closeMediaModal()">&times;</span>
            <div id="modalContent"></div>
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
                        <img id="previewImage" src="" alt="预览图片" class="preview-main-image">
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
    
    <script>
        // 筛选收藏
        function filterFavorites(type) {
            const url = new URL(window.location);
            if (type) {
                url.searchParams.set('type', type);
            } else {
                url.searchParams.delete('type');
            }
            window.location.href = url.toString();
        }
        
        // 搜索功能
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const keyword = e.target.value.trim();
            if (keyword.length > 2) {
                // 这里可以添加实时搜索功能
                console.log('搜索:', keyword);
            }
        });
        
        // 查看收藏
        function viewFavorite(favoriteId) {
            // 这里可以打开收藏详情模态框或跳转到详情页
            alert('查看收藏 ID: ' + favoriteId);
        }
        
        // 删除收藏
        function deleteFavorite(favoriteId) {
            if (confirm('确定要删除这个收藏吗？')) {
                fetch('/Chat_System/favorites/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `favorite_id=${favoriteId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('删除成功');
                        location.reload();
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
        
        // 打开媒体模态框
        function openMediaModal(filePath, type) {
            const modal = document.getElementById('mediaModal');
            const modalContent = document.getElementById('modalContent');
            
            if (type === 'image') {
                modalContent.innerHTML = `<img src="${filePath}" alt="预览图片">`;
            } else if (type === 'video') {
                modalContent.innerHTML = `<video controls autoplay><source src="${filePath}" type="video/mp4">您的浏览器不支持视频播放。</video>`;
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        // 全局变量存储当前预览的文件列表和索引
        let currentPreviewFiles = [];
        let currentPreviewIndex = 0;
        
        // 显示收藏的多文件图片预览
        function showFavoritesImagePreview(favoriteId, fileIndex) {
            // 获取收藏的完整文件信息
            fetch(`/Chat_System/favorites/getFavoriteData?id=${favoriteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.metadata) {
                        const metadata = JSON.parse(data.metadata);
                        const fileUrls = metadata.files.urls || [];
                        const fileNames = metadata.files.names || [];
                        
                        // 存储当前预览的文件信息
                        currentPreviewFiles = fileUrls.map((url, index) => ({
                            url: url,
                            name: fileNames[index] || ''
                        }));
                        currentPreviewIndex = fileIndex;
                        
                        // 显示模态框
                        const modal = document.getElementById('imagePreviewModal');
                        modal.classList.remove('hidden');
                        
                        // 生成缩略图
                        generateFavoritesThumbnails(fileUrls, fileNames);
                        
                        // 显示指定索引的图片
                        if (fileUrls.length > fileIndex) {
                            showFavoritesPreviewImage(fileUrls[fileIndex], fileNames[fileIndex], fileIndex);
                        }
                        
                        // 添加滚动和触摸事件监听器
                        addPreviewEventListeners();
                    }
                })
                .catch(error => {
                    console.error('获取收藏数据失败:', error);
                });
        }
        
        // 显示单张图片预览
        function showSingleImagePreview(filePath, fileName) {
            const modal = document.getElementById('imagePreviewModal');
            modal.classList.remove('hidden');
            
            // 存储当前预览的文件信息
            currentPreviewFiles = [{
                url: filePath,
                name: fileName
            }];
            currentPreviewIndex = 0;
            
            // 生成单张图片的缩略图
            generateFavoritesThumbnails([filePath], [fileName]);
            
            // 显示图片
            showFavoritesPreviewImage(filePath, fileName, 0);
            
            // 添加滚动和触摸事件监听器
            addPreviewEventListeners();
        }
        
        // 生成收藏页面的缩略图
        function generateFavoritesThumbnails(fileUrls, fileNames) {
            const thumbnailsContainer = document.getElementById('previewThumbnails');
            thumbnailsContainer.innerHTML = '';
            
            fileUrls.forEach((url, index) => {
                const fileName = fileNames[index] || '';
                const extension = fileName.split('.').pop().toLowerCase();
                const isVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(extension);
                
                const thumbnail = document.createElement('div');
                thumbnail.className = 'thumbnail-item';
                thumbnail.onclick = () => showFavoritesPreviewImage(url, fileName, index);
                
                if (isVideo) {
                    thumbnail.innerHTML = `
                        <video class="thumbnail-media">
                            <source src="${url}" type="video/${extension}">
                        </video>
                        <div class="thumbnail-play-icon">▶</div>
                    `;
                } else {
                    thumbnail.innerHTML = `<img src="${url}" alt="缩略图" class="thumbnail-media">`;
                }
                
                thumbnailsContainer.appendChild(thumbnail);
            });
        }
        
        // 显示收藏页面的预览图片
        function showFavoritesPreviewImage(url, fileName, index) {
            const extension = fileName.split('.').pop().toLowerCase();
            const isVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(extension);
            
            const previewImage = document.getElementById('previewImage');
            const previewVideo = document.getElementById('previewVideo');
            
            if (isVideo) {
                previewImage.style.display = 'none';
                previewVideo.style.display = 'block';
                previewVideo.src = url;
                previewVideo.load();
            } else {
                previewImage.style.display = 'block';
                previewVideo.style.display = 'none';
                previewImage.src = url;
            }
            
            // 更新缩略图选中状态
            document.querySelectorAll('.thumbnail-item').forEach((item, i) => {
                item.classList.toggle('active', i === index);
            });
        }
        
        // 隐藏图片预览
        function hideImagePreview() {
            document.getElementById('imagePreviewModal').classList.add('hidden');
            // 移除事件监听器
            removePreviewEventListeners();
        }
        
        // 添加预览事件监听器
        function addPreviewEventListeners() {
            const modal = document.getElementById('imagePreviewModal');
            
            // 鼠标滚轮事件
            modal.addEventListener('wheel', handleWheelScroll, { passive: false });
            
            // 触摸事件
            let touchStartX = 0;
            let touchStartY = 0;
            
            modal.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }, { passive: true });
            
            modal.addEventListener('touchend', function(e) {
                if (!touchStartX || !touchStartY) return;
                
                const touchEndX = e.changedTouches[0].clientX;
                const touchEndY = e.changedTouches[0].clientY;
                
                const diffX = touchStartX - touchEndX;
                const diffY = touchStartY - touchEndY;
                
                // 水平滑动距离大于垂直滑动距离，且滑动距离足够大
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        // 向左滑动，显示下一张
                        showNextImage();
                    } else {
                        // 向右滑动，显示上一张
                        showPreviousImage();
                    }
                }
                
                touchStartX = 0;
                touchStartY = 0;
            }, { passive: true });
            
            // 键盘事件
            document.addEventListener('keydown', handleKeyPress);
        }
        
        // 移除预览事件监听器
        function removePreviewEventListeners() {
            const modal = document.getElementById('imagePreviewModal');
            modal.removeEventListener('wheel', handleWheelScroll);
            document.removeEventListener('keydown', handleKeyPress);
        }
        
        // 处理滚轮滚动
        function handleWheelScroll(e) {
            e.preventDefault();
            
            if (currentPreviewFiles.length <= 1) return;
            
            if (e.deltaY > 0) {
                // 向下滚动，显示下一张
                showNextImage();
            } else {
                // 向上滚动，显示上一张
                showPreviousImage();
            }
        }
        
        // 处理键盘按键
        function handleKeyPress(e) {
            if (currentPreviewFiles.length <= 1) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    showPreviousImage();
                    break;
                case 'ArrowRight':
                    showNextImage();
                    break;
            }
        }
        
        // 显示下一张图片
        function showNextImage() {
            if (currentPreviewFiles.length <= 1) return;
            
            currentPreviewIndex = (currentPreviewIndex + 1) % currentPreviewFiles.length;
            const file = currentPreviewFiles[currentPreviewIndex];
            showFavoritesPreviewImage(file.url, file.name, currentPreviewIndex);
        }
        
        // 显示上一张图片
        function showPreviousImage() {
            if (currentPreviewFiles.length <= 1) return;
            
            currentPreviewIndex = currentPreviewIndex === 0 ? 
                currentPreviewFiles.length - 1 : currentPreviewIndex - 1;
            const file = currentPreviewFiles[currentPreviewIndex];
            showFavoritesPreviewImage(file.url, file.name, currentPreviewIndex);
        }
        
        // 关闭媒体模态框
        function closeMediaModal() {
            const modal = document.getElementById('mediaModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
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
                hideImagePreview();
            }
        });
        
        // 点击图片预览模态框外部关闭
        document.getElementById('imagePreviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideImagePreview();
            }
        });
    </script>
    <?php $footerVariant = 'default'; include BASE_PATH . '/app/views/components/site-footer.php'; ?>
</body>
</html>
