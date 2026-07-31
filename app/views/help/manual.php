<?php
/**
 * 用户手册页面 — 跟随当前界面语言
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 3));
}
require_once BASE_PATH . '/core/session.php';
ensureSessionStarted();
require_once BASE_PATH . '/lang/Language.php';

$lang = Language::getInstance();
$currentLang = $lang->getCurrentLanguage();
$isLoggedIn = isset($_SESSION['user_id']);

$manualFile = BASE_PATH . '/lang/manual/' . $currentLang . '.php';
if (!file_exists($manualFile)) {
    $manualFile = BASE_PATH . '/lang/manual/zh.php';
}
$manual = include $manualFile;
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($manual['page_title']); ?> — <?php echo __('app_name'); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <link rel="stylesheet" href="/Chat_System/public/css/user-manual.css">
</head>
<body class="manual-page">
    <div class="manual-shell">
        <header class="manual-header">
            <div class="manual-header-inner">
                <div class="manual-brand">
                    <a href="<?php echo $isLoggedIn ? '/Chat_System/dashboard' : '/Chat_System/'; ?>" class="manual-back">
                        <span class="manual-back-icon" aria-hidden="true">←</span>
                        <span><?php echo htmlspecialchars($isLoggedIn ? $manual['back_dashboard'] : $manual['back_home']); ?></span>
                    </a>
                    <div class="manual-brand-text">
                        <h1><?php echo htmlspecialchars($manual['page_title']); ?></h1>
                        <p class="manual-subtitle"><?php echo htmlspecialchars($manual['subtitle']); ?></p>
                    </div>
                </div>
                <div class="manual-header-actions">
                    <?php include BASE_PATH . '/app/views/components/languageSwitcher.php'; ?>
                </div>
            </div>
        </header>

        <div class="manual-body">
            <nav class="manual-toc" aria-label="<?php echo htmlspecialchars($manual['toc_title']); ?>">
                <h2><?php echo htmlspecialchars($manual['toc_title']); ?></h2>
                <ol>
                    <?php foreach ($manual['sections'] as $i => $section): ?>
                        <li>
                            <a href="#<?php echo htmlspecialchars($section['id']); ?>">
                                <span class="toc-num"><?php echo $i + 1; ?></span>
                                <span class="toc-label"><?php
                                    echo htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $section['title']));
                                ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <main class="manual-content" id="manualContent">
                <?php foreach ($manual['sections'] as $section): ?>
                    <section class="manual-section" id="<?php echo htmlspecialchars($section['id']); ?>">
                        <h2><?php echo htmlspecialchars($section['title']); ?></h2>

                        <?php if (!empty($section['intro'])): ?>
                            <p class="manual-intro"><?php echo htmlspecialchars($section['intro']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($section['preview_items'])): ?>
                            <div class="manual-preview-grid">
                                <?php foreach ($section['preview_items'] as $item): ?>
                                    <article class="manual-preview-card">
                                        <div class="manual-preview-icon"><?php echo $item['icon']; ?></div>
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($item['desc']); ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($section['blocks'])): ?>
                            <?php foreach ($section['blocks'] as $block): ?>
                                <div class="manual-block">
                                    <?php if (!empty($block['heading'])): ?>
                                        <h3><?php echo htmlspecialchars($block['heading']); ?></h3>
                                    <?php endif; ?>

                                    <?php if (!empty($block['steps'])): ?>
                                        <ol class="manual-steps">
                                            <?php foreach ($block['steps'] as $step): ?>
                                                <li><?php echo htmlspecialchars($step); ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php endif; ?>

                                    <?php if (!empty($block['list'])): ?>
                                        <ul class="manual-list">
                                            <?php foreach ($block['list'] as $item): ?>
                                                <li><?php echo htmlspecialchars($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <?php if (!empty($block['tips'])): ?>
                                        <ul class="manual-tips">
                                            <?php foreach ($block['tips'] as $tip): ?>
                                                <li><?php echo htmlspecialchars($tip); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <?php if (!empty($block['table'])): ?>
                                        <div class="manual-table-wrap">
                                            <table class="manual-table">
                                                <thead>
                                                    <tr>
                                                        <?php foreach ($block['table']['headers'] as $header): ?>
                                                            <th><?php echo htmlspecialchars($header); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($block['table']['rows'] as $row): ?>
                                                        <tr>
                                                            <?php foreach ($row as $cell): ?>
                                                                <td><?php echo htmlspecialchars($cell); ?></td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($section['faq'])): ?>
                            <div class="manual-faq">
                                <?php foreach ($section['faq'] as $item): ?>
                                    <details class="manual-faq-item">
                                        <summary><?php echo htmlspecialchars($item['q']); ?></summary>
                                        <p><?php echo htmlspecialchars($item['a']); ?></p>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </main>
        </div>
    </div>

    <script>
        (function () {
            var content = document.getElementById('manualContent');
            var links = document.querySelectorAll('.manual-toc a');

            function setActive(id) {
                links.forEach(function (link) {
                    link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                });
            }

            links.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    var id = this.getAttribute('href').slice(1);
                    var target = document.getElementById(id);
                    if (!target || !content) return;
                    e.preventDefault();
                    content.scrollTo({
                        top: target.offsetTop - 16,
                        behavior: 'smooth'
                    });
                    setActive(id);
                    history.replaceState(null, '', '#' + id);
                });
            });

            if (content) {
                content.addEventListener('scroll', function () {
                    var sections = content.querySelectorAll('.manual-section');
                    var current = sections[0] && sections[0].id;
                    var scrollTop = content.scrollTop + 40;
                    sections.forEach(function (section) {
                        if (section.offsetTop <= scrollTop) {
                            current = section.id;
                        }
                    });
                    if (current) setActive(current);
                });
            }

            if (location.hash) {
                var initial = document.getElementById(location.hash.slice(1));
                if (initial && content) {
                    content.scrollTop = initial.offsetTop - 16;
                    setActive(initial.id);
                }
            } else if (links[0]) {
                setActive(links[0].getAttribute('href').slice(1));
            }
        })();
    </script>
</body>
</html>
