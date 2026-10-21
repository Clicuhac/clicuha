<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/personality.php';
require_once __DIR__ . '/audit.php';

function clicuha_is_admin(PDO $pdo): bool
{
    return (current_user($pdo)['role'] ?? '') === 'admin';
}

function clicuha_can_manage(array $nick, int $actor, bool $admin = false): bool
{
    if ($actor <= 0 || !empty($nick['deleted_at'])) return false;
    if ($admin || (int)($nick['owner_id'] ?? 0) === $actor) return true;
    return (int)$nick['is_published'] === 0
        && empty($nick['first_published_at'])
        && (int)($nick['created_by'] ?? 0) === $actor;
}

function clicuha_can_view(array $nick, int $actor, bool $admin = false): bool
{
    if (empty($nick['deleted_at']) && (int)$nick['is_published'] === 1) return true;
    return $actor > 0 && ($admin || (int)($nick['owner_id'] ?? 0) === $actor
        || (int)($nick['created_by'] ?? 0) === $actor);
}

function clicuha_assert_view(PDO $pdo, array $nick): void
{
    if (!clicuha_can_view($nick, (int)($_SESSION['user_id'] ?? 0), clicuha_is_admin($pdo))) {
        clicuha_forbidden('Ця Clicuha ще не є публічною.');
    }
}

// Caller holds this row lock until all related writes are committed.
function clicuha_lock(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare('SELECT * FROM nicknames WHERE id=? FOR UPDATE');
    $stmt->execute([$id]);
    $nick = $stmt->fetch();
    if (!$nick) {
        http_response_code(404);
        exit('Клікуху не знайдено');
    }
    return $nick;
}

function clicuha_assert_manage(PDO $pdo, array $nick): void
{
    if (!clicuha_can_manage($nick, (int)($_SESSION['user_id'] ?? 0), clicuha_is_admin($pdo))) {
        clicuha_forbidden();
    }
}

// Shared by both ownership and formation lists. No client-supplied readiness.
function clicuha_publication_action(PDO $pdo, int $id, string $action): void
{
    if (!in_array($action, ['publish','cabinet'], true)) {
        http_response_code(400);
        exit('Невідома дія');
    }
    $pdo->beginTransaction();
    $nick = clicuha_lock($pdo, $id);
    clicuha_assert_manage($pdo, $nick);
    $from = $action === 'publish' ? 0 : 1;
    if ((int)$nick['is_published'] !== $from) {
        $pdo->rollBack();
        http_response_code(409);
        exit('Стан Clicuha вже змінився. Оновіть сторінку.');
    }
    if ($action === 'publish' && !is_personality_ready($pdo, $id)) {
        $pdo->rollBack();
        http_response_code(422);
        exit('Публікація заборонена: ядро особистості ще не READY.');
    }
    $sql = $action === 'publish'
        ? 'UPDATE nicknames SET is_published=1,first_published_at=COALESCE(first_published_at,NOW()) WHERE id=?'
        : 'UPDATE nicknames SET is_published=0 WHERE id=?';
    $pdo->prepare($sql)->execute([$id]);
    $pdo->commit();
    clicuha_audit($pdo,(int)$_SESSION['user_id'],$action === 'publish' ? 'clicuha_publish' : 'clicuha_to_cabinet','clicuha_id='.$id.'; title='.$nick['title']);
}
