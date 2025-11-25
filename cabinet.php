<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang/lang.php';

// Підключення хедера
require_once __DIR__ . '/partials/header.php';
?>
<main class="container my-4">

    <div class="row g-4">

        <!-- Ліва панель -->
        <div class="col-md-3">
            <div class="p-3 shadow-sm rounded bg-white">
                <h5>Управління</h5>
                <ul class="list-unstyled">
                    <li>• <a href="/my_nicks.php">Всі мої клікухи</a></li>
                    <li>• <a href="/create.php">Створити клікуху</a></li>
                    <li>• <a href="/settings.php">Налаштування</a></li>
                    <li>• <a href="/style.php">Інтер'єр кабінету</a></li>
                </ul>
            </div>
        </div>

        <!-- Центр: Події -->
        <div class="col-md-3">
            <div class="p-3 shadow-sm rounded bg-white">
                <h5>Події Clicuha</h5>
                <p>Тут з’являться батли, тусовки, галереї та інші події.</p>
                <p>Поки що це заглушка. Потім буде вертикальна стрічка подій.</p>
            </div>
        </div>

        <!-- Центр: Моя печера -->
        <div class="col-md-3">
            <div class="p-3 shadow-sm rounded bg-white text-center">
                <h5>Моя печера</h5>
                <p>Особистий простір творця — твої клікухи та історії.</p>
                <a class="btn btn-primary" href="/create.php">Я — Творець</a>
            </div>
        </div>

        <!-- Права колонка (реклама) -->
        <div class="col-md-3">
            <div class="p-3 shadow-sm rounded text-white" style="background:#0b1620;">
                <h5>Реклама Clicuha Bot Network</h5>
                <p>Вертикальна стрічка для банерів ботів, партнерських проектів та івентів.</p>
            </div>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
