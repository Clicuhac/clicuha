<?php $adminSection = $adminSection ?? 'dashboard'; ?>
<aside class="admin-sidebar">
  <a class="admin-brand" href="/admin/">Clicuha <span>ADMIN</span></a>
  <nav class="admin-nav" aria-label="Admin navigation">
    <a class="<?= $adminSection === 'dashboard' ? 'active' : '' ?>" href="/admin/">Dashboard</a>

    <div class="admin-nav-group">CONTENT</div>
    <a class="<?= $adminSection === 'nicknames' ? 'active' : '' ?>" href="/admin/nicknames.php">Clicuhas</a>
    <a class="<?= $adminSection === 'users' ? 'active' : '' ?>" href="/admin/users.php">Users</a>
    <span>Qualities <small>soon</small></span>
    <span>Communication <small>soon</small></span>
    <span>Moderation <small>soon</small></span>
    <a class="<?= $adminSection === 'archive' ? 'active' : '' ?>" href="/admin/archive.php">Archive</a>
    <a class="<?= $adminSection === 'logs' ? 'active' : '' ?>" href="/admin/logs.php">Logs</a>

    <div class="admin-nav-group">PLATFORM</div>
    <span>Advertising <small>soon</small></span>
    <span>Payments <small>soon</small></span>
    <span>System <small>soon</small></span>
  </nav>
  <div class="admin-sidebar-bottom" style="margin-top:18px;">
    <a href="/cabinet.php">← Кабінет</a>
    <a href="/logout.php">Вийти</a>
  </div>
</aside>
