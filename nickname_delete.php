<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';


// Користувач має бути залогінений
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Беремо id або з POST, або з GET (щоб працювали і форма, і лінк)
$id = 0;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if ($id <= 0) {
    header('Location: /cabinet.php?deleted=0&reason=bad_id');
    exit;
}

// 1) Перевіряємо, що така клікуха існує
$stmt = $pdo->prepare("
    SELECT id, user_id, deleted_at
    FROM nicknames
    WHERE id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$nick = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nick) {
    header('Location: /cabinet.php?deleted=0&reason=not_found');
    exit;
}

// 2) Перевірка власника
if ((int)$nick['user_id'] !== $userId) {
    header('Location: /cabinet.php?deleted=0&reason=forbidden');
    exit;
}

// 3) Якщо вже видалена — нічого не робимо
if (!empty($nick['deleted_at'])) {
    header('Location: /cabinet.php?deleted=0&reason=already_deleted');
    exit;
}

// 4) М'яке видалення: ставимо мітку часу замість DELETE
$del = $pdo->prepare("
    UPDATE nicknames
    SET deleted_at = NOW()
    WHERE id = :id
      AND user_id = :uid
      AND deleted_at IS NULL
");
$del->execute([
    ':id'  => $id,
    ':uid' => $userId,
]);

header('Location: /cabinet.php?deleted=1');
exit;
