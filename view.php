<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/avatar.php';
require_once __DIR__ . '/lib/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); echo 'Not found'; exit; }

$stmt = $pdo->prepare('SELECT * FROM nicknames WHERE id = :id AND deleted_at IS NULL LIMIT 1');
$stmt->execute([':id'=>$id]);
$clicuha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$clicuha) { http_response_code(404); echo 'Not found'; exit; }

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$canEdit = $currentUserId !== null && (int)$clicuha['user_id'] === $currentUserId;
$currentUser = current_user($pdo);
$isAdmin = $currentUser && (($currentUser['role'] ?? 'user') === 'admin');
$owner = null;
if ($isAdmin && !empty($clicuha['user_id'])) {
    $ownerStmt = $pdo->prepare('SELECT id, email, username FROM users WHERE id = :id LIMIT 1');
    $ownerStmt->execute([':id' => (int)$clicuha['user_id']]);
    $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$avatarReady = clicuha_avatar_column_exists($pdo);
$avatarUrl = $avatarReady ? clicuha_avatar_url($clicuha['avatar_path'] ?? null) : null;
$persona = null;
$personaFile = __DIR__ . '/data/ai_personas.php';
if (is_file($personaFile) && !empty($clicuha['slug'])) {
    $personas = require $personaFile;
    $persona = $personas[(string)$clicuha['slug']] ?? null;
}
if ($persona && !empty($persona['avatar_data_uri'])) {
    $avatarUrl = $persona['avatar_data_uri'];
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($clicuha['title']) ?> · Clicuha</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/sema.css?v=125">
<style>
.ai-wrap{max-width:1320px}.ai-head{display:flex;justify-content:space-between;gap:20px;align-items:flex-start}.ai-title{font-size:2.25rem}.ai-grid{display:grid;grid-template-columns:1.05fr 1.55fr .95fr;gap:18px}.ai-card{background:#fff;border:1px solid #e9e4ef;border-radius:16px;box-shadow:0 8px 24px rgba(40,25,60,.06);padding:20px}.ai-card h2{font-size:1rem;font-weight:800;margin-bottom:16px}.ai-tags{display:flex;flex-wrap:wrap;gap:8px}.ai-tag{padding:7px 10px;border:1px solid #dcc8f6;background:#f8f2ff;border-radius:8px;font-size:.88rem}.ai-avatar{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:12px;border:1px solid #ddd}.ai-status{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #eee}.ai-status:last-child{border-bottom:0}.ai-quote{background:#f7efff;border:1px solid #e0c9ff;border-radius:12px;padding:16px}.ai-list{margin:0;padding-left:20px}.ai-list li{margin-bottom:8px}.ai-memory{grid-column:1/3}.ai-intentions{grid-column:3}.ai-counts{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:14px}.ai-count{border:1px dashed #d9bdf6;border-radius:10px;padding:12px;text-align:center}.ai-actions{grid-column:1/4;display:flex;gap:12px}.ai-room{background:linear-gradient(135deg,#b96df2,#8c4ee8);border:0;color:#fff}.ai-room:hover{color:#fff;opacity:.94}.ai-badge{background:#efe3ff;color:#5c2e8b;border:1px solid #d8bef6}.ai-spark{color:#9b4fe8}.ai-subtitle{white-space:pre-wrap}.ai-section-kicker{font-weight:800;margin:18px 0 10px}.ai-strong{border-left:4px solid #50b46a}.ai-shadow{border-left:4px solid #f0a43b}@media(max-width:992px){.ai-grid{grid-template-columns:1fr 1fr}.ai-memory,.ai-intentions,.ai-actions{grid-column:auto}.ai-actions{grid-column:1/3}.ai-counts{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.ai-grid{grid-template-columns:1fr}.ai-memory,.ai-intentions,.ai-actions{grid-column:1}.ai-actions{flex-direction:column}.ai-title{font-size:1.8rem}}
</style>
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>
<main class="container ai-wrap py-4">
<div class="ai-head mb-4">
  <div>
    <div class="text-muted small">Clicuha #<?= $id ?></div>
    <h1 class="ai-title mb-1"><?= h($clicuha['title']) ?><?= $persona ? ' <span class="ai-spark">✦</span>' : '' ?></h1>
    <?php if ($clicuha['slug']): ?><div class="text-muted">@<?= h($clicuha['slug']) ?></div><?php endif; ?>
    <div class="mt-2 d-flex gap-2 flex-wrap">
      <?php if (empty($clicuha['user_id'])): ?><span class="badge bg-success">Вільна</span><?php endif; ?>
      <?php if ($persona): ?><span class="badge ai-badge">✦ <?= h($persona['badge']) ?></span><?php endif; ?>
    </div>
  </div>
  <a href="/index.php" class="btn btn-outline-secondary">← Галерея</a>
</div>

<?php if ($persona): ?>
<div class="ai-grid">
  <section class="ai-card">
    <h2>Who is…</h2>
    <p class="ai-subtitle mb-4"><?= h($clicuha['description'] ?? '') ?></p>
    <p class="mb-0"><?= h($persona['tagline']) ?></p>
  </section>

  <section class="ai-card">
    <h2>Характеристики</h2>
    <div class="ai-section-kicker">♙ Профіль рис</div>
    <div class="ai-tags"><?php foreach ($persona['traits'] as $item): ?><span class="ai-tag"><?= h($item) ?></span><?php endforeach; ?></div>
    <div class="ai-section-kicker">♥ Поведінка та реакції</div>
    <div class="ai-tags"><?php foreach ($persona['behaviors'] as $item): ?><span class="ai-tag"><?= h($item) ?></span><?php endforeach; ?></div>
  </section>

  <aside class="ai-card">
    <h2>Аватар</h2>
    <?php if($avatarUrl):?><img class="ai-avatar" src="<?=h($avatarUrl)?>" alt="Аватар <?=h($clicuha['title'])?>"><?php endif;?>
    <h2 class="mt-3 mb-1">Статус</h2>
    <div class="ai-status"><span class="text-muted">Автор</span><strong>Відкрито</strong></div>
    <div class="ai-status"><span class="text-muted">Власність</span><strong><?= empty($clicuha['user_id']) ? 'Вільна' : 'Закріплена' ?></strong></div>
    <?php if (!empty($clicuha['created_at'])): ?><div class="ai-status"><span class="text-muted">Створено</span><strong><?= h(date('d.m.Y', strtotime((string)$clicuha['created_at']))) ?></strong></div><?php endif; ?>
    <div class="ai-quote mt-3"><strong>“</strong> <?= h($persona['quote']) ?><div class="text-end small mt-2">— <?= h($clicuha['title']) ?> ✦</div></div>
  </aside>

  <section class="ai-card ai-strong">
    <h2>Сильні сторони</h2>
    <ul class="ai-list"><?php foreach ($persona['strengths'] as $item): ?><li><?= h($item) ?></li><?php endforeach; ?></ul>
  </section>

  <section class="ai-card ai-shadow">
    <h2>Тіні (недоліки)</h2>
    <ul class="ai-list"><?php foreach ($persona['shadows'] as $item): ?><li><?= h($item) ?></li><?php endforeach; ?></ul>
  </section>

  <section class="ai-card ai-memory">
    <h2>▢ Пам’ять і досвід</h2>
    <p><?= h($persona['memory_text']) ?></p>
    <div class="ai-counts"><?php foreach ($persona['counts'] as $label=>$count): ?><div class="ai-count"><strong><?= h((string)$label) ?></strong><div class="fs-5 mt-1"><?= (int)$count ?></div></div><?php endforeach; ?></div>
  </section>

  <section class="ai-card ai-intentions">
    <h2>⚑ Перші наміри</h2>
    <ol class="ai-list"><?php foreach ($persona['intentions'] as $item): ?><li><?= h($item) ?></li><?php endforeach; ?></ol>
  </section>

  <div class="ai-actions">
    <a class="btn ai-room px-4" href="/room.php?id=<?= $id ?>">✦ Увійти в ROOM</a>
    <button class="btn btn-outline-secondary" type="button" disabled>◉ Спостерігати</button>
    <button class="btn btn-outline-secondary" type="button" disabled>♡ Симпатія</button>
    <?php if ($canEdit): ?><a href="/edit_nickname.php?id=<?= $id ?>" class="btn btn-primary ms-auto"><?= h(t('btn_edit')) ?></a><?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="clic-detail-grid">
<section class="clic-panel"><div class="clic-panel-body"><div class="clic-panel-title">Who is…</div><p class="mb-0" style="white-space:pre-wrap"><?= h($clicuha['description'] ?? '') ?></p><div class="clic-action-row mt-4"><a href="/index.php" class="btn btn-outline-secondary">← <?= h(t('latest_nicknames')) ?></a><?php if ($canEdit): ?><a href="/edit_nickname.php?id=<?= $id ?>" class="btn btn-primary"><?= h(t('btn_edit')) ?></a><?php endif; ?></div></div></section>
<section class="clic-panel"><div class="clic-panel-body"><div class="clic-panel-title">Характеристики</div><div class="clic-module-placeholder"><strong>Профіль рис</strong><p class="clic-panel-muted mb-0 mt-2">Тут з’являться характеристики, категорії, шкали та інші параметри цієї Clicuha.</p></div><div class="clic-module-placeholder"><strong>Поведінка та реакції</strong><p class="clic-panel-muted mb-0 mt-2">Зона для реакцій, зв’язків між рисами та майбутньої еволюції персонажа.</p></div></div></section>
<aside class="clic-panel"><div class="clic-panel-body"><div class="clic-panel-title">Аватар</div><div class="clic-avatar-box"><?php if($avatarUrl):?><img src="<?=h($avatarUrl)?>" alt="Аватар <?=h($clicuha['title'])?>"><?php else:?><div class="clic-avatar-placeholder"><strong><?=h(mb_strtoupper(mb_substr((string)$clicuha['title'],0,1)))?></strong><span>Поки без аватара</span></div><?php endif;?></div><div class="clic-panel-title mt-3">Статус</div><div class="clic-status-line"><span class="text-muted">Автор</span><strong><?= (int)$clicuha['is_anonymous'] === 1 ? 'Анонімно' : 'Відкрито' ?></strong></div><div class="clic-status-line"><span class="text-muted">Власність</span><strong><?= empty($clicuha['user_id']) ? 'Вільна' : 'Закріплена' ?></strong></div><?php if (!empty($clicuha['created_at'])): ?><div class="clic-status-line"><span class="text-muted">Створено</span><strong><?= h(date('d.m.Y', strtotime((string)$clicuha['created_at']))) ?></strong></div><?php endif; ?></div></aside>
</div>
<?php endif; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
