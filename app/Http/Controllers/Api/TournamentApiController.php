<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tournament;

class TournamentApiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $category = $request->query('category');
        $query = Tournament::orderBy('id', 'desc');

        if ($status) $query->where('status', $status);
        if ($category) $query->where('category', $category);

        return response()->json([
            'success' => true,
            'count' => $query->count(),
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $tournament = Tournament::find($id);
        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $tournament]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:20',
            'user_id' => 'nullable|integer',
            'format' => 'nullable|string',
            'series_type' => 'nullable|string',
            'category' => 'nullable|string',
            'city' => 'nullable|string',
            'year' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        if (empty($data['short_name'])) {
            $data['short_name'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 6));
        }

        $tournament = Tournament::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tournament saved to MySQL database successfully!',
            'data' => $tournament
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $tournament = Tournament::find($id);
        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        $tournament->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tournament updated successfully!',
            'data' => $tournament
        ]);
    }

    public function destroy($id)
    {
        $tournament = Tournament::find($id);
        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        $tournament->delete();

        return response()->json(['success' => true, 'message' => 'Tournament deleted successfully']);
    }
}
