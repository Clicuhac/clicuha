<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/avatar.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT id, title, slug, description, avatar_path, created_at
    FROM nicknames
    WHERE user_id = :uid
      AND deleted_at IS NULL
    ORDER BY created_at DESC
");
$stmt->execute([':uid' => $userId]);
$my = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Мої клікухи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css?v=124">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>
<main class="container py-4">
    <h1 class="h4 mb-4">Мої клікухи</h1>

    <?php if (empty($my)): ?>
        <div class="alert alert-info">У тебе ще немає жодної клікухи.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($my as $nick): ?>
                <?php
                    $avatarUrl = clicuha_avatar_url($nick['avatar_path'] ?? null);
                    $initial = mb_strtoupper(mb_substr(trim((string)$nick['title']), 0, 1, 'UTF-8'), 'UTF-8');
                    if ($initial === '') $initial = 'C';
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 clic-gallery-card">
                        <div class="card-body d-flex flex-column">
                            <div class="clic-gallery-head">
                                <a class="clic-gallery-avatar" href="/view.php?id=<?= (int)$nick['id'] ?>" aria-label="<?= h($nick['title']) ?>">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?= h($avatarUrl) ?>" alt="<?= h($nick['title']) ?>">
                                    <?php else: ?>
                                        <span aria-hidden="true"><?= h($initial) ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="clic-gallery-titlebox">
                                    <h5 class="card-title mb-1"><?= h($nick['title']) ?></h5>
                                    <?php if (!empty($nick['slug'])): ?><div class="text-muted small">@<?= h($nick['slug']) ?></div><?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($nick['description'])): ?>
                                <p class="small mt-3 mb-3"><?= nl2br(h($nick['description'])) ?></p>
                            <?php endif; ?>

                            <a href="/edit_nickname.php?id=<?= (int)$nick['id'] ?>" class="btn btn-sm btn-primary mt-auto align-self-start">Редагувати</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
