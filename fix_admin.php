<?php
// Read lines 1-467 which are clean
$lines = file('c:/xampp/htdocs/score-tracker/score-tracker-laravel/app/Http/Controllers/AdminController.php');
$clean = implode('', array_slice($lines, 0, 467));

// Append the fixed methods
$clean .= '
    public function addArticle(Request $request)
    {
        $title = trim($request->input(\'title\', \'\'));
        if (!empty($title)) {
            $imageUrl = trim($request->input(\'image_url\', \'\'));
            if (empty($imageUrl)) {
                $images = [
                    \'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=600&auto=format&fit=crop&q=80\',
                    \'https://images.unsplash.com/photo-1531415074868-036b1c57e32b?w=600&auto=format&fit=crop&q=80\',
                    \'https://images.unsplash.com/photo-1512719994953-eabf50895df7?w=600&auto=format&fit=crop&q=80\'
                ];
                $imageUrl = $images[array_rand($images)];
            }

            Article::create([
                \'category\' => $request->input(\'category\', \'INTERNATIONAL\'),
                \'title\' => $title,
                \'summary\' => $request->input(\'summary\', \'\'),
                \'image_url\' => $imageUrl,
                \'read_time\' => $request->input(\'read_time\', \'3 MIN READ\'),
                \'published_date\' => date(\'M d\')
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Article created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'Article title is required.\');
    }

    public function addNews(Request $request)
    {
        $title = trim($request->input(\'title\', \'\'));
        if (!empty($title)) {
            News::create([
                \'category\' => $request->input(\'category\', \'CRICKET\'),
                \'title\' => $title,
                \'summary\' => $request->input(\'summary\', \'\'),
                \'image_url\' => $request->input(\'image_url\', \'\')
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Latest news created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'News title is required.\');
    }

    public function addPrediction(Request $request, $id = null)
    {
        $title = trim($request->input(\'title\', \'\'));
        $summary = trim($request->input(\'summary\', \'\'));
        $type = $request->input(\'type\', \'PREDICTION\');

        if (!empty($title)) {
            $matchTitle = \'Upcoming Match\';
            if ($id) {
                $tournament = Tournament::find($id);
                if ($tournament) {
                    $matchTitle = $tournament->short_name . \' Match\';
                }
            }

            if ($type === \'FANTASY\') {
                FantasyTip::create([
                    \'tag\' => \'FANTASY\',
                    \'title\' => $title,
                    \'summary\' => $summary
                ]);
            } else {
                Prediction::create([
                    \'tag\' => \'PREDICTION\',
                    \'match_title\' => $matchTitle,
                    \'title\' => $title,
                    \'summary\' => $summary
                ]);
            }

            return back()->with(\'success\', "$type created successfully!");
        }

        return back()->with(\'error\', \'Title is required.\');
    }

    public function addFantasyTip(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);
        
        $request->validate([
            \'title\' => \'required\',
            \'summary\' => \'required\'
        ]);

        FantasyTip::create([
            \'title\' => $request->title,
            \'summary\' => $request->summary,
            \'tag\' => \'FANTASY\'
        ]);

        return back()->with(\'success\', \'Fantasy tip added successfully!\');
    }

    public function addSeries(Request $request)
    {
        $name = trim($request->input(\'name\', \'\'));
        if (!empty($name)) {
            $short = strtoupper(substr(preg_replace(\'/[^A-Za-z0-9]/\', \'\', $name), 0, 6));
            Tournament::create([
                \'user_id\' => 1,
                \'name\' => $name,
                \'short_name\' => $short,
                \'format\' => $request->input(\'format\', \'T20\'),
                \'series_type\' => $request->input(\'series_type\', \'LOCAL\'),
                \'category\' => \'local\',
                \'city\' => $request->input(\'city\', \'Mumbai\'),
                \'year\' => \'2026\',
                \'status\' => $request->input(\'status\', \'ongoing\'),
                \'views_count\' => 100
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Series created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'Series name is required.\');
    }

    public function addTeamRanking(Request $request)
    {
        $teamName = trim($request->input(\'team_name\', \'\'));
        if (!empty($teamName)) {
            TeamRanking::create([
                \'rank_num\' => (int)$request->input(\'rank_num\', 1),
                \'team_name\' => $teamName,
                \'matches_played\' => (int)$request->input(\'matches_played\', 0),
                \'won\' => (int)$request->input(\'won\', 0),
                \'nrr\' => $request->input(\'nrr\', \'0.00\'),
                \'points\' => (int)$request->input(\'points\', 0)
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Team Ranking created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'Team name is required.\');
    }

    public function addPopularTeam(Request $request)
    {
        $name = trim($request->input(\'name\', \'\'));
        if (!empty($name)) {
            $short = strtoupper(substr(preg_replace(\'/[^A-Za-z0-9]/\', \'\', $name), 0, 4));
            Team::create([
                \'name\' => $name,
                \'short_name\' => $short,
                \'city\' => $request->input(\'city\', \'India\'),
                \'country\' => \'India\',
                \'color_code\' => $request->input(\'color_code\', \'#2563eb\'),
                \'team_type\' => \'international\'
            ]);

            return redirect()->route(\'admin.dashboard\')->with(\'success\', \'Popular team created successfully!\');
        }

        return redirect()->route(\'admin.dashboard\')->with(\'error\', \'Team name is required.\');
    }

    public function publishTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->status = \'published\';
        $tournament->save();
        return back()->with(\'success\', \'Tournament published successfully!\');
    }

    public function markOngoing($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->status = \'ongoing\';
        $tournament->save();
        return back()->with(\'success\', \'Tournament marked as ongoing!\');
    }

    public function markCompleted($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->status = \'completed\';
        $tournament->save();
        return back()->with(\'success\', \'Tournament marked as completed!\');
    }

    public function scorer($id)
    {
        $match = CricketMatch::with([\'team1\', \'team2\', \'tournament\'])->findOrFail($id);
        $isLocal = false;
        
        $lastBalls = \App\Models\BallByBall::where(\'match_id\', $id)
            ->orderBy(\'id\', \'desc\')
            ->take(12)
            ->get();
            
        return view(\'admin.scorer\', compact(\'match\', \'isLocal\', \'lastBalls\'));
    }

    public function deleteTeam($id)
    {
        $team = \App\Models\Team::findOrFail($id);
        $team->delete();
        return back()->with(\'success\', \'Team deleted successfully.\');
    }

    public function deletePlayer($id)
    {
        $player = \App\Models\Player::findOrFail($id);
        $player->delete();
        return back()->with(\'success\', \'Player deleted successfully.\');
    }

    public function matchDetail($id)
    {
        $match = \App\Models\CricketMatch::with([\'team1\', \'team2\', \'tournament\'])->findOrFail($id);
        $balls = \App\Models\BallByBall::where(\'match_id\', $id)->orderBy(\'created_at\', \'desc\')->get();
        return view(\'admin.match_detail\', compact(\'match\', \'balls\'));
    }

    public function tournamentPreview($id)
    {
        $tournament = \App\Models\Tournament::with([\'teams\', \'matches.team1\', \'matches.team2\'])->findOrFail($id);
        $teams = $tournament->teams;
        $matches = $tournament->matches;

        $pointsTable = [];
        foreach ($teams as $team) {
            $pointsTable[$team->id] = [
                \'team\' => $team,
                \'p\'    => 0,
                \'w\'    => 0,
                \'l\'    => 0,
                \'pts\'  => 0,
                \'nrr\'  => \'0.00\',
            ];
        }

        foreach ($matches as $match) {
            if ($match->status === \'completed\') {
                if (isset($pointsTable[$match->team1_id])) {
                    $pointsTable[$match->team1_id][\'p\']++;
                    if ($match->team1_score > $match->team2_score) {
                        $pointsTable[$match->team1_id][\'w\']++;
                        $pointsTable[$match->team1_id][\'pts\'] += 2;
                    } else {
                        $pointsTable[$match->team1_id][\'l\']++;
                    }
                }
                if (isset($pointsTable[$match->team2_id])) {
                    $pointsTable[$match->team2_id][\'p\']++;
                    if ($match->team2_score > $match->team1_score) {
                        $pointsTable[$match->team2_id][\'w\']++;
                        $pointsTable[$match->team2_id][\'pts\'] += 2;
                    } else {
                        $pointsTable[$match->team2_id][\'l\']++;
                    }
                }
            }
        }

        usort($pointsTable, fn($a, $b) => $b[\'pts\'] <=> $a[\'pts\']);

        return view(\'admin.tournament_preview\', compact(\'tournament\', \'teams\', \'matches\', \'pointsTable\'));
    }

    public function deleteMatch($id)
    {
        $match = CricketMatch::findOrFail($id);
        $match->delete();
        return back()->with(\'success\', \'Match deleted successfully.\');
    }
}
';

file_put_contents('c:/xampp/htdocs/score-tracker/score-tracker-laravel/app/Http/Controllers/AdminController.php', $clean);
echo "Done - file rebuilt successfully";
