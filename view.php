<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); echo 'Not found'; exit; }
$stmt = $pdo->prepare('SELECT * FROM nicknames WHERE id = :id AND deleted_at IS NULL LIMIT 1');
$stmt->execute([':id'=>$id]);
$clicuha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$clicuha) { http_response_code(404); echo 'Not found'; exit; }
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$canEdit = $currentUserId !== null && (int)$clicuha['user_id'] === $currentUserId;
?>
<!doctype html><html lang="<?= h($lang) ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= h($clicuha['title']) ?> · Clicuha</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="/assets/css/sema.css?v=123"></head><body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>
<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-8"><div class="card shadow-sm border-0"><div class="card-body">
<h1 class="card-title h3 mb-2"><?= h($clicuha['title']) ?></h1><?php if ($clicuha['slug']): ?><p class="text-muted mb-3">@<?= h($clicuha['slug']) ?></p><?php endif; ?><hr><p class="card-text" style="white-space:pre-wrap"><?= h($clicuha['description'] ?? '') ?></p>
<div class="mt-4 d-flex justify-content-between"><a href="/index.php" class="btn btn-outline-secondary">← <?= h(t('latest_nicknames')) ?></a><?php if ($canEdit): ?><a href="/edit_nickname.php?id=<?= $id ?>" class="btn btn-primary"><?= h(t('btn_edit')) ?></a><?php endif; ?></div>
</div></div></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
