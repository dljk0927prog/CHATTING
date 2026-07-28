<?php
/**
 * 统一 Session 初始化：使用项目内可写目录，避免 C:\xampp\tmp 权限问题
 */
function ensureSessionStarted() {
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
    $sessionPath = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';

    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0755, true);
    }

    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    session_start();
}
