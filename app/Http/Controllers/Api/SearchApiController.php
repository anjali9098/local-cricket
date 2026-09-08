<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CricketMatch;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\Player;
use App\Models\Article;
use App\Models\News;
use App\Models\Prediction;
use App\Models\FantasyTip;
use App\Models\Venue;
use App\Models\WebStory;
use App\Models\GlossaryTerm;
use Illuminate\Support\Str;

class SearchApiController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));
        $category = strtolower(trim($request->query('type', 'all')));

        if (empty($q) || strlen($q) < 1) {
            return response()->json([
                'success' => true,
                'query' => '',
                'total_count' => 0,
                'results' => [
                    'all' => [],
                    'players' => [],
                    'teams' => [],
                    'matches' => [],
                    'tournaments' => [],
                    'articles' => [],
                    'news' => [],
                    'predictions' => [],
                    'fantasy_tips' => [],
                    'venues' => [],
                    'web_stories' => [],
                    'glossary' => []
                ]
            ]);
        }

        $allResults = [];
        $isNumeric = is_numeric($q);

        // 1. PLAYERS
        $players = Player::with('team')
            ->where(function($query) use ($q, $isNumeric) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('short_name', 'LIKE', "%{$q}%")
                      ->orWhere('role', 'LIKE', "%{$q}%")
                      ->orWhere('country', 'LIKE', "%{$q}%")
                      ->orWhere('batting_style', 'LIKE', "%{$q}%")
                      ->orWhere('bowling_style', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->take(8)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'type' => 'player',
                    'type_label' => 'Player',
                    'title' => $p->name,
                    'subtitle' => ($p->role ?: 'Player') . ($p->team ? ' • ' . $p->team->name : ($p->country ? ' • ' . $p->country : '')),
                    'badge' => '🏏 ' . strtoupper($p->role ?: 'PLAYER'),
                    'badge_class' => 'badge-player',
                    'image' => $p->profile_image ?: null,
                    'initials' => $p->initials ?: strtoupper(substr($p->name, 0, 2)),
                    'url' => url('/player/' . $p->id)
                ];
            });

        // 2. TEAMS
        $teams = Team::where(function($query) use ($q, $isNumeric) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('short_name', 'LIKE', "%{$q}%")
                      ->orWhere('city', 'LIKE', "%{$q}%")
                      ->orWhere('country', 'LIKE', "%{$q}%")
                      ->orWhere('team_type', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->take(8)
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'type' => 'team',
                    'type_label' => 'Team',
                    'title' => $t->name,
                    'subtitle' => ($t->short_name ? 'Code: ' . $t->short_name . ' • ' : '') . ($t->team_type ? ucfirst($t->team_type) : 'Cricket Team'),
                    'badge' => '🛡️ ' . strtoupper($t->short_name ?: 'TEAM'),
                    'badge_class' => 'badge-team',
                    'image' => $t->logo_url ?: $t->logo,
                    'initials' => strtoupper(substr($t->name, 0, 2)),
                    'url' => url('/players?team=' . $t->id)
                ];
            });

        // 3. MATCHES
        $matches = CricketMatch::with(['team1', 'team2', 'venue', 'tournament'])
            ->where(function($query) use ($q, $isNumeric) {
                $query->where('custom_note', 'LIKE', "%{$q}%")
                      ->orWhere('result_text', 'LIKE', "%{$q}%")
                      ->orWhere('match_type', 'LIKE', "%{$q}%")
                      ->orWhere('status', 'LIKE', "%{$q}%")
                      ->orWhereHas('team1', function($tq) use ($q) { $tq->where('name', 'LIKE', "%{$q}%"); })
                      ->orWhereHas('team2', function($tq) use ($q) { $tq->where('name', 'LIKE', "%{$q}%"); });
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function($m) {
                $t1Name = $m->team1 ? $m->team1->name : 'Team 1';
                $t2Name = $m->team2 ? $m->team2->name : 'Team 2';
                $score1 = $m->team1_score !== null ? "{$m->team1_score}/{$m->team1_wickets}" : '';
                $score2 = $m->team2_score !== null ? "{$m->team2_score}/{$m->team2_wickets}" : '';
                $scoreText = $score1 && $score2 ? " ({$score1} vs {$score2})" : ($score1 ? " ({$score1})" : '');

                return [
                    'id' => $m->id,
                    'type' => 'match',
                    'type_label' => 'Match',
                    'title' => "{$t1Name} vs {$t2Name}{$scoreText}",
                    'subtitle' => ($m->match_type ?: 'Match') . ' • ' . ($m->result_text ?: ($m->custom_note ?: ($m->match_date ? date('d M Y', strtotime($m->match_date)) : 'Cricket Match'))),
                    'badge' => $m->status === 'live' ? '🔴 LIVE' : ($m->status === 'completed' ? '🏁 COMPLETED' : '📅 UPCOMING'),
                    'badge_class' => $m->status === 'live' ? 'badge-live' : ($m->status === 'completed' ? 'badge-completed' : 'badge-upcoming'),
                    'image' => null,
                    'url' => url('/matches/' . $m->id)
                ];
            });

        // 4. TOURNAMENTS / SERIES
        $tournaments = Tournament::where(function($query) use ($q, $isNumeric) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('category', 'LIKE', "%{$q}%")
                      ->orWhere('format', 'LIKE', "%{$q}%")
                      ->orWhere('city', 'LIKE', "%{$q}%")
                      ->orWhere('hosting_country', 'LIKE', "%{$q}%")
                      ->orWhere('teams_list', 'LIKE', "%{$q}%")
                      ->orWhere('venues_list', 'LIKE', "%{$q}%")
                      ->orWhere('year', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->take(8)
            ->get()
            ->map(function($tour) {
                return [
                    'id' => $tour->id,
                    'type' => 'tournament',
                    'type_label' => 'Series',
                    'title' => $tour->name,
                    'subtitle' => ($tour->year ? $tour->year . ' • ' : '') . ($tour->category ? ucfirst($tour->category) . ' • ' : '') . ($tour->hosting_country ?: ($tour->city ?: 'Series')),
                    'badge' => '🏆 ' . strtoupper($tour->category ?: 'SERIES'),
                    'badge_class' => 'badge-series',
                    'image' => $tour->banner_url ?: $tour->poster_image,
                    'url' => url('/t/' . $tour->id)
                ];
            });

        // 5. ARTICLES
        $articles = Article::where(function($query) use ($q, $isNumeric) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('summary', 'LIKE', "%{$q}%")
                      ->orWhere('category', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%")
                      ->orWhere('content', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'type' => 'article',
                    'type_label' => 'Article',
                    'title' => $a->title,
                    'subtitle' => ($a->published_date ? $a->published_date . ' • ' : '') . ($a->read_time ?: '5 MIN READ'),
                    'badge' => '📝 ' . strtoupper($a->category ?: 'ARTICLE'),
                    'badge_class' => 'badge-article',
                    'image' => $a->image_url ?: null,
                    'url' => url('/article/' . $a->id)
                ];
            });

        // 6. NEWS
        $news = News::where(function($query) use ($q, $isNumeric) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('summary', 'LIKE', "%{$q}%")
                      ->orWhere('category', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%")
                      ->orWhere('content', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function($n) {
                return [
                    'id' => $n->id,
                    'type' => 'news',
                    'type_label' => 'News',
                    'title' => $n->title,
                    'subtitle' => ($n->published_date ? $n->published_date . ' • ' : '') . ($n->category ? strtoupper($n->category) : 'NEWS'),
                    'badge' => '📰 ' . strtoupper($n->category ?: 'NEWS'),
                    'badge_class' => 'badge-news',
                    'image' => $n->image_url ?: null,
                    'url' => url('/news/' . $n->id)
                ];
            });

        // 7. PREDICTIONS & PREVIEWS
        $predictions = Prediction::where(function($query) use ($q, $isNumeric) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('summary', 'LIKE', "%{$q}%")
                      ->orWhere('match_title', 'LIKE', "%{$q}%")
                      ->orWhere('tag', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function($pr) {
                return [
                    'id' => $pr->id,
                    'type' => 'prediction',
                    'type_label' => 'Prediction',
                    'title' => $pr->title,
                    'subtitle' => $pr->match_title ?: 'Match Prediction & Tips',
                    'badge' => '🎯 ' . strtoupper($pr->tag ?: 'PREDICTION'),
                    'badge_class' => 'badge-prediction',
                    'image' => $pr->poster_image ?: null,
                    'url' => url('/news/' . $pr->id)
                ];
            });

        // 8. FANTASY TIPS
        $fantasyTips = FantasyTip::where(function($query) use ($q, $isNumeric) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('summary', 'LIKE', "%{$q}%")
                      ->orWhere('tag', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function($ft) {
                return [
                    'id' => $ft->id,
                    'type' => 'fantasy',
                    'type_label' => 'Fantasy Tip',
                    'title' => $ft->title,
                    'subtitle' => $ft->summary ? Str::limit(strip_tags($ft->summary), 65) : 'Fantasy Dream Team Tips',
                    'badge' => '⚡ ' . strtoupper($ft->tag ?: 'FANTASY TIP'),
                    'badge_class' => 'badge-fantasy',
                    'image' => $ft->poster_image ?: null,
                    'url' => url('/news/' . $ft->id)
                ];
            });

        // 9. VENUES / STADIUMS
        $venues = Venue::where(function($query) use ($q, $isNumeric) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('city', 'LIKE', "%{$q}%")
                      ->orWhere('country', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->take(8)
            ->get()
            ->map(function($v) {
                return [
                    'id' => $v->id,
                    'type' => 'venue',
                    'type_label' => 'Venue',
                    'title' => $v->name,
                    'subtitle' => ($v->city ? $v->city . ', ' : '') . ($v->country ?: 'Cricket Stadium') . ($v->capacity ? ' • Cap: ' . (is_numeric($v->capacity) ? number_format((float)$v->capacity) : $v->capacity) : ''),
                    'badge' => '🏟️ VENUE',
                    'badge_class' => 'badge-venue',
                    'image' => $v->image_url ?: null,
                    'url' => url('/venues/' . $v->id)
                ];
            });

        // 10. WEB STORIES
        $webStories = WebStory::where(function($query) use ($q, $isNumeric) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('tag', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->orderBy('id', 'desc')
            ->take(8)
            ->get()
            ->map(function($ws) {
                return [
                    'id' => $ws->id,
                    'type' => 'story',
                    'type_label' => 'Web Story',
                    'title' => $ws->title,
                    'subtitle' => $ws->tag ? ucfirst($ws->tag) : 'Cricket Visual Story',
                    'badge' => '📱 STORY',
                    'badge_class' => 'badge-story',
                    'image' => $ws->image_url ?: null,
                    'url' => url('/web-story/' . $ws->id)
                ];
            });

        // 11. GLOSSARY TERMS
        $glossary = GlossaryTerm::where(function($query) use ($q, $isNumeric) {
                $query->where('term', 'LIKE', "%{$q}%")
                      ->orWhere('definition', 'LIKE', "%{$q}%")
                      ->orWhere('keywords', 'LIKE', "%{$q}%");
                if ($isNumeric) {
                    $query->orWhere('id', (int)$q);
                }
            })
            ->take(8)
            ->get()
            ->map(function($g) {
                return [
                    'id' => $g->id,
                    'type' => 'glossary',
                    'type_label' => 'Glossary',
                    'title' => $g->term,
                    'subtitle' => $g->definition ? Str::limit(strip_tags($g->definition), 75) : 'Cricket Glossary Term',
                    'badge' => '📖 GLOSSARY',
                    'badge_class' => 'badge-glossary',
                    'image' => $g->poster_image ?: null,
                    'url' => url('/glossary/' . $g->id)
                ];
            });

        // Combined Top Results
        $combined = collect()
            ->concat($players->take(3))
            ->concat($teams->take(3))
            ->concat($matches->take(3))
            ->concat($tournaments->take(2))
            ->concat($articles->take(2))
            ->concat($news->take(2))
            ->concat($predictions->take(2))
            ->concat($fantasyTips->take(2))
            ->concat($venues->take(2))
            ->concat($webStories->take(2))
            ->concat($glossary->take(2));

        $totalCount = $players->count() + $teams->count() + $matches->count() + $tournaments->count()
                    + $articles->count() + $news->count() + $predictions->count() + $fantasyTips->count()
                    + $venues->count() + $webStories->count() + $glossary->count();

        return response()->json([
            'success' => true,
            'query' => $q,
            'total_count' => $totalCount,
            'results' => [
                'all' => $combined->values(),
                'players' => $players->values(),
                'teams' => $teams->values(),
                'matches' => $matches->values(),
                'tournaments' => $tournaments->values(),
                'articles' => $articles->values(),
                'news' => $news->values(),
                'predictions' => $predictions->values(),
                'fantasy_tips' => $fantasyTips->values(),
                'venues' => $venues->values(),
                'web_stories' => $webStories->values(),
                'glossary' => $glossary->values()
            ]
        ]);
    }

    /**
     * Unified Common Super Admin Search & Filter API Endpoint
     * GET /api/admin/search?model={players|teams|matches|series|...}&q={query}&filter={filter_val}&role={role}&category={category}
     */
    public function adminSearch(Request $request)
    {
        $model = strtolower(trim($request->query('model', 'all')));
        $q = trim($request->query('q', ''));
        $role = strtolower(trim($request->query('role', '')));
        $category = strtolower(trim($request->query('category', '')));
        $type = strtolower(trim($request->query('type', '')));
        $status = strtolower(trim($request->query('status', '')));
        $limit = (int)$request->query('limit', 50);
        if ($limit < 1 || $limit > 200) $limit = 50;

        $results = [];
        $isNumeric = is_numeric($q);

        // 1. PLAYERS
        if ($model === 'players' || $model === 'all') {
            $pQuery = Player::with('team');
            if (!empty($q)) {
                $pQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('role', 'LIKE', "%{$q}%")
                          ->orWhere('country', 'LIKE', "%{$q}%")
                          ->orWhere('batting_style', 'LIKE', "%{$q}%")
                          ->orWhere('bowling_style', 'LIKE', "%{$q}%")
                          ->orWhere('keywords', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            if (!empty($role)) {
                if (str_contains($role, 'wk') || str_contains($role, 'keeper')) {
                    $pQuery->where(function($query) {
                        $query->where('role', 'LIKE', '%wk%')
                              ->orWhere('role', 'LIKE', '%keeper%')
                              ->orWhere('role', 'LIKE', '%wicket%');
                    });
                } elseif ($role === 'batsman') {
                    $pQuery->where('role', 'LIKE', '%bat%');
                } elseif ($role === 'bowler') {
                    $pQuery->where('role', 'LIKE', '%bowl%');
                } elseif (str_contains($role, 'all')) {
                    $pQuery->where('role', 'LIKE', '%all%');
                } else {
                    $pQuery->where('role', 'LIKE', "%{$role}%");
                }
            }
            $results['players'] = $pQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 2. TEAMS
        if ($model === 'teams' || $model === 'all') {
            $tQuery = Team::query();
            if (!empty($q)) {
                $tQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('short_name', 'LIKE', "%{$q}%")
                          ->orWhere('city', 'LIKE', "%{$q}%")
                          ->orWhere('country', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            if (!empty($type)) {
                $tQuery->where('team_type', 'LIKE', "%{$type}%");
            }
            $results['teams'] = $tQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 3. MATCHES
        if ($model === 'matches' || $model === 'all') {
            $mQuery = CricketMatch::with(['team1', 'team2', 'tournament', 'venue']);
            if (!empty($q)) {
                $mQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('match_type', 'LIKE', "%{$q}%")
                          ->orWhere('stage', 'LIKE', "%{$q}%")
                          ->orWhereHas('team1', function($tq) use ($q) { $tq->where('name', 'LIKE', "%{$q}%"); })
                          ->orWhereHas('team2', function($tq) use ($q) { $tq->where('name', 'LIKE', "%{$q}%"); });
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            if (!empty($status)) {
                $mQuery->where('status', $status);
            }
            $results['matches'] = $mQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 4. TOURNAMENTS / SERIES
        if ($model === 'series' || $model === 'tournaments' || $model === 'all') {
            $sQuery = Tournament::query();
            if (!empty($q)) {
                $sQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('category', 'LIKE', "%{$q}%")
                          ->orWhere('tournament_type', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            if (!empty($category)) {
                $sQuery->where('category', 'LIKE', "%{$category}%");
            }
            $results['series'] = $sQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 5. ARTICLES
        if ($model === 'articles' || $model === 'all') {
            $aQuery = Article::query();
            if (!empty($q)) {
                $aQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('title', 'LIKE', "%{$q}%")
                          ->orWhere('category', 'LIKE', "%{$q}%")
                          ->orWhere('keywords', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            if (!empty($category)) {
                $aQuery->where('category', 'LIKE', "%{$category}%");
            }
            $results['articles'] = $aQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 6. NEWS
        if ($model === 'news' || $model === 'all') {
            $nQuery = News::query();
            if (!empty($q)) {
                $nQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('title', 'LIKE', "%{$q}%")
                          ->orWhere('category', 'LIKE', "%{$q}%")
                          ->orWhere('keywords', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            if (!empty($category)) {
                $nQuery->where('category', 'LIKE', "%{$category}%");
            }
            $results['news'] = $nQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 7. PREDICTIONS & FANTASY TIPS
        if ($model === 'predictions' || $model === 'all') {
            $predQuery = Prediction::query();
            if (!empty($q)) {
                $predQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('title', 'LIKE', "%{$q}%")
                              ->orWhere('summary', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            $results['predictions'] = $predQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        if ($model === 'fantasy_tips' || $model === 'all') {
            $fanQuery = FantasyTip::query();
            if (!empty($q)) {
                $fanQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('title', 'LIKE', "%{$q}%")
                             ->orWhere('summary', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            $results['fantasy_tips'] = $fanQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 8. VENUES
        if ($model === 'venues' || $model === 'all') {
            $vQuery = Venue::query();
            if (!empty($q)) {
                $vQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('city', 'LIKE', "%{$q}%")
                          ->orWhere('country', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            $results['venues'] = $vQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 9. WEB STORIES
        if ($model === 'web_stories' || $model === 'all') {
            $wsQuery = WebStory::query();
            if (!empty($q)) {
                $wsQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('title', 'LIKE', "%{$q}%")
                           ->orWhere('tag', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            $results['web_stories'] = $wsQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        // 10. GLOSSARY
        if ($model === 'glossary' || $model === 'all') {
            $gQuery = GlossaryTerm::query();
            if (!empty($q)) {
                $gQuery->where(function($query) use ($q, $isNumeric) {
                    $query->where('term', 'LIKE', "%{$q}%")
                          ->orWhere('definition', 'LIKE', "%{$q}%");
                    if ($isNumeric) $query->orWhere('id', (int)$q);
                });
            }
            $results['glossary'] = $gQuery->orderBy('id', 'desc')->take($limit)->get();
        }

        $totalFound = 0;
        foreach ($results as $k => $list) {
            $totalFound += count($list);
        }

        return response()->json([
            'success' => true,
            'model' => $model,
            'query' => $q,
            'total_count' => $totalFound,
            'data' => $model !== 'all' && isset($results[$model]) ? $results[$model] : $results
        ]);
    }
}

