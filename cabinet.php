<?php
require __DIR__ . '/config.php'; // тут вже стартує сесія

// Тема інтер'єру (за замовчуванням — classic)
$userTheme = $_SESSION['cabinet_theme'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кабінет</title>

    <!-- Базовий стиль -->
    <link rel="stylesheet" href="assets/css/cabinet-base.css">

    <!-- Тема -->
    <link rel="stylesheet" href="assets/css/themes/<?= htmlspecialchars($userTheme, ENT_QUOTES, 'UTF-8') ?>.css">
</head>

<body class="cabinet-theme-<?= htmlspecialchars($userTheme, ENT_QUOTES, 'UTF-8') ?>">

<?php include 'partials/navbar.php'; ?>

<main class="cabinet-layout">

    <!-- Ліва колонка: управління -->
    <aside class="cabinet-sidebar">
        <h3>Управління</h3>
        <ul>
            <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
            <li><a href="create_event.php">Створити подію</a></li>
            <!-- Тут пізніше додамо нормальну кнопку "Я – Творець" всередині окремого блоку -->
            <li><a href="settings.php">Налаштування</a></li>
            <li><a href="theme.php">Інтер'єр кабінету</a></li>
        </ul>
    </aside>

    <!-- Стрічка подій -->
    <section class="cabinet-events">
        <h3>Події Clicuha</h3>
        <div class="placeholder">
            Тут буде стрічка подій — батли, тусовки, галереї…
        </div>
    </section>

    <!-- Персональний простір -->
    <section class="cabinet-personal">
        <h3>Моя печера</h3>
        <div class="placeholder">
            Тут твій особистий контент, нотатки, чернетки…
        </div>
    </section>

    <!-- Архів / бібліотека -->
    <section class="cabinet-extra">
        <h3>Архів / Бібліотека</h3>
        <div class="placeholder">
            Тут будуть матеріали, історія, клікухи…
        </div>
    </section>

    <!-- Права реклама -->
    <aside class="cabinet-ads">
        <div class="ads-block">
            <p>Тут буде контекстна реклама Clicuha Bot Network</p>
        </div>
    </aside>

</main>

</body>
</html>




