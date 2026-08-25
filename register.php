<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /cabinet.php');
    exit;
}

$errors = [];
$email = '';
$pseudonymEnabled = false;
$pseudonym = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $pseudonymEnabled = isset($_POST['use_pseudonym']);
    $pseudonym = trim($_POST['pseudonym'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 chars.';
    }
    if ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    }
    if ($pseudonymEnabled && $pseudonym === '') {
        $errors[] = 'Вкажіть авторський псевдонім або вимкніть опцію.';
    }
    if ($pseudonymEnabled && mb_strlen($pseudonym) > 32) {
        $errors[] = 'Псевдонім має містити не більше 32 символів.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        }
    }

    if (!$errors && $pseudonymEnabled) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
        $stmt->execute([':username' => $pseudonym]);
        if ($stmt->fetch()) {
            $errors[] = 'Такий псевдонім уже використовується.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (email, password_hash, pass_hash, username) VALUES (:email, :password_hash, :pass_hash, :username)'
        );
        $stmt->execute([
            ':email' => $email,
            ':password_hash' => $hash,
            ':pass_hash' => $hash,
            ':username' => $pseudonymEnabled ? $pseudonym : null,
        ]);
        header('Location: /login.php?registered=1');
        exit;
    }
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Join Clicuha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>
<main class="container py-4">
  <div class="row justify-content-center"><div class="col-md-7 col-lg-6"><div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-3">Join Clicuha</h1>
    <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" novalidate>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= h($email) ?>" required></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Repeat password</label><input type="password" name="password2" class="form-control" required></div>
      <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="usePseudonym" name="use_pseudonym" <?= $pseudonymEnabled ? 'checked' : '' ?>><label class="form-check-label" for="usePseudonym">Використовувати авторський псевдонім</label></div>
      <div class="mb-3"><label class="form-label">Ваш псевдонім</label><input type="text" maxlength="32" name="pseudonym" class="form-control" id="pseudonymInput" placeholder="Наприклад, Sema Writer" value="<?= h($pseudonym) ?>" <?= $pseudonymEnabled ? '' : 'disabled' ?>><div class="form-text">Показуватиметься як ім’я автора ваших неанонімних клікух.</div></div>
      <div class="d-flex gap-3 mt-4"><button type="submit" class="clic-btn clic-btn-yes">Створити акаунт</button><a href="/login.php" class="clic-btn clic-btn-no text-decoration-none text-reset">Вже є акаунт</a></div>
    </form>
  </div></div></div></div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const usePseudonym=document.getElementById('usePseudonym');
const pseudonymInput=document.getElementById('pseudonymInput');
if(usePseudonym){usePseudonym.addEventListener('change',()=>{pseudonymInput.disabled=!usePseudonym.checked;if(!usePseudonym.checked)pseudonymInput.value='';});}
</script>
</body>
</html>
