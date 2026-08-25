<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/avatar.php';
require_once __DIR__ . '/lib/audit.php';
$userId=isset($_SESSION['user_id'])?(int)$_SESSION['user_id']:0;if($userId<=0){header('Location: /login.php');exit;}if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}if(empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],(string)($_POST['csrf_token']??''))){http_response_code(403);echo 'Невірний токен форми';exit;}if(!clicuha_avatar_column_exists($pdo)){http_response_code(503);echo 'Модуль аватара ще не активовано в базі даних.';exit;}
$id=(int)($_POST['id']??0);$stmt=$pdo->prepare('SELECT id,title,avatar_path FROM nicknames WHERE id=:id AND user_id=:uid AND deleted_at IS NULL LIMIT 1');$stmt->execute([':id'=>$id,':uid'=>$userId]);$nick=$stmt->fetch(PDO::FETCH_ASSOC);if(!$nick){http_response_code(404);echo 'Клікуху не знайдено';exit;}
try{$newPath=clicuha_save_avatar_upload($_FILES['avatar']??[],$id);$oldPath=$nick['avatar_path']??null;$stmt=$pdo->prepare('UPDATE nicknames SET avatar_path=:path WHERE id=:id AND user_id=:uid LIMIT 1');$stmt->execute([':path'=>$newPath,':id'=>$id,':uid'=>$userId]);clicuha_remove_avatar_file($oldPath);clicuha_audit($pdo,$userId,'clicuha_avatar','clicuha_id='.$id.'; title='.(string)$nick['title']);header('Location: /edit_nickname.php?id='.$id.'&avatar=updated');exit;}catch(Throwable $e){header('Location: /edit_nickname.php?id='.$id.'&avatar_error='.rawurlencode($e->getMessage()));exit;}
