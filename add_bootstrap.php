<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!defined('SLUG_MAX_LEN')) {
    define('SLUG_MAX_LEN', 30);
}
function clicuha_translit(string $value): string
{
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ye','ж'=>'zh','з'=>'z','и'=>'y','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ю'=>'yu','я'=>'ya','ь'=>'','ъ'=>'','’'=>'','\''=>'','ы'=>'y','э'=>'e','ё'=>'yo'];
    return strtr(mb_strtolower($value, 'UTF-8'), $map);
}
function clicuha_slug(string $value): string
{
    $value = clicuha_translit(trim($value));
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) $value = strtolower($converted);
    }
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim(preg_replace('/-+/', '-', $value) ?? '', '-');
    return substr($value, 0, SLUG_MAX_LEN);
}
function clicuha_unique_slug(PDO $pdo, string $base): string
{
    if ($base === '') return '';
    $slug = $base;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM nicknames WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        if ((int)$stmt->fetchColumn() === 0) return $slug;
        $i++;
        $suffix = '-' . $i;
        $trimmed = substr($base, 0, max(1, SLUG_MAX_LEN - strlen($suffix)));
        $slug = rtrim($trimmed, '-') . $suffix;
    }
}

$errors = [];
$title = '';
$slugInput = '';
$desc = '';
$isAnonymous = 0;
$created = isset($_GET['created']) && $_GET['created'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        echo 'Invalid CSRF token';
        exit;
    }
    $title = trim($_POST['title'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    if ($title === '') $errors[] = 'Назва Clicuha обовʼязкова.';
    $slug = clicuha_slug($slugInput !== '' ? $slugInput : $title);
    if ($slug !== '') $slug = clicuha_unique_slug($pdo, $slug);
    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO nicknames (title, slug, description, is_anonymous, user_id) VALUES (:title, :slug, :description, :is_anonymous, :user_id)");
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug !== '' ? $slug : null,
            ':description' => $desc !== '' ? $desc : null,
            ':is_anonymous' => $isAnonymous,
            ':user_id' => (int)$_SESSION['user_id'],
        ]);
        header('Location: /add_bootstrap.php?created=1');
        exit;
    }
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <title>New Clicuha</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>
<div class="container py-4"><div class="row justify-content-center"><div class="col-md-8 col-lg-6"><div class="card shadow-sm"><div class="card-body">
<h1 class="h4 mb-3">New Clicuha</h1>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<form method="post" novalidate>
<input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
<div class="mb-3"><label class="form-label" for="title">Clicuha*</label><input type="text" name="title" id="title" class="form-control" value="<?= h($title) ?>" required></div>
<div class="mb-3"><label class="form-label" for="slug">Slug (optional)</label><input type="text" name="slug" id="slug" class="form-control" value="<?= h($slugInput) ?>" placeholder="lan-amazon"></div>
<div class="mb-3"><label class="form-label" for="description">Who is… (optional)</label><textarea name="description" id="description" class="form-control" rows="3"><?= h($desc) ?></textarea></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" <?= $isAnonymous ? 'checked' : '' ?>><label class="form-check-label" for="is_anonymous">Анонімно</label></div>
<button type="submit" class="clic-btn clic-btn-yes">Зберегти клікуху</button>
</form>
</div></div></div></div></div>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Клікуху додано</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">Нова клікуха створена і закріплена за вашим акаунтом.</div><div class="modal-footer"><a href="/my_nicknames.php" class="btn btn-outline-secondary">Мої клікухи</a><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Добре</button></div></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const title=document.getElementById('title');const slug=document.getElementById('slug');let slugTouched=slug&&slug.value.trim()!=='';
  const map={'а':'a','б':'b','в':'v','г':'h','ґ':'g','д':'d','е':'e','є':'ye','ж':'zh','з':'z','и':'y','і':'i','ї':'yi','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'ts','ч':'ch','ш':'sh','щ':'sch','ю':'yu','я':'ya','ь':'','ъ':'','’':'','\'':'','ы':'y','э':'e','ё':'yo'};
  function makeSlug(value){return value.toLowerCase().split('').map(ch=>Object.prototype.hasOwnProperty.call(map,ch)?map[ch]:ch).join('').normalize('NFD').replace(/[^\w\-\s]+/g,'').replace(/[\s_]+/g,'-').replace(/[^a-z0-9-]/g,'-').replace(/-+/g,'-').replace(/^-+|-+$/g,'').slice(0,30);}
  if(slug)slug.addEventListener('input',()=>{slugTouched=slug.value.trim()!=='';});if(title)title.addEventListener('input',()=>{if(slug&&!slugTouched)slug.value=makeSlug(title.value);});
  <?php if ($created): ?>const modalEl=document.getElementById('successModal');if(modalEl)new bootstrap.Modal(modalEl).show();<?php endif; ?>
});
</script>
</body></html>
