<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/clicuha_access.php';
$userId = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) clicuha_forbidden('Невірний токен форми.');
$id = (int)($_POST['id'] ?? 0);
$pdo->beginTransaction();
$nick = clicuha_lock($pdo, $id);
clicuha_assert_manage($pdo, $nick);
$pdo->prepare('UPDATE nicknames SET deleted_at=NOW(),is_published=0 WHERE id=?')->execute([$id]);
$pdo->commit();
clicuha_audit($pdo,$userId,'clicuha_archive','clicuha_id='.$id.'; title='.$nick['title']);
header('Location: /cabinet.php?deleted=1');
exit;
