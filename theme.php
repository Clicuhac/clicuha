<?php
require __DIR__ . '/config.php';
session_start();

$availableThemes = ['classic', 'cave', 'palace', 'vigvam'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'classic';
    if (!in_array($theme, $availableThemes, true)) {
        $theme = 'classic';
    }

    $_SESSION['cabinet_theme'] = $theme;

    // Спроба зберегти в БД (якщо є поле cabinet_theme — ок, якщо ні, просто ігноруємо помилку)
    if (!empty($_SESSION['user_id'] ?? null)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET cabinet_theme = :t WHERE id = :id");
            $stmt->execute([
                ':t'  => $theme,
                ':id' => $_SESSION['user_id'],
            ]);
        } catch (Throwable $e) {
            // тихо ігноруємо, щоб не ламати сторінку
        }
    }

    header('Location: cabinet.php');
    exit;
}

$current = $_SESSION['cabinet_theme'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Інтер'єр кабінету</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Інтер'єр кабінету</h1>

    <form method="post" class="card p-3">
        <div class="mb-3">
            <label class="form-label">Оберіть тему:</label>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="theme" id="tClassic"
                       value="classic" <?= $current === 'classic' ? 'checked' : '' ?>>
                <label class="form-check-label" for="tClassic">Classic</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="theme" id="tCave"
                       value="cave" <?= $current === 'cave' ? 'checked' : '' ?>>
                <label class="form-check-label" for="tCave">Моя печера</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="theme" id="tPalace"
                       value="palace" <?= $current === 'palace' ? 'checked' : '' ?>>
                <label class="form-check-label" for="tPalace">Мій палац</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="theme" id="tVigvam"
                       value="vigvam" <?= $current === 'vigvam' ? 'checked' : '' ?>>
                <label class="form-check-label" for="tVigvam">My Vigvam</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Застосувати</button>
        <a href="cabinet.php" class="btn btn-link">Повернутися до кабінету</a>
    </form>
</div>
</body>
</html>

