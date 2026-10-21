<?php
require __DIR__.'/../lib/clicuha_access.php';
function check(bool $condition): void { if (!$condition) throw new RuntimeException('Assertion failed'); }
$core=['traits'=>['curious','independent','creative'],'motivation'=>'Досліджувати','vulnerability'=>'Страх'];
check(personality_missing($core) === []);
foreach ([['curious','curious','curious'],['unknown','independent','creative'],[]] as $traits) {
    check(personality_missing(array_replace($core,['traits'=>$traits])) !== []);
}
check(personality_missing(array_replace($core,['motivation'=>" \n\t\u{00A0}"])) !== []);
check(personality_missing(array_replace($core,['vulnerability'=>[]])) !== []);
check(personality_missing($core,99) !== []);
foreach (personality_presets() as $preset) check(personality_missing($preset['core']) === []);
$draft=['created_by'=>1,'owner_id'=>null,'is_published'=>0,'first_published_at'=>null,'deleted_at'=>null];
check(clicuha_can_manage($draft,1));
check(!clicuha_can_manage($draft,2));
check(clicuha_can_view($draft,1));
check(!clicuha_can_view($draft,0));
check(!clicuha_can_view($draft,2));
$public=array_replace($draft,['is_published'=>1,'first_published_at'=>'2026-10-21']);
check(!clicuha_can_manage($public,1));
check(clicuha_can_view($public,0));
check(!clicuha_can_manage(array_replace($public,['is_published'=>0]),1));
check(clicuha_can_manage(array_replace($public,['owner_id'=>2]),2));
check(clicuha_can_manage($public,2,true));
echo "PASS validator and access matrix\n";
