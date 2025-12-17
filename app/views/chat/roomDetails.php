<?php
// roomDetails.php - 房间详细信息页面

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
    <title><?php echo str_replace('{name}', htmlspecialchars($roomInfo['display_name']), __('room_details_title')); ?></title>
    <link rel="stylesheet" href="/CHATTING/public/css/style.css">
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
        
        .room-details-container {
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8f4ff 0%, #ffffff 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .room-details-container {
                margin: 0;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .room-header {
                flex-direction: column;
                text-align: center;
            }
            
            .room-avatar-large {
                margin-right: 0;
                margin-bottom: 20px;
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            
            .room-name {
                font-size: 1.8rem;
            }
            
            .room-type {
                font-size: 1rem;
                padding: 6px 12px;
            }
            
            .back-btn {
                width: 40px;
                height: 40px;
                top: 15px;
                right: 15px;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .info-section h3 {
                font-size: 1.3rem;
                margin-bottom: 15px;
            }
            
            .info-item {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .info-label {
                font-size: 0.85rem;
                margin-bottom: 5px;
            }
            
            .info-value {
                font-size: 1rem;
            }
            
            .btn {
                min-height: 44px;
                padding: 12px 20px;
                font-size: 16px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-group .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .header-section {
                padding: 20px 15px;
            }
            
            .room-avatar-large {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .room-name {
                font-size: 1.5rem;
            }
            
            .room-type {
                font-size: 0.9rem;
                padding: 5px 10px;
            }
            
            .content-section {
                padding: 20px 15px;
            }
            
            .info-section h3 {
                font-size: 1.2rem;
                margin-bottom: 12px;
            }
            
            .info-item {
                padding: 12px;
            }
            
            .info-label {
                font-size: 0.8rem;
            }
            
            .info-value {
                font-size: 0.95rem;
            }
            
            .btn {
                min-height: 42px;
                padding: 10px 16px;
                font-size: 16px;
            }
        }
        
        .room-details-container::before {
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
        
        .room-header {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .room-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a3c5c;
            font-size: 2.5rem;
            font-weight: bold;
            margin-right: 25px;
            position: relative;
            overflow: hidden;
            border: 4px solid rgba(168, 85, 247, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.15);
        }
        
        .room-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .room-title {
            flex: 1;
        }
        
        .room-name {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4a3c5c;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .room-type {
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
        
        .room-type.group {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.2);
            color: #059669;
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
        
        .group-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            background: #28a745;
            color: white;
            border-radius: 8px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .content-section {
            padding: 40px 30px;
        }
        
        .info-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4a3c5c;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #a855f7;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #a855f7, #8b5cf6);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .info-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f4ff 100%);
            padding: 20px;
            border-radius: 15px;
            border-left: 5px solid #a855f7;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.08), rgba(139, 92, 246, 0.08));
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.15);
        }
        
        .info-label {
            font-weight: 600;
            color: #a855f7;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }
        
        .members-section {
            margin-top: 25px;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #28a745;
            position: relative;
            overflow: hidden;
        }
        
        .member-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
            border-radius: 50%;
            transform: translate(25px, -25px);
        }
        
        .member-item:hover {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }
        
        .member-status {
            font-size: 0.8rem;
            color: #666;
        }
        
        .member-role {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .member-role.admin {
            background: #28a745;
        }
        
        .member-role.creator {
            background: #dc3545;
        }
        
        .recent-messages {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }
        
        .message-item {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-sender {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .message-content {
            color: #666;
            font-size: 0.9rem;
            margin-top: 2px;
        }
        
        .message-time {
            color: #999;
            font-size: 0.8rem;
            margin-top: 2px;
        }
        
        .recent-messages {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .message-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-item:hover {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin: 0 -15px;
        }
        
        .message-sender {
            font-weight: 600;
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .message-content {
            color: #2c3e50;
            font-size: 0.95rem;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .message-time {
            color: #95a5a6;
            font-size: 0.8rem;
            font-style: italic;
        }
        
        .empty-state {
            text-align: center;
            color: #95a5a6;
            padding: 60px 20px;
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
        
        /* 操作按钮样式 */
        .action-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-width: 160px;
            justify-content: center;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .action-btn:hover::before {
            left: 100%;
        }
        
        .action-btn .btn-icon {
            font-size: 1.2rem;
            z-index: 2;
            position: relative;
        }
        
        .action-btn .btn-text {
            z-index: 2;
            position: relative;
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }
        
        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
            background: linear-gradient(135deg, #ff5252 0%, #e53935 100%);
        }
        
        .clear-history-btn {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }
        
        .clear-history-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
            background: linear-gradient(135deg, #f57c00 0%, #ef6c00 100%);
        }
        
        .block-btn {
            background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 167, 38, 0.3);
        }
        
        .block-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 167, 38, 0.4);
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        }
        
        .nickname-btn {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
        }
        
        .nickname-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(23, 162, 184, 0.4);
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
        }
        
        .action-btn:active {
            transform: translateY(0);
        }
        
        /* 备注模态框样式 */
        .nickname-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: none;
        }
        
        .nickname-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.3s ease-out;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.2s ease;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .close-btn:hover {
            background: #e9ecef;
            color: #495057;
            transform: scale(1.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #17a2b8;
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
        }
        
        .form-control[readonly] {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .modal-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* 滚动条美化 */
        .recent-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .recent-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .recent-messages::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 3px;
        }
        
        .recent-messages::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .room-details-container {
                margin: 0;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .room-header {
                flex-direction: column;
                text-align: center;
            }
            
            .room-avatar-large {
                margin-right: 0;
                margin-bottom: 20px;
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            
            .room-name {
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
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .info-item {
                padding: 15px;
            }
            
            .member-item {
                padding: 15px;
            }
            
            .member-avatar {
                width: 40px;
                height: 40px;
                margin-right: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-btn {
                min-width: auto;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .room-name {
                font-size: 1.5rem;
            }
            
            .room-type {
                font-size: 1rem;
                padding: 6px 12px;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
            
            .info-item {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="room-details-container">
        <!-- 返回按钮 -->
        <a href="/CHATTING/dashboard" class="back-btn" title="<?php echo __('room_details_back_dashboard'); ?>"></a>
        
        <!-- 房间头部信息 -->
        <div class="header-section">
            <div class="room-header">
                    <div class="room-avatar-large">
                        <?php 
                        $roomAvatar = $roomInfo['avatar'] ?? null;
                        $avatarPath = BASE_PATH . '/public/uploads/avatars/' . $roomAvatar;
                        
                        if (!empty($roomAvatar) && 
                            $roomAvatar !== 'default_avatar.png' && 
                            $roomAvatar !== 'group_avatar.png' && 
                            file_exists($avatarPath)) {
                            echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($roomAvatar) . '" alt="' . __('room_details_avatar_alt') . '" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                            echo '<div style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; align-items:center; justify-content:center; font-size:2rem; font-weight:bold; border-radius:50%;">' . strtoupper(substr($roomInfo['display_name'], 0, 1)) . '</div>';
                        } else {
                            echo '<div style="width:100%; height:100%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:bold; border-radius:50%;">' . strtoupper(substr($roomInfo['display_name'], 0, 1)) . '</div>';
                        }
                        ?>
                        <?php if ($roomInfo['type'] === 'group'): ?>
                            <div class="group-badge">群</div>
                        <?php endif; ?>
                    </div>
                    <div class="room-title">
                        <div class="room-name"><?php echo htmlspecialchars($roomInfo['display_name']); ?></div>
                        <div class="room-type <?php echo $roomInfo['type']; ?>">
                            <?php echo $roomInfo['type'] === 'group' ? __('room_details_group_chat') : __('room_details_private_chat'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 内容区域 -->
        <div class="content-section">
            <!-- 基本信息 -->
            <div class="info-section">
                    <h3 class="section-title"><?php echo __('room_details_basic_info'); ?></h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><?php echo __('room_details_chat_id'); ?></div>
                            <div class="info-value">#<?php echo $roomInfo['id']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><?php echo __('room_details_created_time'); ?></div>
                            <div class="info-value"><?php echo date('Y-m-d H:i:s', strtotime($roomInfo['created_at'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><?php echo __('room_details_last_message'); ?></div>
                            <div class="info-value"><?php echo !empty($roomInfo['last_message']) ? htmlspecialchars($roomInfo['last_message']) : __('room_details_no_message'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><?php echo __('room_details_last_activity'); ?></div>
                            <div class="info-value"><?php echo !empty($roomInfo['last_message_time']) ? date('Y-m-d H:i:s', strtotime($roomInfo['last_message_time'])) : __('room_details_no_activity'); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- 群组成员（仅群组显示） -->
                <?php if ($roomInfo['type'] === 'group' && !empty($members)): ?>
                <div class="info-section">
                    <h3 class="section-title"><?php echo __('room_details_group_members'); ?> (<?php echo count($members); ?><?php echo __('room_details_member_count'); ?>)</h3>
                    <div class="members-section">
                        <?php foreach ($members as $member): ?>
                        <div class="member-item">
                            <div class="member-avatar">
                                <?php 
                                $memberAvatar = $member['avatar'] ?? null;
                                $memberAvatarPath = BASE_PATH . '/public/uploads/avatars/' . $memberAvatar;
                                
                                if (!empty($memberAvatar) && 
                                    $memberAvatar !== 'default_avatar.png' && 
                                    file_exists($memberAvatarPath)) {
                                    echo '<img src="/CHATTING/public/uploads/avatars/' . htmlspecialchars($memberAvatar) . '" alt="' . __('room_details_avatar_alt') . '" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                                    echo '<div style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; align-items:center; justify-content:center; font-size:1rem; font-weight:bold; border-radius:50%;">' . strtoupper(substr($member['username'], 0, 1)) . '</div>';
                                } else {
                                    echo '<div style="width:100%; height:100%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:bold; border-radius:50%;">' . strtoupper(substr($member['username'], 0, 1)) . '</div>';
                                }
                                ?>
                            </div>
                            <div class="member-info">
                                <div class="member-name"><?php echo htmlspecialchars($member['username']); ?></div>
                                <div class="member-status">
                                    <?php 
                                    if ($member['status'] === 'online') {
                                        echo '🟢 ' . __('room_details_member_online');
                                    } elseif ($member['status'] === 'away') {
                                        echo '🟡 ' . __('room_details_member_away');
                                    } else {
                                        echo '⚫ ' . __('room_details_member_offline');
                                    }
                                    ?>
                                    • <?php echo __('room_details_join_time'); ?>: <?php echo date('Y-m-d', strtotime($member['joined_at'])); ?>
                                </div>
                            </div>
                            <?php if (!empty($member['role'])): ?>
                                <div class="member-role <?php echo $member['role']; ?>">
                                    <?php 
                                    if ($member['role'] === 'creator') {
                                        echo __('room_details_group_owner');
                                    } elseif ($member['role'] === 'admin') {
                                        echo __('room_details_group_admin');
                                    } else {
                                        echo __('room_details_group_member');
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 聊天记录操作 -->
                <div class="info-section">
                    <h3 class="section-title"><?php echo __('room_details_chat_operations'); ?></h3>
                    <div class="action-buttons">
                        <button class="action-btn clear-history-btn" onclick="clearChatHistory(<?php echo $roomInfo['id']; ?>, '<?php echo $roomInfo['type']; ?>')">
                            <span class="btn-icon">🗑️</span>
                            <span class="btn-text"><?php echo __('room_details_clear_history'); ?></span>
                        </button>
                    </div>
                </div>
                
                <!-- 操作按钮（仅私聊显示） -->
                <?php if ($roomInfo['type'] === 'private'): ?>
                <div class="info-section">
                    <h3 class="section-title"><?php echo __('room_details_friend_operations'); ?></h3>
                    <div class="action-buttons">
                        <button class="action-btn nickname-btn" onclick="showNicknameModal(<?php echo $roomInfo['id']; ?>, '<?php echo htmlspecialchars($roomInfo['display_name']); ?>', '<?php echo htmlspecialchars($roomInfo['nickname'] ?? ''); ?>')">
                            <span class="btn-icon">✏️</span>
                            <span class="btn-text"><?php echo !empty($roomInfo['nickname']) ? __('room_details_edit_nickname') : __('room_details_set_nickname'); ?></span>
                        </button>
                        <button class="action-btn block-btn" onclick="blockFriend(<?php echo $roomInfo['id']; ?>)">
                            <span class="btn-icon">🚫</span>
                            <span class="btn-text"><?php echo __('room_details_block_friend'); ?></span>
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteFriend(<?php echo $roomInfo['id']; ?>)">
                            <span class="btn-icon">🗑️</span>
                            <span class="btn-text"><?php echo __('room_details_delete_friend'); ?></span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 备注设置模态框 -->
    <div class="nickname-modal" id="nicknameModal">
        <div class="modal-overlay" onclick="closeNicknameModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3><?php echo __('room_details_nickname_modal_title'); ?></h3>
                    <button class="close-btn" onclick="closeNicknameModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="friendName"><?php echo __('room_details_friend_username'); ?>：</label>
                        <input type="text" id="friendName" readonly class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="nicknameInput"><?php echo __('room_details_nickname_label'); ?>：</label>
                        <input type="text" id="nicknameInput" class="form-control" placeholder="<?php echo __('room_details_nickname_placeholder'); ?>" maxlength="50">
                    </div>
                    <div class="form-group">
                        <small class="form-text text-muted"><?php echo __('room_details_nickname_hint'); ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeNicknameModal()"><?php echo __('cancel'); ?></button>
                    <button class="btn btn-primary" onclick="saveNickname()"><?php echo __('save'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 删除好友功能
        function deleteFriend(roomId) {
            if (confirm('<?php echo __('room_details_confirm_delete_friend'); ?>')) {
                fetch('/CHATTING/chat/deleteFriend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `room_id=${roomId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo __('room_details_friend_deleted'); ?>');
                        // 跳转回仪表板
                        window.location.href = '/CHATTING/dashboard';
                    } else {
                        alert('<?php echo __('room_details_delete_failed'); ?>: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo __('room_details_delete_failed_retry'); ?>');
                });
            }
        }
        
        // 清空聊天记录功能
        function clearChatHistory(roomId, roomType) {
            console.log('clearChatHistory called with:', { roomId, roomType });
            
            const roomTypeText = roomType === 'group' ? '<?php echo __('room_details_group_chat'); ?>' : '<?php echo __('room_details_private_chat'); ?>';
            if (confirm('<?php echo __('room_details_confirm_clear_history'); ?>'.replace('{type}', roomTypeText))) {
                console.log('User confirmed clear history operation');
                
                // 显示加载状态
                const button = event.target.closest('.action-btn');
                const originalText = button.querySelector('.btn-text').textContent;
                button.querySelector('.btn-text').textContent = '<?php echo __('room_details_clearing'); ?>';
                button.disabled = true;
                
                console.log('Sending request to clear history...');
                console.log('Request URL:', '/CHATTING/chat/clearHistory');
                console.log('Request data:', { room_id: roomId, room_type: roomType });
                
                fetch('/CHATTING/chat/clearHistory', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `room_id=${roomId}&room_type=${roomType}`
                })
                .then(response => {
                    console.log('Response received:', response);
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        console.log('Clear history successful');
                        alert('<?php echo __('room_details_history_cleared'); ?>');
                        // 跳转回聊天页面
                        if (roomType === 'group') {
                            window.location.href = `/CHATTING/chat/group?id=${roomId}`;
                        } else {
                            window.location.href = `/CHATTING/chat/room?id=${roomId}`;
                        }
                    } else {
                        console.error('Clear history failed:', data.message);
                        alert('<?php echo __('room_details_clear_failed'); ?>: ' + data.message);
                        // 恢复按钮状态
                        button.querySelector('.btn-text').textContent = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    alert('<?php echo __('room_details_clear_failed_retry'); ?>');
                    // 恢复按钮状态
                    button.querySelector('.btn-text').textContent = originalText;
                    button.disabled = false;
                });
            } else {
                console.log('User cancelled clear history operation');
            }
        }
        
        // 封锁好友功能
        function blockFriend(roomId) {
            if (confirm('<?php echo __('room_details_confirm_block_friend'); ?>')) {
                fetch('/CHATTING/chat/blockFriend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `room_id=${roomId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo __('room_details_friend_blocked'); ?>');
                        // 跳转回仪表板
                        window.location.href = '/CHATTING/dashboard';
                    } else {
                        alert('<?php echo __('room_details_block_failed'); ?>: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo __('room_details_block_failed_retry'); ?>');
                });
            }
        }
        
        // 显示备注设置模态框
        function showNicknameModal(roomId, friendName, currentNickname) {
            const modal = document.getElementById('nicknameModal');
            const friendNameInput = document.getElementById('friendName');
            const nicknameInput = document.getElementById('nicknameInput');
            
            // 设置当前值
            friendNameInput.value = friendName;
            nicknameInput.value = currentNickname;
            
            // 显示模态框
            modal.classList.add('show');
            
            // 聚焦到备注输入框
            setTimeout(() => {
                nicknameInput.focus();
                nicknameInput.select();
            }, 300);
            
            // 存储房间ID用于保存
            modal.dataset.roomId = roomId;
        }
        
        // 关闭备注设置模态框
        function closeNicknameModal() {
            const modal = document.getElementById('nicknameModal');
            modal.classList.remove('show');
        }
        
        // 保存备注
        function saveNickname() {
            const modal = document.getElementById('nicknameModal');
            const roomId = modal.dataset.roomId;
            const nickname = document.getElementById('nicknameInput').value.trim();
            
            // 显示加载状态
            const saveBtn = modal.querySelector('.btn-primary');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = '<?php echo __('room_details_saving'); ?>';
            saveBtn.disabled = true;
            
            fetch('/CHATTING/chat/setFriendNickname', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `room_id=${roomId}&nickname=${encodeURIComponent(nickname)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo __('room_details_nickname_saved'); ?>');
                    // 刷新页面以显示新的备注
                    window.location.reload();
                } else {
                    alert('<?php echo __('room_details_nickname_failed'); ?>: ' + data.message);
                    // 恢复按钮状态
                    saveBtn.textContent = originalText;
                    saveBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo __('room_details_nickname_failed_retry'); ?>');
                // 恢复按钮状态
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        }
        
        // 键盘支持
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeNicknameModal();
            }
            if (event.key === 'Enter' && document.getElementById('nicknameModal').classList.contains('show')) {
                saveNickname();
            }
        });
    </script>
</body>
</html>
