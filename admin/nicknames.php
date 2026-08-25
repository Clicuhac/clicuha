<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/avatar.php';

$admin = require_admin($pdo);

if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf'] ?? '');
    if (!hash_equals((string)$_SESSION['admin_csrf'], $token)) {
        http_response_code(403); exit('Invalid CSRF token');
    }
    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    if ($id > 0 && $action === 'archive') {
        $stmt = $pdo->prepare('UPDATE nicknames SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $_SESSION['admin_flash'] = 'Клікуху відправлено в архів.';
    } elseif ($id > 0 && $action === 'restore') {
        $stmt = $pdo->prepare('UPDATE nicknames SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL');
        $stmt->execute([$id]);
        $_SESSION['admin_flash'] = 'Клікуху відновлено.';
    }
    $returnStatus = (string)($_POST['return_status'] ?? 'all');
    $returnQ = trim((string)($_POST['return_q'] ?? ''));
    header('Location: /admin/nicknames.php?status=' . urlencode($returnStatus) . ($returnQ !== '' ? '&q=' . urlencode($returnQ) : ''));
    exit;
}

$flash = (string)($_SESSION['admin_flash'] ?? ''); unset($_SESSION['admin_flash']);
$q = trim((string)($_GET['q'] ?? ''));
$status = (string)($_GET['status'] ?? 'all');
$allowedStatuses = ['all','active','free','owned','anonymous','avatar','no_avatar','archived'];
if (!in_array($status,$allowedStatuses,true)) $status='all';
$where=[];$params=[];
if($q!==''){ $where[]='(n.title LIKE :q OR n.slug LIKE :q OR u.email LIKE :q OR u.username LIKE :q)'; $params[':q']='%'.$q.'%'; }
switch($status){case'active':$where[]='n.deleted_at IS NULL';break;case'free':$where[]='n.deleted_at IS NULL AND n.user_id IS NULL';break;case'owned':$where[]='n.deleted_at IS NULL AND n.user_id IS NOT NULL';break;case'anonymous':$where[]='n.deleted_at IS NULL AND n.is_anonymous = 1';break;case'avatar':$where[]="n.deleted_at IS NULL AND n.avatar_path IS NOT NULL AND n.avatar_path <> ''";break;case'no_avatar':$where[]="n.deleted_at IS NULL AND (n.avatar_path IS NULL OR n.avatar_path = '')";break;case'archived':$where[]='n.deleted_at IS NOT NULL';break;}
$sql="SELECT n.id,n.title,n.slug,n.user_id,n.is_anonymous,n.avatar_path,n.created_at,n.deleted_at,u.email,u.username FROM nicknames n LEFT JOIN users u ON u.id=n.user_id";
if($where)$sql.=' WHERE '.implode(' AND ',$where);$sql.=' ORDER BY n.created_at DESC LIMIT 200';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$nicknames=$stmt->fetchAll(PDO::FETCH_ASSOC);
$filters=['all'=>'Усі','active'=>'Активні','free'=>'Вільні','owned'=>'З власником','anonymous'=>'Анонімні','avatar'=>'З аватаром','no_avatar'=>'Без аватара','archived'=>'Архів'];
?>
<!doctype html><html lang="<?= h($lang) ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Clicuhas · Clicuha Admin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><style>
:root{--admin-side:#17151d;--admin-bg:#f4f3f7;--admin-lav:#cba3f7}body{margin:0;background:var(--admin-bg);color:#222}.admin-layout{min-height:100vh;display:grid;grid-template-columns:230px 1fr}.admin-sidebar{background:var(--admin-side);color:#fff;padding:24px 18px;display:flex;flex-direction:column;min-height:100vh}.admin-brand{color:#fff;font-weight:800;font-size:1.2rem;text-decoration:none;margin-bottom:30px}.admin-brand span{color:var(--admin-lav);font-size:.75rem;margin-left:4px}.admin-nav{display:grid;gap:8px}.admin-nav a,.admin-nav span{color:#d7d3dc;text-decoration:none;padding:10px 12px;border-radius:10px}.admin-nav a.active{color:#17151d;background:var(--admin-lav);font-weight:700}.admin-nav small{float:right;opacity:.55}.admin-sidebar-bottom{margin-top:auto;display:grid;gap:8px}.admin-sidebar-bottom a{color:#cfcad5;text-decoration:none;font-size:.9rem}.admin-main{padding:32px}.panel{background:#fff;border-radius:16px;box-shadow:0 8px 22px rgba(26,18,37,.06);padding:22px}.filter-link{display:inline-block;padding:7px 11px;border-radius:10px;text-decoration:none;color:#555;background:#eeeaf2;margin:0 5px 6px 0}.filter-link.active{background:var(--admin-lav);color:#17151d;font-weight:700}.avatar{width:42px;height:42px;border-radius:10px;object-fit:cover;background:#eee}.avatar-empty{width:42px;height:42px;border-radius:10px;background:#eee;display:grid;place-items:center;color:#999;font-size:.7rem}.badge-soft{background:#eeeaf2;color:#4c4058}.actions{white-space:nowrap}.table>:not(caption)>*>*{padding:.75rem .55rem}@media(max-width:800px){.admin-layout{grid-template-columns:1fr}.admin-sidebar{min-height:auto}.admin-main{padding:20px}}
</style></head><body><div class="admin-layout"><?php $adminSection='nicknames';require __DIR__.'/../partials/admin_sidebar.php';?><main class="admin-main">
<div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><h1 class="h3 mb-1">Clicuhas</h1><div class="text-secondary">Керування клікухами · показано <?=count($nicknames)?></div></div><div class="text-end small text-secondary"><strong><?=h(($admin['username']??'')?:$admin['email'])?></strong><br>administrator</div></div>
<?php if($flash!==''):?><div class="alert alert-success py-2"><?=h($flash)?></div><?php endif;?>
<section class="panel"><form class="row g-2 mb-3" method="get"><div class="col-md-8"><input class="form-control" name="q" value="<?=h($q)?>" placeholder="Пошук: назва, @slug, власник, email"></div><div class="col-md-auto"><input type="hidden" name="status" value="<?=h($status)?>"><button class="btn btn-dark">Знайти</button></div><?php if($q!==''):?><div class="col-md-auto"><a class="btn btn-outline-secondary" href="/admin/nicknames.php?status=<?=h($status)?>">Очистити</a></div><?php endif;?></form>
<div class="mb-3"><?php foreach($filters as $key=>$label):?><a class="filter-link <?=$status===$key?'active':''?>" href="?status=<?=h($key)?><?=$q!==''?'&q='.urlencode($q):''?>"><?=h($label)?></a><?php endforeach;?></div>
<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Avatar</th><th>Clicuha</th><th>Owner</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody><?php foreach($nicknames as $n):?><tr>
<td><?php $avatarUrl=clicuha_avatar_url($n['avatar_path']??null); if($avatarUrl):?><img class="avatar" src="<?=h($avatarUrl)?>" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"><div class="avatar-empty" style="display:none">—</div><?php else:?><div class="avatar-empty">—</div><?php endif;?></td>
<td><strong><?=h($n['title'])?></strong><?php if(!empty($n['slug'])):?><div class="small text-secondary">@<?=h($n['slug'])?></div><?php endif;?><?php if(!empty($n['is_anonymous'])):?><span class="badge badge-soft">anonymous</span><?php endif;?></td>
<td><?=h(($n['username']??'')?:($n['email']??'—'))?></td><td><?php if(!empty($n['deleted_at'])):?><span class="badge text-bg-secondary">Архів</span><?php elseif(empty($n['user_id'])):?><span class="badge text-bg-success">Вільна</span><?php else:?><span class="badge text-bg-primary">Належить</span><?php endif;?></td><td class="small text-secondary"><?=h((string)$n['created_at'])?></td>
<td class="text-end actions"><a class="btn btn-sm btn-outline-secondary" href="/view.php?id=<?=(int)$n['id']?>">View</a> <?php if(!empty($n['deleted_at'])):?><form method="post" class="d-inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="id" value="<?=(int)$n['id']?>"><input type="hidden" name="action" value="restore"><input type="hidden" name="return_status" value="<?=h($status)?>"><input type="hidden" name="return_q" value="<?=h($q)?>"><button class="btn btn-sm btn-outline-success">Restore</button></form><?php else:?><form method="post" class="d-inline" onsubmit="return confirm('Відправити цю клікуху в архів?');"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="id" value="<?=(int)$n['id']?>"><input type="hidden" name="action" value="archive"><input type="hidden" name="return_status" value="<?=h($status)?>"><input type="hidden" name="return_q" value="<?=h($q)?>"><button class="btn btn-sm btn-outline-danger">Archive</button></form><?php endif;?></td></tr><?php endforeach;?><?php if(!$nicknames):?><tr><td colspan="6" class="text-secondary py-4 text-center">Нічого не знайдено.</td></tr><?php endif;?></tbody></table></div></section></main></div></body></html>
