<?php
/**
 * 语言切换控制器
 */
// 确保BASE_PATH已定义
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}
require_once BASE_PATH . '/lang/Language.php';

class LanguageController {
    
    /**
     * 切换语言 (路由: language/switch)
     */
    public function switch() {
        // 确保会话已启动
        if (session_status() == PHP_SESSION_NONE) {
            require_once BASE_PATH . '/core/session.php';
            ensureSessionStarted();
        }
        
        // 获取语言参数
        $language = $_GET['lang'] ?? $_POST['lang'] ?? 'zh';
        
        // 验证语言代码
        $supportedLanguages = ['zh', 'en', 'ms'];
        if (!in_array($language, $supportedLanguages)) {
            $language = 'zh'; // 默认中文
        }
        
        // 使用Language类设置语言
        $langInstance = Language::getInstance();
        $result = $langInstance->setLanguage($language);
        
        // 返回JSON响应
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode([
                'success' => true,
                'language' => $language,
                'message' => 'Language switched successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'language' => $language,
                'message' => 'Failed to switch language'
            ]);
        }
        exit;
    }
    
    /**
     * 获取当前语言信息
     */
    public function getCurrentLanguage() {
        // 确保会话已启动
        if (session_status() == PHP_SESSION_NONE) {
            require_once BASE_PATH . '/core/session.php';
            ensureSessionStarted();
        }
        
        $currentLanguage = $_SESSION['language'] ?? 'zh';
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'language' => $currentLanguage,
            'supported_languages' => ['zh', 'en', 'ms']
        ]);
        exit;
    }
    
    /**
     * 获取所有支持的语言
     */
    public function getSupportedLanguages() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'languages' => [
                'zh' => '中文',
                'en' => 'English',
                'ms' => 'Bahasa Melayu'
            ]
        ]);
        exit;
    }
}
