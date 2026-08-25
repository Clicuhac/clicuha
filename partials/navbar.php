<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$loggedIn = !empty($_SESSION['user_id']);
$currentPageFile = basename($_SERVER['PHP_SELF']);
$query = $_GET;
unset($query['lang']);
if (!function_exists('lang_href')) {
    function lang_href(string $code, array $query): string {
        $query['lang'] = $code;
        return '?' . http_build_query($query);
    }
}
?>
<nav class="navbar navbar-expand-lg border-bottom mb-4 clic-nav">
  <div class="container">
    <a class="navbar-brand" href="/index.php">Clicuha</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/index.php"><?= h(t('menu.home')) ?></a></li>
        <?php if ($loggedIn): ?><li class="nav-item"><a class="nav-link" href="/my_nicknames.php"><?= h(t('menu.my')) ?></a></li><?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="/about.php"><?= h(t('menu.about')) ?></a></li>
        <li class="nav-item"><a class="nav-link" href="/contacts.php"><?= h(t('menu.contacts')) ?></a></li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <?php if ($loggedIn): ?>
          <li class="nav-item"><a href="/add_bootstrap.php" class="btn btn-success btn-sm"><?= h(t('menu.new')) ?></a></li>
          <li class="nav-item"><a href="/cabinet.php" class="btn btn-outline-primary btn-sm"><?= h(t('menu.cabinet')) ?></a></li>
          <li class="nav-item"><a href="/logout.php" class="btn btn-outline-danger btn-sm"><?= h(t('auth_logout')) ?></a></li>
        <?php else: ?>
          <?php if ($currentPageFile !== 'login.php'): ?><li class="nav-item"><a href="/login.php" class="btn btn-warning btn-sm"><?= h(t('auth_login')) ?></a></li><?php endif; ?>
          <?php if ($currentPageFile !== 'register.php'): ?><li class="nav-item"><a href="/register.php" class="btn btn-outline-warning btn-sm"><?= h(t('auth_join')) ?></a></li><?php endif; ?>
        <?php endif; ?>
        <li class="nav-item"><a href="<?= h(lang_href('uk', $query)) ?>" class="btn btn-outline-dark btn-sm">UA</a></li>
        <li class="nav-item"><a href="<?= h(lang_href('en', $query)) ?>" class="btn btn-outline-dark btn-sm">EN</a></li>
        <li class="nav-item"><a href="<?= h(lang_href('ru', $query)) ?>" class="btn btn-outline-dark btn-sm">RU</a></li>
      </ul>
    </div>
  </div>
</nav>
