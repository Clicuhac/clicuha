<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib/avatar.php';
require_once __DIR__.'/lib/audit.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals((string)$_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Невірний токен форми');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT title FROM nicknames WHERE id = :id AND user_id = :uid AND deleted_at IS NULL AND is_published = 0');
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $title = $stmt->fetchColumn();

        if ($title !== false) {
            $update = $pdo->prepare('UPDATE nicknames SET is_published = 1 WHERE id = :id AND user_id = :uid AND deleted_at IS NULL AND is_published = 0');
            $update->execute([':id' => $id, ':uid' => $userId]);
            if ($update->rowCount() > 0) {
                clicuha_audit($pdo, $userId, 'clicuha_publish', 'clicuha_id=' . $id . '; title=' . (string)$title);
                $_SESSION['publish_flash'] = 'Клікуху опубліковано.';
            }
        }
    }

    header('Location: /my_nicknames.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id,title,slug,description,avatar_path,created_at,is_published FROM nicknames WHERE user_id=:uid AND deleted_at IS NULL ORDER BY created_at DESC');
$stmt->execute([':uid'=>$userId]);
$my=$stmt->fetchAll(PDO::FETCH_ASSOC);
$flash=(string)($_SESSION['publish_flash']??'');
unset($_SESSION['publish_flash']);
?>
<!doctype html>
<html lang="<?=h($lang)?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Мої клікухи</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/sema.css?v=124">
</head>
<body class="bg-light">
<?php require __DIR__.'/partials/navbar.php';?>
<main class="container py-4">
<h1 class="h4 mb-4">Мої клікухи</h1>
<?php if($flash):?><div class="alert alert-success"><?=h($flash)?></div><?php endif;?>
<?php if(empty($my)):?>
<div class="alert alert-info">У тебе наразі немає жодної публічної або кабінетної клікухи.</div>
<?php else:?><div class="row g-3">
<?php foreach($my as $nick):
$avatarUrl=clicuha_avatar_url($nick['avatar_path']??null);
$initial=mb_strtoupper(mb_substr(trim((string)$nick['title']),0,1,'UTF-8'),'UTF-8');
if($initial==='')$initial='C';?>
<div class="col-12 col-md-6 col-lg-4"><div class="card shadow-sm h-100 clic-gallery-card"><div class="card-body d-flex flex-column">
<div class="clic-gallery-head"><a class="clic-gallery-avatar" href="/view.php?id=<?=(int)$nick['id']?>"><?php if($avatarUrl):?><img src="<?=h($avatarUrl)?>" alt="<?=h($nick['title'])?>"><?php else:?><span><?=h($initial)?></span><?php endif;?></a><div class="clic-gallery-titlebox"><h5 class="card-title mb-1"><?=h($nick['title'])?></h5><?php if($nick['slug']):?><div class="text-muted small">@<?=h($nick['slug'])?></div><?php endif;?></div></div>
<div class="my-2"><?php if((int)$nick['is_published']===1):?><span class="badge bg-success">Публічна</span><?php else:?><span class="badge bg-secondary">У кабінеті</span><?php endif;?></div>
<?php if($nick['description']):?><p class="small mt-1 mb-3"><?=nl2br(h($nick['description']))?></p><?php endif;?>
<div class="mt-auto d-flex gap-2 flex-wrap">
<a href="/edit_nickname.php?id=<?=(int)$nick['id']?>" class="btn btn-sm btn-primary">Редагувати</a>
<?php if((int)$nick['is_published']===0):?>
<form method="post" class="d-inline" onsubmit="return confirm('Опублікувати цю клікуху в Галереї?');">
<input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
<input type="hidden" name="id" value="<?=(int)$nick['id']?>">
<button type="submit" class="btn btn-sm btn-success">Опублікувати</button>
</form>
<?php endif;?>
</div>
</div></div></div>
<?php endforeach;?></div><?php endif;?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
