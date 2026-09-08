<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$adminRoutes = "Route::get('/admin/match/{id}', [AdminController::class, 'matchDetail'])->name('admin.match.detail');\n    Route::get('/admin/tournament/{id}/preview', [AdminController::class, 'tournamentPreview'])->name('admin.tournament.preview');\n";
$content = preg_replace("/Route::post\('\/admin\/team\/\{id\}\/delete'.*?;/s", $adminRoutes . "$0", $content);

file_put_contents($file, $content);

$adminFile = 'app/Http/Controllers/AdminController.php';
$adminContent = file_get_contents($adminFile);
$adminLogic = '
    public function matchDetail($id)
    {
        $match = \App\Models\CricketMatch::with([\'team1\', \'team2\', \'tournament\'])->findOrFail($id);
        $balls = \App\Models\BallByBall::where(\'match_id\', $id)->orderBy(\'created_at\', \'desc\')->get();
        // Return same view as public but inside admin folder so it extends admin layout
        return view(\'admin.match_detail\', compact(\'match\', \'balls\'));
    }

    public function tournamentPreview($id)
    {
        $tournament = \App\Models\Tournament::with([\'teams\', \'matches\'])->findOrFail($id);
        $teams = $tournament->teams;
        $matches = $tournament->matches;
        return view(\'admin.tournament_preview\', compact(\'tournament\', \'teams\', \'matches\'));
    }
}';
$adminContent = preg_replace('/\}\s*$/', $adminLogic, $adminContent);
file_put_contents($adminFile, $adminContent);
echo "Routes and Controller updated";
