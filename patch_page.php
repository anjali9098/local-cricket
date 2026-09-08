<?php
$file = 'app/Http/Controllers/PageController.php';
$content = file_get_contents($file);

$append = <<<EOT

    public function tournamentDetail(\$id)
    {
        \$tournament = \App\Models\Tournament::with(['teams', 'matches.team1', 'matches.team2'])->findOrFail(\$id);
        
        // Calculate points table
        \$pointsTable = [];
        foreach (\$tournament->teams as \$team) {
            \$pointsTable[\$team->id] = [
                'team' => \$team,
                'p' => 0, 'w' => 0, 'l' => 0, 'pts' => 0, 'nrr' => '0.00'
            ];
        }
        
        foreach (\$tournament->matches as \$match) {
            if (\$match->status === 'completed') {
                if (isset(\$pointsTable[\$match->team1_id])) \$pointsTable[\$match->team1_id]['p']++;
                if (isset(\$pointsTable[\$match->team2_id])) \$pointsTable[\$match->team2_id]['p']++;
                
                // Simple logic: whoever has more runs wins (if implemented fully, handle wickets/overs)
                if (\$match->team1_score > \$match->team2_score) {
                    if (isset(\$pointsTable[\$match->team1_id])) { \$pointsTable[\$match->team1_id]['w']++; \$pointsTable[\$match->team1_id]['pts'] += 2; }
                    if (isset(\$pointsTable[\$match->team2_id])) { \$pointsTable[\$match->team2_id]['l']++; }
                } elseif (\$match->team2_score > \$match->team1_score) {
                    if (isset(\$pointsTable[\$match->team2_id])) { \$pointsTable[\$match->team2_id]['w']++; \$pointsTable[\$match->team2_id]['pts'] += 2; }
                    if (isset(\$pointsTable[\$match->team1_id])) { \$pointsTable[\$match->team1_id]['l']++; }
                }
            }
        }
        
        usort(\$pointsTable, function(\$a, \$b) {
            return \$b['pts'] <=> \$a['pts'];
        });

        return view('pages.tournament_public', compact('tournament', 'pointsTable'));
    }
}
EOT;

$content = preg_replace('/}\s*$/', $append, $content);
file_put_contents($file, $content);
echo "Done";
