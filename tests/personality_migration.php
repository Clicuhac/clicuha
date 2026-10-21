<?php
// Uses disposable tables only, in an explicitly named *_test database.
$env = parse_ini_file(__DIR__.'/../.env', false, INI_SCANNER_RAW);
if (!str_ends_with($env['DB_NAME'], '_test')) throw new RuntimeException('Test DB required');
$pdo = new PDO('mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_NAME'].';charset=utf8mb4', $env['DB_USER'], $env['DB_PASS'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$suffix = bin2hex(random_bytes(5));
$n = 'ready_migration_nicknames_'.$suffix;
$p = 'ready_migration_personalities_'.$suffix;
try {
    $pdo->exec("CREATE TABLE `$n` LIKE nicknames");
    $pdo->exec("ALTER TABLE `$n` DROP COLUMN first_published_at");
    $pdo->exec("INSERT INTO `$n` (title,created_by,owner_id,is_published,deleted_at) VALUES ('public owned',11,12,1,NULL),('public free',11,NULL,1,NULL),('cabinet',11,NULL,0,NULL),('archived',11,12,1,NOW())");
    $before = $pdo->query("SELECT * FROM `$n` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $migration = file_get_contents(__DIR__.'/../migrations/2026-10-21_personality_ready.sql');
    $migration = str_replace(['clicuha_personalities','nicknames','fk_personality_nickname'], [$p,$n,'fk_personality_'.$suffix], $migration);
    $pdo->exec($migration);
    $after = $pdo->query("SELECT * FROM `$n` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($after as &$row) {
        if (!$row['first_published_at']) throw new RuntimeException('Missing conservative legacy boundary');
        unset($row['first_published_at']);
    }
    unset($row);
    if ($before !== $after) throw new RuntimeException('Migration changed legacy data');
    if ((int)$pdo->query("SELECT COUNT(*) FROM `$p`")->fetchColumn() !== 0) throw new RuntimeException('Invented personality');
    $id = (int)$before[0]['id'];
    $pdo->prepare("INSERT INTO `$p` (nickname_id,core_json) VALUES (?,?)")->execute([$id,'{}']);
    $pdo->exec("DELETE FROM `$n` WHERE id=$id");
    if ((int)$pdo->query("SELECT COUNT(*) FROM `$p`")->fetchColumn() !== 0) throw new RuntimeException('FK cascade failed');
    echo "PASS migration preserves all legacy fields, adds no synthetic core and enforces FK cascade\n";
} finally {
    $pdo->exec("DROP TABLE IF EXISTS `$p`");
    $pdo->exec("DROP TABLE IF EXISTS `$n`");
}
