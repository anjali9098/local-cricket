<?php
foreach(['admin', 'local'] as $role) {
    $file = "resources/views/$role/manage-tournament.blade.php";
    if(!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Replace known corrupted sequences
    $replacements = [
        'âœ…' => '?',
        'â Œ' => '?',
        'ðŸ“ ' => '??',
        'ðŸŒ ' => '??',
        'ðŸ“‹' => '??',
        'ðŸ‘¥' => '??',
        'ðŸ ’' => '??'
    ];
    
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    
    // Also try to replace by regex if they have other weird artifacts
    // In blade files, there might be other corrupted chars. 
    
    file_put_contents($file, $content);
}
echo "Done";
