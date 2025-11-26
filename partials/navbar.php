<?php
// Навбар для всіх сторінок

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// зберігаємо поточні параметри, крім lang
$query = $_GET ?? [];
unset($query['lang']);

$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
$qs = http_build_query($query);
$qs = $qs ? $qs . '&' : '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="/index.php">Clicuha</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php"><?= t('menu.home') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/?view=list"><?= t('menu.nick') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/?page=about"><?= t('menu.about') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/?page=contacts"><?= t('menu.contacts') ?></a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="/cabinet.php" class="btn btn-outline-dark btn-sm">
                        <?= t('menu.cabinet') ?>
                    </a>
                    <a href="/logout.php" class="btn btn-dark btn-sm">
                        <?= t('menu.logout') ?>
                    </a>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-outline-dark btn-sm">Login</a>
                    <a href="/register.php" class="btn btn-dark btn-sm">Join</a>
                <?php endif; ?>
            </div>

            <!-- мовні кнопки -->
            <div class="d-flex gap-2 ms-3">
                <a href="?lang=uk" class="btn btn-outline-secondary btn-sm">UA</a>
                <a href="?lang=en" class="btn btn-outline-secondary btn-sm">EN</a>
                <a href="?lang=ru" class="btn btn-outline-secondary btn-sm">RU</a>
            </div>

        </div>
    </div>
</nav>

