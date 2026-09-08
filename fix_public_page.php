<?php
foreach(['admin', 'local'] as $role) {
    $file = "resources/views/$role/manage-tournament.blade.php";
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace("route('tournaments')", "route('tournament.public', \$tournament->id)", $content);
    file_put_contents($file, $content);
}
echo "Done";
