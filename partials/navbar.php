<?php
// тут вже є t(), бо config.php підключається ДО navbar.php
// і там оголошено $lang

// зберігаємо поточні параметри, крім lang
$query = $_GET;
unset($query['lang']);
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
$qs = http_build_query($query);
$qs = $qs ? $qs . '&' : '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">


    <div class="container">
        <a class="navbar-brand" href="/">Clicuha</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/"><?= t('menu.home') ?></a>
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
<div class="d-flex align-items-center gap-2 ms-auto">

<?php if (!empty($_SESSION['user_id'])): ?>

    <!-- Авторизований користувач -->
    <a href="/cabinet.php" class="btn btn-outline-dark btn-sm">
        Кабінет
    </a>

    <a href="/logout.php" class="btn btn-dark btn-sm">
        Вийти
    </a>

<?php else: ?>

    <!-- Гість -->
    <a href="/login.php"
       class="btn btn-sm auth-btn auth-btn-login">
        Login
    </a>

    <a href="/register.php" class="nav-link">
        Join
    </a>

<?php endif; ?>


    <!-- Якщо гість -->
    <a href="/login.php"
       class="btn btn-sm auth-btn auth-btn-login">
        Login
    </a>

    <a href="/register.php" class="nav-link">
        Join
    </a>

<?php endif; ?>

</div>


                <!-- Мовні кнопочки, як були -->
     <div class="d-flex gap-2 ms-3">
    <a href="<?= $baseUrl . '?' . $qs . 'lang=uk' ?>"
       class="btn btn-outline-light btn-sm lang-btn">UA</a>

    <a href="<?= $baseUrl . '?' . $qs . 'lang=en' ?>"
       class="btn btn-outline-light btn-sm lang-btn">EN</a>

    <a href="<?= $baseUrl . '?' . $qs . 'lang=ru' ?>"
       class="btn btn-outline-light btn-sm lang-btn">RU</a>
</div>



            </div>

        </div>
    </div>
</nav>
