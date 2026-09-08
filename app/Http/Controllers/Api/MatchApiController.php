<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CricketMatch;

class MatchApiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = CricketMatch::with(['team1', 'team2', 'venue'])->orderBy('id', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'count' => $query->count(),
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'venue'])->find($id);
        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $match
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'team1_id' => 'required|integer',
            'team2_id' => 'required|integer',
            'venue_id' => 'nullable|integer',
            'match_type' => 'nullable|string',
            'level_type' => 'nullable|string',
            'status' => 'nullable|string',
            'match_date' => 'nullable',
            'team1_score' => 'nullable|integer',
            'team1_wickets' => 'nullable|integer',
            'team1_overs' => 'nullable|numeric',
            'team2_score' => 'nullable|integer',
            'team2_wickets' => 'nullable|integer',
            'team2_overs' => 'nullable|numeric',
            'custom_note' => 'nullable|string'
        ]);

        if (empty($data['match_date'])) {
            $data['match_date'] = now();
        }

        $match = CricketMatch::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Match created successfully in database!',
            'data' => $match
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $match = CricketMatch::find($id);
        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        $match->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Match updated successfully in database!',
            'data' => $match
        ]);
    }

    public function scoreUpdate(Request $request, $id)
    {
        $match = CricketMatch::find($id);
        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        $runs = (int)$request->input('runs', 0);
        $isWicket = (bool)$request->input('is_wicket', false);
        $team = $request->input('team', 1); // 1 or 2

        if ($team == 1) {
            $match->team1_score += $runs;
            if ($isWicket) $match->team1_wickets += 1;
            
            $newOvers = (float)$match->team1_overs + 0.1;
            if (round(($newOvers - floor($newOvers)), 1) >= 0.6) {
                $newOvers = floor($newOvers) + 1.0;
            }
            $match->team1_overs = $newOvers;
        } else {
            $match->team2_score += $runs;
            if ($isWicket) $match->team2_wickets += 1;

            $newOvers = (float)$match->team2_overs + 0.1;
            if (round(($newOvers - floor($newOvers)), 1) >= 0.6) {
                $newOvers = floor($newOvers) + 1.0;
            }
            $match->team2_overs = $newOvers;
        }

        $match->status = 'live';
        if ($request->has('custom_note')) {
            $match->custom_note = $request->input('custom_note');
        }
        $match->save();

        return response()->json([
            'success' => true,
            'message' => "Score recorded: +$runs runs" . ($isWicket ? ' (WICKET!)' : ''),
            'data' => $match
        ]);
    }

    public function destroy($id)
    {
        $match = CricketMatch::find($id);
        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        $match->delete();

        return response()->json([
            'success' => true,
            'message' => 'Match deleted successfully from database'
        ]);
    }

    public function getLiveMatches()
    {
        $matches = CricketMatch::with(['team1', 'team2', 'venue'])
            ->where('status', 'live')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $matches->count(),
            'data' => $matches
        ]);
    }

    public function getUpcomingMatches()
    {
        $matches = CricketMatch::with(['team1', 'team2', 'venue'])
            ->where('status', 'scheduled')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $matches->count(),
            'data' => $matches
        ]);
    }

    public function getMatchStats($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'venue'])->find($id);
        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        $matchService = new \App\Services\MatchService();
        $stats = $matchService->getCalculatedStats($match);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
