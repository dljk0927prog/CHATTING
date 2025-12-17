<?php
// 语言切换器组件
// 需要先包含Language类
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();
$currentLang = $lang->getCurrentLanguage();
$supportedLanguages = $lang->getSupportedLanguages();
?>

<style>
    .language-switcher {
        position: relative;
        display: inline-block;
    }
    
    .language-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 120px;
        justify-content: center;
    }
    
    .language-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .language-button:active {
        transform: translateY(0);
    }
    
    .language-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        margin-top: 8px;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    .language-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .language-option {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #333;
        font-size: 14px;
        font-weight: 500;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }
    
    .language-option:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #e8f0ff 100%);
        color: #667eea;
    }
    
    .language-option.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .language-flag {
        width: 20px;
        height: 15px;
        margin-right: 8px;
        border-radius: 2px;
        object-fit: cover;
    }
    
    .language-icon {
        font-size: 16px;
        margin-right: 8px;
    }
    
    /* 移动端优化 */
    @media (max-width: 768px) {
        .language-button {
            padding: 6px 12px;
            font-size: 12px;
            min-width: 100px;
        }
        
        .language-option {
            padding: 10px 12px;
            font-size: 13px;
        }
        
        .language-flag {
            width: 18px;
            height: 13px;
        }
    }
</style>

<div class="language-switcher">
    <button class="language-button" onclick="toggleLanguageDropdown()">
        <span class="language-icon">🌐</span>
        <span id="current-language-text"><?php echo $lang->getLanguageName($currentLang); ?></span>
        <span style="margin-left: auto;">▼</span>
    </button>
    
    <div class="language-dropdown" id="language-dropdown">
        <?php foreach ($supportedLanguages as $code): ?>
            <button class="language-option <?php echo $code === $currentLang ? 'active' : ''; ?>" 
                    onclick="switchLanguage('<?php echo $code; ?>')">
                <span class="language-icon">
                    <?php 
                    switch($code) {
                        case 'zh': echo '🇨🇳'; break;
                        case 'en': echo '🇺🇸'; break;
                        case 'ms': echo '🇲🇾'; break;
                        default: echo '🌐';
                    }
                    ?>
                </span>
                <?php echo $lang->getLanguageName($code); ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function toggleLanguageDropdown() {
        const dropdown = document.getElementById('language-dropdown');
        dropdown.classList.toggle('show');
    }
    
    function switchLanguage(language) {
        // 显示加载状态
        const button = document.querySelector('.language-button');
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="language-icon">⏳</span>Loading...';
        button.disabled = true;
        
        // 发送AJAX请求切换语言
        fetch('/CHATTING/language/switch?lang=' + language, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 切换成功，刷新页面
                window.location.reload();
            } else {
                // 切换失败，恢复按钮状态
                button.innerHTML = originalText;
                button.disabled = false;
                alert('Language switch failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = originalText;
            button.disabled = false;
            alert('Network error occurred');
        });
    }
    
    // 点击外部关闭下拉菜单
    document.addEventListener('click', function(event) {
        const switcher = document.querySelector('.language-switcher');
        const dropdown = document.getElementById('language-dropdown');
        
        if (!switcher.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
    
    // 键盘支持
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdown = document.getElementById('language-dropdown');
            dropdown.classList.remove('show');
        }
    });
</script>
