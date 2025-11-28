<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = !empty($_SESSION['user_id'] ?? null);
$page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4"clic-nav,>
    <div class="container">
        <a class="navbar-brand" href="/index.php">Clicuha</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            <!-- Ліве меню -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="/index.php">Головна</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/nicknames.php">Клікухи</a>
                </li>

                <?php if ($loggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/my_nicknames.php">Мої клікухи</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="/about.php">Про нас</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/contacts.php">Контакти</a>
                </li>

            </ul>

            <!-- Праві кнопки -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <?php if ($loggedIn): ?>

                    <!-- Нова клікуха -->
                    <li class="nav-item">
                        <a href="/add_bootstrap.php" class="btn btn-success btn-sm">
                            Нова клікуха
                        </a>
                    </li>

                    <!-- Кабінет -->
                    <li class="nav-item">
                        <a href="/cabinet.php" class="btn btn-outline-primary btn-sm">
                            Кабінет
                        </a>
                    </li>

                    <!-- Вийти -->
                    <li class="nav-item">
                        <a href="/logout.php" class="btn btn-outline-danger btn-sm">
                            Вийти
                        </a>
                    </li>

                <?php else: ?>

                    <!-- Гість -->

                    <?php if ($page !== 'login.php'): ?>
                        <li class="nav-item">
                            <a href="/login.php" class="btn btn-warning btn-sm">Login</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($page !== 'register.php'): ?>
                        <li class="nav-item">
                            <a href="/register.php" class="btn btn-outline-warning btn-sm">Join</a>
                        </li>
                    <?php endif; ?>

                <?php endif; ?>

                <!-- Мовні кнопки -->
                <li class="nav-item">
                    <a href="?lang=ua" class="btn btn-outline-dark btn-sm">UA</a>
                </li>

                <li class="nav-item">
                    <a href="?lang=en" class="btn btn-outline-dark btn-sm">EN</a>
                </li>

                <li class="nav-item">
                    <a href="?lang=ru" class="btn btn-outline-dark btn-sm">RU</a>
                </li>

            </ul>
        </div>
    </div>
</nav>
