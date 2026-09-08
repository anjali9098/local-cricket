<?php
foreach(['admin', 'local'] as $role) {
    $file = "resources/views/$role/manage-tournament.blade.php";
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Replace the score button
    $search = '/<button type="button" onclick="openScorerModal\(\{\{ \$match->id \}\}, \'\{\{ \$match->team1->name \?\? \'\' \}\} vs \{\{ \$match->team2->name \?\? \'\' \}\}\'\)" style="background: var\(--primary\); color: var\(--bg-main\); font-weight: 800; font-size: 0.85rem; padding: 6px 14px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;">\s*<span>\(\(??\)\)<\/span> Score\s*<\/button>/u';
    
    $route = $role . '.scorer';
    $replace = '<a href="{{ route(\''.$route.'\', $match->id) }}" style="background: var(--primary); color: var(--bg-main); font-weight: 800; font-size: 0.85rem; padding: 6px 14px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                            <span>((??))</span> Score
                        </a>';
    
    $content = preg_replace($search, $replace, $content);

    // Remove the modal and script
    $modalRegex = '/<!-- Live Scorer Console Modal -->.*<\/script>/is';
    $content = preg_replace($modalRegex, '', $content);
    
    file_put_contents($file, $content);
}
echo "Done";
