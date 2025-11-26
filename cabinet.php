<?php
// Тимчасово показуємо помилки, щоб не було "білої тиші"
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключаємо конфіг (PDO, сесія тощо)
require_once __DIR__ . '/config.php';

// Гарантуємо, що сесія є
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// (опційно) якщо захочеш — можна знову ввімкнути редірект лише для залогінених
// if (empty($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Проста логіка мов тільки для цієї сторінки
$supportedLangs = ['ua', 'en', 'ru'];
$lang = 'ua';

if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLangs, true)) {
    $lang = $_GET['lang'];
    setcookie('lang', $lang, time() + 365 * 24 * 60 * 60, '/');
} elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supportedLangs, true)) {
    $lang = $_COOKIE['lang'];
}

// Дуже прості переклади тільки для кабінету
$text = [
    'ua' => [
        'title'         => 'Кабінет автора',
        'menu_cabinet'  => 'Кабінет',
        'menu_logout'   => 'Вийти',
        'menu_home'     => 'Головна',
        'menu_nicks'    => 'Клікухи',
        'menu_about'    => 'Про нас',
        'menu_contacts' => 'Контакти',

        'left_title'      => 'Управління',
        'left_all'        => 'Всі мої клікухи',
        'left_create'     => 'Створити клікуху',
        'left_settings'   => 'Налаштування',
        'left_layout'     => 'Інтер\'єр кабінету',

        'events_title'    => 'Події Clicuha',
        'events_text'     => 'Тут з’являться батли, тусовки, галереї та інші події — усе, де фігурують твої клікухи. Згодом тут буде вертикальна стрічка найближчих і минулих подій з можливістю фільтрувати за датою й типом.',

        'cave_title'      => 'Моя печера',
        'cave_text'       => 'Особистий простір Творця. Тут житимуть твої клікухи, історії та галереї.',
        'cave_btn'        => 'Я — Творець',
        'cave_hint'       => 'Натисни, щоб створити свою першу клікуху або нову істоту для галереї.',

        'archive_title'   => 'Архів / Бібліотека',
        'archive_text'    => 'Тут згодом буде список збережених історій, ілюстрацій, батлів та інших артефактів Clicuha — повна хроніка твоєї творчості.',

        'ads_title'       => 'Реклама Clicuha Bot Network',
        'ads_text'        => 'Вертикальна стрічка для банерів ботів, партнерських проєктів та івентів. У майбутньому тут можна показувати "reel"-формат картинок, прев’ю квестів, промо-історій та спеціальних акцій.',
        'ads_text2'       => 'Це твоя окрема вітрина для ботів і персонажів, які живуть поруч із клікухами.',

        'footer'          => 'CABINET v0.6-standalone',
    ],
    'en' => [
        'title'         => 'Author\'s cabinet',
        'menu_cabinet'  => 'Cabinet',
        'menu_logout'   => 'Logout',
        'menu_home'     => 'Home',
        'menu_nicks'    => 'Nicknames',
        'menu_about'    => 'About',
        'menu_contacts' => 'Contacts',

        'left_title'      => 'Control panel',
        'left_all'        => 'All my nicknames',
        'left_create'     => 'Create a nickname',
        'left_settings'   => 'Settings',
        'left_layout'     => 'Cabinet layout',

        'events_title'    => 'Clicuha Events',
        'events_text'     => 'Here you will see battles, parties, galleries and other events featuring your nicknames. Later this will become a vertical feed of upcoming and past events with filters.',

        'cave_title'      => 'My cave',
        'cave_text'       => 'Personal space of the Creator. Your nicknames, stories and galleries live here.',
        'cave_btn'        => 'I am the Creator',
        'cave_hint'       => 'Click to create your first nickname or a new creature for the gallery.',

        'archive_title'   => 'Archive / Library',
        'archive_text'    => 'Later here will be a list of saved stories, illustrations, battles and other Clicuha artefacts — a full chronicle of your creativity.',

        'ads_title'       => 'Clicuha Bot Network Ads',
        'ads_text'        => 'Vertical feed for bot banners, partner projects and events. In the future you can show reel-like images, quest previews and promo stories here.',
        'ads_text2'       => 'This is your separate showcase for bots and characters living next to your nicknames.',

        'footer'          => 'CABINET v0.6-standalone',
    ],
    'ru' => [
        'title'         => 'Кабинет автора',
        'menu_cabinet'  => 'Кабинет',
        'menu_logout'   => 'Выйти',
        'menu_home'     => 'Главная',
        'menu_nicks'    => 'Кликухи',
        'menu_about'    => 'О нас',
        'menu_contacts' => 'Контакты',

        'left_title'      => 'Управление',
        'left_all'        => 'Все мои кликухи',
        'left_create'     => 'Создать кликуху',
        'left_settings'   => 'Настройки',
        'left_layout'     => 'Интерьер кабинета',

        'events_title'    => 'События Clicuha',
        'events_text'     => 'Здесь появятся баттлы, тусовки, галереи и другие события с твоими кликухами. Потом тут будет вертикальная лента ближайших и прошедших событий с фильтрами.',

        'cave_title'      => 'Моя пещера',
        'cave_text'       => 'Личное пространство Творца. Здесь живут твои кликухи, истории и галереи.',
        'cave_btn'        => 'Я — Творец',
        'cave_hint'       => 'Нажми, чтобы создать свою первую кликуху или новое существо для галереи.',

        'archive_title'   => 'Архив / Библиотека',
        'archive_text'    => 'Здесь позже будет список сохранённых историй, иллюстраций, баттлов и других артефактов Clicuha — полная хроника твоего творчества.',

        'ads_title'       => 'Реклама Clicuha Bot Network',
        'ads_text'        => 'Вертикальная лента для баннеров ботов, партнёрских проектов и ивентов. В будущем здесь можно показывать "reel"-формат картинок, превью квестов и промо-историй.',
        'ads_text2'       => 'Это отдельная витрина для ботов и персонажей, которые живут рядом с кликухами.',

        'footer'          => 'CABINET v0.6-standalone',
    ],
];

