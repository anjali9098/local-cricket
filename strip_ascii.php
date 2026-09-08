<?php
foreach(['admin', 'local'] as $role) {
    $file = "resources/views/$role/manage-tournament.blade.php";
    if(!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Replace all weird characters with standard text or empty string
    // The weird chars look like ðŸŒ , ðŸ“ , ðŸ—‘ï¸, etc.
    // They are UTF-8 mojibake. Let's just remove them manually using regex.
    $content = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', $content);
    
    // Now add back the safe emojis using HTML entities or standard text if needed
    // But wait, removing all non-ASCII removes all emojis. That's fine, we can put standard text.
    // Let's check what it looks like after removing non-ASCII.
    
    file_put_contents($file, $content);
}
echo "Done";
