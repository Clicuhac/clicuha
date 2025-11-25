<?php
// TEST-DEPLOY-002
git add .
git commit -m "deploy test 2"
git push

// CABINET VERSION: LANI-TEST-1


require_once __DIR__ . '/config.php';

// Припускаємо, що session_start() вже викликається в config.php
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Тема інтер'єру кабінету
$allowedThemes = ['classic', 'cave', 'palace', 'vigvam'];
$userTheme = $_SESSION['cabinet_theme'] ?? 'classic';

if (isset($_GET['theme'])) {
    $requestedTheme = (string)$_GET['theme'];
    if (in_array($requestedTheme, $allowedThemes, true)) {
        $_SESSION['cabinet_theme'] = $requestedTheme;
        $userTheme = $requestedTheme;
    }
    // щоб уникнути повторної відправки параметра
    header('Location: /cabinet.php');
    exit;
}

// Тягнемо дані користувача
$userStmt = $pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = :id LIMIT 1');
$userStmt->execute([':id' => $userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // якщо раптом користувача видалили — глушимо сесію і просимо перелогін
    session_destroy();
    header('Location: /login.php');
    exit;
}

$profileError = '';
$profileSuccess = '';

// Обробка форми оновлення ніка
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'profile') {
    $newUsername = trim((string)($_POST['username'] ?? ''));

    if ($newUsername === '') {
        $profileError = 'Введи нік.';
    } elseif (mb_strlen($newUsername) < 3 || mb_strlen($newUsername) > 32) {
        $profileError = 'Нік має бути довжиною від 3 до 32 символів.';
    } else {
        // Перевіримо унікальність
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u AND id <> :id');
        $checkStmt->execute([
            ':u'  => $newUsername,
            ':id' => $userId,
        ]);
        $exists = (int)$checkStmt->fetchColumn();

        if ($exists > 0) {
            $profileError = 'Такий нік вже використовується. Обери інший.';
        } else {
            $updateStmt = $pdo->prepare('UPDATE users SET username = :u WHERE id = :id');
            $updateStmt->execute([
                ':u'  => $newUsername,
                ':id' => $userId,
            ]);

            $profileSuccess   = 'Нік оновлено.';
            $user['username'] = $newUsername;
        }
    }
}

// Тимчасово тягнемо всі клікухи (без фільтру по user_id)
$nickStmt = $pdo->query("
    SELECT id, title, slug, is_public, created_at
    FROM nicknames
    ORDER BY created_at DESC
");
$myNicknames = $nickStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кабінет автора — Clicuha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + базові стилі сайту -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css?v=123">
    <!-- Стиль інтер'єру кабінету -->
    <link rel="stylesheet" href="/assets/css/cabinet-base.css">
</head>
<body class="bg-light">

<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="cabinet-wrapper theme-<?=
    htmlspecialchars($userTheme, ENT_QUOTES, 'UTF-8')
?>">
    <!-- Ліва основна частина -->
    <section class="cabinet-main container py-4">

        <h1 class="h4 mb-3">Кабінет автора</h1>

        <?php if ($profileError !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($profileSuccess !== ''): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($profileSuccess, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Профіль -->
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 mb-3">Мій профіль</h2>

                        <dl class="row small mb-3">
                            <dt class="col-4">Логін:</dt>
                            <dd class="col-8 mb-1">
                                <?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </dd>

                            <dt class="col-4">Email:</dt>
                            <dd class="col-8 mb-1">
                                <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </dd>
                        </dl>

                        <form method="post" class="mt-auto">
                            <input type="hidden" name="form_type" value="profile">
                            <div class="mb-2">
                                <label for="username" class="form-label small mb-1">Змінити нік</label>
                                <input
                                    type="text"
                                    name="username"
                                    id="username"
                                    class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    maxlength="32"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Оновити нік
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Інтер'єр / тема кабінету -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h2 class="h6 mb-3">Інтер'єр кабінету</h2>
                        <p class="small text-muted">
                            Обери антураж: класика, печера, палац або вігвам. Це змінює настрій, але не функціонал.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="/cabinet.php?theme=classic"
                               class="btn btn-sm <?= $userTheme === 'classic' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                Класика
                            </a>
                            <a href="/cabinet.php?theme=cave"
                               class="btn btn-sm <?= $userTheme === 'cave' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                Печера
                            </a>
                            <a href="/cabinet.php?theme=palace"
                               class="btn btn-sm <?= $userTheme === 'palace' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                Палац
                            </a>
                            <a href="/cabinet.php?theme=vigvam"
                               class="btn btn-sm <?= $userTheme === 'vigvam' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                Вігвам
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Клікухи + дії -->
            <div class="col-md-7">
                <!-- Швидкі дії -->
                <div class="card mb-4">
                    <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between">
                        <div class="mb-3 mb-sm-0">
                            <h2 class="h6 mb-1">Творчі дії</h2>
                            <p class="small text-muted mb-0">
                                Створи нову клікуху або подію в своєму всесвіті Clicuha.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="/?open=create" class="btn btn-sm btn-outline-primary">
                                Я – Творець (клікуха)
                            </a>
                            <a href="/create_event.php" class="btn btn-sm btn-outline-secondary">
                                Створити подію
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Список клікух -->
                <div class="card">
                    <div class="card-body">
                        <h2 class="h6 mb-3">Всі мої клікухи</h2>

                        <?php if (empty($myNicknames)): ?>
                            <p class="small text-muted mb-0">
                                Поки що немає жодної клікухи. Натисни “Я – Творець”, щоб створити першу.
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>Назва</th>
                                        <th>Slug</th>
                                        <th class="text-center">Публічна</th>
                                        <th>Створено</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($myNicknames as $nick): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($nick['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= htmlspecialchars($nick['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($nick['is_public'])): ?>
                                                    ✅
                                                <?php else: ?>
                                                    🔒
                                                <?php endif; ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= htmlspecialchars($nick['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="/nickname.php?slug=<?= urlencode($nick['slug'] ?? '') ?>"
                                                   class="btn btn-sm btn-link">
                                                    Відкрити
                                                </a>
                                                <a href="/edit_nickname.php?id=<?= (int)$nick['id'] ?>"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    Редагувати
                                                </a>
                                                <a href="/delete_nickname.php?id=<?= (int)$nick['id'] ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Точно видалити цю клікуху?');">
                                                    Видалити
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </section>

    <!-- Права колонка для майбутньої реклами / віджетів -->
    <aside class="cabinet-ads">
        <div class="ads-block">
            <p class="small text-muted mb-0">
                Тут буде контекстна реклама Clicuha Bot Network, статистика або міні-віджети.
            </p>
        </div>
    </aside>

</main>

<footer class="border-top py-4 mt-4">
    <div class="container small text-muted">
        &copy; <?= date('Y') ?> Clicuha — міні-соцмережа кличок і персонажів.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>







