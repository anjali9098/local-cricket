<?php
$file = 'app/Http/Controllers/LocalController.php';
$content = file_get_contents($file);

$search = '/(public function publishTournament\(\$id\)\s*\{[^\}]*\$tournament->save\(\);\s*)return back\(\)->with\([^\)]+\);\s*\}/';
$replace = '$1return redirect()->route(\'tournament.public\', \$id)->with(\'success\', \'Tournament published!\'); }';

$content = preg_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Done";
