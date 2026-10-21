<?php
declare(strict_types=1);

function personality_traits(): array
{
    return ['curious'=>'Допитливість', 'empathetic'=>'Емпатійність',
        'independent'=>'Незалежність', 'creative'=>'Творчість',
        'persistent'=>'Наполегливість', 'cautious'=>'Обережність',
        'sociable'=>'Товариськість', 'reflective'=>'Вдумливість'];
}

function personality_presets(): array
{
    return [
        'explorer_v1'=>['label'=>'Дослідниця', 'core'=>[
            'traits'=>['curious','independent','persistent'],
            'motivation'=>'Досліджувати нове та знаходити власні відповіді.',
            'vulnerability'=>'Важко зупинитися, коли відповідь ще не знайдена.']],
        'companion_v1'=>['label'=>'Уважна співрозмовниця', 'core'=>[
            'traits'=>['empathetic','sociable','reflective'],
            'motivation'=>'Розуміти інших і допомагати знаходити порозуміння.',
            'vulnerability'=>'Важко відстоювати власні межі у конфлікті.']],
    ];
}

function personality_missing(array $core, int $version = 1): array
{
    if ($version !== 1) {
        return ['Непідтримувана версія ядра.'];
    }
    $traits = is_array($core['traits'] ?? null) ? $core['traits'] : [];
    $known = array_unique(array_filter($traits, fn($t) => is_string($t) && isset(personality_traits()[$t])));
    $missing = [];
    if (count($known) < 3) {
        $missing[] = 'Додайте різні риси характеру: ще ' . (3 - count($known)) . '.';
    }
    foreach (['motivation'=>'Додайте мотивацію.', 'vulnerability'=>'Додайте обмеження або вразливість.'] as $key=>$label) {
        $value = $core[$key] ?? null;
        if (!is_string($value) || preg_match('/^\s*$/u', $value) !== 0) {
            $missing[] = $label;
        }
    }
    return $missing;
}

function get_personality(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT schema_version,preset_key,core_json FROM clicuha_personalities WHERE nickname_id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $decoded = json_decode($row['core_json'], true);
    $row['core'] = is_array($decoded) ? $decoded : [];
    return $row;
}

function get_personality_missing_elements(PDO $pdo, int $id): array
{
    $row = get_personality($pdo, $id);
    return personality_missing($row['core'] ?? [], (int)($row['schema_version'] ?? 1));
}

function is_personality_ready(PDO $pdo, int $id): bool
{
    return get_personality_missing_elements($pdo, $id) === [];
}
