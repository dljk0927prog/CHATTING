<?php
if (!function_exists('__')) {
    require_once BASE_PATH . '/lang/Language.php';
}
$chatI18nKeys = [
    'chat_send_message', 'chat_type_message', 'message_input_placeholder',
    'message_recalled_label', 'message_recalled_desc', 'file_message_text',
    'message_files_count', 'file_type_with_ext', 'file_preview', 'voice_preview',
    'image_preview_title', 'max_files_alert', 'files_selected', 'total_size_mb',
    'audio_not_supported', 'download', 'confirm_recall', 'recall_success',
    'recall_failed', 'recall_failed_retry', 'confirm_delete_message',
    'delete_success', 'delete_failed', 'quote_label', 'quote_message',
    'unknown_user', 'avatar_alt_suffix', 'sending', 'send_failed',
    'file_send_success', 'file_send_failed', 'voice_send_success', 'voice_send_failed',
    'voice_message', 'image', 'video', 'file', 'message_image_alt',
    'message_recall', 'message_delete',     'message_edit', 'message_favorite_short',
    'message_pin', 'message_unpin', 'preview_image_alt', 'more_files_label',
    'message_edit_success', 'message_edit_failed', 'message_edit_failed_retry',
    'message_share',
];
$chatI18n = [];
foreach ($chatI18nKeys as $key) {
    $chatI18n[$key] = __($key);
}
?>
<script>window.chatI18n = <?php echo json_encode($chatI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
