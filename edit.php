<?php
require_once __DIR__ . '/config.php';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "Невірний ID";
    exit;
}

// Зчитуємо існуючу клікуху
$stmt = $pdo->prepare("SELECT id, title, slug, description, is_anonymous FROM nicknames WHERE id = :id LIMIT 1");

$stmt->execute([':id' => $id]);
$nick = $stmt->fetch();

if (!$nick) {
    http_response_code(404);
    echo "Клікуху не знайдено";
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slugInput  = trim($_POST['slug'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

    if ($title === '') {
        $errors[] = ' Clicuhaухи обовʼязкова.';
    }

    // генеруємо / чистимо slug
    if ($slugInput === '') {
        $slug = generate_slug_base($title);
    } else {
        $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower($slugInput));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        if (strlen($slug) > SLUG_MAX_LEN) {
            $slug = substr($slug, 0, SLUG_MAX_LEN);
            $slug = preg_replace('/-+$/', '', $slug);
        }
    }

    if ($slug !== '') {
        $slug = ensure_unique_slug($pdo, $slug, (int)$nick['id']);
    } else {
        $slug = null;
    }

    if (!$errors) {
               $stmt = $pdo->prepare(
            "UPDATE nicknames
             SET title = :title,
                 slug = :slug,
                 description = :description,
                 is_anonymous = :is_anonymous
             WHERE id = :id
             LIMIT 1"
        );

               $stmt->execute([
            ':title'       => $title,
            ':slug'        => $slug,
            ':description' => $desc !== '' ? $desc : null,
            ':is_anonymous'=> $is_anonymous,
            ':id'          => $id,
        ]);


        header('Location: /?updated=1');
        exit;
    } else {
        $nick['title']       = $title;
        $nick['slug']        = $slugInput;
        $nick['description'] = $desc;
        $nick['is_anonymous'] = $is_anonymous;
    }


}
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <title>Repair клікуху — Clicuha</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="mb-3">
      <a href="/" class="btn btn-link">&larr; На головну</a>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3">Repair клікуху</h1>

            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                  <div><?= h($e) ?></div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label"> Clicuha*</label>
                <input type="text" name="title" class="form-control"
                       value="<?= h($nick['title']) ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Slug (латиниця, опційно)</label>
                <input type="text" name="slug" class="form-control"
                       value="<?= h($nick['slug']) ?>">
              </div>

                            <div class="mb-3">
                <label class="form-label">Who is… (опційно)</label>
                <textarea name="description" class="form-control" rows="3"><?= h($nick['description']) ?></textarea>
              </div>
<div style="background: yellow; padding: 5px;">TEST ЧЕКБОКС</div>

              <!-- чекбокс анонімності -->
              <div class="mb-3 form-check">
                <input type="checkbox"
                       name="is_anonymous"
                       value="1"
                       class="form-check-input"
                       id="is_anonymous"
                       <?= !empty($nick['is_anonymous']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_anonymous">
                  Показувати автора як «анонімно»
                </label>
              </div>

              <button type="submit" class="btn btn-success">Зберегти зміни</button>
              <a href="/" class="btn btn-danger">Скасувати</a>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
