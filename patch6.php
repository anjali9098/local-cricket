<?php
foreach(['admin', 'local'] as $role) {
    $file = "resources/views/$role/manage-tournament.blade.php";
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);

    $route = $role . '.scorer';
    $search = '/<button type="button" onclick="openScorerModal.*?<\/button>/s';
    $replace = '<a href="{{ route(\''.$route.'\', $match->id) }}" style="background: var(--primary); color: var(--bg-main); font-weight: 800; font-size: 0.85rem; padding: 6px 14px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                            <span>((LIVE))</span> Score
                        </a>';
    
    $content = preg_replace($search, $replace, $content);
    file_put_contents($file, $content);
}
echo "Done";
