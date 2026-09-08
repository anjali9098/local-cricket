<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;

class TeamApiController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('team_type');
        $query = Team::orderBy('id', 'desc');
        if ($type) $query->where('team_type', $type);

        return response()->json([
            'success' => true,
            'count' => $query->count(),
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['success' => false, 'message' => 'Team not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $team]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:20',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'color_code' => 'nullable|string',
            'team_type' => 'nullable|string'
        ]);

        $team = Team::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Team added to database successfully!',
            'data' => $team
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['success' => false, 'message' => 'Team not found'], 404);
        }

        $team->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully!',
            'data' => $team
        ]);
    }

    public function destroy($id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['success' => false, 'message' => 'Team not found'], 404);
        }

        $team->delete();
        return response()->json(['success' => true, 'message' => 'Team deleted successfully']);
    }
}
