<?php
// add_simple.php — красива сторінка створення клікухи
declare(strict_types=1);

require_once __DIR__ . '/config.php';


// ВКЛЮЧАЄМО помилки, щоб не було «білих сторінок»
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// ---------- SLUG-утиліти (такі ж, як на головній) ----------
if (!defined('SLUG_MAX_LEN')) {
    define('SLUG_MAX_LEN', 30);
}

if (!function_exists('basic_translit')) {
    function basic_translit(string $s): string
    {
        $map = [
            // укр + рос
            'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'h',
            'ґ' => 'g',  'д' => 'd',  'е' => 'e',  'є' => 'ye',
            'ж' => 'zh', 'з' => 'z',  'и' => 'y',  'і' => 'i',
            'ї' => 'yi', 'й' => 'y',  'к' => 'k',  'л' => 'l',
            'м' => 'm',  'н' => 'n',  'о' => 'o',  'п' => 'p',
            'р' => 'r',  'с' => 's',  'т' => 't',  'у' => 'u',
            'ф' => 'f',  'х' => 'h',  'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch',
            'ю' => 'yu', 'я' => 'ya',
            // «м’які»/службові
            'ь' => '',   'ъ' => '',   '’' => '',   '\'' => '',
            // рос специфічні
            'ы' => 'y',  'э' => 'e',  'ё' => 'yo',
        ];
        return strtr($s, $map);
    }

    function generate_slug_base(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        $s = mb_strtolower($title, 'UTF-8');
        $s = basic_translit($s);

        if (function_exists('iconv')) {
            $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($tmp !== false) {
                $s = strtolower($tmp);
            }
        }

        $parts = preg_split('/[^a-z0-9]+/i', $s, -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) {
            return '';
        }

        $stop = [
            'z','iz','vid','do','na','za','u','v','ta','i',
            's','ot','po','no','da','ne','ni',
            'the','a','an','of','to','for','and','or',
        ];
        $stop = array_unique($stop);

        $clean = [];
        foreach ($parts as $w) {
            if (in_array($w, $stop, true)) {
                continue;
            }
            $clean[] = $w;
        }
        if (!$clean) {
            $clean = $parts;
        }

        $slug = implode('-', $clean);

        if (strlen($slug) > SLUG_MAX_LEN) {
            $slug = substr($slug, 0, SLUG_MAX_LEN);
            $slug = preg_replace('/-+$/', '', $slug);
        }

        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    function normalize_slug(string $raw): string
    {
        $slug = mb_strtolower($raw, 'UTF-8');
        $slug = basic_translit($slug);

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
            if ($converted !== false) {
                $slug = strtolower($converted);
            }
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        if (strlen($slug) > SLUG_MAX_LEN) {
            $slug = substr($slug, 0, SLUG_MAX_LEN);
            $slug = trim($slug, '-');
        }

        return $slug;
    }

    function ensure_unique_slug(PDO $pdo, string $slug, ?int $ignoreId = null): string
    {
        if ($slug === '') {
            return '';
        }

        $base = $slug;
        $i    = 1;

        while (true) {
            $sql = "SELECT COUNT(*) FROM nicknames WHERE slug = :slug";
            if ($ignoreId !== null) {
                $sql .= " AND id <> :id";
            }

            $stmt   = $pdo->prepare($sql);
            $params = [':slug' => $slug];
            if ($ignoreId !== null) {
                $params[':id'] = $ignoreId;
            }
            $stmt->execute($params);

            if ((int)$stmt->fetchColumn() === 0) {
                return $slug;
            }

            $i++;
            $suffix = '-' . $i;
            $slug   = $base;

            if (strlen($slug) > SLUG_MAX_LEN - strlen($suffix)) {
                $slug = substr($slug, 0, SLUG_MAX_LEN - strlen($suffix));
                $slug = rtrim($slug, '-');
            }

            $slug .= $suffix;
        }
    }
}

// ---------- ОБРОБКА ФОРМИ ----------
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $slugInput   = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $userId      = $_SESSION['user_id'] ?? null;

    if ($title === '') {
        $errors[] = 'Введіть назву клікухи.';
    }

    if ($slugInput === '') {
        $slug = generate_slug_base($title);
    } else {
        $slug = normalize_slug($slugInput);
    }

    if ($slug !== '') {
        $slug = ensure_unique_slug($pdo, $slug, null);
    } else {
        $slug = null;
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO nicknames (user_id, title, short_title, slug, description, is_anonymous)
                 VALUES (:user_id, :title, :short_title, :slug, :description, :is_anonymous)"
            );
            $stmt->execute([
                ':user_id'      => $userId,
                ':title'        => $title,
                ':short_title'  => null,
                ':slug'         => $slug,
                ':description'  => $description !== '' ? $description : null,
                ':is_anonymous' => $isAnonymous,
            ]);

            // після успіху — в кабінет
            header('Location: /cabinet.php?added=1');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'DB error: ' . $e->getMessage();
        }
    }
}

?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang ?? 'uk') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Додати клікуху — Clicuha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">

<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container py-4">
  <h1 class="h4 mb-4">Додати клікуху</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><?= h($e) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="card shadow-sm">
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Clicuha*</label>
        <input type="text" name="title" class="form-control"
               value="<?= isset($title) ? h($title) : '' ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Slug (латиниця, опційно)</label>
        <input type="text" name="slug" class="form-control"
               placeholder="якщо пусто — згенеруємо"
               value="<?= isset($slugInput) ? h($slugInput) : '' ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Who is… (опис, опційно)</label>
        <textarea name="description" rows="4" class="form-control"><?= isset($description) ? h($description) : '' ?></textarea>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1"
               <?= !empty($isAnonymous) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_anonymous">
          Показувати автора як «анонімно»
        </label>
      </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="/cabinet.php" class="btn btn-outline-secondary">Назад у кабінет</a>
      <button type="submit" class="btn btn-primary">Зберегти клікуху</button>
    </div>
  </form>
</main>

<footer class="border-top py-4 mt-4">
  <div class="container text-center small text-muted">
    © <span id="y"></span> Clicuha
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var y = document.getElementById('y');
  if (y) y.textContent = new Date().getFullYear();
});
</script>
</body>
</html>
