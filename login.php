<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/config.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

// якщо вже залогінений – одразу в кабінет
if (!empty($_SESSION['user_id'])) {
    header('Location: /cabinet.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors[] = 'Введіть email.';
    }
    if ($password === '') {
        $errors[] = 'Введіть пароль.';
    }

    if (!$errors) {
       $stmt = $pdo->prepare(
    'SELECT id, email, password_hash
     FROM users
     WHERE email = :email
     LIMIT 1'
);

        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Невірний email або пароль.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            header('Location: /cabinet.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clicuha – Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">

<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-3">Вхід</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="card p-3" style="max-width:420px;">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Пароль</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">
      Увійти
    </button>
  </form>
</main>

<footer class="border-top py-4 mt-4">
  <div class="container text-center small text-muted">
    © <span id="y"></span> Clicuha
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('y').textContent = new Date().getFullYear();
</script>
</body>
</html>
