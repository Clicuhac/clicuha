<?php
require_once __DIR__ . '/config.php'; // тут уже є session_start()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'classic';

    // захист від дурних значень
    $allowed = ['classic', 'cave', 'palace', 'vigvam'];
    if (!in_array($theme, $allowed, true)) {
        $theme = 'classic';
    }

    // в сесію
    $_SESSION['cabinet_theme'] = $theme;

    // якщо користувач залогінений — збережемо в БД
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare(
            "UPDATE users SET cabinet_theme = :t WHERE id = :id LIMIT 1"
        );
        $stmt->execute([
            ':t'  => $theme,
            ':id' => $_SESSION['user_id'],
        ]);
    }

    header("Location: cabinet.php");
    exit;
}

// поточна тема для select
$currentTheme = $_SESSION['cabinet_theme'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Інтер'єр кабінету</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sema.css?v=123">
</head>
<body class="p-4">

<h3 class="mb-3">Оберіть інтер'єр кабінету:</h3>

<form method="post" class="d-flex gap-2 align-items-center">
    <select name="theme" class="form-select w-auto">
        <option value="classic" <?= $currentTheme === 'classic' ? 'selected' : '' ?>>Classic</option>
        <option value="cave"    <?= $currentTheme === 'cave'    ? 'selected' : '' ?>>Моя печера (поки не готово)</option>
        <option value="palace"  <?= $currentTheme === 'palace'  ? 'selected' : '' ?>>Мой дворец (поки не готово)</option>
        <option value="vigvam"  <?= $currentTheme === 'vigvam'  ? 'selected' : '' ?>>My Vigvam (поки не готово)</option>
    </select>

    <button type="submit" class="btn btn-primary">Застосувати</button>
</form>

</body>
</html>





