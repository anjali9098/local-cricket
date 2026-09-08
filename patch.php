<?php
$file = 'resources/views/admin/manage-tournament.blade.php';
$content = file_get_contents($file);
$content = str_replace('<a href="#" style="background: rgba(255, 255, 255, 0.05);', '<a href="{{ route(\'tournaments\') }}" style="background: rgba(255, 255, 255, 0.05);', $content);
file_put_contents($file, $content);
echo "Done";
