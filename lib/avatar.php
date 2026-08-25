<?php
declare(strict_types=1);

function clicuha_avatar_column_exists(PDO $pdo): bool
{
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM nicknames LIKE 'avatar_path'");
    $exists = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    return $exists;
}

function clicuha_avatar_url(?string $path): ?string
{
    $path = trim((string)$path);
    if ($path === '' || str_contains($path, '..')) {
        return null;
    }
    return '/' . ltrim($path, '/');
}

function clicuha_save_avatar_upload(array $file, int $clicuhaId): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не вдалося завантажити файл.');
    }
    if (($file['size'] ?? 0) <= 0 || (int)$file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Аватар має бути не більшим за 5 МБ.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Дозволені лише JPEG, PNG або WebP.');
    }

    $dir = __DIR__ . '/../uploads/clicuha';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Не вдалося створити папку для аватарів.');
    }

    $filename = 'clicuha-' . $clicuhaId . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Не вдалося зберегти аватар.');
    }

    return 'uploads/clicuha/' . $filename;
}

function clicuha_remove_avatar_file(?string $path): void
{
    $path = trim((string)$path);
    if ($path === '' || !str_starts_with($path, 'uploads/clicuha/') || str_contains($path, '..')) {
        return;
    }
    $full = __DIR__ . '/../' . $path;
    if (is_file($full)) {
        @unlink($full);
    }
}
