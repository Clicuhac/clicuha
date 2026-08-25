<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/audit.php';
$errors=[];
if(!empty($_SESSION['user_id'])){header('Location: /cabinet.php');exit;}
$hasBlockedAt=(bool)$pdo->query("SHOW COLUMNS FROM users LIKE 'blocked_at'")->fetch(PDO::FETCH_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']??'');$password=$_POST['password']??'';
    if($email==='')$errors[]='Введіть email.';if($password==='')$errors[]='Введіть пароль.';
    if(!$errors){
        $sql='SELECT id,email,password_hash,pass_hash'.($hasBlockedAt?',blocked_at':'').' FROM users WHERE email=:email LIMIT 1';
        $stmt=$pdo->prepare($sql);$stmt->execute([':email'=>$email]);$user=$stmt->fetch(PDO::FETCH_ASSOC);
        $storedHash=$user?(($user['password_hash']??'')?:($user['pass_hash']??'')):'';
        if(!$user||$storedHash===''||!password_verify($password,$storedHash)){$errors[]='Невірний email або пароль.';}
        elseif($hasBlockedAt&&!empty($user['blocked_at'])){clicuha_audit($pdo,(int)$user['id'],'login_blocked','blocked account login attempt');$errors[]='Цей акаунт заблоковано адміністратором.';}
        else{$loginStmt=$pdo->prepare('UPDATE users SET last_login=NOW() WHERE id=:id');$loginStmt->execute([':id'=>(int)$user['id']]);clicuha_audit($pdo,(int)$user['id'],'login','successful login');session_regenerate_id(true);$_SESSION['user_id']=(int)$user['id'];$_SESSION['LAST_ACTIVITY']=time();header('Location: /cabinet.php');exit;}
    }
}
?>
<!doctype html><html lang="<?=h($lang)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Clicuha – Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="/assets/css/sema.css?v=123"></head><body class="bg-light"><?php require __DIR__.'/partials/navbar.php';?><main class="container py-4"><h1 class="h4 mb-3">Вхід</h1><?php if($errors):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><?=h($e)?></div><?php endforeach;?></div><?php endif;?><form method="post" class="card p-3" style="max-width:420px;"><div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" autocomplete="email" required value="<?=h($email??'')?>"></div><div class="mb-3"><label class="form-label">Пароль</label><input type="password" name="password" class="form-control" autocomplete="current-password" required></div><button type="submit" class="btn btn-primary">Увійти</button></form></main><footer class="border-top py-4 mt-4"><div class="container text-center small text-muted">© <?=date('Y')?> Clicuha</div></footer><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>