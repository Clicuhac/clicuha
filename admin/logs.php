<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';

$admin = require_admin($pdo);
$q = trim((string)($_GET['q'] ?? ''));

$columnRows = $pdo->query('SHOW COLUMNS FROM logs')->fetchAll(PDO::FETCH_ASSOC);
$columns = array_values(array_map(static fn(array $row): string => (string)$row['Field'], $columnRows));

function log_ident(string $name): string {
    return '`' . str_replace('`', '``', $name) . '`';
}

$orderCandidates = ['created_at', 'timestamp', 'logged_at', 'date', 'id'];
$orderColumn = null;
foreach ($orderCandidates as $candidate) {
    if (in_array($candidate, $columns, true)) { $orderColumn = $candidate; break; }
}

$params = [];
$sql = 'SELECT * FROM logs';
if ($q !== '' && $columns) {
    $parts = [];
    foreach ($columns as $column) {
        $parts[] = 'COALESCE(CAST(' . log_ident($column) . " AS CHAR), '')";
    }
    $sql .= " WHERE CONCAT_WS(' ', " . implode(', ', $parts) . ') LIKE :search';
    $params[':search'] = '%' . $q . '%';
}
if ($orderColumn !== null) $sql .= ' ORDER BY ' . log_ident($orderColumn) . ' DESC';
$sql .= ' LIMIT 300';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [
    'id'=>'ID','created_at'=>'Date / time','timestamp'=>'Date / time','logged_at'=>'Date / time','date'=>'Date / time',
    'user_id'=>'User ID','email'=>'Email','username'=>'User','action'=>'Action','event'=>'Event','type'=>'Type',
    'entity'=>'Object','entity_id'=>'Object ID','ip'=>'IP','ip_address'=>'IP','message'=>'Message','details'=>'Details','meta'=>'Details'
];
?>
<!doctype html><html lang="<?=h($lang)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Logs · Clicuha Admin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><style>
:root{--admin-side:#17151d;--admin-bg:#f4f3f7;--admin-lav:#cba3f7}body{margin:0;background:var(--admin-bg);color:#222}.admin-layout{min-height:100vh;display:grid;grid-template-columns:230px 1fr}.admin-sidebar{background:var(--admin-side);color:#fff;padding:24px 18px;display:flex;flex-direction:column;min-height:100vh}.admin-brand{color:#fff;font-weight:800;font-size:1.2rem;text-decoration:none;margin-bottom:30px}.admin-brand span{color:var(--admin-lav);font-size:.75rem;margin-left:4px}.admin-nav{display:grid;gap:8px}.admin-nav a,.admin-nav span{color:#d7d3dc;text-decoration:none;padding:10px 12px;border-radius:10px}.admin-nav a.active{color:#17151d;background:var(--admin-lav);font-weight:700}.admin-nav small{float:right;opacity:.55}.admin-sidebar-bottom{margin-top:auto;display:grid;gap:8px}.admin-sidebar-bottom a{color:#cfcad5;text-decoration:none;font-size:.9rem}.admin-main{padding:32px}.panel{background:#fff;border-radius:16px;box-shadow:0 8px 22px rgba(26,18,37,.06);padding:22px}.log-cell{max-width:420px;white-space:normal;word-break:break-word}.schema{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.78rem}.table>:not(caption)>*>*{padding:.75rem .55rem}@media(max-width:800px){.admin-layout{grid-template-columns:1fr}.admin-sidebar{min-height:auto}.admin-main{padding:20px}}
</style></head><body><div class="admin-layout"><?php $adminSection='logs';require __DIR__.'/../partials/admin_sidebar.php';?><main class="admin-main">
<div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><h1 class="h3 mb-1">Logs</h1><div class="text-secondary">Системний журнал · показано <?=count($logs)?></div></div><div class="text-end small text-secondary"><strong><?=h(($admin['username']??'')?:$admin['email'])?></strong><br>administrator</div></div>
<section class="panel"><form class="row g-2 mb-3" method="get"><div class="col-md-8"><input class="form-control" name="q" value="<?=h($q)?>" placeholder="Пошук по всіх полях журналу"></div><div class="col-md-auto"><button class="btn btn-dark">Знайти</button></div><?php if($q!==''):?><div class="col-md-auto"><a class="btn btn-outline-secondary" href="/admin/logs.php">Очистити</a></div><?php endif;?></form>
<?php if(!$columns):?><div class="alert alert-warning mb-0">Таблиця logs не має доступних колонок.</div><?php else:?><div class="small text-secondary mb-3">Структура: <span class="schema"><?=h(implode(' · ',$columns))?></span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><?php foreach($columns as $column):?><th><?=h($labels[$column]??$column)?></th><?php endforeach;?></tr></thead><tbody><?php foreach($logs as $row):?><tr><?php foreach($columns as $column):?><td class="small log-cell"><?=h((string)($row[$column]??''))?></td><?php endforeach;?></tr><?php endforeach;?><?php if(!$logs):?><tr><td colspan="<?=count($columns)?>" class="text-center text-secondary py-4"><?= $q!=='' ? 'Нічого не знайдено.' : 'Журнал поки порожній.' ?></td></tr><?php endif;?></tbody></table></div><?php endif;?></section></main></div></body></html>
