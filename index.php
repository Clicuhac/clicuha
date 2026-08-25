<?php
require __DIR__ . '/config.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;
$total = (int)$pdo->query("SELECT COUNT(*) FROM nicknames WHERE deleted_at IS NULL")->fetchColumn();
$totalPages = max(1, (int)ceil($total / $limit));
$stmt = $pdo->prepare("SELECT n.*, u.username AS author_username FROM nicknames n LEFT JOIN users u ON u.id = n.user_id WHERE n.deleted_at IS NULL ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$nicknames = $stmt->fetchAll(PDO::FETCH_ASSOC);
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clicuha — <?= h(t('latest_nicknames')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">
<?php require __DIR__.'/partials/navbar.php'; ?>
<main class="container py-4">
  <p class="text-muted"><?= h(t('gallery.intro')) ?></p>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= h(t('latest_nicknames')) ?></h1>
    <?php if ($currentUserId): ?><a href="/add_bootstrap.php" class="btn btn-sm btn-primary"><?= h(t('menu.new')) ?></a><?php endif; ?>
  </div>
  <div class="row g-3">
    <?php if (!$nicknames): ?><div class="col-12"><div class="alert alert-secondary"><?= h(t('gallery.empty')) ?></div></div><?php endif; ?>
    <?php foreach ($nicknames as $n): ?>
      <div class="col-12 col-sm-6 col-lg-4"><div class="card h-100"><div class="card-body d-flex flex-column">
        <h2 class="h5 mb-1"><?= h($n['title']) ?></h2>
        <?php if ($n['slug']): ?><div class="text-muted small">@<?= h($n['slug']) ?></div><?php endif; ?>
        <?php $ownerId = ($n['user_id'] === null || $n['user_id'] === '') ? null : (int)$n['user_id']; ?>
        <div class="mt-1 mb-2">
          <?php if ($ownerId === null): ?><span class="badge bg-success"><?= h(t('status_free')) ?></span>
          <?php elseif ((int)$n['is_anonymous'] === 0 && $n['author_username']): ?><span class="badge bg-primary"><?= h(t('creator')) ?>: @<?= h($n['author_username']) ?></span>
          <?php else: ?><span class="badge bg-primary"><?= h(t('status_owned')) ?></span><?php endif; ?>
        </div>
        <?php if ($n['description']): ?><p class="small text-muted mb-1"><?= h(mb_substr($n['description'],0,140)) ?><?= mb_strlen($n['description']) > 140 ? '…' : '' ?></p><?php endif; ?>
        <a href="/view.php?id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline-primary mt-auto">Go →</a>
      </div></div></div>
    <?php endforeach; ?>
  </div>
  <?php if ($totalPages > 1): ?><nav><ul class="pagination justify-content-center mt-4">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page-1 ?>">«</a></li>
    <?php for ($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?>
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page+1 ?>">»</a></li>
  </ul></nav><?php endif; ?>
</main>
<footer class="border-top py-3 mt-4 text-center small text-muted">© <?= date('Y') ?> Clicuha</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
