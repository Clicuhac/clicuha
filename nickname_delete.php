<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/audit.php';
if(empty($_SESSION['user_id'])){header('Location: /login.php');exit;}if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo 'Method not allowed';exit;}if(empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],(string)($_POST['csrf_token']??''))){http_response_code(403);echo 'Invalid CSRF token';exit;}
$userId=(int)$_SESSION['user_id'];$id=(int)($_POST['id']??0);if($id<=0){header('Location: /cabinet.php?deleted=0&reason=bad_id');exit;}
$stmt=$pdo->prepare('SELECT id,user_id,title,deleted_at FROM nicknames WHERE id=:id LIMIT 1');$stmt->execute([':id'=>$id]);$nick=$stmt->fetch(PDO::FETCH_ASSOC);if(!$nick){header('Location: /cabinet.php?deleted=0&reason=not_found');exit;}if((int)$nick['user_id']!==$userId){header('Location: /cabinet.php?deleted=0&reason=forbidden');exit;}if(!empty($nick['deleted_at'])){header('Location: /cabinet.php?deleted=0&reason=already_deleted');exit;}
$del=$pdo->prepare('UPDATE nicknames SET deleted_at=NOW() WHERE id=:id AND user_id=:uid AND deleted_at IS NULL');$del->execute([':id'=>$id,':uid'=>$userId]);if($del->rowCount()>0)clicuha_audit($pdo,$userId,'clicuha_archive','clicuha_id='.$id.'; title='.(string)$nick['title']);header('Location: /cabinet.php?deleted=1');exit;
