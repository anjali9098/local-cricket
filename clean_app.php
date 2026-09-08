<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);
$content = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', $content);
file_put_contents($file, $content);
echo "Done";
