<?php
require __DIR__ . '/config.php';
session_start();

// тема інтер'єру кабінету
$userTheme = $_SESSION['cabinet_theme'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Кабінет</title>

    <!-- Глобальні стилі сайту (як на інших сторінках) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css">

    <!-- Стилі макета кабінету -->
    <link rel="stylesheet" href="/assets/css/cabinet-base.css">
    <link rel="stylesheet" href="/assets/css/themes/<?= h($userTheme) ?>.css">
</head>

<body class="bg-light cabinet-theme-<?= h($userTheme) ?>">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="cabinet-layout py-4">

    <!-- Ліва колонка: управління -->
    <aside class="cabinet-sidebar">
        <h3>Управління</h3>
        <ul>
            <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
            <li><a href="create_event.php">Створити подію</a></li>
            <li><a href="settings.php">Налаштування</a></li>
            <li><a href="theme.php">Інтер'єр кабінету</a></li>
        </ul>
    </aside>

    <!-- Події на сайті -->
    <section class="cabinet-events">
        <h3>Події Clicuha</h3>
        <div class="placeholder">
            Тут буде стрічка подій — батли, тусовки, галереї…
        </div>
    </section>

    <!-- Особистий простір користувача -->
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

    <!-- Права колонка: реклама -->
    <aside class="cabinet-ads">
        <div class="ads-block">
            Тут буде контекстна реклама Clicuha Bot Network
        </div>
    </aside>

</main>

</body>
</html>


