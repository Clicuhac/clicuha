<?php
// Світлий кабінет: 4 вертикальні колонки

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Якщо користувач не залогінений — на вхід
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кабінет автора</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body.cab-body {
            background: #f5f5f7;
        }

        /* Верхній бар */
        .cab-navbar {
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
        }
        .cab-navbar .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .cab-main {
            padding: 2rem 0 3rem;
        }

        /* Базовий стиль вертикальної колонки */
        .cab-column {
            border-radius: 1rem;
            padding: 1.4rem 1.35rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 360px;           /* щоб це була саме "колонка" */
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
        }

        .cab-column h2,
        .cab-column h3,
        .cab-column h5 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
        }

        .cab-column p {
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }

        .cab-column ul {
            padding-left: 1rem;
            margin-bottom: 0;
            font-size: 0.92rem;
        }

        .cab-column li + li {
            margin-top: 0.35rem;
        }

        .cab-column a {
            color: inherit;
            text-decoration: none;
        }

        .cab-column a:hover {
            text-decoration: underline;
        }

        /* Варіанти фону */
        .cab-column--light {
            background: #ffffff;
            color: #111827;
        }

        .cab-column--menu {
            background: #ffffff;
            color: #111827;
        }

        .cab-column--events {
            background: #ffffff;
            color: #111827;
        }

        .cab-column--center {
            background: #ffffff;
            color: #111827;
        }

        .cab-column--ads {
            background: #111827;
            color: #e5e7eb;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.35);
        }

        .cab-column--ads p {
            font-size: 0.88rem;
        }

        .cab-column--ads h5 {
            font-size: 1rem;
        }

        .cab-footer {
            font-size: 0.8rem;
            color: #6b7280;
        }

        /* Внутрішні блоки в центральній колонці (Печера + Архів) */
        .cab-subblock {
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            background: #f9fafb;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            margin-bottom: 0.8rem;
        }

        .cab-subblock h6 {
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
        }

        .cab-subblock:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 991.98px) {
            .cab-column {
                min-height: 0;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="cab-body">

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

        <!-- ОДИН РЯД: 4 ВЕРТИКАЛЬНІ КОЛОНКИ -->
        <div class="row g-4 mb-4">

            <!-- 1. УПРАВЛІННЯ (вузька, як reel) -->
            <div class="col-12 col-md-3 col-lg-2">
                <section class="cab-column cab-column--menu h-100">
                    <div>
                        <h5>Управління</h5>
                        <ul>
                            <li><a href="my_nicknames.php">Всі мої клікухи</a></li>
                            <li><a href="add_bootstrap.php">Створити клікуху</a></li>
                            <li><a href="settings.php">Налаштування</a></li>
                            <li><a href="theme.php">Інтер'єр кабінету</a></li>
                        </ul>
                    </div>
                </section>
            </div>

            <!-- 2. Події Clicuha (вертикальна стрічка) -->
            <div class="col-12 col-md-3 col-lg-4">
                <section class="cab-column cab-column--events h-100">
                    <div>
                        <h5>Події Clicuha</h5>
                        <p>
                            Тут з’являться батли, тусовки, галереї та інші події — усе,
                            де фігурують твої клікухи.
                        </p>
                        <p>
                            Поки що це заглушка. Згодом тут буде
                            вертикальна стрічка найближчих і минулих подій
                            з можливістю фільтрувати за датою й типом.
                        </p>
                    </div>
                </section>
            </div>

            <!-- 3. Центральна колонка: Печера + Архів -->
            <div class="col-12 col-md-3 col-lg-4">
                <section class="cab-column cab-column--center h-100">
                    <div>

                        <div class="cab-subblock text-center">
                            <h6>Моя печера</h6>
                            <p style="font-size:0.9rem;">
                                Особистий простір Творця. Тут житимуть твої клікухи,
                                історії та галереї.
                            </p>
                            <a href="add_bootstrap.php" class="btn btn-primary btn-sm">
                                Я — Творець
                            </a>
                            <p class="mt-2 mb-0" style="font-size:0.82rem;">
                                Натисни, щоб створити свою першу клікуху
                                або нову істоту для галереї.
                            </p>
                        </div>

                        <div class="cab-subblock">
                            <h6>Архів / Бібліотека</h6>
                            <p style="font-size:0.88rem;">
                                Тут згодом буде список збережених історій, ілюстрацій, батлів
                                та інших артефактів Clicuha — повна хроніка твоєї творчості.
                            </p>
                            <p class="mb-0" style="font-size:0.82rem;">
                                Пізніше можна зробити тут теж вертикальну стрічку,
                                розділену на «історії», «батли», «ілюстрації» тощо.
                            </p>
                        </div>

                    </div>
                </section>
            </div>

            <!-- 4. Реклама (вузька вертикальна лента, як reel) -->
            <div class="col-12 col-md-3 col-lg-2">
                <section class="cab-column cab-column--ads h-100">
                    <div>
                        <h5>Реклама Clicuha Bot Network</h5>
                        <p>
                            Вертикальна стрічка для банерів ботів,
                            партнерських проєктів та івентів.
                        </p>
                        <p>
                            У майбутньому тут можна показувати
                            «reel»-формат картинок, прев’ю квестів,
                            промо-історій та спеціальних акцій.
                        </p>
                        <p class="mb-0">
                            Це твоя окрема вітрина для ботів і персонажів,
                            які живуть поруч із клікухами.
                        </p>
                    </div>
                </section>
            </div>

        </div>

        <div class="text-center mt-1 cab-footer">
            © <span id="cab-year"></span> Clicuha · CABINET v0.5-columns
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('cab-year').textContent = new Date().getFullYear();
</script>
</body>
</html>
