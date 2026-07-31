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
        @mkdir($sessionPath, 0777, true);
    }

    $savePath = null;
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        $savePath = $sessionPath;
    } else {
        $fallback = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'chat_system_sessions';
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0777, true);
        }
        if (is_dir($fallback) && is_writable($fallback)) {
            $savePath = $fallback;
        }
    }

    if ($savePath !== null) {
        session_save_path($savePath);
        // 若当前 cookie 对应的 sess 文件不可写，换新 ID 避免 Permission denied 刷屏
        if (isset($_COOKIE[session_name()])) {
            $sid = preg_replace('/[^a-zA-Z0-9,-]/', '', (string) $_COOKIE[session_name()]);
            if ($sid !== '') {
                $sessFile = $savePath . DIRECTORY_SEPARATOR . 'sess_' . $sid;
                if (is_file($sessFile) && !is_writable($sessFile)) {
                    @unlink($sessFile);
                    session_id(bin2hex(random_bytes(16)));
                }
            }
        }
    }

    $started = false;
    set_error_handler(static function ($severity, $message) {
        if ($severity === E_WARNING && (
            strpos($message, 'session_start') !== false
            || strpos($message, 'Failed to read session data') !== false
        )) {
            return true;
        }
        return false;
    });
    try {
        $started = session_start();
    } finally {
        restore_error_handler();
    }

    if (!$started && session_status() === PHP_SESSION_NONE) {
        session_id(bin2hex(random_bytes(16)));
        @session_start();
    }
}
