<?php $adminSection = $adminSection ?? 'dashboard'; ?>
<aside class="admin-sidebar">
  <a class="admin-brand" href="/admin/">Clicuha <span>ADMIN</span></a>
  <nav class="admin-nav" aria-label="Admin navigation">
    <a class="<?= $adminSection === 'dashboard' ? 'active' : '' ?>" href="/admin/">Dashboard</a>
    <a class="<?= $adminSection === 'nicknames' ? 'active' : '' ?>" href="/admin/nicknames.php">Clicuhas</a>
    <span>Users <small>soon</small></span>
    <span>Archive <small>soon</small></span>
    <span>Logs <small>soon</small></span>
  </nav>
  <div class="admin-sidebar-bottom">
    <a href="/cabinet.php">← Кабінет</a>
    <a href="/logout.php">Вийти</a>
  </div>
</aside>
