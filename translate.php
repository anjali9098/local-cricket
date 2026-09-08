<?php
$files = [
    'resources/views/admin/dashboard.blade.php',
    'resources/views/local/dashboard.blade.php'
];
foreach($files as $file) {
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace('Aapke tournaments aur rewards', 'Your tournaments and rewards', $content);
    $content = str_replace('MERE TOURNAMENTS', 'MY TOURNAMENTS', $content);
    file_put_contents($file, $content);
}

$scorerFile = 'resources/views/admin/scorer.blade.php';
if(file_exists($scorerFile)) {
    $content = file_get_contents($scorerFile);
    $content = str_replace('Ball Record Karein', 'Record Ball', $content);
    file_put_contents($scorerFile, $content);
}

echo "Done";
