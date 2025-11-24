<?php
require __DIR__ . '/config.php';

// Старт сесії, якщо раптом ще не стартувала
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Тема інтер'єру кабінету
$userTheme = $_SESSION['cabinet_theme'] ?? 'classic';

// Функція екранування, якщо раптом нема
if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кабінет автора</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Загальні стилі сайту (як і було) -->
    <link rel="stylesheet" href="assets/css/sema.css">

    <!-- Базовий макет кабінету -->
    <link rel="stylesheet" href="assets/css/cabinet-base.css?v=1">

    <!-- Тема інтер'єру -->
    <link rel="stylesheet" href="assets/css/themes/<?= h($userTheme) ?>.css?v=1">
</head>
<body class="cabinet-page cabinet-theme-<?= h($userTheme) ?>">

<?php
// Верхнє меню, як і раніше
if (file_exists(__DIR__ . '/partials/navbar.php')) {
    include __DIR__ . '/partials/navbar.php';
}
?>

<main class="cabinet-shell">
    <div class="cabinet-layout">

        <!-- Ліва панель: управління -->
        <aside class="cabinet-panel cabinet-sidebar">
            <h3>Управління</h3>
            <ul>
                <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
                <li><a href="add_bootstrap.php">Створити клікуху</a></li>
                <li><a href="settings.php">Налаштування</a></li>
                <li><a href="theme.php">Інтер'єр кабінету</a></li>
            </ul>
        </aside>

        <!-- Події на сайті -->
        <section class="cabinet-panel cabinet-events">
            <h3>Події Clicuha</h3>
            <p class="placeholder">
                Тут буде стрічка подій — батли, тусовки, галереї…
            </p>
        </section>

        <!-- Особистий простір / Моя печера -->
        <section class="cabinet-panel cabinet-personal">
            <h3>Моя печера</h3>

            <p class="placeholder">
                Тут твій особистий контент, нотатки, чернетки…
            </p>

            <a href="add_bootstrap.php" class="btn btn-primary w-100 mb-2">
                Я – Творець
            </a>

            <small class="text-muted d-block">
                Натисни, щоб створити свою першу клікуху або нову істоту для галереї.
            </small>
        </section>

        <!-- Архів / Бібліотека -->
        <section class="cabinet-panel cabinet-library">
            <h3>Архів / Бібліотека</h3>
            <p class="placeholder">
                Тут будуть матеріали, історія клікух, збережені події…
            </p>
        </section>

        <!-- Права колонка: реклама / бот-мережа -->
        <aside class="cabinet-panel cabinet-ads">
            <h3>Реклама Clicuha Bot Network</h3>
            <p class="placeholder mb-0">
                Тут буде контекстна реклама з нашої мережі ботів:
                Telegram, Insta, TikTok та інші канали.
            </p>
        </aside>
    </div>
</main>

</body>
</html>

</html>



