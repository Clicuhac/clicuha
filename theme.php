<?php
require_once __DIR__ . '/config.php';
session_start();

// Якщо раптом немає юзера в сесії – краще відправити на логін
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Список дозволених тем (щоб ніякий сміттєвий value не заліз)
$allowedThemes = ['classic', 'cave', 'palace', 'vigvam'];

// Поточна тема – з сесії або classic за замовчуванням
$currentTheme = $_SESSION['cabinet_theme'] ?? 'classic';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'classic';

    if (!in_array($theme, $allowedThemes, true)) {
        $theme = 'classic';
    }

    try {
        // Оновлюємо сесію
        $_SESSION['cabinet_theme'] = $theme;
        $currentTheme = $theme;

        // Оновлюємо БД
        $stmt = $pdo->prepare('UPDATE users SET cabinet_theme = :t WHERE id = :id');
        $stmt->execute([
            ':t'  => $theme,
            ':id' => $_SESSION['user_id'],
        ]);

        // Якщо все добре – у кабінет
        header('Location: cabinet.php');
        exit;

    } catch (Throwable $e) {
        // Тимчасово показуємо помилку, щоб не було «порожньої» сторінки
        http_response_code(500);
        echo '<pre>Theme update failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Інтерʼєр кабінету</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>

    <div class="container py-4">
    <h3>Оберіть інтерʼєр кабінету:</h3>

    <form method="post">
        <select name="theme">
            <option value="classic" <?= $currentTheme === 'classic' ? 'selected' : '' ?>>Classic</option>
            <option value="cave" <?= $currentTheme === 'cave' ? 'selected' : '' ?>>Моя печера (поки не готово)</option>
            <option value="palace" <?= $currentTheme === 'palace' ? 'selected' : '' ?>>Мой дворец (поки не готово)</option>
            <option value="vigvam" <?= $currentTheme === 'vigvam' ? 'selected' : '' ?>>My Vigvam (поки не готово)</option>
        </select>

        <button type="submit">Застосувати</button>
    </form>

    <p>
        Після зміни теми тебе має перекинути в
        <a href="cabinet.php">кабінет</a>.
    </p>
    </div>
</body>
</html>






