<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// Wrap navbar
$content = preg_replace('/<!-- Main Lovable Navbar -->/', "@if(!request()->is('admin*'))\n    <!-- Main Lovable Navbar -->", $content);
$content = preg_replace('/<\/nav>/', "</nav>\n    @endif", $content);

// Wrap footer
$content = preg_replace('/<footer class="site-footer">/', "@if(!request()->is('admin*'))\n    <footer class=\"site-footer\">", $content);
$content = preg_replace('/<\/footer>/', "</footer>\n    @endif", $content);

file_put_contents($file, $content);
echo "Done";
