<?php
require_once __DIR__ . '/config.php';


// якщо session_start() НЕ в config.php – розкоментуй:
// session_start();

// чистимо сесію
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// після виходу можна кидати на головну або на /login.php
header('Location: /');
exit;
