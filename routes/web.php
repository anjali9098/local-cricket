<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SitemapController;

// =========================================================================
// XML Sitemaps (Google Search Console & SEO Standard - Matching Possible11 Structure)
// =========================================================================
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap_index.xml', [SitemapController::class, 'index']);
Route::get('/sitemap/pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap/series.xml', [SitemapController::class, 'series'])->name('sitemap.series');
Route::get('/sitemap/live-score.xml', [SitemapController::class, 'matches'])->name('sitemap.live-score');
Route::get('/sitemap/matches.xml', [SitemapController::class, 'matches'])->name('sitemap.matches');
Route::get('/sitemap/cricket-players.xml', [SitemapController::class, 'players'])->name('sitemap.cricket-players');
Route::get('/sitemap/players.xml', [SitemapController::class, 'players'])->name('sitemap.players');
Route::get('/sitemap/team.xml', [SitemapController::class, 'teams'])->name('sitemap.team');
Route::get('/sitemap/teams.xml', [SitemapController::class, 'teams']);
Route::get('/sitemap/news.xml', [SitemapController::class, 'news'])->name('sitemap.news');
Route::get('/news-sitemap.xml', [SitemapController::class, 'news']);
Route::get('/sitemap/articles.xml', [SitemapController::class, 'articles'])->name('sitemap.articles');
Route::get('/sitemap/prediction.xml', [SitemapController::class, 'predictions'])->name('sitemap.prediction');
Route::get('/sitemap/predictions.xml', [SitemapController::class, 'predictions']);
Route::get('/sitemap/fantasy.xml', [SitemapController::class, 'fantasy'])->name('sitemap.fantasy');
Route::get('/sitemap/fantasy-tips.xml', [SitemapController::class, 'fantasy']);
Route::get('/sitemap/ground.xml', [SitemapController::class, 'venues'])->name('sitemap.ground');
Route::get('/sitemap/venues.xml', [SitemapController::class, 'venues']);
Route::get('/sitemap/local-cricket.xml', [SitemapController::class, 'localCricket'])->name('sitemap.local-cricket');
Route::get('/sitemap/local.xml', [SitemapController::class, 'localCricket'])->name('sitemap.local');
Route::get('/sitemap/web-stories.xml', [SitemapController::class, 'webStories'])->name('sitemap.webstories');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'csrf_token' => csrf_token(),
        'time' => time(),
    ]);
})->name('ping');

// 2. Dedicated Navigation Pages (Each with its own page & route)
Route::get('/live', [PageController::class, 'live'])->name('live');
Route::get('/matches', [PageController::class, 'matches'])->name('matches');
Route::get('/matches/{slug}/{id}', [PageController::class, 'matchDetail'])->name('matches.detail.slug');
Route::get('/matches/{id}', [PageController::class, 'matchDetail'])->name('matches.detail');
Route::get('/match/{slug}/{id}', [PageController::class, 'matchDetail']);
Route::get('/match/{id}', [PageController::class, 'matchDetail']);

Route::get('/stats', [PageController::class, 'stats'])->name('stats');
Route::get('/series', [PageController::class, 'series'])->name('series');
Route::get('/tournaments', [PageController::class, 'tournaments'])->name('tournaments');
Route::get('/tournament/{slug}/{id}', [PageController::class, 'tournamentDetail'])->name('tournament.public.slug');
Route::get('/tournament/{id}', [PageController::class, 'tournamentDetail'])->name('tournament.public');
Route::get('/series/{slug}/{id}', [PageController::class, 'tournamentDetail'])->name('series.public.slug');
Route::get('/series/{id}', [PageController::class, 'tournamentDetail'])->name('series.public');
Route::get('/series/{slug}/{id}/stats/{stat}', [PageController::class, 'seriesStatDetail'])->name('series.stat.slug');
Route::get('/series/{id}/stats/{stat}', [PageController::class, 'seriesStatDetail'])->name('series.stat');
Route::get('/tournament/{slug}/{id}/stats/{stat}', [PageController::class, 'seriesStatDetail'])->name('tournament.stat.slug');
Route::get('/tournament/{id}/stats/{stat}', [PageController::class, 'seriesStatDetail'])->name('tournament.stat');
Route::get('/t/{slug}/{id}/stats/{stat}', [PageController::class, 'seriesStatDetail']);
Route::get('/t/{id}/stats/{stat}', [PageController::class, 'seriesStatDetail']);
Route::get('/t/{slug}/{id}', [PageController::class, 'tournamentDetail']);
Route::get('/t/{id}', [PageController::class, 'tournamentDetail']);

