<?php
$file = 'app/Http/Controllers/AdminController.php';
$content = file_get_contents($file);

$oldLogic = '    public function tournamentPreview($id)
    {
        $tournament = \App\Models\Tournament::with([\'teams\', \'matches\'])->findOrFail($id);
        $teams = $tournament->teams;
        $matches = $tournament->matches;
        return view(\'admin.tournament_preview\', compact(\'tournament\', \'teams\', \'matches\'));
    }';

$newLogic = '    public function tournamentPreview($id)
    {
        $tournament = \App\Models\Tournament::with([\'teams\', \'matches\'])->findOrFail($id);
        $teams = $tournament->teams;
        $matches = $tournament->matches;
        
        // Mock points table for preview
        $pointsTable = [];
        foreach($teams as $team) {
            $pointsTable[] = (object)[
                \'team_name\' => $team->name,
                \'matches_played\' => 0,
                \'won\' => 0,
                \'lost\' => 0,
                \'points\' => 0,
                \'nrr\' => \'0.00\'
            ];
        }

        return view(\'admin.tournament_preview\', compact(\'tournament\', \'teams\', \'matches\', \'pointsTable\'));
    }';

$content = str_replace($oldLogic, $newLogic, $content);
file_put_contents($file, $content);
echo "AdminController updated";
