<?php
$files = ['resources/views/admin/match_detail.blade.php', 'resources/views/admin/tournament_preview.blade.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace("@extends('layouts.app')", "@extends('layouts.admin')", $content);
        file_put_contents($file, $content);
    }
}
echo "Layouts updated for admin views";
