<?php
declare(strict_types=1);

function clicuha_forbidden(string $message = 'У вас немає доступу до цієї дії.'): never
{
    http_response_code(403);
    $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>403 · Clicuha</title><link rel="stylesheet" href="/assets/css/sema.css"><style>body{background:#f7f3fc;font-family:system-ui;padding:2rem}.access-card{max-width:32rem;margin:10vh auto;background:white;padding:2rem;border-radius:1rem;border:1px solid #dcc8f6}a{color:#633290}</style></head><body><main class="access-card"><h1>403 · Доступ обмежено</h1><p>' . $safeMessage . '</p><a href="/cabinet.php">До кабінету</a> · <a href="/index.php">До Галереї</a></main></body></html>';
    exit;
}
