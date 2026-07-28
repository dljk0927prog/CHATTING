<?php
// message-bubble-bar.php - 消息气泡操作栏（仅 HTML，样式见 public/css/message-bubble-bar.css）
if (!function_exists('__')) {
    require_once BASE_PATH . '/lang/Language.php';
}

$isOwnMessage = $message['sender_id'] == $_SESSION['user_id'];
$messageAge = time() - strtotime($message['created_at']);
$canRecall = $isOwnMessage && $messageAge <= 120;
$isTextOnly = ($message['message_type'] ?? 'text') === 'text' && empty($message['file_path']);
$isPinned = !empty($isPinnedMessage) || !empty($message['is_pinned']);
$recallAction = ($message['message_type'] ?? 'text') === 'text' && $canRecall ? 'recallMessage' : 'deleteMessage';
$recallLabel = ($message['message_type'] ?? 'text') === 'text' && $canRecall ? __('message_recall') : __('message_delete');
$pinLabel = $isPinned ? __('message_unpin') : __('message_pin');
?>
<div class="message-bubble-bar" id="bubble-<?php echo (int)$message['id']; ?>"
     onmouseenter="keepBubbleVisible(this)"
     onmouseleave="hideBubbleOnLeave(this)">
    <?php if ($isOwnMessage): ?>
        <button type="button" class="bubble-btn"
                onclick="<?php echo $recallAction; ?>(<?php echo (int)$message['id']; ?>)"
                aria-label="<?php echo htmlspecialchars($recallLabel); ?>">
            <span class="bubble-icon" aria-hidden="true"><?php echo ($message['message_type'] ?? 'text') === 'text' && $canRecall ? '↩️' : '🗑️'; ?></span>
            <span class="bubble-tooltip"><?php echo htmlspecialchars($recallLabel); ?></span>
        </button>
        <?php if ($isTextOnly): ?>
        <button type="button" class="bubble-btn"
                onclick="editMessage(<?php echo (int)$message['id']; ?>, '<?php echo htmlspecialchars($message['content'], ENT_QUOTES); ?>')"
                aria-label="<?php echo htmlspecialchars(__('message_edit')); ?>">
            <span class="bubble-icon" aria-hidden="true">✏️</span>
            <span class="bubble-tooltip"><?php echo htmlspecialchars(__('message_edit')); ?></span>
        </button>
        <?php endif; ?>
    <?php endif; ?>
    <button type="button" class="bubble-btn"
            onclick="toggleFavorite(<?php echo (int)$message['id']; ?>)"
            aria-label="<?php echo htmlspecialchars(__('message_favorite_short')); ?>">
        <span class="bubble-icon" aria-hidden="true">⭐</span>
        <span class="bubble-tooltip"><?php echo htmlspecialchars(__('message_favorite_short')); ?></span>
    </button>
    <button type="button" class="bubble-btn<?php echo $isPinned ? ' pinned' : ''; ?>"
            onclick="togglePin(<?php echo (int)$message['id']; ?>)"
            aria-label="<?php echo htmlspecialchars($pinLabel); ?>"
            data-pinned="<?php echo $isPinned ? 'true' : 'false'; ?>">
        <span class="bubble-icon" aria-hidden="true">📌</span>
        <span class="bubble-tooltip"><?php echo htmlspecialchars($pinLabel); ?></span>
    </button>
    <button type="button" class="bubble-btn"
            onclick="quoteMessage(<?php echo (int)$message['id']; ?>)"
            aria-label="<?php echo htmlspecialchars(__('quote_message')); ?>">
        <span class="bubble-icon" aria-hidden="true">💬</span>
        <span class="bubble-tooltip"><?php echo htmlspecialchars(__('quote_label')); ?></span>
    </button>
    <button type="button" class="bubble-btn"
            onclick="forwardMessage(<?php echo (int)$message['id']; ?>)"
            aria-label="<?php echo htmlspecialchars(__('message_share')); ?>">
        <span class="bubble-icon" aria-hidden="true">📤</span>
        <span class="bubble-tooltip"><?php echo htmlspecialchars(__('message_share')); ?></span>
    </button>
</div>
