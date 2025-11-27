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

$currentUserId = $_SESSION['user_id'] ?? null;
$canEdit      = $currentUserId && isset($clicuha['user_id']) && (int)$clicuha['user_id'] === (int)$currentUserId;

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
<?php require __DIR__ . '/partials/navbar.php'; ?>

<nav class="navbar navbar-light clic-nav mb-4">
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

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-outline-secondary">&larr; back</a>
                            <a href="index.php#card-<?= (int)$clicuha['id'] ?>" class="btn btn-outline-primary">All Clicuhas</a>
                        </div>

                        <?php if ($canEdit): ?>
                            <button type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editNicknameModal">
                                Редагувати
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<div class="modal fade" id="editNicknameModal" tabindex="-1" aria-labelledby="editNicknameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="/edit_nickname.php?id=<?= (int)$clicuha['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="editNicknameModalLabel">Редагувати клікуху</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="edit-title">Назва</label>
                    <input type="text"
                           class="form-control"
                           id="edit-title"
                           name="title"
                           value="<?= htmlspecialchars($clicuha['title'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="edit-slug">Slug (@ім'я)</label>
                    <input type="text"
                           class="form-control"
                           id="edit-slug"
                           name="slug"
                           value="<?= htmlspecialchars($clicuha['slug'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="edit-description">Опис</label>
                    <textarea class="form-control"
                              id="edit-description"
                              name="description"
                              rows="4"><?= htmlspecialchars($clicuha['description'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="submit" class="btn btn-primary">Зберегти</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
