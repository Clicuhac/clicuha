<?php
require __DIR__ . '/config.php';
session_start();

// Доступні теми: ключ = значення, яке підставляємо в CSS-файл
$availableThemes = [
    'classic' => 'Classic',
    'cave'    => 'Моя печера',
    'palace'  => 'Мій палац',
    'vigvam'  => 'My Vigvam',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'classic';

    // захист від сміття
    if (!array_key_exists($theme, $availableThemes)) {
        $theme = 'classic';
    }

    // зберігаємо в сесії
    $_SESSION['cabinet_theme'] = $theme;

    // опційно — спробувати зберегти в БД, якщо є таке поле
    if (!empty($_SESSION['user_id'] ?? null)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET cabinet_theme = :t WHERE id = :id");
            $stmt->execute([
                ':t'  => $theme,
                ':id' => $_SESSION['user_id'],
            ]);
        } catch (Throwable $e) {
            // тихо ігноруємо помилку, щоб не ламати сторінку
        }
    }

    // Повертаємось в кабінет
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
</head>
<body>
<h3>Оберіть інтер'єр кабінету:</h3>

<form method="post">
    <select name="theme">
        <?php foreach ($availableThemes as $value => $label): ?>
            <option value="<?= h($value) ?>" <?= $current === $value ? 'selected' : '' ?>>
                <?= h($label) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Застосувати</button>
</form>

</body>
</html>


