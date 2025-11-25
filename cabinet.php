<?php
// Кабінет автора — світла вертикальна версія через спільний header/footer

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Якщо не залогінений — відправляємо на форму входу
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Можна передати заголовок у хедер, якщо він це підтримує
$pageTitle = 'Кабінет автора';

require __DIR__ . '/partials/header.php';
?>

<style>
    /* Локальні стилі тільки для кабінету, щоб не ломати інші сторінки */

    body {
        background: #f5f5f7;
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

    .cab-card,
    .cab-ads {
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(15,23,42,0.06);
        padding: 1.5rem 1.75rem;
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
        box-shadow: 0 10px 25px rgba(15,23,42,0.4);
    }

    .cab-ads h3 {
        font-size: 1.05rem;
        margin-bottom: 0.75rem;
    }

    .cab-ads p {
        font-size: 0.9rem;
    }

    .cab-footer-small {
        font-size: 0.8rem;
        color: #6b7280;
    }
</style>

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

            <!-- ПРАВА КОЛОНКА: ВСЕ ВЕРТИКАЛЬНО -->
            <div class="col-12 col-md-9">
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

                    <!-- Реклама Clicuha Bot Network -->
                    <div class="col-12">
                        <aside class="cab-ads">
                            <h3>Реклама Clicuha Bot Network</h3>
                            <p class="mb-0">
                                Тут у майбутньому можна буде показувати банери ботів,
                                партнерські проєкти, квести для клікух
                                та інші веселощі з бот-мережі.
                            </p>
                        </aside>
                    </div>

                    <div class="col-12 text-center mt-2 cab-footer-small">
                        CABINET v0.3-light
                    </div>

                </div>
            </div>

        </div>
    </div>
</main>

<?php
require __DIR__ . '/partials/footer.php';
