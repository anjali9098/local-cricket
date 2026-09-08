<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// Wrap navbar
$content = str_replace('<!-- Main Lovable Navbar -->', "@if(!request()->is('admin*'))\n    <!-- Main Lovable Navbar -->", $content);
$content = str_replace('</nav>', "</nav>\n    @endif", $content);

// Wrap footer
$content = str_replace('<footer class="main-footer">', "@if(!request()->is('admin*'))\n    <footer class=\"main-footer\">", $content);
$content = str_replace('</footer>', "</footer>\n    @endif", $content);

file_put_contents($file, $content);
echo "Done";
