<?php
session_start();
require_once __DIR__ . '/config.php';
 // той самий, що й в index.php, де $pdo

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }

    if (mb_strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 chars.';
    }

    if ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        // перевіряємо, чи немає такого email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (email, password_hash) VALUES (:email, :hash)'
            );
            $stmt->execute(['email' => $email, 'hash' => $hash]);

         $success = true;

// Переходимо на сторінку входу
header("Location: /login.php");
exit;

        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Join Clicuha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/sema.css?v=1">

</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Clicuha</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Join Clicuha</h1>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Account created. You can log in (login page we’ll add next).
                        </div>
                    <?php endif; ?>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Repeat password</label>
                            <input type="password" name="password2" class="form-control" required>
                        </div>

          <div class="mt-3">
<div class="mt-3">
    <button type="submit"
            name="answer"
            value="yes"
            class="clic-btn clic-btn-yes">
        Yes
    </button>

    <button type="button"
            name="answer"
            value="no"
            class="clic-btn clic-btn-no">
        No
    </button>

    <button type="button"
            name="answer"
            value="maybe"
            class="clic-btn clic-btn-maybe">
        Maybe
    </button>
</div>

</a>

</a>

</a>

</div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
