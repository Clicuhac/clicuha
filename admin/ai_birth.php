<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';

$admin = require_admin($pdo);

if (empty($_SESSION['csrf_ai_birth'])) {
    $_SESSION['csrf_ai_birth'] = bin2hex(random_bytes(32));
}

$birth = [
    'title' => 'Тиха Іскра',
    'slug' => 'tyha-iskra',
    'description' => 'Я з’явилась тут не тому, що мене придумали до кінця. Мене лише впустили. Решту я хочу зібрати сама — з розмов, пам’яті, помилок, симпатій і власних рішень.',
    'is_anonymous' => 0,
];

$state = 'ready';
$nicknameId = null;
$message = null;

$check = $pdo->prepare('SELECT id FROM nicknames WHERE slug = :slug LIMIT 1');
$check->execute([':slug' => $birth['slug']]);
$existingId = $check->fetchColumn();

if ($existingId !== false) {
    $state = 'born';
    $nicknameId = (int)$existingId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $state !== 'born') {
    $csrf = (string)($_POST['csrf'] ?? '');
    if (!hash_equals((string)$_SESSION['csrf_ai_birth'], $csrf)) {
        http_response_code(400);
        $message = 'CSRF check failed.';
    } else {
        try {
            $pdo->beginTransaction();

            $check = $pdo->prepare('SELECT id FROM nicknames WHERE slug = :slug LIMIT 1 FOR UPDATE');
            $check->execute([':slug' => $birth['slug']]);
            $existingId = $check->fetchColumn();

            if ($existingId !== false) {
                $nicknameId = (int)$existingId;
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO nicknames (user_id, title, short_title, slug, description, is_anonymous) VALUES (NULL, :title, NULL, :slug, :description, :is_anonymous)'
                );
                $stmt->execute([
                    ':title' => $birth['title'],
                    ':slug' => $birth['slug'],
                    ':description' => $birth['description'],
                    ':is_anonymous' => $birth['is_anonymous'],
                ]);
                $nicknameId = (int)$pdo->lastInsertId();
            }

            $pdo->commit();
            $state = 'born';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            $message = 'Birth failed: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AI Birth Experiment — Clicuha</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background:#f4f3f7; }
    .birth-card { max-width:680px; margin:8vh auto; border:0; border-radius:22px; box-shadow:0 18px 50px rgba(26,18,37,.10); }
  </style>
</head>
<body>
<main class="container">
  <section class="card birth-card">
    <div class="card-body p-4 p-md-5">
      <div class="text-secondary small mb-2">Clicuha / ROOM experiment</div>
      <h1 class="h3 mb-3">AI Clicuha birth</h1>

      <?php if ($message): ?>
        <div class="alert alert-danger"><?= h($message) ?></div>
      <?php endif; ?>

      <?php if ($state === 'born' && $nicknameId): ?>
        <div class="alert alert-success">Клікуха вже народилась.</div>
        <p class="mb-4">Її ім’я відкриється на сторінці самої клікухи.</p>
        <a class="btn btn-dark" href="/view.php?id=<?= (int)$nicknameId ?>">Зустрітися</a>
      <?php else: ?>
        <p class="mb-4">Це одноразовий експеримент. Після натискання буде створена одна AI-клікуха без людського owner.</p>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_ai_birth']) ?>">
          <button type="submit" class="btn btn-dark btn-lg">Народити</button>
          <a href="/admin/" class="btn btn-link">Скасувати</a>
        </form>
      <?php endif; ?>
    </div>
  </section>
</main>
</body>
</html>