$t = $text[$lang] ?? $text['ua'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($t['title']) ?> · Clicuha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Підключаємо той самий Bootstrap / стилі, що й на головній (мінімально) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        body {
            background: #f4f5f9;
        }
        .cab-nav {
            background: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .cab-nav .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.03em;
        }
        .cab-nav .nav-link.active {
            font-weight: 600;
        }
        .cab-wrapper {
            padding: 2.5rem 0 3rem;
        }
        .cab-card {
            border-radius: 18px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 8px 20px rgba(15,23,42,0.04);
            background: #ffffff;
            height: 100%;
        }
        .cab-card-dark {
            background: #050816;
            color: #f9fafb;
            border: none;
        }
        .cab-card-dark h5,
        .cab-card-dark p {
            color: #f9fafb;
        }
        .cab-sidebar-title {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: .75rem;
        }
        .cab-footer-small {
            font-size: .8rem;
            color: #9ca3af;
        }
        .cab-lang-btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
        }
    </style>
</head>
<body>

<!-- Навбар "як на головній" у світлому варіанті -->
<nav class="navbar navbar-expand-lg cab-nav">
    <div class="container">
        <a class="navbar-brand" href="/index.php">Clicuha</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cabinetNav"
                aria-controls="cabinetNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="cabinetNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/index.php"><?= htmlspecialchars($t['menu_home']) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/my_nicknames.php"><?= htmlspecialchars($t['menu_nicks']) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/about.php"><?= htmlspecialchars($t['menu_about']) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/contacts.php"><?= htmlspecialchars($t['menu_contacts']) ?></a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="/cabinet.php"><?= htmlspecialchars($t['menu_cabinet']) ?></a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="btn-group" role="group">
                    <a href="?lang=ua" class="btn btn-outline-secondary cab-lang-btn<?= $lang === 'ua' ? ' active' : '' ?>">UA</a>
                    <a href="?lang=en" class="btn btn-outline-secondary cab-lang-btn<?= $lang === 'en' ? ' active' : '' ?>">EN</a>
                    <a href="?lang=ru" class="btn btn-outline-secondary cab-lang-btn<?= $lang === 'ru' ? ' active' : '' ?>">RU</a>
                </div>
                <a href="/logout.php" class="btn btn-outline-danger btn-sm">
                    <?= htmlspecialchars($t['menu_logout']) ?>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="cab-wrapper">
    <div class="container">
        <div class="row g-4">
            <!-- Ліва колонка: Управління -->
            <div class="col-12 col-md-3">
                <aside class="cab-card p-4 h-100">
                    <div class="cab-sidebar-title"><?= htmlspecialchars($t['left_title']) ?></div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="/my_nicknames.php" class="link-dark text-decoration-none">• <?= htmlspecialchars($t['left_all']) ?></a></li>
                        <li class="mb-2"><a href="/add_bootstrap.php" class="link-dark text-decoration-none">• <?= htmlspecialchars($t['left_create']) ?></a></li>
                        <li class="mb-2"><a href="#" class="link-secondary text-decoration-none">• <?= htmlspecialchars($t['left_settings']) ?></a></li>
                        <li><a href="#" class="link-secondary text-decoration-none">• <?= htmlspecialchars($t['left_layout']) ?></a></li>
                    </ul>
                </aside>
            </div>

            <!-- Події Clicuha -->
            <div class="col-12 col-md-3">
                <section class="cab-card p-4 h-100">
                    <h5 class="mb-3"><?= htmlspecialchars($t['events_title']) ?></h5>
                    <p class="mb-0"><?= htmlspecialchars($t['events_text']) ?></p>
                </section>
            </div>

            <!-- Моя печера + Архів (вертикально один під одним) -->
            <div class="col-12 col-md-3">
                <section class="cab-card p-4 mb-4">
                    <h5 class="mb-3 text-center"><?= htmlspecialchars($t['cave_title']) ?></h5>
                    <p class="mb-3"><?= htmlspecialchars($t['cave_text']) ?></p>
                    <div class="text-center mb-2">
                        <a href="/add_bootstrap.php" class="btn btn-primary">
                            <?= htmlspecialchars($t['cave_btn']) ?>
                        </a>
                    </div>
                    <p class="text-muted small mb-0 text-center"><?= htmlspecialchars($t['cave_hint']) ?></p>
                </section>

                <section class="cab-card p-4">
                    <h5 class="mb-3"><?= htmlspecialchars($t['archive_title']) ?></h5>
                    <p class="mb-0"><?= htmlspecialchars($t['archive_text']) ?></p>
                </section>
            </div>

            <!-- Права колонка: вертикальна реклама -->
            <div class="col-12 col-md-3">
                <section class="cab-card cab-card-dark p-4 h-100">
                    <h5 class="mb-3"><?= htmlspecialchars($t['ads_title']) ?></h5>
                    <p class="mb-3"><?= htmlspecialchars($t['ads_text']) ?></p>
                    <p class="mb-0"><?= htmlspecialchars($t['ads_text2']) ?></p>
                </section>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center cab-footer-small">
                © 2025 Clicuha · <?= htmlspecialchars($t['footer']) ?>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
