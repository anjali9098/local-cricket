<?php
foreach(['app/Http/Controllers/AdminController.php', 'app/Http/Controllers/LocalController.php'] as $file) {
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace the return back() with redirect()->route('tournament.public', $id) in publishTournament
    // We need to be careful. Let's use regex for publishTournament
    
    $search = '/(public function publishTournament\(\$id\)\s*\{[^\}]*\$tournament->save\(\);\s*)return back\(\)->with\([^\)]+\);\s*\}/';
    $replace = '$1return redirect()->route(\'tournament.public\', \$id)->with(\'success\', \'Tournament published!\'); }';
    
    $content = preg_replace($search, $replace, $content);
    file_put_contents($file, $content);
}
echo "Done";
