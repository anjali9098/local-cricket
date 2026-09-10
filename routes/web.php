<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

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
Route::get('/matches/{id}', [PageController::class, 'matchDetail'])->name('matches.detail');
Route::get('/stats', [PageController::class, 'stats'])->name('stats');
Route::get('/tournaments', [PageController::class, 'tournaments'])->name('tournaments');
Route::get('/t/{id}', [PageController::class, 'tournamentDetail'])->name('tournament.public');
Route::get('/news', [PageController::class, 'news'])->name('news');
Route::get('/news/{id}', [PageController::class, 'showNews'])->name('news.show');
Route::get('/article/{id}', [PageController::class, 'showArticle'])->name('article.show');
Route::get('/compare', [PageController::class, 'compare'])->name('compare');
Route::get('/teams', [PageController::class, 'teams'])->name('teams');
Route::get('/players', [PageController::class, 'players'])->name('players');
Route::get('/player/{id}', [PageController::class, 'playerProfile'])->name('player.profile');
Route::get('/players/{id}', [PageController::class, 'playerProfile'])->name('players.show');
Route::get('/venues', [PageController::class, 'venues'])->name('venues');
Route::get('/venues/{id}', [PageController::class, 'showVenue'])->name('venues.show');
Route::get('/venue/{id}', [PageController::class, 'showVenue'])->name('venue.show');
Route::get('/web-stories', [PageController::class, 'webStories'])->name('webstories.all');
Route::get('/web-story/{id}', [PageController::class, 'showWebStory'])->name('webstories.show');
Route::get('/glossary', [PageController::class, 'glossary'])->name('glossary.all');
Route::get('/glossary/{id}', [PageController::class, 'showGlossaryTerm'])->name('glossary.show');
Route::get('/player-birthdays', [PageController::class, 'playerBirthdays'])->name('player.birthdays');
Route::get('/search', [PageController::class, 'globalSearch'])->name('search');
Route::get('/api/admin/search', [\App\Http\Controllers\Api\SearchApiController::class, 'adminSearch'])->name('api.admin.search');

// 3. CrickArena Local Cricket Portal (Locked behind login / sign up)
Route::middleware(['localadmin'])->group(function () {
    Route::get('/local', [LocalController::class, 'index'])->name('local.dashboard');
    Route::post('/local/create-tournament', [LocalController::class, 'createTournament'])->name('local.create-tournament');
    Route::get('/local/tournament/{id}/manage', [LocalController::class, 'manageTournament'])->name('local.manage-tournament');
    Route::post('/local/tournament/{id}/publish', [LocalController::class, 'publishTournament'])->name('local.publish-tournament');
    Route::post('/local/tournament/{id}/add-team', [LocalController::class, 'addTeam'])->name('local.add-team');
    Route::post('/local/tournament/{id}/add-player', [LocalController::class, 'addPlayer'])->name('local.add-player');
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
    Route::get('/local/match/{id}/toss', [LocalController::class, 'toss'])->name('local.toss');
    Route::post('/local/match/{id}/save-toss', [LocalController::class, 'saveToss'])->name('local.save-toss');
    Route::get('/local/match/{id}/opening-players', [LocalController::class, 'openingPlayers'])->name('local.opening-players');
    Route::post('/local/match/{id}/start-innings', [LocalController::class, 'startInnings'])->name('local.start-innings');
    Route::get('/local/match/{id}', [LocalController::class, 'matchDetail'])->name('local.match.detail');
    Route::get('/local/tournament/{id}/preview', [LocalController::class, 'tournamentPreview'])->name('local.tournament.preview');
    Route::post('/local/match/{id}/delete', [LocalController::class, 'deleteMatch'])->name('local.delete-match');
    Route::post('/local/match/add-scorecard-stat', [LocalController::class, 'addScorecardStat'])->name('local.add-scorecard-stat');
    Route::post('/local/tournament/{id}/request-delete', [LocalController::class, 'requestDelete'])->name('local.request-delete');
});

// 4. Super Admin Panel (Manage & add data directly into phpMyAdmin MySQL)
Route::middleware(['superadmin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/create-tournament', [AdminController::class, 'createTournament'])->name('admin.create-tournament');
    Route::get('/admin/tournament/{id}/manage', [AdminController::class, 'manageTournament'])->name('admin.manage-tournament');
    Route::post('/admin/tournament/{id}/add-team', [AdminController::class, 'addTeam'])->name('admin.add-team');
    Route::post('/admin/tournament/{id}/add-player', [AdminController::class, 'addPlayer'])->name('admin.add-player');
    Route::get('/admin/match/{id}/scorer', [AdminController::class, 'scorer'])->name('admin.scorer');
    Route::post('/admin/score-update', [AdminController::class, 'updateScore'])->name('admin.score-update');
    Route::post('/admin/score-undo', [AdminController::class, 'undoScore'])->name('admin.undo-score');
    Route::post('/admin/match/{id}/change-players', [AdminController::class, 'changeActivePlayers'])->name('admin.change-players');
    Route::post('/admin/match/{id}/switch-innings', [AdminController::class, 'switchInnings'])->name('admin.switch-innings');
    Route::get('/admin/match/{id}/toss', [AdminController::class, 'toss'])->name('admin.toss');
    Route::post('/admin/match/{id}/save-toss', [AdminController::class, 'saveToss'])->name('admin.save-toss');
    Route::get('/admin/match/{id}/opening-players', [AdminController::class, 'openingPlayers'])->name('admin.opening-players');
    Route::post('/admin/match/{id}/start-innings', [AdminController::class, 'startInnings'])->name('admin.start-innings');
    Route::post('/admin/match/{id}/delete', [AdminController::class, 'deleteMatch'])->name('admin.delete-match');
    Route::post('/admin/tournament/{id}/add-match', [AdminController::class, 'addMatch'])->name('admin.add-match');
    Route::get('/admin/match/{id}', [AdminController::class, 'matchDetail'])->name('admin.match.detail');
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

    // GET & POST routes for individual forms (without slugs/hyphens)
    Route::get('/admin/series', [AdminController::class, 'showAddSeriesForm'])->name('admin.series');
    Route::post('/admin/series', [AdminController::class, 'addSeries'])->name('admin.series.post');
    Route::post('/admin/series/update/{id}', [AdminController::class, 'updateSeries'])->name('admin.series.update');

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

    // Venues Management
    Route::get('/admin/venues', [AdminController::class, 'showVenuesForm'])->name('admin.venues');
    Route::post('/admin/venues', [AdminController::class, 'createVenue'])->name('admin.venues.post');
    Route::post('/admin/venues/update/{id}', [AdminController::class, 'updateVenue'])->name('admin.venues.update');
    Route::post('/admin/venues/delete/{id}', [AdminController::class, 'deleteVenue'])->name('admin.venues.delete');

    // Quick Add & JSON APIs for modals
    Route::post('/admin/teams/quick-add', [AdminController::class, 'quickAddTeam'])->name('admin.teams.quick-add');
    Route::get('/admin/teams/json', [AdminController::class, 'getTeamsJson'])->name('admin.teams.json');
    Route::post('/admin/venues/quick-add', [AdminController::class, 'quickAddVenue'])->name('admin.venues.quick-add');
    Route::get('/admin/venues/json', [AdminController::class, 'getVenuesJson'])->name('admin.venues.json');
});

// 5. Authentication (Email/Password, Google OAuth, Password Reset)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/google', [AuthController::class, 'showGoogleLoginSim'])->name('google.login');
Route::post('/login/google', [AuthController::class, 'googleLoginSimPost'])->name('google.login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token?}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

