<?php

require_once __DIR__ . '/config.php';


// Користувач має бути залогіненим
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT id, title, slug, description, created_at
    FROM nicknames
    WHERE user_id = :uid
      AND deleted_at IS NULL
    ORDER BY created_at DESC
");
$stmt->execute([':uid' => $userId]);
$my = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Мої клікухи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container py-4">
    <h1 class="h4 mb-4">Мої клікухи</h1>

    <?php if (empty($my)): ?>
        <div class="alert alert-info">У тебе ще немає жодної клікухи.</div>
    <?php else: ?>

        <div class="row">
            <?php foreach ($my as $nick): ?>
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">

                            <h5 class="card-title">
                                <?= htmlspecialchars($nick['title']) ?>
                            </h5>

                            <div class="text-muted mb-2">@<?= htmlspecialchars($nick['slug']) ?></div>

                            <p class="small">
                                <?= nl2br(htmlspecialchars($nick['description'])) ?>
                            </p>

                            <a href="/edit_nickname.php?id=<?= (int)$nick['id'] ?>" class="btn btn-sm btn-primary">
    Редагувати
</a>


                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

</body>
</html>
