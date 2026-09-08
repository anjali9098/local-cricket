<?php
$file = 'resources/views/admin/manage-tournament.blade.php';
$content = file_get_contents($file);

$content = str_replace("route('matches.detail', \$match->id)", "route('admin.match.detail', \$match->id)", $content);
$content = str_replace("route('tournament.public', \$tournament->id)", "route('admin.tournament.preview', \$tournament->id)", $content);

file_put_contents($file, $content);
echo "Buttons updated";