Route::get('/news', [PageController::class, 'news'])->name('news');
Route::get('/news/{slug}/{id}', [PageController::class, 'showNews'])->name('news.show.slug');
Route::get('/news/{id}', [PageController::class, 'showNews'])->name('news.show');

Route::get('/predictions', [PageController::class, 'predictions'])->name('predictions');
Route::get('/prediction', [PageController::class, 'predictions']);
Route::get('/prediction/{slug}/{id}', [PageController::class, 'showPrediction'])->name('prediction.show.slug');
Route::get('/prediction/{id}', [PageController::class, 'showPrediction'])->name('prediction.show');
Route::get('/predictions/{slug}/{id}', [PageController::class, 'showPrediction']);
Route::get('/predictions/{id}', [PageController::class, 'showPrediction']);

Route::get('/fantasy', [PageController::class, 'fantasyTips'])->name('fantasy');
Route::get('/fantasy-tips', [PageController::class, 'fantasyTips'])->name('fantasy.tips');
Route::get('/fantasy/{slug}/{id}', [PageController::class, 'showFantasyTip'])->name('fantasy.show.slug');
Route::get('/fantasy/{id}', [PageController::class, 'showFantasyTip'])->name('fantasy.show');
Route::get('/fantasy-tips/{slug}/{id}', [PageController::class, 'showFantasyTip'])->name('fantasy.tip.show');
Route::get('/fantasy-tips/{id}', [PageController::class, 'showFantasyTip']);

Route::get('/match-previews', [PageController::class, 'matchPreviews'])->name('previews');
Route::get('/previews', [PageController::class, 'matchPreviews']);
Route::get('/preview/{slug}/{id}', [PageController::class, 'showMatchPreview'])->name('preview.show.slug');
Route::get('/preview/{id}', [PageController::class, 'showMatchPreview'])->name('preview.show');
Route::get('/match-preview/{slug}/{id}', [PageController::class, 'showMatchPreview']);
Route::get('/match-preview/{id}', [PageController::class, 'showMatchPreview']);

Route::get('/articles', [PageController::class, 'articles'])->name('articles');
Route::get('/article/{slug}/{id}', [PageController::class, 'showArticle'])->name('article.show.slug');
Route::get('/article/{id}', [PageController::class, 'showArticle'])->name('article.show');
Route::get('/articles/{slug}/{id}', [PageController::class, 'showArticle']);
Route::get('/articles/{id}', [PageController::class, 'showArticle']);

Route::get('/compare', [PageController::class, 'compare'])->name('compare');
Route::get('/teams', [PageController::class, 'teams'])->name('teams');
Route::get('/players', [PageController::class, 'players'])->name('players');
Route::get('/player/{slug}/{id}', [PageController::class, 'playerProfile'])->name('player.profile.slug');
Route::get('/player/{id}', [PageController::class, 'playerProfile'])->name('player.profile');
Route::get('/players/{slug}/{id}', [PageController::class, 'playerProfile'])->name('players.show.slug');
Route::get('/players/{id}', [PageController::class, 'playerProfile'])->name('players.show');
Route::get('/p/{slug}/{id}', [PageController::class, 'playerProfile'])->name('player.show.slug');
Route::get('/p/{id}', [PageController::class, 'playerProfile'])->name('player.show');

Route::get('/venues', [PageController::class, 'venues'])->name('venues');
Route::get('/venue/{slug}/{id}', [PageController::class, 'showVenue'])->name('venue.show.slug');
Route::get('/venue/{id}', [PageController::class, 'showVenue'])->name('venue.show');
Route::get('/venues/{slug}/{id}', [PageController::class, 'showVenue'])->name('venues.show.slug');
Route::get('/venues/{id}', [PageController::class, 'showVenue'])->name('venues.show');

