<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/avatar.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/clicuha_access.php';
$userId = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) clicuha_forbidden('Невірний токен форми.');
$id = (int)($_POST['id'] ?? 0);
$pdo->beginTransaction();
$nick = clicuha_lock($pdo, $id);
clicuha_assert_manage($pdo, $nick);
$newPath = null;
try {
    $newPath = clicuha_save_avatar_upload($_FILES['avatar'] ?? [], $id);
    $pdo->prepare('UPDATE nicknames SET avatar_path=? WHERE id=?')->execute([$newPath,$id]);
    $pdo->commit();
    clicuha_remove_avatar_file($nick['avatar_path'] ?? null);
    clicuha_audit($pdo,$userId,'clicuha_avatar','clicuha_id='.$id.'; title='.$nick['title']);
    header('Location: /edit_nickname.php?id='.$id.'&avatar=updated');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($newPath !== null) clicuha_remove_avatar_file($newPath);
    header('Location: /edit_nickname.php?id='.$id.'&avatar_error='.rawurlencode($e->getMessage()));
}
exit;
