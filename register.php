<?php
session_start();
require_once __DIR__ . '/config.php';
 // той самий, що й в index.php, де $pdo

$errors = [];
$success = false;

$pseudonymEnabled = isset($_POST['use_pseudonym']);
$pseudonym = trim($_POST['pseudonym'] ?? '');

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

    if ($pseudonymEnabled && $pseudonym === '') {
        $errors[] = 'Вкажіть авторський псевдонім або вимкніть опцію.';
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
<nav class="navbar navbar-light clic-nav mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Clicuha</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h1 class="h4 mb-0">Join Clicuha</h1>
                        <button type="button"
                                class="ms-auto clic-btn clic-btn-no"
                                data-bs-toggle="modal"
                                data-bs-target="#profileModal">
                            Edit profile
                        </button>
                    </div>

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

                    <form method="post" class="needs-validation" novalidate>
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

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="usePseudonym" name="use_pseudonym" <?= $pseudonymEnabled ? 'checked' : '' ?>>
                            <label class="form-check-label" for="usePseudonym">
                                Використовувати авторський псевдонім
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ваш псевдонім</label>
                            <input type="text"
                                   name="pseudonym"
                                   class="form-control"
                                   id="pseudonymInput"
                                   placeholder="Наприклад, Sema Writer"
                                   value="<?= htmlspecialchars($pseudonym) ?>"
                                   <?= $pseudonymEnabled ? '' : 'disabled' ?>>
                            <div class="form-text">Показуватиметься замість реального імені в профілі.</div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="clic-btn clic-btn-yes">Створити акаунт</button>
                            <a href="/login.php" class="clic-btn clic-btn-no text-decoration-none text-reset">Вже є акаунт</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: profile edit -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Редагування профілю</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" readonly>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="modalUsePseudonym">
                    <label class="form-check-label" for="modalUsePseudonym">Авторський псевдонім</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Псевдонім</label>
                    <input type="text" class="form-control" id="modalPseudonym" placeholder="Наприклад, Clicuha Fan">
                </div>
                <div class="mb-3">
                    <label class="form-label">Про себе</label>
                    <textarea class="form-control" rows="3" placeholder="Кілька слів про вас"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="clic-btn clic-btn-no" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="clic-btn clic-btn-yes">Зберегти</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const usePseudonym = document.getElementById('usePseudonym');
const pseudonymInput = document.getElementById('pseudonymInput');

if (usePseudonym) {
    usePseudonym.addEventListener('change', () => {
        pseudonymInput.disabled = !usePseudonym.checked;
        if (!usePseudonym.checked) {
            pseudonymInput.value = '';
        }
    });
}
</script>
</body>
</html>
