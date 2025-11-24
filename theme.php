<?php
require __DIR__ . '/config.php'; // сесія вже стартує тут

// Поточна тема (щоб select підсвітив обрану)
$currentTheme = $_SESSION['cabinet_theme'] ?? 'classic';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'classic';

    // пишемо в сесію
    $_SESSION['cabinet_theme'] = $theme;

    // зберігаємо в БД, якщо є user_id
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET cabinet_theme = :t WHERE id = :id");
        $stmt->execute([
            ':t'  => $theme,
            ':id' => $_SESSION['user_id'],
        ]);
    }

    header("Location: cabinet.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Інтер'єр кабінету</title>
    <link rel="stylesheet" href="assets/css/cabinet-base.css">
</head>
<body class="cabinet-theme-<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8') ?>">

<?php include 'partials/navbar.php'; ?>

<main class="cabinet-theme-page">
    <h3>Оберіть інтер'єр кабінету:</h3>

    <form method="post">
        <select name="theme">
            <option value="classic" <?= $currentTheme === 'classic' ? 'selected' : '' ?>>Classic</option>
            <option value="cave" <?= $currentTheme === 'cave' ? 'selected' : '' ?>>Моя печера (поки не готово)</option>
            <option value="palace" <?= $currentTheme === 'palace' ? 'selected' : '' ?>>Мой дворец (поки не готово)</option>
            <option value="vigvam" <?= $currentTheme === 'vigvam' ? 'selected' : '' ?>>My Vigvam (поки не готово)</option>
        </select>
        <button type="submit">Застосувати</button>
    </form>
</main>

</body>
</html>



