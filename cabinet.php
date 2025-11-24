<?php
require_once __DIR__ . '/config.php';

session_start();

// Перевірка логіну (якщо в тебе вже є щось подібне — лиши свій варіант)
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Тема інтер'єру (із сесії або classic за замовчуванням)
$userTheme = $_SESSION['cabinet_theme'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Кабінет</title>

    <!-- Bootstrap як на index.php -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Основний стиль сайту -->
    <link rel="stylesheet" href="/assets/css/sema.css?v=<?= time() ?>">

    <!-- Базовий стиль кабінету -->
    <link rel="stylesheet" href="/assets/css/cabinet-base.css?v=<?= time() ?>">

    <!-- Тема кабінету -->
    <link rel="stylesheet" href="/assets/css/themes/<?= htmlspecialchars($userTheme) ?>.css?v=<?= time() ?>">
</head>

<body class="cabinet-theme-<?= htmlspecialchars($userTheme) ?> bg-light">

<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="cabinet-layout container py-4">

    <!-- Ліва колонка: управління -->
    <aside class="cabinet-sidebar">
        <h3 class="h5 mb-3">Управління</h3>
        <ul class="list-unstyled">
            <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
            <li><a href="create_event.php">Створити подію</a></li>
            <li><a href="settings.php">Налаштування</a></li>
            <li><a href="theme.php">Інтер&apos;єр кабінету</a></li>
        </ul>
    </aside>

    <!-- Стрічка подій -->
    <section class="cabinet-events">
        <h3 class="h5 mb-3">Події Clicuha</h3>
        <p class="placeholder">
            Тут буде стрічка подій — батли, тусовки, галереї…
        </p>
    </section>

    <!-- Персональний простір -->
    <section class="cabinet-personal">
        <h3 class="h5 mb-3">Моя печера</h3>
        <p class="placeholder">
            Тут твій особистий контент, нотатки, чернетки…
        </p>

        <!-- Кнопка створення клікухи, щоб не загубилась -->
        <a href="add_bootstrap.php" class="btn btn-primary mt-3">
            Я — Творець
        </a>
    </section>

    <!-- Архів / бібліотека -->
    <section class="cabinet-extra">
        <h3 class="h5 mb-3">Архів / Бібліотека</h3>
        <p class="placeholder">
            Тут будуть матеріали, історія, клікухи…
        </p>
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





