<?php
// Update AdminController and LocalController publish method
$files = ['app/Http/Controllers/AdminController.php', 'app/Http/Controllers/LocalController.php'];
foreach($files as $file) {
    $content = file_get_contents($file);
    $content = preg_replace("/\\\$tournament->status = 'ongoing';.*?return redirect\(\)->route\('tournament\.public', \\\$id\)->with\('success', 'Tournament published!'\);/s", "\$tournament->status = 'published';\n        \$tournament->save();\n        return back()->with('success', 'Tournament published successfully!');", $content);
    file_put_contents($file, $content);
}

// Update PageController to block draft tournaments
$pageFile = 'app/Http/Controllers/PageController.php';
$pageContent = file_get_contents($pageFile);
$blockCode = '
    public function tournamentDetail($id)
    {
        $tournament = \App\Models\Tournament::with([\'teams\', \'matches.team1\', \'matches.team2\'])->findOrFail($id);
        
        if ($tournament->status === \'draft\') {
            abort(404, \'Tournament is not published yet.\');
        }
';
$pageContent = preg_replace("/\s*public function tournamentDetail\(\\$id\)\s*\{\s*\\\$tournament = \\\App\\\Models\\\Tournament::with\(\['teams', 'matches.team1', 'matches.team2'\]\)->findOrFail\(\\$id\);\s*/s", $blockCode, $pageContent);
file_put_contents($pageFile, $pageContent);

echo "Controllers fixed!";
