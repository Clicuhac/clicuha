<?php
// 4 години життя сесії
$lifetime = 60 * 60 * 4;

// 1) Стартуємо сесію і виставляємо параметри ТІЛЬКИ якщо вона ще не запущена
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $lifetime);
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'domain'   => '',   // можна залишити порожнім
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// 2) "Soft"-контроль останньої активності — тільки якщо сесія активна
if (session_status() === PHP_SESSION_ACTIVE) {
    if (
        isset($_SESSION['LAST_ACTIVITY']) &&
        (time() - $_SESSION['LAST_ACTIVITY'] > $lifetime)
    ) {
        $_SESSION = [];
        session_destroy();
        header('Location: /login.php');
        exit;
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}




// 1. Читання параметра ?lang=uk або ?lang=en
if (!empty($_GET['lang'])) {
    $lang = preg_replace('/[^a-z]/', '', $_GET['lang']);
    $_SESSION['lang'] = $lang;
}

// 2. Поточна мова або uk
$lang = $_SESSION['lang'] ?? 'uk';

// 3. Завантаження файлу перекладів
$langFile = __DIR__ . "/lang/$lang.php";
if (!file_exists($langFile)) {
    $langFile = __DIR__ . "/lang/uk.php";
}

$translations = include $langFile;


// 4. Функція перекладу
if (!function_exists('t')) {
    function t(string $key): string
    {
        global $translations;
        return $translations[$key] ?? $key;
    }
}




$envFile = __DIR__.'/.env';
$env = [];
if (is_file($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW) ?: [];
}

$DB_HOST = $env['DB_HOST'] ?? 'localhost';
$DB_NAME = $env['DB_NAME'] ?? '';
$DB_USER = $env['DB_USER'] ?? '';
$DB_PASS = $env['DB_PASS'] ?? '';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
    http_response_code(500);
    echo "<pre>DB connection failed: ".htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')."</pre>";
    exit;
}

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
