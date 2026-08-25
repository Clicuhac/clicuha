<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$currentUserId = $_SESSION['user_id'] ?? null;
if (!$currentUserId) {
    header('Location: /login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!defined('SLUG_MAX_LEN')) define('SLUG_MAX_LEN', 30);
if (!function_exists('basic_translit')) {
    function basic_translit(string $s): string {
        $map=['а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ye','ж'=>'zh','з'=>'z','и'=>'y','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ю'=>'yu','я'=>'ya','ь'=>'','ъ'=>'','’'=>'','\''=>'','ы'=>'y','э'=>'e','ё'=>'yo'];
        return strtr(mb_strtolower($s,'UTF-8'),$map);
    }
    function normalize_slug(string $raw): string {
        $slug=basic_translit(trim($raw));
        if(function_exists('iconv')){$c=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$slug);if($c!==false)$slug=strtolower($c);}
        $slug=preg_replace('/[^a-z0-9]+/','-',$slug)??'';
        $slug=trim(preg_replace('/-+/','-',$slug)??'','-');
        return substr($slug,0,SLUG_MAX_LEN);
    }
    function ensure_unique_slug(PDO $pdo,string $slug,int $ignoreId): string {
        if($slug==='') return '';
        $base=$slug;$i=1;
        while(true){$stmt=$pdo->prepare('SELECT COUNT(*) FROM nicknames WHERE slug=:slug AND id<>:id');$stmt->execute([':slug'=>$slug,':id'=>$ignoreId]);if((int)$stmt->fetchColumn()===0)return $slug;$i++;$suffix='-'.$i;$slug=rtrim(substr($base,0,SLUG_MAX_LEN-strlen($suffix)),'-').$suffix;}
    }
}

$id=(int)($_GET['id']??0);
if($id<=0){http_response_code(400);echo 'Некоректний ID';exit;}
$stmt=$pdo->prepare('SELECT * FROM nicknames WHERE id=:id AND deleted_at IS NULL LIMIT 1');
$stmt->execute([':id'=>$id]);
$nick=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$nick){http_response_code(404);echo 'Клікуху не знайдено';exit;}
if((int)$nick['user_id']!==(int)$currentUserId){http_response_code(403);echo 'Це не ваша клікуха 🙂';exit;}

$errors=[];
$title=$nick['title']??'';$slugInput=$nick['slug']??'';$description=$nick['description']??'';$isAnonymous=(int)($nick['is_anonymous']??0)===1?1:0;
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!hash_equals($_SESSION['csrf_token'],(string)($_POST['csrf_token']??''))){http_response_code(403);echo 'Невірний токен форми';exit;}
    $title=trim($_POST['title']??'');$slugInput=trim($_POST['slug']??'');$description=trim($_POST['description']??'');$isAnonymous=isset($_POST['is_anonymous'])?1:0;
    if($title==='')$errors[]='Clicuha не може бути порожньою.';
    $slug=normalize_slug($slugInput!==''?$slugInput:$title);
    $slug=$slug!==''?ensure_unique_slug($pdo,$slug,$id):null;
    if(!$errors){$stmt=$pdo->prepare('UPDATE nicknames SET title=:title,slug=:slug,description=:description,is_anonymous=:is_anonymous WHERE id=:id AND user_id=:uid AND deleted_at IS NULL LIMIT 1');$stmt->execute([':title'=>$title,':slug'=>$slug,':description'=>$description!==''?$description:null,':is_anonymous'=>$isAnonymous,':id'=>$id,':uid'=>(int)$currentUserId]);header('Location: /my_nicknames.php?updated=1');exit;}
}
?>
<!doctype html><html lang="<?= h($lang) ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Редагувати клікуху — Clicuha</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="/assets/css/sema.css?v=123"></head><body class="bg-light">
<?php require __DIR__.'/partials/navbar.php'; ?>
<main class="container py-4"><h1 class="h4 mb-4">Редагувати клікуху</h1><?php if($errors):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><?=h($e)?></div><?php endforeach;?></div><?php endif;?>
<form method="post" class="card shadow-sm"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><div class="card-body"><div class="mb-3"><label class="form-label">Назва</label><input type="text" name="title" class="form-control" value="<?=h($title)?>" required></div><div class="mb-3"><label class="form-label">Slug (@ім’я)</label><input type="text" name="slug" class="form-control" value="<?=h($slugInput)?>"></div><div class="mb-3"><label class="form-label">Опис (Who is…)</label><textarea name="description" rows="4" class="form-control"><?=h($description)?></textarea></div><div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" <?=$isAnonymous?'checked':''?>><label class="form-check-label" for="is_anonymous">Показувати автора як «анонімно»</label></div></div><div class="card-footer d-flex justify-content-between"><a href="/my_nicknames.php" class="btn btn-outline-secondary">Повернутись до списку</a><button type="submit" class="btn btn-primary">Зберегти</button></div></form>
<form method="post" action="/nickname_delete.php" class="mt-3" onsubmit="return confirm('Точно відправити клікуху в архів?');"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><button type="submit" class="btn btn-outline-danger">Видалити</button></form></main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
