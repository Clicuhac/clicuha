<?php
require __DIR__ . '/config.php';

// Показ помилок тимчасово
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// Пагінація
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Загальна кількість
$total = (int)$pdo->query("SELECT COUNT(*) FROM nicknames")->fetchColumn();
$totalPages = max(1, ceil($total / $limit));

// Дані
$stmt = $pdo->prepare("
    SELECT n.*, u.username AS author_username
    FROM nicknames n
    LEFT JOIN users u ON u.id = n.user_id
    ORDER BY n.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$nicknames = $stmt->fetchAll(PDO::FETCH_ASSOC);
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clicuha — Галерея</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=<?= time() ?>">
</head>
<body class="bg-light">

<?php require __DIR__.'/partials/navbar.php'; ?>

<main class="container py-4">

  <h1 class="h4 mb-3">Hello world</h1>
  <p class="text-muted">Галерея творчих клікух. Створюй образ, дивись інших, грайся з персонажами.</p>

  <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h4">Галерея</h2>

      <a href="/cabinet.php?open=create" class="btn btn-sm btn-primary">
        Створити клікуху
      </a>
  </div>

  <div class="row g-3">

    <?php if (!$nicknames): ?>
        <div class="col-12">
            <div class="alert alert-secondary">Поки нічого немає.</div>
        </div>
    <?php endif; ?>

    <?php foreach ($nicknames as $n): ?>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body d-flex flex-column">

            <h3 class="h5 mb-1"><?= h($n['title']) ?></h3>

            <?php if ($n['slug']): ?>
              <div class="text-muted small">@<?= h($n['slug']) ?></div>
            <?php endif; ?>

            <?php
              $ownerId = ($n['user_id'] === null || $n['user_id'] === '') ? null : (int)$n['user_id'];
              $isOwnedByCurrentUser = $ownerId !== null && $currentUserId !== null && $ownerId === $currentUserId;
            ?>
            <div class="mt-1 mb-2">
              <?php if ($isOwnedByCurrentUser): ?>
                <span class="badge bg-secondary">Вже належить</span>
              <?php elseif ($ownerId === null): ?>
                <span class="badge bg-success">Вільна</span>
              <?php else: ?>
                <?php if ((int)$n['is_anonymous'] === 0 && $n['author_username']): ?>
                    <span class="badge bg-secondary">Автор: @<?= h($n['author_username']) ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary">Вже належить</span>
                <?php endif; ?>
              <?php endif; ?>
            </div>

            <?php if ($n['description']): ?>
                <p class="small text-muted mb-1">
                  <?= h(mb_substr($n['description'], 0, 100)) ?>…
                </p>
            <?php endif; ?>

            <a href="view.php?id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline-primary mt-auto">
              Go →
            </a>

          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>

  <?php if ($totalPages > 1): ?>
    <nav>
      <ul class="pagination justify-content-center mt-4">

        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>">«</a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>">»</a>
        </li>

      </ul>
    </nav>
  <?php endif; ?>

</main>

<footer class="border-top py-3 mt-4 text-center small text-muted">
  © <?= date('Y') ?> Clicuha
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