Route::get('/web-stories', [PageController::class, 'webStories'])->name('webstories.all');
Route::get('/web-story', [PageController::class, 'webStories']);
Route::get('/webstory', [PageController::class, 'webStories']);
Route::get('/webstories', [PageController::class, 'webStories']);
Route::get('/web-story/{slug}/{id}', [PageController::class, 'showWebStory'])->name('webstories.show.slug');
Route::get('/web-story/{id}', [PageController::class, 'showWebStory'])->name('webstories.show');
Route::get('/webstory/{slug}/{id}', [PageController::class, 'showWebStory']);
Route::get('/webstory/{id}', [PageController::class, 'showWebStory']);
Route::get('/web-stories/{slug}/{id}', [PageController::class, 'showWebStory']);
Route::get('/web-stories/{id}', [PageController::class, 'showWebStory']);

Route::get('/glossary', [PageController::class, 'glossary'])->name('glossary.all');
Route::get('/glossary-terms', [PageController::class, 'glossary']);
Route::get('/glossary/{slug}/{id}', [PageController::class, 'showGlossaryTerm'])->name('glossary.show.slug');
Route::get('/glossary/{id}', [PageController::class, 'showGlossaryTerm'])->name('glossary.show');
Route::get('/glossary-term/{slug}/{id}', [PageController::class, 'showGlossaryTerm']);
Route::get('/glossary-term/{id}', [PageController::class, 'showGlossaryTerm']);

Route::get('/player-birthdays', [PageController::class, 'playerBirthdays'])->name('player.birthdays');
Route::get('/player-birthday', [PageController::class, 'playerBirthdays']);
Route::get('/birthdays', [PageController::class, 'playerBirthdays']);
Route::get('/rankings', [PageController::class, 'stats']);
Route::get('/search', [PageController::class, 'globalSearch'])->name('search');
Route::get('/api/admin/search', [\App\Http\Controllers\Api\SearchApiController::class, 'adminSearch'])->name('api.admin.search');

// 3. CrickArena Local Cricket Portal (Locked behind login / sign up)
Route::middleware(['localadmin'])->group(function () {
    Route::get('/local', [LocalController::class, 'index'])->name('local.dashboard');
    Route::post('/local/create-tournament', [LocalController::class, 'createTournament'])->name('local.create-tournament');
    Route::get('/local/tournament/{slug}/{id}/manage', [LocalController::class, 'manageTournament'])->name('local.manage-tournament.slug');
    Route::get('/local/tournament/{id}/manage', [LocalController::class, 'manageTournament'])->name('local.manage-tournament');
    Route::post('/local/tournament/{id}/publish', [LocalController::class, 'publishTournament'])->name('local.publish-tournament');
    Route::post('/local/tournament/{id}/add-team', [LocalController::class, 'addTeam'])->name('local.add-team');
    Route::post('/local/tournament/{id}/add-player', [LocalController::class, 'addPlayer'])->name('local.add-player');
    Route::get('/local/match/{slug}/{id}/scorer', [LocalController::class, 'scorer'])->name('local.scorer.slug');
    Route::get('/local/match/{id}/scorer', [LocalController::class, 'scorer'])->name('local.scorer');
    Route::post('/local/tournament/{id}/add-match', [LocalController::class, 'addMatch'])->name('local.add-match');
    Route::post('/local/match/{id}/update-status', [LocalController::class, 'updateMatchStatus'])->name('local.match.update-status');
    Route::post('/local/team/{id}/delete', [LocalController::class, 'deleteTeam'])->name('local.delete-team');
    Route::post('/local/player/{id}/delete', [LocalController::class, 'deletePlayer'])->name('local.delete-player');

    Route::post('/local/tournament/{id}/mark-ongoing', [LocalController::class, 'markOngoing'])->name('local.mark-ongoing');
    Route::post('/local/tournament/{id}/mark-completed', [LocalController::class, 'markCompleted'])->name('local.mark-completed');
    Route::post('/local/add-news', [LocalController::class, 'addNews'])->name('local.add-news');
    Route::post('/local/score-update', [LocalController::class, 'updateScore'])->name('local.score-update');
    Route::post('/local/score-undo', [LocalController::class, 'undoScore'])->name('local.undo-score');
    Route::post('/local/match/{id}/change-players', [LocalController::class, 'changeActivePlayers'])->name('local.change-players');
    Route::post('/local/match/{id}/switch-innings', [LocalController::class, 'switchInnings'])->name('local.switch-innings');
    Route::post('/local/tournament/{id}/add-prediction', [LocalController::class, 'addPrediction'])->name('local.add-prediction');
    Route::post('/local/tournament/{id}/add-fantasy-tip', [LocalController::class, 'addFantasyTip'])->name('local.add-fantasy-tip');

    // New local routes — mirror admin tournament management
    Route::get('/local/match/{slug}/{id}/toss', [LocalController::class, 'toss'])->name('local.toss.slug');
    Route::get('/local/match/{id}/toss', [LocalController::class, 'toss'])->name('local.toss');
    Route::post('/local/match/{id}/save-toss', [LocalController::class, 'saveToss'])->name('local.save-toss');
    Route::get('/local/match/{slug}/{id}/opening-players', [LocalController::class, 'openingPlayers'])->name('local.opening-players.slug');
    Route::get('/local/match/{id}/opening-players', [LocalController::class, 'openingPlayers'])->name('local.opening-players');
    Route::post('/local/match/{id}/start-innings', [LocalController::class, 'startInnings'])->name('local.start-innings');
    Route::get('/local/match/{slug}/{id}', [LocalController::class, 'matchDetail'])->name('local.match.detail.slug');
    Route::get('/local/match/{id}', [LocalController::class, 'matchDetail'])->name('local.match.detail');
    Route::get('/local/tournament/{slug}/{id}/preview', [LocalController::class, 'tournamentPreview'])->name('local.tournament.preview.slug');
    Route::get('/local/tournament/{id}/preview', [LocalController::class, 'tournamentPreview'])->name('local.tournament.preview');
    Route::post('/local/match/{id}/delete', [LocalController::class, 'deleteMatch'])->name('local.delete-match');
    Route::post('/local/match/add-scorecard-stat', [LocalController::class, 'addScorecardStat'])->name('local.add-scorecard-stat');
    Route::post('/local/tournament/{id}/request-delete', [LocalController::class, 'requestDelete'])->name('local.request-delete');
});

