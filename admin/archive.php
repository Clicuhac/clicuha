<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/avatar.php';

$admin = require_admin($pdo);

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf'] ?? '');
    if (!hash_equals((string)$_SESSION['admin_csrf'], $token)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && (string)($_POST['action'] ?? '') === 'restore') {
        $stmt = $pdo->prepare('UPDATE nicknames SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL');
        $stmt->execute([$id]);
        $_SESSION['admin_archive_flash'] = $stmt->rowCount() > 0
            ? 'Клікуху відновлено з архіву.'
            : 'Клікуха вже була відновлена.';
    }

    header('Location: /admin/archive.php');
    exit;
}

$flash = (string)($_SESSION['admin_archive_flash'] ?? '');
unset($_SESSION['admin_archive_flash']);

$stmt = $pdo->query(
    "SELECT n.id, n.title, n.slug, n.user_id, n.is_anonymous, n.avatar_path, n.created_at, n.deleted_at,
            u.email, u.username
     FROM nicknames n
     LEFT JOIN users u ON u.id = n.user_id
     WHERE n.deleted_at IS NOT NULL
     ORDER BY n.deleted_at DESC
     LIMIT 200"
);
$nicknames = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Archive · Clicuha Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
:root{--admin-side:#17151d;--admin-bg:#f4f3f7;--admin-lav:#cba3f7}body{margin:0;background:var(--admin-bg);color:#222}.admin-layout{min-height:100vh;display:grid;grid-template-columns:230px 1fr}.admin-sidebar{background:var(--admin-side);color:#fff;padding:24px 18px;display:flex;flex-direction:column;min-height:100vh}.admin-brand{color:#fff;font-weight:800;font-size:1.2rem;text-decoration:none;margin-bottom:30px}.admin-brand span{color:var(--admin-lav);font-size:.75rem;margin-left:4px}.admin-nav{display:grid;gap:8px}.admin-nav a,.admin-nav span{color:#d7d3dc;text-decoration:none;padding:10px 12px;border-radius:10px}.admin-nav a.active{color:#17151d;background:var(--admin-lav);font-weight:700}.admin-nav small{float:right;opacity:.55}.admin-sidebar-bottom{margin-top:auto;display:grid;gap:8px}.admin-sidebar-bottom a{color:#cfcad5;text-decoration:none;font-size:.9rem}.admin-main{padding:32px}.panel{background:#fff;border-radius:16px;box-shadow:0 8px 22px rgba(26,18,37,.06);padding:22px}.avatar{width:42px;height:42px;border-radius:10px;object-fit:cover;background:#eee}.avatar-empty{width:42px;height:42px;border-radius:10px;background:#eee;display:grid;place-items:center;color:#999;font-size:.7rem}.table>:not(caption)>*>*{padding:.8rem .55rem}@media(max-width:800px){.admin-layout{grid-template-columns:1fr}.admin-sidebar{min-height:auto}.admin-main{padding:20px}}
</style>
</head>
<body><div class="admin-layout">
<?php $adminSection='archive'; require __DIR__ . '/../partials/admin_sidebar.php'; ?>
<main class="admin-main">
<div class="d-flex justify-content-between align-items-start mb-4"><div><h1 class="h3 mb-1">Archive</h1><div class="text-secondary">Архівні клікухи · <?= count($nicknames) ?></div></div><div class="text-end small text-secondary"><strong><?= h(($admin['username'] ?? '') ?: $admin['email']) ?></strong><br>administrator</div></div>
<?php if ($flash !== ''): ?><div class="alert alert-success py-2"><?= h($flash) ?></div><?php endif; ?>
<section class="panel"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Avatar</th><th>Clicuha</th><th>Owner</th><th>Created</th><th>Archived</th><th></th></tr></thead><tbody>
<?php foreach ($nicknames as $n): ?><tr>
<td><?php $avatarUrl=clicuha_avatar_url($n['avatar_path'] ?? null); if($avatarUrl):?><img class="avatar" src="<?=h($avatarUrl)?>" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"><div class="avatar-empty" style="display:none">—</div><?php else:?><div class="avatar-empty">—</div><?php endif;?></td>
<td><strong><?= h($n['title']) ?></strong><?php if(!empty($n['slug'])):?><div class="small text-secondary">@<?= h($n['slug']) ?></div><?php endif;?><?php if(!empty($n['is_anonymous'])):?><span class="badge text-bg-light">anonymous</span><?php endif;?></td>
<td><?= h(($n['username'] ?? '') ?: ($n['email'] ?? '—')) ?><?php if(!empty($n['email']) && !empty($n['username'])):?><div class="small text-secondary"><?= h($n['email']) ?></div><?php endif;?></td>
<td class="small text-secondary"><?= h((string)$n['created_at']) ?></td>
<td class="small"><strong><?= h((string)$n['deleted_at']) ?></strong></td>
<td class="text-end"><form method="post" class="d-inline"><input type="hidden" name="csrf" value="<?= h($_SESSION['admin_csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><input type="hidden" name="action" value="restore"><button class="btn btn-sm btn-outline-success">Restore</button></form></td>
</tr><?php endforeach; ?>
<?php if(!$nicknames):?><tr><td colspan="6" class="text-center text-secondary py-4">Архів порожній.</td></tr><?php endif; ?>
</tbody></table></div></section>
</main></div></body></html>
