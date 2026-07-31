<?php
/**
 * 用户手册直接入口（不依赖 rewrite，避免路由未命中时点击无反应）
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/session.php';
ensureSessionStarted();
require_once BASE_PATH . '/lang/Language.php';

define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');

include VIEW_PATH . '/help/manual.php';