// 4. Super Admin Panel (Manage & add data directly into phpMyAdmin MySQL)
Route::middleware(['superadmin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/create-tournament', [AdminController::class, 'createTournament'])->name('admin.create-tournament');
    Route::get('/admin/tournament/{slug}/{id}/manage', [AdminController::class, 'manageTournament'])->name('admin.manage-tournament.slug');
    Route::get('/admin/tournament/{id}/manage', [AdminController::class, 'manageTournament'])->name('admin.manage-tournament');
    Route::post('/admin/tournament/{id}/add-team', [AdminController::class, 'addTeam'])->name('admin.add-team');
    Route::post('/admin/tournament/{id}/add-player', [AdminController::class, 'addPlayer'])->name('admin.add-player');
    Route::get('/admin/match/{slug}/{id}/scorer', [AdminController::class, 'scorer'])->name('admin.scorer.slug');
    Route::get('/admin/match/{id}/scorer', [AdminController::class, 'scorer'])->name('admin.scorer');
    Route::post('/admin/score-update', [AdminController::class, 'updateScore'])->name('admin.score-update');
    Route::post('/admin/score-undo', [AdminController::class, 'undoScore'])->name('admin.undo-score');
    Route::post('/admin/match/{id}/change-players', [AdminController::class, 'changeActivePlayers'])->name('admin.change-players');
    Route::post('/admin/match/{id}/switch-innings', [AdminController::class, 'switchInnings'])->name('admin.switch-innings');
    Route::get('/admin/match/{slug}/{id}/toss', [AdminController::class, 'toss'])->name('admin.toss.slug');
    Route::get('/admin/match/{id}/toss', [AdminController::class, 'toss'])->name('admin.toss');
    Route::post('/admin/match/{id}/save-toss', [AdminController::class, 'saveToss'])->name('admin.save-toss');
    Route::get('/admin/match/{slug}/{id}/opening-players', [AdminController::class, 'openingPlayers'])->name('admin.opening-players.slug');
    Route::get('/admin/match/{id}/opening-players', [AdminController::class, 'openingPlayers'])->name('admin.opening-players');
    Route::post('/admin/match/{id}/start-innings', [AdminController::class, 'startInnings'])->name('admin.start-innings');
    Route::post('/admin/match/{id}/delete', [AdminController::class, 'deleteMatch'])->name('admin.delete-match');
    Route::post('/admin/tournament/{id}/add-match', [AdminController::class, 'addMatch'])->name('admin.add-match');
    Route::get('/admin/match/{slug}/{id}', [AdminController::class, 'matchDetail'])->name('admin.match.detail.slug');
    Route::get('/admin/match/{id}', [AdminController::class, 'matchDetail'])->name('admin.match.detail');
    Route::get('/admin/tournament/{slug}/{id}/preview', [AdminController::class, 'tournamentPreview'])->name('admin.tournament.preview.slug');
    Route::get('/admin/tournament/{id}/preview', [AdminController::class, 'tournamentPreview'])->name('admin.tournament.preview');
    Route::post('/admin/team/{id}/delete', [AdminController::class, 'deleteTeam'])->name('admin.delete-team');
    Route::post('/admin/player/{id}/delete', [AdminController::class, 'deletePlayer'])->name('admin.delete-player');

    Route::post('/admin/tournament/{id}/publish', [AdminController::class, 'publishTournament'])->name('admin.publish-tournament');
    Route::post('/admin/tournament/{id}/mark-ongoing', [AdminController::class, 'markOngoing'])->name('admin.mark-ongoing');
    Route::post('/admin/tournament/{id}/mark-completed', [AdminController::class, 'markCompleted'])->name('admin.mark-completed');
    Route::post('/admin/tournament/{id}/add-prediction', [AdminController::class, 'addPrediction'])->name('admin.add-prediction');
    Route::post('/admin/tournament/{id}/add-fantasy-tip', [AdminController::class, 'addFantasyTip'])->name('admin.add-fantasy-tip');
    Route::post('/admin/update-match', [AdminController::class, 'updateMatch'])->name('admin.update-match');
    Route::post('/admin/add-ball-commentary', [AdminController::class, 'addBallCommentary'])->name('admin.add-ball-commentary');
    Route::post('/admin/add-scorecard-stat', [AdminController::class, 'addScorecardStat'])->name('admin.add-scorecard-stat');

    Route::get('/admin/series', [AdminController::class, 'showAddSeriesForm'])->name('admin.series');
    Route::get('/admin/series/{slug}/{id}/edit', [AdminController::class, 'showAddSeriesForm'])->name('admin.series.edit.slug');
    Route::get('/admin/series/{id}/edit', [AdminController::class, 'showAddSeriesForm'])->name('admin.series.edit');
    Route::post('/admin/series', [AdminController::class, 'addSeries'])->name('admin.series.post');
    Route::post('/admin/series/update/{id}', [AdminController::class, 'updateSeries'])->name('admin.series.update');
    Route::post('/admin/series/sync-possible11', [AdminController::class, 'syncPossible11Series'])->name('admin.series.sync-possible11');

    Route::get('/admin/match', [AdminController::class, 'showCreateMatchForm'])->name('admin.match');
    Route::post('/admin/match', [AdminController::class, 'createMatch'])->name('admin.match.post');

    Route::get('/admin/fantasy', [AdminController::class, 'showAddFantasyTipForm'])->name('admin.fantasy');
    Route::post('/admin/fantasy', [AdminController::class, 'addFantasyTip'])->name('admin.fantasy.post');
    Route::post('/admin/fantasy/delete/{id}', [AdminController::class, 'deleteFantasyTip'])->name('admin.fantasy.delete');

    Route::get('/admin/prediction', [AdminController::class, 'showAddPredictionForm'])->name('admin.prediction');
    Route::post('/admin/prediction', [AdminController::class, 'addPrediction'])->name('admin.prediction.post');
    Route::post('/admin/prediction/update/{id}', [AdminController::class, 'updatePrediction'])->name('admin.prediction.update');
    Route::post('/admin/prediction/delete/{id}', [AdminController::class, 'deletePrediction'])->name('admin.prediction.delete');

    Route::get('/admin/match-preview', [AdminController::class, 'showMatchPreviewForm'])->name('admin.match-preview');
    Route::post('/admin/match-preview', [AdminController::class, 'addMatchPreview'])->name('admin.match-preview.post');
    Route::post('/admin/match-preview/update/{id}', [AdminController::class, 'updateMatchPreview'])->name('admin.match-preview.update');
    Route::post('/admin/match-preview/delete/{id}', [AdminController::class, 'deleteMatchPreview'])->name('admin.match-preview.delete');

    Route::get('/admin/article', [AdminController::class, 'showAddArticleForm'])->name('admin.article');
    Route::post('/admin/article', [AdminController::class, 'addArticle'])->name('admin.article.post');

    Route::get('/admin/news', [AdminController::class, 'showAddNewsForm'])->name('admin.news');
    Route::post('/admin/news', [AdminController::class, 'addNews'])->name('admin.news.post');

    Route::get('/admin/popular', [AdminController::class, 'showTeamsForm'])->name('admin.popular');
    Route::post('/admin/popular', [AdminController::class, 'createTeam'])->name('admin.popular.post');
    Route::post('/admin/popular/update/{id}', [AdminController::class, 'updateTeam'])->name('admin.popular.update');
    Route::post('/admin/popular/delete/{id}', [AdminController::class, 'deleteTeamEntry'])->name('admin.popular.delete');

    Route::get('/admin/ranking', [AdminController::class, 'showAddTeamRankingForm'])->name('admin.ranking');
    Route::post('/admin/ranking', [AdminController::class, 'addTeamRanking'])->name('admin.ranking.post');

    Route::get('/admin/story', [AdminController::class, 'showAddWebStoryForm'])->name('admin.story');
    Route::post('/admin/story', [AdminController::class, 'addWebStory'])->name('admin.story.post');

    Route::get('/admin/glossary', [AdminController::class, 'showAddGlossaryForm'])->name('admin.glossary');
    Route::post('/admin/glossary', [AdminController::class, 'addGlossary'])->name('admin.glossary.post');

    // Delete & Update routes for admin content management
    Route::post('/admin/prediction/{id}/delete', [AdminController::class, 'deletePrediction'])->name('admin.prediction.delete');
    Route::post('/admin/prediction/update/{id}', [AdminController::class, 'updatePrediction'])->name('admin.prediction.update');

    Route::post('/admin/fantasy/{id}/delete', [AdminController::class, 'deleteFantasyTip'])->name('admin.fantasy.delete');
    Route::post('/admin/fantasy/update/{id}', [AdminController::class, 'updateFantasyTip'])->name('admin.fantasy.update');

    Route::post('/admin/article/{id}/delete', [AdminController::class, 'deleteArticle'])->name('admin.article.delete');
    Route::post('/admin/article/update/{id}', [AdminController::class, 'updateArticle'])->name('admin.article.update');

    Route::post('/admin/news/{id}/delete', [AdminController::class, 'deleteNews'])->name('admin.news.delete');
    Route::post('/admin/news/update/{id}', [AdminController::class, 'updateNews'])->name('admin.news.update');

    Route::post('/admin/story/{id}/delete', [AdminController::class, 'deleteWebStory'])->name('admin.story.delete');
    Route::post('/admin/story/update/{id}', [AdminController::class, 'updateWebStory'])->name('admin.story.update');

    Route::post('/admin/glossary/{id}/delete', [AdminController::class, 'deleteGlossaryTerm'])->name('admin.glossary.delete');
    Route::post('/admin/glossary/update/{id}', [AdminController::class, 'updateGlossaryTerm'])->name('admin.glossary.update');

    // Approval / Deletion requests routes
    Route::get('/admin/notifications', [AdminController::class, 'showNotifications'])->name('admin.notifications');
    Route::post('/admin/tournament/{id}/approve', [AdminController::class, 'approveTournament'])->name('admin.approve-tournament');
    Route::post('/admin/tournament/{id}/delete', [AdminController::class, 'deleteTournament'])->name('admin.delete-tournament');
    Route::post('/admin/tournament/{id}/reject-deletion', [AdminController::class, 'rejectDeletion'])->name('admin.reject-deletion');

    // Player ranking and stats management routes
    Route::post('/admin/ranking/update/{id}', [AdminController::class, 'updateTeamRanking'])->name('admin.ranking.update');
    Route::post('/admin/player-ranking', [AdminController::class, 'addPlayerRanking'])->name('admin.player-ranking.post');
    Route::post('/admin/player-ranking/update/{id}', [AdminController::class, 'updatePlayerRanking'])->name('admin.player-ranking.update');
    Route::post('/admin/ranking/delete/{id}', [AdminController::class, 'deleteTeamRanking'])->name('admin.ranking.delete');
    Route::post('/admin/player-ranking/delete/{id}', [AdminController::class, 'deletePlayerRanking'])->name('admin.player-ranking.delete');

    // Match Preview (alag section)
    Route::get('/admin/match-preview', [AdminController::class, 'showMatchPreviewForm'])->name('admin.match-preview');
    Route::post('/admin/match-preview', [AdminController::class, 'addMatchPreview'])->name('admin.match-preview.post');
    Route::post('/admin/match-preview/update/{id}', [AdminController::class, 'updateMatchPreview'])->name('admin.match-preview.update');
    Route::post('/admin/match-preview/delete/{id}', [AdminController::class, 'deleteMatchPreview'])->name('admin.match-preview.delete');

    // Teams Management
    Route::get('/admin/teams', [AdminController::class, 'showTeamsForm'])->name('admin.teams');
    Route::post('/admin/teams', [AdminController::class, 'createTeam'])->name('admin.teams.post');
    Route::post('/admin/teams/update/{id}', [AdminController::class, 'updateTeam'])->name('admin.teams.update');
    Route::post('/admin/teams/delete/{id}', [AdminController::class, 'deleteTeamEntry'])->name('admin.teams.delete');

    // Players Management
    Route::get('/admin/players', [AdminController::class, 'showPlayersForm'])->name('admin.players');
    Route::post('/admin/players', [AdminController::class, 'createPlayer'])->name('admin.players.post');
    Route::post('/admin/players/update/{id}', [AdminController::class, 'updatePlayer'])->name('admin.players.update');
    Route::post('/admin/players/delete/{id}', [AdminController::class, 'deletePlayerEntry'])->name('admin.players.delete');
    Route::post('/admin/players/sync-possible11', [AdminController::class, 'syncPossible11Players'])->name('admin.players.sync-possible11');

    // Venues Management
    Route::get('/admin/venues', [AdminController::class, 'showVenuesForm'])->name('admin.venues');
    Route::post('/admin/venues', [AdminController::class, 'createVenue'])->name('admin.venues.post');
    Route::post('/admin/venues/update/{id}', [AdminController::class, 'updateVenue'])->name('admin.venues.update');
    Route::post('/admin/venues/delete/{id}', [AdminController::class, 'deleteVenue'])->name('admin.venues.delete');
    Route::post('/admin/venues/sync-possible11', [AdminController::class, 'syncPossible11Venues'])->name('admin.venues.sync-possible11');

    // Quick Add & JSON APIs for modals
    Route::post('/admin/teams/quick-add', [AdminController::class, 'quickAddTeam'])->name('admin.teams.quick-add');
    Route::get('/admin/teams/json', [AdminController::class, 'getTeamsJson'])->name('admin.teams.json');
    Route::post('/admin/venues/quick-add', [AdminController::class, 'quickAddVenue'])->name('admin.venues.quick-add');
    Route::get('/admin/venues/json', [AdminController::class, 'getVenuesJson'])->name('admin.venues.json');
    // Standard Image Uploader (Crop, Multi-Resolution & WebP/AVIF Export)
    Route::get('/admin/image-uploader', [AdminController::class, 'showImageUploader'])->name('admin.image-uploader');
    Route::post('/admin/image-uploader/upload', [AdminController::class, 'uploadStandardImage'])->name('admin.image-uploader.upload');
});

// 5. Authentication (Email/Password, Google OAuth, Email OTP Verification, Password Reset)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/google', [AuthController::class, 'showGoogleLoginSim'])->name('google.login');
Route::post('/login/google', [AuthController::class, 'googleLoginSimPost'])->name('google.login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Email OTP Verification
Route::get('/verify-email', [AuthController::class, 'showVerifyOtp'])->name('verification.notice');
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp']);
Route::post('/verify-email', [AuthController::class, 'verifyOtp'])->name('verification.verify');
Route::post('/verify-email/resend', [AuthController::class, 'resendOtp'])->name('verification.resend');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token?}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

