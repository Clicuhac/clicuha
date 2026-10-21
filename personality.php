<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/clicuha_access.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) clicuha_forbidden('Невірний токен форми.');
    $pdo->beginTransaction();
    $nick = clicuha_lock($pdo, $id);
    clicuha_assert_manage($pdo, $nick);
    $presetKey = null;
    if (($_POST['mode'] ?? '') === 'preset') {
        $presetKey = is_string($_POST['preset_key'] ?? null) ? $_POST['preset_key'] : '';
        $preset = personality_presets()[$presetKey] ?? null;
        if (!$preset) $errors[] = 'Оберіть відомий типаж.';
        $core = $preset['core'] ?? [];
    } else {
        $traits = is_array($_POST['traits'] ?? null) ? $_POST['traits'] : [];
        $core = [
            'traits'=>array_values(array_unique(array_filter($traits, fn($t)=>is_string($t) && isset(personality_traits()[$t])))),
            'motivation'=>is_string($_POST['motivation'] ?? null) ? trim($_POST['motivation']) : '',
            'vulnerability'=>is_string($_POST['vulnerability'] ?? null) ? trim($_POST['vulnerability']) : '',
        ];
    }
    foreach (['motivation','vulnerability'] as $field) {
        if (mb_strlen($core[$field] ?? '') > 1000) $errors[] = 'Текст ядра: максимум 1000 символів у полі.';
    }
    if ((int)$nick['is_published'] === 1 && personality_missing($core)) {
        $errors[] = 'Спочатку поверніть Clicuha до кабінету, щоб зберегти неповне ядро.';
    }
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO clicuha_personalities (nickname_id,schema_version,preset_key,core_json) VALUES (?,1,?,?) ON DUPLICATE KEY UPDATE schema_version=1,preset_key=VALUES(preset_key),core_json=VALUES(core_json),updated_at=CURRENT_TIMESTAMP');
        $stmt->execute([$id,$presetKey,json_encode($core, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        $pdo->commit();
        header('Location: /personality.php?id='.$id.'&saved=1');
        exit;
    }
    $pdo->rollBack();
    http_response_code(422);
} else {
    $stmt = $pdo->prepare('SELECT * FROM nicknames WHERE id=?');
    $stmt->execute([$id]);
    $nick = $stmt->fetch();
    if (!$nick) { http_response_code(404); exit('Клікуху не знайдено'); }
    clicuha_assert_manage($pdo, $nick);
    $stored = get_personality($pdo, $id);
    $core = $stored['core'] ?? [];
}
$missing = personality_missing($core, (int)($stored['schema_version'] ?? 1));
?>
<!doctype html><html lang="<?=h($lang)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Формування особистості · Clicuha</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="/assets/css/sema.css"></head><body><?php require __DIR__.'/partials/navbar.php';?><main class="container py-4" style="max-width:780px">
<h1 class="h3">Формування: <?=h($nick['title'])?></h1>
<?php foreach($errors as $error):?><p class="alert alert-danger"><?=h($error)?></p><?php endforeach;?>
<?php if(isset($_GET['saved'])):?><p class="alert alert-success">Ядро збережено.</p><?php endif;?>
<p><?= $missing ? 'Особистість ще формується' : 'READY · Мінімальне ядро сформовано' ?></p>
<?php if($missing):?><div class="alert alert-info">До READY бракує:<ul><?php foreach($missing as $item):?><li><?=h($item)?></li><?php endforeach;?></ul></div><?php endif;?>
<form method="post" class="card card-body mb-4"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><input type="hidden" name="mode" value="manual"><fieldset><legend class="h5">Риси характеру — щонайменше три</legend><?php foreach(personality_traits() as $key=>$label):?><label class="d-block"><input type="checkbox" name="traits[]" value="<?=h($key)?>" <?=in_array($key,$core['traits']??[],true)?'checked':''?>> <?=h($label)?></label><?php endforeach;?></fieldset>
<label class="mt-3" for="motivation">Мотивація — чого прагне</label><textarea class="form-control" id="motivation" name="motivation" maxlength="1000"><?=h($core['motivation']??'')?></textarea>
<label class="mt-3" for="vulnerability">Обмеження / вразливість — що дається трудно</label><textarea class="form-control" id="vulnerability" name="vulnerability" maxlength="1000"><?=h($core['vulnerability']??'')?></textarea>
<button class="btn btn-primary mt-3">Зберегти ядро</button></form>
<form method="post" class="card card-body mb-4"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><input type="hidden" name="mode" value="preset"><label for="preset_key">Або заповнити готовим типажем (замінює поточне ядро)</label><select id="preset_key" name="preset_key" class="form-select"><?php foreach(personality_presets() as $key=>$preset):?><option value="<?=h($key)?>"><?=h($preset['label'])?></option><?php endforeach;?></select><button class="btn btn-outline-primary mt-3">Застосувати типаж</button></form>
<a href="/forming.php">На формуванні</a> · <a href="/my_nicknames.php">Мої клікухи</a> · <a href="/edit_nickname.php?id=<?=$id?>">Основні дані</a>
</main></body></html>
