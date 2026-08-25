<?php
declare(strict_types=1);

function clicuha_audit(PDO $pdo, ?int $userId, string $action, string $details = ''): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO logs (user_id, action, details, ip, created_at) VALUES (:user_id, :action, :details, :ip, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => substr($action, 0, 100),
            ':details' => $details !== '' ? $details : null,
            ':ip' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        ]);
    } catch (Throwable $e) {
        // Audit must never break the user action it records.
        error_log('Clicuha audit failed: ' . $e->getMessage());
    }
}
