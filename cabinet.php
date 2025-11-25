<?php
// Увімкнути помилки (на проді потім можна вимкнути)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// єдина домовлена форма підключення
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Перевірка авторизації
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Тема інтер’єру кабінету (classic / cave / palace / vigvam ...)
$userTheme = $_SESSION['cabinet_theme'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кабінет автора</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Базовий стиль кабінету -->
    <link rel="stylesheet" href="assets/css/cabinet-base.css">

    <!-- Тема інтер’єру (якщо є такий css) -->
    <link rel="stylesheet" href="assets/css/themes/<?php echo htmlspecialchars($userTheme, ENT_QUOTES); ?>.css">

    <style>
        /* Якщо немає своїх css – мінімальний запасний варіант */

        body {
            background: #f5f5f5;
        }

        .navbar-brand {
            font-weight: 700;
        }

        main.cabinet-layout {
            padding: 2rem 0;
        }

        .cabinet-sidebar {
            min-height: 100%;
        }

        .cabinet-sidebar ul {
            list-style: disc;
            padding-left: 1.5rem;
        }

        .cabinet-sidebar a {
            text-decoration: none;
        }

        .cabinet-card {
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            padding: 1.5rem;
            height: 100%;
        }

        .cabinet-card h2 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .cabinet-ads {
            background: #111827;
            color: #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            height: 100%;
        }

        .cabinet-ads h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>

<!-- ПРОСТИЙ СТАРИЙ НАВБАР -->
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
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
                <!-- Мовні кнопки чисто для вигляду, логіку потім -->
                <a href="?lang=ua" class="btn btn-sm btn-outline-secondary">UA</a>
                <a href="?lang=en" class="btn btn-sm btn-outline-secondary">EN</a>
                <a href="?lang=ru" class="btn btn-sm btn-outline-secondary">RU</a>
            </div>
        </div>
    </div>
</nav>

<main class="cabinet-layout">
    <div class="container">
        <div class="row g-3">

            <!-- ЛІВА КОЛОНКА: УПРАВЛІННЯ -->
            <div class="col-12 col-md-3">
                <aside class="cabinet-sidebar bg-light rounded-3 p-3 h-100">
                    <h5 class="mb-3">Управління</h5>
                    <ul class="small">
                        <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
                        <!-- Ось тут наша зникла опція -->
                        <li><a href="add_bootstrap.php">Створити клікуху</a></li>
                        <li><a href="settings.php">Налаштування</a></li>
                        <li><a href="theme.php">Інтер'єр кабінету</a></li>
                    </ul>
                </aside>
            </div>

            <!-- СЕРЕДНЯ ЗОНА: КАРТКИ -->
            <div class="col-12 col-md-6">
                <div class="row g-3">

                    <!-- Події -->
                    <div class="col-12">
                        <section class="cabinet-card">
                            <h2>Події Clicuha</h2>
                            <p class="small text-muted mb-2">
                                Тут з’являться батли, тусовки, галереї та інші події.
                            </p>
                            <p class="mb-0 small text-secondary">
                                (поки що заглушка, потім підключимо реальні події)
                            </p>
                        </section>
                    </div>

                    <!-- Моя печера -->
                    <div class="col-12">
                        <section class="cabinet-card text-center">
                            <h2>Моя печера</h2>
                            <p class="small text-muted">
                                Особистий простір Творця. Тут житимуть твої клікухи.
                            </p>
                            <a href="add_bootstrap.php" class="btn btn-primary">
                                Я — Творець
                            </a>
                            <p class="small text-muted mt-2 mb-0">
                                Натисни, щоб створити свою першу клікуху
                                або нову істоту для галереї.
                            </p>
                        </section>
                    </div>

                    <!-- Архів / бібліотека -->
                    <div class="col-12">
                        <section class="cabinet-card">
                            <h2>Архів / Бібліотека</h2>
                            <p class="small text-muted mb-0">
                                Тут згодом буде список збережених історій, ілюстрацій,
                                батлів та інших артефактів Clicuha.
                            </p>
                        </section>
                    </div>

                </div>
            </div>

            <!-- ПРАВА КОЛОНКА: РЕКЛАМА / BOT NETWORK -->
            <div class="col-12 col-md-3">
                <aside class="cabinet-ads d-flex flex-column justify-content-between">
                    <div>
                        <h3>Реклама Clicuha Bot Network</h3>
                        <p class="small mb-0">
                            Тут у майбутньому можна буде показувати банери ботів,
                            партнерські проєкти та інші веселощі.
                        </p>
                    </div>
                </aside>
            </div>

        </div>
    </div>
</main>

<footer class="border-top py-4 mt-4">
    <div class="container text-center small text-muted">
        © <span id="y"></span> Clicuha
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('y').textContent = new Date().getFullYear();
</script>
</body>
</html>








