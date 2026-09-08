<?php
$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

$content = str_replace("route('tournament.public', \$t->id)", "route('admin.tournament.preview', \$t->id)", $content);

file_put_contents($file, $content);
echo "Dashboard view updated";
