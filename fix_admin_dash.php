<?php
$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);
$content = str_replace('$tournaments', '$adminTournaments', $content);
file_put_contents($file, $content);
echo "Done";
