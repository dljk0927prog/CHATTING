<?php
// message-bubble-bar.php - 可复用的消息气泡栏组件
// 需要传入的变量: $message, $isOwnMessage, $canRecall, $messageAge

// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();
?>

<style>
    /* 消息气泡栏样式 */
    .message-bubble-bar {
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        border-radius: 25px;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        pointer-events: none; /* 防止干扰鼠标事件 */
    }
    
    .message-bubble-bar.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto; /* 显示时允许交互 */
    }

    .bubble-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        width: 36px;
        height: 36px;
        position: relative;
    }

    .bubble-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.1);
    }

    .bubble-btn .icon {
        font-size: 16px;
    }

    .bubble-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        margin-bottom: 5px;
        z-index: 1001;
    }

    .bubble-btn:hover .bubble-tooltip {
        opacity: 1;
        visibility: visible;
    }
    
    /* 收藏按钮状态样式 */
    .bubble-btn.favorited {
        color: #ffd700;
        background: rgba(255, 215, 0, 0.2);
    }
    
    .bubble-btn.favorited:hover {
        background: rgba(255, 215, 0, 0.3);
    }
    
    /* 置顶按钮状态样式 */
    .bubble-btn.pinned {
        color: #ff6b6b;
        background: rgba(255, 107, 107, 0.2);
    }
    
    .bubble-btn.pinned:hover {
        background: rgba(255, 107, 107, 0.3);
    }

    .bubble-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.8);
    }
    
    /* 确保消息容器有相对定位 */
    .message {
        position: relative;
    }
    
    .message-content {
        position: relative;
    }

    /* 移动端优化 */
    @media (max-width: 768px) {
        .message-bubble-bar {
            gap: 8px;
            padding: 6px 12px;
            top: calc(100% + 6px);
            /* 移动端长按显示时的特殊样式 */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .bubble-btn {
            width: 32px;
            height: 32px;
            font-size: 14px;
            /* 移动端按钮优化 */
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        
        .bubble-btn:active {
            transform: scale(0.95);
            background: rgba(255, 255, 255, 0.3);
        }
    }

    @media (max-width: 480px) {
        .message-bubble-bar {
            gap: 6px;
            padding: 4px 8px;
            top: calc(100% + 4px);
            /* 小屏幕设备优化 */
            left: 50%;
            transform: translateX(-50%);
            max-width: calc(100vw - 20px);
            overflow-x: auto;
            white-space: nowrap;
        }
        
        .bubble-btn {
            width: 28px;
            height: 28px;
            font-size: 12px;
            flex-shrink: 0;
        }
    }
    
    /* 移动端长按时的视觉反馈 */
    .message.long-press-active {
        transform: scale(1.02);
        transition: transform 0.1s ease;
    }
    
    .message.long-press-active .message-bubble-bar {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateX(-50%) scale(1.05);
    }
    
    /* 防止移动端长按时选中文本和右键菜单 */
    .message {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
        -webkit-tap-highlight-color: transparent;
        /* 禁用右键菜单 */
        -webkit-context-menu: none;
        -moz-context-menu: none;
        -ms-context-menu: none;
        context-menu: none;
    }
    
    /* 确保消息内容也禁用右键菜单 */
    .message * {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
        -webkit-context-menu: none;
        -moz-context-menu: none;
        -ms-context-menu: none;
        context-menu: none;
    }
    
    /* 移动端气泡栏显示动画优化 */
    @media (max-width: 768px) {
        .message-bubble-bar.show {
            animation: mobileBubbleShow 0.2s ease-out;
        }
    }
    
    @keyframes mobileBubbleShow {
        from {
            opacity: 0;
            transform: translateX(-50%) scale(0.8);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) scale(1);
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
    }
    
    .recipient-name {
        font-weight: 500;
        color: #333;
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
    
    /* 错误提示样式 */
    .no-recipients {
        text-align: center;
        color: #666;
        padding: 20px;
        font-style: italic;
    }
    
    .message-preview {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }
    
    /* 通知动画 */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>

<!-- 消息气泡栏 -->
<div class="message-bubble-bar" id="bubble-<?php echo $message['id']; ?>" 
     onmouseenter="keepBubbleVisible(this)" 
     onmouseleave="hideBubbleOnLeave(this)">
    <?php 
    $isOwnMessage = $message['sender_id'] == $_SESSION['user_id'];
    $messageAge = time() - strtotime($message['created_at']);
    $canRecall = $isOwnMessage && $messageAge <= 120; // 2分钟内可以撤回
    ?>
    
    <?php if ($isOwnMessage): ?>
        <!-- 撤回/删除按钮 -->
        <button class="bubble-btn" 
                onclick="<?php echo $canRecall ? 'recallMessage' : 'deleteMessage'; ?>(<?php echo $message['id']; ?>)"
                title="<?php echo $canRecall ? '撤回消息' : '删除消息'; ?>">
            <?php echo $canRecall ? '↩️' : '🗑️'; ?>
            <div class="bubble-tooltip"><?php echo $canRecall ? '撤回' : '删除'; ?></div>
        </button>
        
        <!-- 修改按钮（仅文本消息） -->
        <?php if ($message['message_type'] === 'text' && empty($message['file_path'])): ?>
            <button class="bubble-btn" 
                    onclick="editMessage(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['content'], ENT_QUOTES); ?>')"
                    title="修改消息">
                ✏️
                <div class="bubble-tooltip">修改</div>
            </button>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- 收藏按钮 -->
    <button class="bubble-btn" 
            onclick="toggleFavorite(<?php echo $message['id']; ?>)"
            title="收藏消息">
        ⭐
        <div class="bubble-tooltip">收藏</div>
    </button>
    
    <!-- 置顶按钮 -->
    <button class="bubble-btn <?php echo isset($isPinnedMessage) && $isPinnedMessage ? 'pinned' : ''; ?>" 
            onclick="togglePin(<?php echo $message['id']; ?>)"
            title="<?php echo isset($isPinnedMessage) && $isPinnedMessage ? '取消置顶' : '置顶消息'; ?>"
            data-pinned="<?php echo isset($isPinnedMessage) && $isPinnedMessage ? 'true' : 'false'; ?>">
        📌
        <div class="bubble-tooltip"><?php echo isset($isPinnedMessage) && $isPinnedMessage ? '取消置顶' : '置顶'; ?></div>
    </button>
    
    <!-- 引用按钮 -->
    <button class="bubble-btn" 
            onclick="quoteMessage(<?php echo $message['id']; ?>)"
            title="引用消息">
        💬
        <div class="bubble-tooltip">引用</div>
    </button>
    
    <!-- 转发按钮 -->
    <button class="bubble-btn" 
            onclick="forwardMessage(<?php echo $message['id']; ?>)"
            title="转发消息">
        📤
        <div class="bubble-tooltip">转发</div>
    </button>
</div>

<script>
// 消息气泡栏功能脚本
    
    // 鼠标进入气泡栏时保持显示
    function keepBubbleVisible(bubbleElement) {
        console.log('鼠标进入气泡栏');
        bubbleElement.classList.add('show');
    }
    
    // 鼠标离开气泡栏时延迟隐藏
    function hideBubbleOnLeave(bubbleElement) {
        console.log('鼠标离开气泡栏');
        setTimeout(() => {
            if (!bubbleElement.matches(':hover')) {
                console.log('隐藏气泡栏');
                bubbleElement.classList.remove('show');
            }
        }, 500);
    }
    
    // 消息气泡栏功能函数现在在chat-common.js中统一处理
    
    // showNotification函数已在chat-common.js中定义，无需重复定义
</script>
