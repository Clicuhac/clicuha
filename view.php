<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/avatar.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); echo 'Not found'; exit; }
$stmt = $pdo->prepare('SELECT * FROM nicknames WHERE id = :id AND deleted_at IS NULL LIMIT 1');
$stmt->execute([':id'=>$id]);
$clicuha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$clicuha) { http_response_code(404); echo 'Not found'; exit; }
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$canEdit = $currentUserId !== null && (int)$clicuha['user_id'] === $currentUserId;
$avatarReady = clicuha_avatar_column_exists($pdo);
$avatarUrl = $avatarReady ? clicuha_avatar_url($clicuha['avatar_path'] ?? null) : null;
?>
<!doctype html><html lang="<?= h($lang) ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= h($clicuha['title']) ?> · Clicuha</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="/assets/css/sema.css?v=123"><link rel="stylesheet" href="/assets/css/detail.css?v=2"></head><body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>
<main class="container py-4">
<div class="mb-4"><div class="text-muted small">Clicuha #<?= $id ?></div><h1 class="h3 mb-1"><?= h($clicuha['title']) ?></h1><?php if ($clicuha['slug']): ?><div class="text-muted">@<?= h($clicuha['slug']) ?></div><?php endif; ?></div>
<div class="clic-detail-grid">
<section class="clic-panel"><div class="clic-panel-body"><div class="clic-panel-title">Who is…</div><p class="mb-0" style="white-space:pre-wrap"><?= h($clicuha['description'] ?? '') ?></p><div class="clic-action-row mt-4"><a href="/index.php" class="btn btn-outline-secondary">← <?= h(t('latest_nicknames')) ?></a><?php if ($canEdit): ?><a href="/edit_nickname.php?id=<?= $id ?>" class="btn btn-primary"><?= h(t('btn_edit')) ?></a><?php endif; ?></div></div></section>
<section class="clic-panel"><div class="clic-panel-body"><div class="clic-panel-title">Характеристики</div><div class="clic-module-placeholder"><strong>Профіль рис</strong><p class="clic-panel-muted mb-0 mt-2">Тут з’являться характеристики, категорії, шкали та інші параметри цієї Clicuha.</p></div><div class="clic-module-placeholder"><strong>Поведінка та реакції</strong><p class="clic-panel-muted mb-0 mt-2">Зона для реакцій, зв’язків між рисами та майбутньої еволюції персонажа.</p></div></div></section>
<aside class="clic-panel"><div class="clic-panel-body"><div class="clic-panel-title">Аватар</div><div class="clic-avatar-box"><?php if($avatarUrl):?><img src="<?=h($avatarUrl)?>" alt="Аватар <?=h($clicuha['title'])?>"><?php else:?><div class="clic-avatar-placeholder"><strong><?=h(mb_strtoupper(mb_substr((string)$clicuha['title'],0,1)))?></strong><span>Поки без аватара</span></div><?php endif;?></div><div class="clic-panel-title mt-3">Статус</div><div class="clic-status-line"><span class="text-muted">Автор</span><strong><?= (int)$clicuha['is_anonymous'] === 1 ? 'Анонімно' : 'Відкрито' ?></strong></div><div class="clic-status-line"><span class="text-muted">Власність</span><strong><?= empty($clicuha['user_id']) ? 'Вільна' : 'Закріплена' ?></strong></div><?php if (!empty($clicuha['created_at'])): ?><div class="clic-status-line"><span class="text-muted">Створено</span><strong><?= h(date('d.m.Y', strtotime((string)$clicuha['created_at']))) ?></strong></div><?php endif; ?><div class="clic-module-placeholder mt-3"><strong>Майбутні можливості</strong><p class="clic-panel-muted mb-0 mt-2">Події, активність, історія, купівля/передача та інші модулі.</p></div></div></aside>
</div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
