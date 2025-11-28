<?php
require_once __DIR__ . '/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $slug         = trim($_POST['slug'] ?? '');
    $desc         = trim($_POST['description'] ?? '');
    $isAnonymous  = isset($_POST['is_anonymous']) ? 1 : 0;

    if ($title === '') {
        $errors[] = ' Clicuhaухи обовʼязкова.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            "INSERT INTO nicknames (title, slug, description, is_anonymous)
             VALUES (:title, :slug, :description, :is_anonymous)"
        );
        $stmt->execute([
            ':title'        => $title,
            ':slug'         => $slug !== '' ? $slug : null,
            ':description'  => $desc !== '' ? $desc : null,
            ':is_anonymous' => $isAnonymous,
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
  <link rel="stylesheet" href="/assets/css/sema.css?v=123">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>

  <div class="container py-4">
    <div class="mb-3">
      <a href="/" class="btn btn-link">&larr; На головну</a>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3">Додати нову клікуху (варіант B)</h1>

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
                <input type="text" name="title" id="title" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Slug (латиниця, опційно)</label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="lan-amazon">
              </div>
              <div class="mb-3">
                <label class="form-label">Who is… (опційно)</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1">
                <label class="form-check-label" for="is_anonymous">
                  Показувати автора як «анонімно»
                </label>
              </div>

              <button type="submit" class="clic-btn clic-btn-yes">Зберегти клікуху</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="successModalLabel">Клікуху додано</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Нова клікуха успішно створена. Ви можете повернутися на головну або додати ще одну.
        </div>
        <div class="modal-footer">
          <a href="/" class="btn btn-outline-secondary">На головну</a>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Добре</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var titleInput = document.getElementById('title');
      var slugInput  = document.getElementById('slug');
      var slugTouched = false;

      function translitToSlug(value) {
        var map = {
          'а':'a','б':'b','в':'v','г':'h','ґ':'g','д':'d','е':'e','є':'ye','ж':'zh','з':'z','и':'y','і':'i','ї':'yi','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'ts','ч':'ch','ш':'sh','щ':'sch','ю':'yu','я':'ya','ь':'','ъ':'','’':'','\'':'','ы':'y','э':'e','ё':'yo'
        };

        var slug = value.toLowerCase().split('').map(function (ch) {
          return Object.prototype.hasOwnProperty.call(map, ch) ? map[ch] : ch;
        }).join('');

        slug = slug.normalize('NFD').replace(/[^\w\-\s]+/g, '').replace(/[\s_]+/g, '-');
        slug = slug.replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, '');
        return slug.slice(0, 30);
      }

      if (slugInput) {
        slugInput.addEventListener('input', function () {
          slugTouched = slugInput.value.trim() !== '';
        });
      }

      if (titleInput) {
        titleInput.addEventListener('input', function () {
          if (!slugInput || slugTouched) return;
          slugInput.value = translitToSlug(titleInput.value);
        });
      }

      <?php if ($success): ?>
      var modalEl = document.getElementById('successModal');
      if (modalEl) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
      <?php endif; ?>
    });
  </script>
</body>
</html>
