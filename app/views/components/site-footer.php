<?php
/**
 * 站点页脚（兼容 iFastNet 等共享主机：内联关键样式，不依赖 @import / flexbox）
 * 可选变量 $footerVariant: default | sidebar | auth
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(__DIR__))));
}
if (!function_exists('__')) {
    require_once BASE_PATH . '/lang/Language.php';
}
$footerVariant = $footerVariant ?? 'default';
$variantClass = 'site-footer--' . preg_replace('/[^a-z]/', '', $footerVariant);
$copyrightText = function_exists('__')
    ? __('site_copyright')
    : 'Copyright © 2026 Desmond Liew. All Rights Reserved.';

$inlineStyle = 'text-align:center;width:100%;margin:0;box-sizing:border-box;';
if ($footerVariant === 'auth') {
    $inlineStyle .= 'position:fixed;left:0;right:0;bottom:0;padding:12px 16px;color:#fff;font-size:12px;line-height:1.5;z-index:100;';
} elseif ($footerVariant === 'sidebar') {
    $inlineStyle .= 'color:#444;font-size:12px;padding:12px 16px;border-top:1px solid #e1e5e9;background:#fff;';
}
?>
<footer class="site-footer <?php echo htmlspecialchars($variantClass); ?>" style="<?php echo $inlineStyle; ?>">
    <p style="margin:0;"><?php echo htmlspecialchars($copyrightText); ?></p>
</footer>
