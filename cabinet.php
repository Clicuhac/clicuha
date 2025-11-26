<?php
// Кабінет автора — версія з загальним хедером/футером

require __DIR__ . '/config.php';

// Простий захист: тільки для авторизованих
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Загальний хедер сайту (той самий, що й на головній)
// Всередині нього вже підключається navbar з мовами
require __DIR__ . '/partials/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Ліва колонка: управління -->
    <section class="col-md-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">
                    <?= t('cabinet.control_panel_title') ?? 'Control panel' ?>
                </h2>

                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <a href="/my_nicknames.php" class="link-dark text-decoration-none">
                            <?= t('cabinet.menu.all_nicknames') ?? 'All my nicknames' ?>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/add_nickname.php" class="link-dark text-decoration-none">
                            <?= t('cabinet.menu.create_nickname') ?? 'Create a nickname' ?>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/settings.php" class="link-dark text-decoration-none">
                            <?= t('cabinet.menu.settings') ?? 'Settings' ?>
                        </a>
                    </li>
                    <li>
                        <a href="/cabinet_layout.php" class="link-dark text-decoration-none">
                            <?= t('cabinet.menu.layout') ?? 'Cabinet layout' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Події Clicuha -->
    <section class="col-md-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">
                    <?= t('cabinet.events_title') ?? 'Clicuha Events' ?>
                </h2>
                <p class="mb-2">
                    <?= t('cabinet.events_intro') ??
                    'Here you will see battles, parties, galleries and other events featuring your nicknames.' ?>
                </p>
                <p class="mb-0 text-muted small">
                    <?= t('cabinet.events_future') ??
                    'Later this will become a vertical feed of upcoming and past events with filters.' ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Моя печера -->
    <section class="col-md-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 mb-3">
                    <?= t('cabinet.cave_title') ?? 'My cave' ?>
                </h2>
                <p>
                    <?= t('cabinet.cave_intro') ??
                    'Personal space of the Creator. Your nicknames, stories and galleries live here.' ?>
                </p>

                <div class="mt-auto">
                    <a href="/add_nickname.php" class="btn btn-primary">
                        <?= t('cabinet.cave_button') ?? 'I am the Creator' ?>
                    </a>
                    <p class="text-muted small mt-2 mb-0">
                        <?= t('cabinet.cave_hint') ??
                        'Click to create your first nickname or a new creature for the gallery.' ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Права колонка: реклама бот-мережі -->
    <aside class="col-md-3">
        <div class="card h-100 shadow-sm bg-dark text-light">
            <div class="card-body">
                <h2 class="h5 mb-3">
                    <?= t('cabinet.ads_title') ?? 'Clicuha Bot Network Ads' ?>
                </h2>
                <p class="mb-2">
                    <?= t('cabinet.ads_intro') ??
                    'Vertical feed for bot banners, partner projects and events.' ?>
                </p>
                <p class="mb-2 text-muted small">
                    <?= t('cabinet.ads_future') ??
                    'In the future you can show reel-like images, quest previews and promo stories here.' ?>
                </p>
                <p class="mb-0 text-muted small">
                    <?= t('cabinet.ads_showcase') ??
                    'This is your separate showcase for bots and characters living next to your nicknames.' ?>
                </p>
            </div>
        </div>
    </aside>
</div>

<!-- Нижній блок: Архів / бібліотека -->
<div class="row mb-4">
    <section class="col-md-6 offset-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">
                    <?= t('cabinet.archive_title') ?? 'Archive / Library' ?>
                </h2>
                <p class="mb-2">
                    <?= t('cabinet.archive_intro') ??
                    'Here will be a list of saved stories, illustrations, battles and other Clicuha artefacts — a full chronicle of your creativity.' ?>
                </p>
                <p class="mb-0 text-muted small">
                    <?= t('cabinet.archive_future') ??
                    'Later we can split it into vertical feeds for stories, battles, illustrations, etc.' ?>
                </p>
            </div>
        </div>
    </section>
</div>

<div class="text-center text-muted small mb-4">
    © 2025 Clicuha · CABINET v0.7-columns
</div>

<?php
// Загальний футер сайту
require __DIR__ . '/partials/footer.php';
