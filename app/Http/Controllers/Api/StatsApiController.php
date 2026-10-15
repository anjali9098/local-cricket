<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamRanking;
use App\Models\IccPlayerRanking;
use App\Models\WebStory;
use App\Models\GlossaryTerm;

class StatsApiController extends Controller
{
    // Team Rankings
    public function getTeamRankings()
    {
        return response()->json(['success' => true, 'data' => TeamRanking::orderBy('rank_num', 'asc')->get()]);
    }

    public function storeTeamRanking(Request $request)
    {
        $data = $request->validate([
            'rank_num' => 'required|integer',
            'team_name' => 'required|string',
            'matches_played' => 'nullable|integer',
            'won' => 'nullable|integer',
            'nrr' => 'nullable|string',
            'points' => 'nullable|integer',
            'category' => 'nullable|string'
        ]);

        $item = TeamRanking::create($data);
        return response()->json(['success' => true, 'message' => 'Team ranking saved to database', 'data' => $item], 201);
    }

    // Player Rankings
    public function getPlayerRankings(Request $request)
    {
        $type = $request->query('type'); // batting or bowling
        $query = IccPlayerRanking::query();
        if ($type) $query->where('category', $type);

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function storePlayerRanking(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'player_name' => 'required|string',
            'country' => 'nullable|string',
            'format' => 'nullable|string',
            'current_rank' => 'nullable|string',
            'best_rank' => 'nullable|string'
        ]);

        $item = IccPlayerRanking::create($data);
        return response()->json(['success' => true, 'message' => 'Player ranking saved to database', 'data' => $item], 201);
    }

    // Points Table (Calculated dynamically)
    public function getPointsTable()
    {
        $matches = \App\Models\CricketMatch::whereIn('status', ['completed', 'finished'])->get();
        return response()->json(['success' => true, 'data' => []]);
    }

    // Web Stories
    public function getWebStories()
    {
        return response()->json(['success' => true, 'data' => WebStory::orderBy('id', 'desc')->get()]);
    }

    public function storeWebStory(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'image_url' => 'required|string',
            'tag' => 'nullable|string'
        ]);

        $item = WebStory::create($data);
        return response()->json(['success' => true, 'message' => 'Web story saved to database', 'data' => $item], 201);
    }

    // Glossary
    public function getGlossary()
    {
        return response()->json(['success' => true, 'data' => GlossaryTerm::orderBy('id', 'asc')->get()]);
    }

    public function storeGlossary(Request $request)
    {
        $data = $request->validate([
            'letter' => 'required|string|max:5',
            'term' => 'required|string|max:100',
            'definition' => 'required|string'
        ]);

        $item = GlossaryTerm::create($data);
        return response()->json(['success' => true, 'message' => 'Glossary term saved to database', 'data' => $item], 201);
    }
}
