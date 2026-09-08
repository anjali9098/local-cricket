<?php
$file = 'resources/views/admin/scorer.blade.php';
$content = file_get_contents($file);
$content = str_replace("@extends('layouts.app')", "@extends(isset(\$isLocal) && \$isLocal ? 'layouts.local' : 'layouts.admin')", $content);
file_put_contents($file, $content);

$adminFile = 'app/Http/Controllers/AdminController.php';
$adminContent = file_get_contents($adminFile);
$oldAdminLogic = '
    public function updateScore(Request $request)
    {
        // Dummy logic to prevent error for now. Later this will update the Match score.
        return back()->with(\'success\', \'Score updated via Live Scorer Console!\');
    }';
$newAdminLogic = '
    public function updateScore(Request $request)
    {
        $matchId = (int)$request->input(\'match_id\', 1);
        $runs = (int)$request->input(\'runs\', 0);
        $isWicket = (bool)$request->input(\'is_wicket\', false);

        $match = \App\Models\CricketMatch::find($matchId);
        if ($match) {
            $match->team1_score += $runs;
            if ($isWicket) {
                $match->team1_wickets += 1;
            }
            
            $newOvers = (float)$match->team1_overs + 0.1;
            if (round(($newOvers - floor($newOvers)), 1) >= 0.6) {
                $newOvers = floor($newOvers) + 1.0;
            }
            $match->team1_overs = $newOvers;
            $match->status = \'live\';
            $match->save();

            return back()->with(\'success\', "Ball recorded: +$runs runs " . ($isWicket ? \'(WICKET!)\' : \'\'));
        }
        return back()->with(\'error\', \'Match not found.\');
    }';
$adminContent = str_replace($oldAdminLogic, $newAdminLogic, $adminContent);
file_put_contents($adminFile, $adminContent);

$localFile = 'app/Http/Controllers/LocalController.php';
$localContent = file_get_contents($localFile);
$localContent = preg_replace('/return redirect\(\)->route\(\'local\.dashboard\'\)->with\(\'success\'/', 'return back()->with(\'success\'', $localContent);
file_put_contents($localFile, $localContent);

echo "Done";
