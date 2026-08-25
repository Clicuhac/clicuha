<?php
declare(strict_types=1);

function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }

    return (int)$_SESSION['user_id'];
}

function current_user(PDO $pdo): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, email, username, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function require_admin(PDO $pdo): array
{
    require_login();

    $user = current_user($pdo);
    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
        http_response_code(403);
        echo '<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>403 · Clicuha</title></head><body><main style="font-family:system-ui;padding:2rem"><h1>403</h1><p>Доступ лише для адміністратора.</p><p><a href="/cabinet.php">Повернутися в кабінет</a></p></main></body></html>';
        exit;
    }

    return $user;
}
