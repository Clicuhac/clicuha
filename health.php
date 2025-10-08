<?php
if (($_GET['k'] ?? '') !== 'CLICUHA_OK') { http_response_code(403); exit('forbidden'); }
echo "OK " . date('Y-m-d H:i:s');
