<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';

$admin = require_admin($pdo);

$stats = [
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'nicknames' => (int)$pdo->query('SELECT COUNT(*) FROM nicknames')->fetchColumn(),
    'active' => (int)$pdo->query('SELECT COUNT(*) FROM nicknames WHERE deleted_at IS NULL')->fetchColumn(),
    'archived' => (int)$pdo->query('SELECT COUNT(*) FROM nicknames WHERE deleted_at IS NOT NULL')->fetchColumn(),
    'with_avatar' => (int)$pdo->query("SELECT COUNT(*) FROM nicknames WHERE deleted_at IS NULL AND avatar_path IS NOT NULL AND avatar_path <> ''")->fetchColumn(),
];

$latestNicknames = $pdo->query(
    "SELECT n.id, n.title, n.slug, n.created_at, u.email, u.username
     FROM nicknames n
     LEFT JOIN users u ON u.id = n.user_id
     WHERE n.deleted_at IS NULL
     ORDER BY n.created_at DESC
     LIMIT 6"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clicuha Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    :root { --admin-side:#17151d; --admin-bg:#f4f3f7; --admin-lav:#cba3f7; }
    body { margin:0; background:var(--admin-bg); color:#222; }
    .admin-layout { min-height:100vh; display:grid; grid-template-columns:230px 1fr; }
    .admin-sidebar { background:var(--admin-side); color:#fff; padding:24px 18px; display:flex; flex-direction:column; min-height:100vh; }
    .admin-brand { color:#fff; font-weight:800; font-size:1.2rem; text-decoration:none; margin-bottom:30px; }
    .admin-brand span { color:var(--admin-lav); font-size:.75rem; margin-left:4px; }
    .admin-nav { display:grid; gap:8px; }
    .admin-nav a,.admin-nav span { color:#d7d3dc; text-decoration:none; padding:10px 12px; border-radius:10px; }
    .admin-nav a.active { color:#17151d; background:var(--admin-lav); font-weight:700; }
    .admin-nav small { float:right; opacity:.55; }
    .admin-nav .admin-nav-group { color:#817b89; font-size:.68rem; font-weight:700; letter-spacing:.12em; padding:12px 12px 0; margin-top:4px; }
    .admin-sidebar-bottom { margin-top:auto; display:grid; gap:8px; }
    .admin-sidebar-bottom a { color:#cfcad5; text-decoration:none; font-size:.9rem; }
    .admin-main { padding:32px; }
    .admin-top { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:28px; }
    .admin-top h1 { margin:0; font-size:1.75rem; }
    .admin-user { text-align:right; font-size:.9rem; color:#666; }
    .stat-card { border:0; border-radius:16px; box-shadow:0 8px 22px rgba(26,18,37,.06); }
    .stat-number { font-size:2rem; font-weight:800; line-height:1; }
    .panel { background:#fff; border-radius:16px; box-shadow:0 8px 22px rgba(26,18,37,.06); padding:22px; }
    .table > :not(caption) > * > * { padding:.8rem .6rem; }
    @media (max-width: 800px) {
      .admin-layout { grid-template-columns:1fr; }
      .admin-sidebar { min-height:auto; }
      .admin-main { padding:20px; }
    }
  </style>
</head>
<body>
<div class="admin-layout">
  <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-top">
      <div>
        <h1>Dashboard</h1>
        <div class="text-secondary">Перший контур адмінки Clicuha</div>
      </div>
      <div class="admin-user">
        <strong><?= h(($admin['username'] ?? '') ?: $admin['email']) ?></strong><br>
        administrator
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-xl"><div class="card stat-card h-100"><div class="card-body"><div class="text-secondary small mb-2">Користувачі</div><div class="stat-number"><?= $stats['users'] ?></div></div></div></div>
      <div class="col-6 col-xl"><div class="card stat-card h-100"><div class="card-body"><div class="text-secondary small mb-2">Усі клікухи</div><div class="stat-number"><?= $stats['nicknames'] ?></div></div></div></div>
      <div class="col-6 col-xl"><div class="card stat-card h-100"><div class="card-body"><div class="text-secondary small mb-2">Активні</div><div class="stat-number"><?= $stats['active'] ?></div></div></div></div>
      <div class="col-6 col-xl"><div class="card stat-card h-100"><div class="card-body"><div class="text-secondary small mb-2">З аватаром</div><div class="stat-number"><?= $stats['with_avatar'] ?></div></div></div></div>
      <div class="col-6 col-xl"><div class="card stat-card h-100"><div class="card-body"><div class="text-secondary small mb-2">В архіві</div><div class="stat-number"><?= $stats['archived'] ?></div></div></div></div>
    </div>

    <section class="panel">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Останні клікухи</h2>
        <span class="small text-secondary">останні 6</span>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Clicuha</th><th>Owner</th><th>Created</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($latestNicknames as $nick): ?>
            <tr>
              <td><strong><?= h($nick['title']) ?></strong><?php if (!empty($nick['slug'])): ?><div class="small text-secondary">@<?= h($nick['slug']) ?></div><?php endif; ?></td>
              <td><?= h(($nick['username'] ?? '') ?: ($nick['email'] ?? '—')) ?></td>
              <td class="text-secondary small"><?= h((string)$nick['created_at']) ?></td>
              <td class="text-end"><a href="/view.php?id=<?= (int)$nick['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$latestNicknames): ?><tr><td colspan="4" class="text-secondary">Клікух поки немає.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>
</body>
</html>
