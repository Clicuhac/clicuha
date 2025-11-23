<?php
// Підключаємо те саме, що й в index.php (ПОДИВИСЬ, як там називається файл)
require_once __DIR__ . '/config.php';
 // або db.php / bootstrap.php - як у тебе на головній

// Беремо id з GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

// !!! СКОПІЮЙ свій SELECT з index.php і адаптуй
// Подивись в index.php, де ти тягнеш список клікух:
// типу: SELECT * FROM nicknames ...
// Тут той самий код, але з WHERE id = :id LIMIT 1

$sql = "SELECT * FROM nicknames WHERE id = :id LIMIT 1"; // ← назву таблиці та полів візьми з index.php
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$clicuha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$clicuha) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

// Якщо у тебе є загальний header.php – підключи його так само, як у index.php
// include __DIR__ . '/header.php';
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($clicuha['title'] ?? $clicuha['name'] ?? 'Clicuha') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Можеш замінити на свій локальний CSS, якщо вже підключав Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Clicuha</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h1 class="card-title h3 mb-2">
                        <?= htmlspecialchars($clicuha['title'] ?? $clicuha['name'] ?? 'Clicuha') ?>
                    </h1>

                    <?php if (!empty($clicuha['slug'])): ?>
                        <p class="text-muted mb-3">
                            @<?= htmlspecialchars($clicuha['slug']) ?>
                        </p>
                    <?php endif; ?>

                    <hr>

                    <p class="card-text" style="white-space: pre-wrap;">
                        <?= htmlspecialchars($clicuha['description'] ?? $clicuha['text'] ?? '') ?>
                    </p>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-secondary">&larr; back</a>
                        <a href="index.php#card-<?= (int)$clicuha['id'] ?>" class="btn btn-outline-primary">All Clicuhas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
