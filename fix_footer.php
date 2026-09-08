<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// 1. Remove the stray @endif
$content = preg_replace('/<\/footer>\s*@endif/', "</footer>", $content);

// 2. Properly wrap the footer
$content = str_replace('<footer class="site-footer">', "@if(!request()->is('admin*'))\n    <footer class=\"site-footer\">", $content);
$content = str_replace('</footer>', "</footer>\n    @endif", $content);

file_put_contents($file, $content);
echo "Done";
