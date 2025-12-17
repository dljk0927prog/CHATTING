<?php
/**
 * 多语言支持类
 * 支持中文、英文、马来文
 */
class Language {
    private static $instance = null;
    private $currentLanguage = 'zh';
    private $translations = [];
    private $supportedLanguages = ['zh', 'en', 'ms'];
    
    private function __construct() {
        // 确保会话已启动
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->initializeLanguage();
    }
    
    /**
     * 获取单例实例
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 初始化语言设置
     */
    private function initializeLanguage() {
        // 从会话中获取语言设置，默认为中文
        if (isset($_SESSION['language']) && in_array($_SESSION['language'], $this->supportedLanguages)) {
            $this->currentLanguage = $_SESSION['language'];
        } else {
            // 尝试从浏览器语言检测
            $this->currentLanguage = $this->detectBrowserLanguage();
            $_SESSION['language'] = $this->currentLanguage;
        }
        
        $this->loadTranslations();
    }
    
    /**
     * 检测浏览器语言
     */
    private function detectBrowserLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($languages as $lang) {
                $lang = trim(explode(';', $lang)[0]);
                $lang = strtolower(substr($lang, 0, 2));
                
                if (in_array($lang, $this->supportedLanguages)) {
                    return $lang;
                }
            }
        }
        return 'zh'; // 默认中文
    }
    
    /**
     * 加载翻译文件
     */
    private function loadTranslations() {
        $langFile = BASE_PATH . '/lang/' . $this->currentLanguage . '.php';
        if (file_exists($langFile)) {
            $this->translations = include $langFile;
        } else {
            // 如果文件不存在，加载默认的中文文件
            $defaultFile = BASE_PATH . '/lang/zh.php';
            if (file_exists($defaultFile)) {
                $this->translations = include $defaultFile;
            }
        }
    }
    
    /**
     * 获取翻译文本
     */
    public function get($key, $default = null) {
        if (isset($this->translations[$key])) {
            return $this->translations[$key];
        }
        
        // 如果找不到翻译，返回默认值或键名
        return $default !== null ? $default : $key;
    }
    
    /**
     * 设置当前语言
     */
    public function setLanguage($language) {
        if (in_array($language, $this->supportedLanguages)) {
            $this->currentLanguage = $language;
            $_SESSION['language'] = $language;
            $this->loadTranslations();
            return true;
        }
        return false;
    }
    
    /**
     * 获取当前语言
     */
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }
    
    /**
     * 获取支持的语言列表
     */
    public function getSupportedLanguages() {
        return $this->supportedLanguages;
    }
    
    /**
     * 获取语言名称
     */
    public function getLanguageName($code) {
        $names = [
            'zh' => '中文',
            'en' => 'English',
            'ms' => 'Bahasa Melayu'
        ];
        return isset($names[$code]) ? $names[$code] : $code;
    }
    
    /**
     * 获取所有语言的名称
     */
    public function getAllLanguageNames() {
        $names = [];
        foreach ($this->supportedLanguages as $code) {
            $names[$code] = $this->getLanguageName($code);
        }
        return $names;
    }
    
    /**
     * 格式化时间
     */
    public function formatTime($timestamp) {
        $now = time();
        $diff = $now - $timestamp;
        
        if ($diff < 60) {
            return $this->get('time_just_now');
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' ' . $this->get('time_minutes_ago');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' ' . $this->get('time_hours_ago');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' ' . $this->get('time_days_ago');
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' ' . $this->get('time_weeks_ago');
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' ' . $this->get('time_months_ago');
        } else {
            $years = floor($diff / 31536000);
            return $years . ' ' . $this->get('time_years_ago');
        }
    }
    
    /**
     * 格式化文件大小
     */
    public function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' ' . $this->get('file_size_gb');
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' ' . $this->get('file_size_mb');
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' ' . $this->get('file_size_kb');
        } else {
            return $bytes . ' ' . $this->get('file_size_bytes');
        }
    }
}

/**
 * 全局翻译函数
 */
function __($key, $default = null) {
    return Language::getInstance()->get($key, $default);
}

/**
 * 设置语言函数
 */
function setLanguage($language) {
    return Language::getInstance()->setLanguage($language);
}

/**
 * 获取当前语言函数
 */
function getCurrentLanguage() {
    return Language::getInstance()->getCurrentLanguage();
}

/**
 * 格式化时间函数
 */
function formatTime($timestamp) {
    return Language::getInstance()->formatTime($timestamp);
}

/**
 * 格式化文件大小函数
 */
function formatFileSize($bytes) {
    return Language::getInstance()->formatFileSize($bytes);
}
