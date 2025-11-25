<?php
// Кабінет автора — світла версія

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Домовлена форма підключення конфігу
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Перевірка авторизації
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Можемо колись підтягувати тему з БД, поки просто light
$currentTheme = 'light';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кабінет автора</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Локальні стилі кабінету (максимально ізольовані) -->
    <style>
        body.cab-body {
            background: #f5f5f7;
        }

        .cab-navbar {
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            background: #ffffff !important;
        }

        .cab-navbar .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .cab-main {
            padding: 2rem 0 3rem;
        }

        .cab-sidebar {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(15,23,42,0.06);
            padding: 1.25rem 1.5rem;
        }

        .cab-sidebar h5 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
        }

        .cab-sidebar ul {
            list-style: disc;
            padding-left: 1.25rem;
            margin-bottom: 0;
        }

        .cab-sidebar li + li {
            margin-top: 0.35rem;
        }

        .cab-sidebar a {
            text-decoration: none;
            color: #111827;
        }

        .cab-sidebar a:hover {
            text-decoration: underline;
        }

        .cab-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(15,23,42,0.06);
            padding: 1.5rem 1.75rem;
            height: 100%;
        }

        .cab-card h2 {
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }

        .cab-card p {
            font-size: 0.9rem;
        }

        .cab-card-muted {
            color: #6b7280;
        }

        .cab-ads {
            background: #111827;
            color: #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(15,23,42,0.4);
            padding: 1.5rem 1.75rem;
            height: 100%;
        }

        .cab-ads h3 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
        }

        .cab-ads p {
            font-size: 0.9rem;
        }

        .cab-footer {
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
</head>
<body class="cab-body">

<!-- ПРОСТИЙ СВІТЛИЙ НАВБАР -->
<nav class="navbar navbar-expand-lg navbar-light cab-navbar">
    <div class="container">
        <a class="navbar-brand" href="/">Clicuha</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                aria-expanded="false" aria-label="Перемкнути навігацію">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/index.php">Головна</a></li>
                <li class="nav-item"><a class="nav-link" href="/gallery.php">Клікухи</a></li>
                <li class="nav-item"><a class="nav-link" href="/about.php">Про нас</a></li>
                <li class="nav-item"><a class="nav-link" href="/contacts.php">Контакти</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="/cabinet.php" class="btn btn-outline-secondary btn-sm">Кабінет</a>
                <a href="/logout.php" class="btn btn-outline-danger btn-sm">Вийти</a>
                <a href="?lang=ua" class="btn btn-sm btn-outline-secondary">UA</a>
                <a href="?lang=en" class="btn btn-sm btn-outline-secondary">EN</a>
                <a href="?lang=ru" class="btn btn-sm btn-outline-secondary">RU</a>
            </div>
        </div>
    </div>
</nav>

<main class="cab-main">
    <div class="container">
        <div class="row g-3">

            <!-- ЛІВА КОЛОНКА: УПРАВЛІННЯ -->
            <div class="col-12 col-md-3">
                <aside class="cab-sidebar h-100">
                    <h5>Управління</h5>
                    <ul>
                        <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
                        <li><a href="add_bootstrap.php">Створити клікуху</a></li>
                        <li><a href="settings.php">Налаштування</a></li>
                        <li><a href="theme.php">Інтер'єр кабінету</a></li>
                    </ul>
                </aside>
            </div>

            <!-- СЕРЕДНЯ КОЛОНКА: ПОДІЇ + ПЕЧЕРА + АРХІВ -->
            <div class="col-12 col-md-6">
                <div class="row g-3">

                    <!-- Події Clicuha -->
                    <div class="col-12">
                        <section class="cab-card">
                            <h2>Події Clicuha</h2>
                            <p class="cab-card-muted mb-2">
                                Тут з’являться батли, тусовки, галереї та інші події.
                            </p>
                            <p class="mb-0 cab-card-muted">
                                Поки що це заглушка — потім підключимо реальні події та календар.
                            </p>
                        </section>
                    </div>

                    <!-- Моя печера -->
                    <div class="col-12">
                        <section class="cab-card text-center">
                            <h2>Моя печера</h2>
                            <p class="cab-card-muted">
                                Особистий простір Творця. Тут житимуть твої клікухи, історії та галереї.
                            </p>
                            <a href="add_bootstrap.php" class="btn btn-primary">
                                Я — Творець
                            </a>
                            <p class="cab-card-muted mt-2 mb-0">
                                Натисни, щоб створити свою першу клікуху
                                або нову істоту для галереї.
                            </p>
                        </section>
                    </div>

                    <!-- Архів / Бібліотека -->
                    <div class="col-12">
                        <section class="cab-card">
                            <h2>Архів / Бібліотека</h2>
                            <p class="cab-card-muted mb-0">
                                Тут згодом буде список збережених історій, ілюстрацій, батлів
                                та інших артефактів Clicuha — повна хроніка твоєї творчості.
                            </p>
                        </section>
                    </div>

                </div>
            </div>

            <!-- ПРАВА КОЛОНКА: РЕКЛАМА / BOT NETWORK -->
            <div class="col-12 col-md-3">
                <aside class="cab-ads d-flex flex-column justify-content-between">
                    <div>
                        <h3>Реклама Clicuha Bot Network</h3>
                        <p class="mb-0">
                            Тут у майбутньому можна буде показувати банери ботів,
                            партнерські проєкти, квести для клікух
                            та інші веселощі з бот-мережі.
                        </p>
                    </div>
                </aside>
            </div>

        </div>
    </div>
</main>

<footer class="cab-footer border-top py-3 mt-4">
    <div class="container text-center">
        © <span id="cab-year"></span> Clicuha · CABINET v0.3-light
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('cab-year').textContent = new Date().getFullYear();
</script>
</body>
</html>









