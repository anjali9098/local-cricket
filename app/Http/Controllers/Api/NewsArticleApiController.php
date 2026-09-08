<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\News;
use App\Models\Prediction;
use App\Models\FantasyTip;

class NewsArticleApiController extends Controller
{
    // Articles
    public function getArticles()
    {
        return response()->json(['success' => true, 'data' => Article::orderBy('id', 'desc')->get()]);
    }

    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'category' => 'nullable|string',
            'image_url' => 'nullable|string',
            'read_time' => 'nullable|string',
            'published_date' => 'nullable|string'
        ]);

        if (empty($data['published_date'])) $data['published_date'] = date('M d');
        if (empty($data['read_time'])) $data['read_time'] = '3 MIN READ';

        $article = Article::create($data);
        return response()->json(['success' => true, 'message' => 'Article saved to database', 'data' => $article], 201);
    }

    // News
    public function getNews()
    {
        return response()->json(['success' => true, 'data' => News::orderBy('id', 'desc')->get()]);
    }

    public function storeNews(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'category' => 'nullable|string',
            'image_url' => 'nullable|string'
        ]);

        $news = News::create($data);
        return response()->json(['success' => true, 'message' => 'News saved to database', 'data' => $news], 201);
    }

    // Predictions
    public function getPredictions()
    {
        return response()->json(['success' => true, 'data' => Prediction::orderBy('id', 'desc')->get()]);
    }

    public function storePrediction(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'match_title' => 'nullable|string',
            'tag' => 'nullable|string'
        ]);

        $pred = Prediction::create($data);
        return response()->json(['success' => true, 'message' => 'Prediction saved to database', 'data' => $pred], 201);
    }

    // Fantasy Tips
    public function getFantasyTips()
    {
        return response()->json(['success' => true, 'data' => FantasyTip::orderBy('id', 'desc')->get()]);
    }

    public function storeFantasyTip(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'tag' => 'nullable|string'
        ]);

        $tip = FantasyTip::create($data);
        return response()->json(['success' => true, 'message' => 'Fantasy tip saved to database', 'data' => $tip], 201);
    }
}
