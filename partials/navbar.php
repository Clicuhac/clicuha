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
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="/index.php">Clicuha</a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">
                        <?= t('menu.home') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php?view=list">
                        <?= t('menu.nick') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php?page=about">
                        <?= t('menu.about') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php?page=contacts">
                        <?= t('menu.contacts') ?>
                    </a>
                </li>
            </ul>

            <!-- Правий блок: авторизація + мови -->
            <div class="d-flex align-items-center gap-3">

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <!-- Авторизований -->
                    <a href="/cabinet.php" class="btn btn-outline-dark btn-sm">
                        Кабінет
                    </a>
                    <a href="/logout.php" class="btn btn-dark btn-sm">
                        Вийти
                    </a>
                <?php else: ?>
                    <!-- Гість -->
                    <a href="/login.php" class="btn btn-sm btn-outline-dark">
                        Login
                    </a>
                    <a href="/register.php" class="nav-link">
                        Join
                    </a>
                <?php endif; ?>

                <!-- Мовні кнопки -->
                <div class="d-flex gap-2">
                    <a href="<?= $baseUrl . '?' . $qs . 'lang=uk' ?>"
                       class="btn btn-outline-secondary btn-sm">
                        UA
                    </a>
                    <a href="<?= $baseUrl . '?' . $qs . 'lang=en' ?>"
                       class="btn btn-outline-secondary btn-sm">
                        EN
                    </a>
                    <a href="<?= $baseUrl . '?' . $qs . 'lang=ru' ?>"
                       class="btn btn-outline-secondary btn-sm">
                        RU
                    </a>
                </div>

            </div>
        </div>
    </div>
</nav>
