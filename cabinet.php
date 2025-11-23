<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';


// якщо в config.php ще не робиться session_start() – розкоментуй:
// session_start();

// Перевірка авторизації
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// 1) Тягнемо дані користувача з БД
$stmt = $pdo->prepare('SELECT id, email, username FROM users WHERE id = :id');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // на всякий випадок – якщо юзер видалений, але сесія висить
    session_destroy();
    header('Location: /login.php');
    exit;
}
// === [A] Мої клікухи поточного користувача ===
$stmt = $pdo->prepare("
    SELECT id, title, slug, description, created_at
    FROM nicknames
    WHERE user_id = :uid
    ORDER BY created_at DESC
    LIMIT 20
");
$stmt->execute([':uid' => $userId]);
$myNicknames = $stmt->fetchAll(PDO::FETCH_ASSOC);



// 2) Обробка зміни ніка
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'profile') {
    $newUsername = trim($_POST['username'] ?? '');

    if ($newUsername === '') {
        $profileError = 'Нік не може бути порожнім.';
    } else {
        // Перевіряємо унікальність
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u AND id <> :id');
        $stmt->execute([
            ':u'  => $newUsername,
            ':id' => $userId,
        ]);
        $exists = (int)$stmt->fetchColumn();

        if ($exists > 0) {
            $profileError = 'Такий нік вже використовується. Обери інший.';
        } else {
            $oldUsername = $user['username'] ?? null;

            $stmt = $pdo->prepare('UPDATE users SET username = :u WHERE id = :id');
            $stmt->execute([
                ':u'  => $newUsername,
                ':id' => $userId,
            ]);

            $profileSuccess = 'Нік оновлено.';

            // Оновимо локальний масив користувача (щоб одразу відобразилось)
            $user['username'] = $newUsername;

            // Примітивний лог змін ніка (можна потім прибрати або ускладнити)
            @file_put_contents(
                __DIR__ . '/logs/nick_changes.log',
                sprintf(
                    "[%s] user_id=%d old=%s new=%s\n",
                    date('c'),
                    $userId,
                    $oldUsername ?? '-',
                    $newUsername
                ),
                FILE_APPEND
            );
        }
    }
}

// 4) Мої клікухи (захищений варіант, щоб не ламав кабінет)
$myNicknames = [];

try {
    // Поки що показуємо просто останні клікухи (без фільтра по автору)
$nickStmt = $pdo->prepare("
    SELECT id, title, slug, status
    FROM nicknames
    ORDER BY created_at DESC
    LIMIT 10
");
$nickStmt->execute();
$myNicknames = $nickStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    // тимчасово глушимо помилку, щоб сторінка не падала
    $myNicknames = [];
    // можна записати в лог, якщо захочеш
    // @file_put_contents(__DIR__.'/logs/nick_errors.log', $e->getMessage()."\n", FILE_APPEND);
}

?>

<!doctype html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clicuha — Кабінет автора</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">


<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container py-4">

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
  <div class="alert alert-success py-2 small">
    Клікуху м’яко відправлено в архів 🙂
  </div>
<?php endif; ?>

  <h1 class="h4 mb-3">Кабінет автора</h1>
  <?php if (!empty($profileError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($profileError) ?></div>
  <?php endif; ?>
  <?php if (!empty($profileSuccess)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($profileSuccess) ?></div>
  <?php endif; ?>

  <div class="card mb-4" style="max-width: 480px;">
    <div class="card-body">
      <h2 class="h6 mb-3">Мій нік</h2>
      <form method="post">
        <input type="hidden" name="form_type" value="profile">
        <div class="mb-3">
          <label class="form-label">Нік (як тебе видно іншим)</label>
          <input
            type="text"
            name="username"
            class="form-control"
            value="<?= htmlspecialchars($user['username'] ?? '') ?>"
            placeholder="наприклад, Mizantrop, LanAmazon, PegasRider..."
          >
        </div>
        <button type="submit" class="btn btn-outline-primary btn-sm">
          Зберегти
        </button>
      </form>
    </div>
  </div>

   <div class="mb-4">
    <p class="mb-1">
     Привіт,
<?= htmlspecialchars($user['username']) ?>
</strong> 👋
    </p>
  </div>


  <div class="row g-3">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h5">Перші кроки</h2>
          <p class="small text-muted">
            Тут з’являться короткі правила, підказки та FAQ для Творців.
          </p>
          <ul class="small mb-3">
            <li>Що таке клікуха</li>
            <li>Як створити свою</li>
            <li>Що можна, а що ні</li>
          </ul>
         <a href="/#nicknames" class="btn btn-primary btn-sm mt-auto">
    Всі клікухи
</a>


        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h5">Створити клікуху</h2>
          <p class="small text-muted">
            Відкриємо головну і модалку створення нової клікухи.
          </p>
        <a href="/add_simple.php" class="btn btn-primary w-100">Я – Творець</a>




        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
         <h2 class="h5">Мої клікухи</h2>

<?php if (empty($myNicknames)): ?>

  <p class="small text-muted mb-3">
    У тебе поки немає власних клікух. Створи першу — і вона з’явиться тут.
  </p>

<?php else: ?>

  <ul class="list-unstyled small mb-3">
    <?php foreach ($myNicknames as $nick): ?>
      <li class="mb-1">
        <strong><?= htmlspecialchars($nick['title']) ?></strong>
        <span class="text-muted">
          @<?= htmlspecialchars($nick['slug']) ?>
        </span>
      </li>
    <?php endforeach; ?>
  </ul>

<?php endif; ?>

<a href="/my_nicknames.php" class="btn btn-primary btn-sm mt-auto">
    Всі мої клікухи
</a>



        </div>
      </div>
    </div>
  </div>
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
