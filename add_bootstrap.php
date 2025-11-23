<?php
require_once __DIR__ . '/config.php';


$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    $desc  = trim($_POST['description'] ?? '');

    if ($title === '') {
        $errors[] = ' Clicuhaухи обовʼязкова.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            "INSERT INTO nicknames (title, slug, description)
             VALUES (:title, :slug, :description)"
        );
        $stmt->execute([
            ':title'       => $title,
            ':slug'        => $slug !== '' ? $slug : null,
            ':description' => $desc !== '' ? $desc : null,
        ]);
        $success = true;
    }
}
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <title>Додати клікуху — Bootstrap</title>
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
            <h1 class="h4 mb-3">Додати нову клікуху (варіант B)</h1>

            <?php if ($success): ?>
              <div class="alert alert-success">Клікуху успішно додано ✅</div>
            <?php endif; ?>

            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                  <div><?= h($e) ?></div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label"> Clicuhaу*</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Slug (латиниця, опційно)</label>
                <input type="text" name="slug" class="form-control" placeholder="lan-amazon">
              </div>
              <div class="mb-3">
                <label class="form-label">Who is… (опційно)</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
              </div>
              <button type="submit" class="btn btn-success"><span style="color:green;font-weight:bold;"><span class="text-success fw-bold">Yes</span></span></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
