<?php
require_once __DIR__ . '/config.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'classic';

    $_SESSION['cabinet_theme'] = $theme;

    $stmt = $pdo->prepare("UPDATE users SET cabinet_theme = :t WHERE id = :id");
    $stmt->execute([
        ':t'  => $theme,
        ':id' => $_SESSION['user_id'],
    ]);

    header("Location: cabinet.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Інтер&apos;єр кабінету</title>
</head>
<body>
    <form method="post">
        <h3>Оберіть інтер&apos;єр кабінету:</h3>
        <select name="theme">
            <option value="classic">Classic</option>
            <option value="cave">Моя печера (поки не готово)</option>
            <option value="palace">Мой дворец (поки не готово)</option>
            <option value="vigvam">My Vigvam (поки не готово)</option>
        </select>
        <button type="submit">Застосувати</button>
    </form>
</body>
</html>




