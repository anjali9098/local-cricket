<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

$content = preg_replace('/@if\(\!request\(\)->is\(\'admin\*\'\)\)\s*<!-- Main Lovable Navbar -->/', '<!-- Main Lovable Navbar -->', $content);
$content = preg_replace('/<\/nav>\s*@endif/', '</nav>', $content);

$content = preg_replace('/@if\(\!request\(\)->is\(\'admin\*\'\)\)\s*<footer class="site-footer">/', '<footer class="site-footer">', $content);
$content = preg_replace('/<\/footer>\s*@endif/', '</footer>', $content);

file_put_contents($file, $content);
echo "Done";
