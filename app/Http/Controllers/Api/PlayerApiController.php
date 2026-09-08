<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;

class PlayerApiController extends Controller
{
    public function index(Request $request)
    {
        $teamId = $request->query('team_id');
        $query = Player::orderBy('id', 'desc');
        if ($teamId) $query->where('team_id', $teamId);

        return response()->json([
            'success' => true,
            'count' => $query->count(),
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $player = Player::find($id);
        if (!$player) {
            return response()->json(['success' => false, 'message' => 'Player not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $player]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'team_id' => 'nullable|integer',
            'role' => 'nullable|string',
            'batting_style' => 'nullable|string',
            'bowling_style' => 'nullable|string',
            'initials' => 'nullable|string',
            'is_popular' => 'nullable|boolean'
        ]);

        if (empty($data['initials'])) {
            $data['initials'] = strtoupper(substr($data['name'], 0, 3));
        }

        $player = Player::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Player created in database successfully!',
            'data' => $player
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $player = Player::find($id);
        if (!$player) {
            return response()->json(['success' => false, 'message' => 'Player not found'], 404);
        }

        $player->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Player updated successfully!',
            'data' => $player
        ]);
    }

    public function destroy($id)
    {
        $player = Player::find($id);
        if (!$player) {
            return response()->json(['success' => false, 'message' => 'Player not found'], 404);
        }

        $player->delete();
        return response()->json(['success' => true, 'message' => 'Player deleted successfully']);
    }
}
