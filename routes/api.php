<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MatchApiController;
use App\Http\Controllers\Api\TournamentApiController;
use App\Http\Controllers\Api\TeamApiController;
use App\Http\Controllers\Api\PlayerApiController;
use App\Http\Controllers\Api\NewsArticleApiController;
use App\Http\Controllers\Api\StatsApiController;

/*
|--------------------------------------------------------------------------
| CricketKaScore & CrickArena RESTful API Routes
|--------------------------------------------------------------------------
| All data sent to these endpoints is validated and stored dynamically in MySQL database (score_tracker)
| and immediately displayed live across all website pages.
*/

// 1. Matches & Live Scoring API
Route::get('/matches', [MatchApiController::class, 'index']);
Route::get('/matches/live', [MatchApiController::class, 'getLiveMatches']);
Route::get('/matches/upcoming', [MatchApiController::class, 'getUpcomingMatches']);
Route::get('/matches/{id}', [MatchApiController::class, 'show']);
Route::get('/matches/{id}/stats', [MatchApiController::class, 'getMatchStats']);
Route::post('/matches', [MatchApiController::class, 'store']);
Route::put('/matches/{id}', [MatchApiController::class, 'update']);
Route::post('/matches/{id}/score', [MatchApiController::class, 'scoreUpdate']);
Route::delete('/matches/{id}', [MatchApiController::class, 'destroy']);

// 2. Tournaments & Series API
Route::get('/tournaments', [TournamentApiController::class, 'index']);
Route::get('/tournaments/{id}', [TournamentApiController::class, 'show']);
Route::post('/tournaments', [TournamentApiController::class, 'store']);
Route::put('/tournaments/{id}', [TournamentApiController::class, 'update']);
Route::delete('/tournaments/{id}', [TournamentApiController::class, 'destroy']);

// 3. Teams API
Route::get('/teams', [TeamApiController::class, 'index']);
Route::get('/teams/{id}', [TeamApiController::class, 'show']);
Route::post('/teams', [TeamApiController::class, 'store']);
Route::put('/teams/{id}', [TeamApiController::class, 'update']);
Route::delete('/teams/{id}', [TeamApiController::class, 'destroy']);

// 4. Players API
Route::get('/players', [PlayerApiController::class, 'index']);
Route::get('/players/{id}', [PlayerApiController::class, 'show']);
Route::post('/players', [PlayerApiController::class, 'store']);
Route::put('/players/{id}', [PlayerApiController::class, 'update']);
Route::delete('/players/{id}', [PlayerApiController::class, 'destroy']);

// 5. Articles, News, Predictions & Fantasy Tips API
Route::get('/articles', [NewsArticleApiController::class, 'getArticles']);
Route::post('/articles', [NewsArticleApiController::class, 'storeArticle']);

Route::get('/news', [NewsArticleApiController::class, 'getNews']);
Route::post('/news', [NewsArticleApiController::class, 'storeNews']);

Route::get('/predictions', [NewsArticleApiController::class, 'getPredictions']);
Route::post('/predictions', [NewsArticleApiController::class, 'storePrediction']);

Route::get('/fantasy-tips', [NewsArticleApiController::class, 'getFantasyTips']);
Route::post('/fantasy-tips', [NewsArticleApiController::class, 'storeFantasyTip']);

// 6. Rankings, Points Table, Stories & Glossary API
Route::get('/rankings/teams', [StatsApiController::class, 'getTeamRankings']);
Route::post('/rankings/teams', [StatsApiController::class, 'storeTeamRanking']);

Route::get('/rankings/players', [StatsApiController::class, 'getPlayerRankings']);
Route::post('/rankings/players', [StatsApiController::class, 'storePlayerRanking']);

Route::get('/points-table', [StatsApiController::class, 'getPointsTable']);
Route::post('/points-table', [StatsApiController::class, 'storePointsTable']);

Route::get('/web-stories', [StatsApiController::class, 'getWebStories']);
Route::post('/web-stories', [StatsApiController::class, 'storeWebStory']);

Route::get('/glossary', [StatsApiController::class, 'getGlossary']);
Route::post('/glossary', [StatsApiController::class, 'storeGlossary']);

// 7. Real-Time Global Search API & Common Super Admin Search API
use App\Http\Controllers\Api\SearchApiController;
Route::get('/search', [SearchApiController::class, 'search']);
Route::get('/admin/search', [SearchApiController::class, 'adminSearch']);

// 8. Possible11 Comprehensive Cricket API Endpoints
Route::prefix('possible11')->group(function () {
    // 1. Series List (Live, Upcoming, Completed)
    Route::get('/series', function (\Illuminate\Http\Request $request, \App\Services\Possible11ApiService $service) {
        $status = $request->input('status', 'live');
        $sport = $request->input('sport', 'Cricket');
        $limit = (int) $request->input('limit', 20);
        $page = (int) $request->input('page', 0);
        return response()->json([
            'status' => 'OK',
            'data' => $service->getSeriesList($status, $sport, $limit, $page)
        ]);
    });

    // 2. Full Details of any Series
    Route::get('/series-detail', function (\Illuminate\Http\Request $request, \App\Services\Possible11ApiService $service) {
        $id = (int) $request->input('id', 0);
        return response()->json([
            'status' => 'OK',
            'data' => $service->getSeriesDetail($id)
        ]);
    });

    // 3. All Teams of any Series
    Route::get('/series-teams', function (\Illuminate\Http\Request $request, \App\Services\Possible11ApiService $service) {
        $id = (int) $request->input('id', 0);
        return response()->json([
            'status' => 'OK',
            'teams' => $service->getSeriesTeams($id)
        ]);
    });

    // 4. Squad of any Team from Series
    Route::get('/series-squad', function (\Illuminate\Http\Request $request, \App\Services\Possible11ApiService $service) {
        $id = (int) $request->input('id', 0);
        $teamId = (int) $request->input('teamId', 0);
        $formatId = (int) ($request->input('formatId', $request->input('$formatId', 2)));
        return response()->json([
            'status' => 'OK',
            'data' => $service->getSeriesSquad($id, $teamId, $formatId)
        ]);
    });

    // 5. Automated Deep Sync (Series + Teams + Matches + Squads)
    Route::match(['get', 'post'], '/sync-series', function (\Illuminate\Http\Request $request, \App\Services\Possible11ApiService $service) {
        $seriesId = $request->input('id', $request->input('series_id'));
        $syncSquads = $request->boolean('sync_squads', false);

        if (!empty($seriesId)) {
            $result = $service->syncSingleSeries((int)$seriesId, $syncSquads);
            return response()->json($result);
        }

        $status = $request->input('status', 'live');
        $sport = $request->input('sport', 'Cricket');
        $limit = (int) $request->input('limit', 50);
        $page = (int) $request->input('page', 0);
        $result = $service->syncSeries($status, $syncSquads, $sport, $limit, $page);
        return response()->json($result);
    });
});



