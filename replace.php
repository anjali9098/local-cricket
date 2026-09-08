<?php
$content = file_get_contents(__DIR__ . '/public/assets/css/style.css');
$content = str_replace('color: #f1f5f9;', 'color: var(--text-main);', $content);
file_put_contents(__DIR__ . '/public/assets/css/style.css', $content);
echo "Replaced";
