<?php
echo "Hello, Clicuha! Deployed ✓";
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clicuha</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<body class="bg-light">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/">Clicuha</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="/">Головна</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Клікухи</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Про проєкт</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Контакти</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main -->
  <main class="container py-5">
    <div class="text-center">
      <h1 class="display-4 mb-3">Hello world</h1>
      <p class="lead">Bootstrap підключено. Далі будуємо Clicuha.</p>
    </div>
  </main>

  <!-- Footer -->
  <footer class="border-top py-4">
    <div class="container text-center small text-muted">
      © <span id="y"></span> Clicuha
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>document.getElementById('y').textContent = new Date().getFullYear();</script>
</body>

<div class="container py-5 text-center">
  <h1 class="display-4 mb-4">Hello world</h1>
  <p class="lead">Bootstrap підключено. Далі робимо Clicuha.</p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


