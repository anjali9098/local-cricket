<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\Tournament;
use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\Venue;
use App\Models\News;
use App\Models\Article;
use App\Models\Prediction;
use App\Models\FantasyTip;
use App\Models\WebStory;

class SitemapController extends Controller
{
    /**
     * Helper to get current Indian Standard Time (IST +05:30) ISO-8601 string
     */
    private function nowIso()
    {
        return Carbon::now('Asia/Kolkata')->toIso8601String();
    }

    /**
     * Helper to format a date to ISO-8601 with IST timezone
     */
    private function formatIso($date = null)
    {
        if (empty($date)) {
            return $this->nowIso();
        }
        try {
            return Carbon::parse($date)->setTimezone('Asia/Kolkata')->toIso8601String();
        } catch (\Throwable $e) {
            return $this->nowIso();
        }
    }

    /**
     * Helper to return XML Response with proper content-type
     */
    private function xmlResponse($view, $data)
    {
        $content = view($view, $data)->render();
        return response($content, 200)->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * Master Sitemap Index File (/sitemap.xml or /sitemap_index.xml)
     * Matches possible11.com sitemap index structure
     */
    public function index()
    {
        $now = $this->nowIso();

        $sitemaps = [
            [
                'loc' => url('/sitemap/pages.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/live-score.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/series.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/cricket-players.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/team.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/news.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/articles.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/prediction.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/fantasy.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/ground.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/local-cricket.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => url('/sitemap/web-stories.xml'),
                'lastmod' => $now,
            ],
        ];

        return $this->xmlResponse('sitemaps.index', compact('sitemaps'));
    }

    /**
     * Main Static & Category Landing Pages (/sitemap/pages.xml)
     * Exact match to possible11.com/sitemap/pages.xml format
     */
    public function pages()
    {
        $now = $this->nowIso();
        $defaultBanner = url('/images/logo.png');

        $staticPages = [
            ['path' => '/', 'priority' => '1.0', 'changefreq' => 'hourly', 'title' => 'Live Cricket Score, Schedule, Stats, News & Fantasy Tips | CricketKaScore'],
            ['path' => '/live', 'priority' => '0.9', 'changefreq' => 'hourly', 'title' => 'Live Cricket Scores & Ball by Ball Updates'],
            ['path' => '/matches', 'priority' => '0.9', 'changefreq' => 'hourly', 'title' => 'All Cricket Matches - Live, Upcoming & Recent Results'],
            ['path' => '/series', 'priority' => '0.85', 'changefreq' => 'daily', 'title' => 'Cricket Series, Tournaments & Leagues'],
            ['path' => '/tournaments', 'priority' => '0.85', 'changefreq' => 'daily', 'title' => 'Cricket Tournaments & Championship Fixtures'],
            ['path' => '/teams', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'International & Domestic Cricket Teams'],
            ['path' => '/players', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Cricket Players Profile, Career Stats & Records'],
            ['path' => '/venues', 'priority' => '0.8', 'changefreq' => 'weekly', 'title' => 'Cricket Grounds, Stadiums & Pitch Reports'],
            ['path' => '/news', 'priority' => '0.85', 'changefreq' => 'daily', 'title' => 'Latest Cricket News, Breaking Updates & Analysis'],
            ['path' => '/articles', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Cricket Editorial Articles & In-Depth Features'],
            ['path' => '/predictions', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Today Match Prediction & Win Probability'],
            ['path' => '/fantasy', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Fantasy Cricket Tips, Playing XI & Captain Picks'],
            ['path' => '/fantasy-tips', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Dream11 & Fantasy Cricket Predictions'],
            ['path' => '/match-previews', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Cricket Match Previews, Pitch Reports & Key Battles'],
            ['path' => '/compare', 'priority' => '0.7', 'changefreq' => 'weekly', 'title' => 'Compare Cricket Players Head to Head Stats'],
            ['path' => '/web-stories', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Visual Web Stories - Cricket Moments & Records'],
            ['path' => '/player-birthday', 'priority' => '0.7', 'changefreq' => 'daily', 'title' => 'Cricket Players Birthday & Celebrations'],
            ['path' => '/rankings', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'ICC Rankings - Team & Player Standings (Test, ODI, T20I)'],
            ['path' => '/stats', 'priority' => '0.8', 'changefreq' => 'daily', 'title' => 'Cricket Records, Top Run Scorers & Wicket Takers'],
            ['path' => '/local', 'priority' => '0.75', 'changefreq' => 'daily', 'title' => 'Local Cricket Tournament Scorer & Match Portal'],
        ];

        $urls = [];
        foreach ($staticPages as $p) {
            $urls[] = [
                'loc' => url($p['path']),
                'lastmod' => $now,
                'priority' => $p['priority'],
                'changefreq' => $p['changefreq'],
                'image' => $defaultBanner,
                'image_title' => $p['title']
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Series & Tournament Hub Pages + Dedicated Stat Category Pages (/sitemap/series.xml)
     */
    public function series()
    {
        $tournaments = Tournament::where('status', '!=', 'draft')->get();
        $urls = [];

        $statCategories = [
            'most-runs', 'most-fours', 'most-sixes', 'most-fifties', 'most-centuries',
            'fours-innings', 'sixes-innings', 'best-strike-rates', 'highest-scores',
            'top-wickets', 'four-wickets', 'five-wickets', 'maidens', 'best-bowling-avg',
            'best-figures', 'best-economies', 'team-runs', 'team-wickets', 'team-fifties',
            'team-centuries', 'highest-team-totals'
        ];

        foreach ($tournaments as $t) {
            $slug = $t->slug;
            $tourUrl = route('tournament.public.slug', ['slug' => $slug, 'id' => $t->id]);
            $banner = $t->banner_url ?: ($t->poster_image ?: url('/images/logo.png'));
            $lastmod = $this->nowIso();

            // 1. Main Series Page
            $urls[] = [
                'loc' => $tourUrl,
                'lastmod' => $lastmod,
                'priority' => '0.85',
                'changefreq' => 'daily',
                'image' => $banner,
                'image_title' => $t->name . ' Schedule, Points Table & Squads'
            ];

            // 2. Dedicated Stat Pages for this series
            foreach ($statCategories as $statKey) {
                $statUrl = route('series.stat.slug', ['slug' => $slug, 'id' => $t->id, 'stat' => $statKey]);
                $statTitle = ucwords(str_replace('-', ' ', $statKey)) . ' - ' . $t->name;
                $urls[] = [
                    'loc' => $statUrl,
                    'lastmod' => $lastmod,
                    'priority' => '0.80',
                    'changefreq' => 'daily',
                    'image' => $banner,
                    'image_title' => $statTitle
                ];
            }
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Live, Upcoming & Completed Matches Scorecards (/sitemap/live-score.xml)
     */
    public function matches()
    {
        $matches = CricketMatch::with(['team1', 'team2'])->where('is_approved', true)->get();
        $urls = [];

        foreach ($matches as $m) {
            $eff = $m->effective_status;
            $priority = ($eff === 'live') ? '0.95' : (($eff === 'upcoming') ? '0.85' : '0.75');
            $changefreq = ($eff === 'live') ? 'always' : (($eff === 'upcoming') ? 'hourly' : 'weekly');
            $img = $m->team1?->logo ?: ($m->team2?->logo ?: url('/images/logo.png'));
            $title = ($m->team1?->name ?? 'Team 1') . ' vs ' . ($m->team2?->name ?? 'Team 2') . ' Live Scorecard';

            $urls[] = [
                'loc' => $m->url,
                'lastmod' => $this->formatIso($m->match_date),
                'priority' => $priority,
                'changefreq' => $changefreq,
                'image' => $img,
                'image_title' => $title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Cricket Players Profile Pages (/sitemap/cricket-players.xml)
     */
    public function players()
    {
        $players = Player::all();
        $urls = [];

        foreach ($players as $p) {
            $img = $p->profile_image ?: url('/images/logo.png');
            $urls[] = [
                'loc' => $p->url,
                'lastmod' => $this->nowIso(),
                'priority' => '0.80',
                'changefreq' => 'weekly',
                'image' => $img,
                'image_title' => $p->name . ' Cricket Profile & Stats'
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Cricket Teams Pages (/sitemap/team.xml)
     */
    public function teams()
    {
        $teams = Team::all();
        $urls = [];

        foreach ($teams as $tm) {
            $img = $tm->logo ?: url('/images/logo.png');
            $urls[] = [
                'loc' => url('/teams'),
                'lastmod' => $this->nowIso(),
                'priority' => '0.75',
                'changefreq' => 'weekly',
                'image' => $img,
                'image_title' => $tm->name . ' Squad & Matches'
            ];
        }

        if (empty($urls)) {
            $urls[] = [
                'loc' => url('/teams'),
                'lastmod' => $this->nowIso(),
                'priority' => '0.75',
                'changefreq' => 'weekly',
                'image' => url('/images/logo.png'),
                'image_title' => 'Cricket Teams Directory'
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Cricket News Articles (/sitemap/news.xml)
     */
    public function news()
    {
        $newsList = News::orderBy('id', 'desc')->get();
        $urls = [];

        foreach ($newsList as $n) {
            $lastmod = $this->formatIso($n->updated_at ?? $n->created_at);
            $urls[] = [
                'loc' => $n->url,
                'lastmod' => $lastmod,
                'priority' => '0.85',
                'changefreq' => 'daily',
                'image' => $n->image_url ?: url('/images/logo.png'),
                'image_title' => $n->title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Editorial Articles (/sitemap/articles.xml)
     */
    public function articles()
    {
        $articles = Article::orderBy('id', 'desc')->get();
        $urls = [];

        foreach ($articles as $a) {
            $urls[] = [
                'loc' => $a->url,
                'lastmod' => $this->nowIso(),
                'priority' => '0.80',
                'changefreq' => 'daily',
                'image' => $a->image_url ?: url('/images/logo.png'),
                'image_title' => $a->title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Match Predictions (/sitemap/prediction.xml)
     */
    public function predictions()
    {
        $predictions = Prediction::orderBy('id', 'desc')->get();
        $urls = [];

        foreach ($predictions as $p) {
            $urls[] = [
                'loc' => $p->url,
                'lastmod' => $this->nowIso(),
                'priority' => '0.80',
                'changefreq' => 'daily',
                'image' => $p->poster_image ?: url('/images/logo.png'),
                'image_title' => $p->title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Fantasy Tips (/sitemap/fantasy.xml)
     */
    public function fantasy()
    {
        $tips = FantasyTip::orderBy('id', 'desc')->get();
        $urls = [];

        foreach ($tips as $f) {
            $urls[] = [
                'loc' => $f->url,
                'lastmod' => $this->nowIso(),
                'priority' => '0.80',
                'changefreq' => 'daily',
                'image' => $f->poster_image ?: url('/images/logo.png'),
                'image_title' => $f->title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Cricket Grounds & Stadiums (/sitemap/ground.xml)
     */
    public function venues()
    {
        $venues = Venue::all();
        $urls = [];

        foreach ($venues as $v) {
            $urls[] = [
                'loc' => $v->url,
                'lastmod' => $this->nowIso(),
                'priority' => '0.75',
                'changefreq' => 'monthly',
                'image' => $v->image_url ?: url('/images/logo.png'),
                'image_title' => $v->name . ' Stadium & Pitch Report'
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Web Stories (/sitemap/web-stories.xml)
     */
    public function webStories()
    {
        $stories = WebStory::orderBy('id', 'desc')->get();
        $urls = [];

        foreach ($stories as $ws) {
            $lastmod = $this->formatIso($ws->updated_at ?? $ws->created_at);
            $img = $ws->image_url ?: ($ws->first_slide_image ?: url('/images/logo.png'));

            $urls[] = [
                'loc' => $ws->url,
                'lastmod' => $lastmod,
                'priority' => '0.85',
                'changefreq' => 'daily',
                'image' => $img,
                'image_title' => $ws->title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }

    /**
     * Local Cricket Portal, Local Tournaments, Series Stats & Local Matches (/sitemap/local-cricket.xml)
     */
    public function localCricket()
    {
        $now = $this->nowIso();
        $urls = [];

        // 1. Main Local Cricket Hub
        $urls[] = [
            'loc' => url('/local'),
            'lastmod' => $now,
            'priority' => '0.90',
            'changefreq' => 'hourly',
            'image' => url('/images/logo.png'),
            'image_title' => 'CrickArena Local Cricket Hub & Live Match Scorer'
        ];

        // 2. Local Tournaments & Their 21 Dedicated Stats Pages
        $statCategories = [
            'most-runs', 'most-fours', 'most-sixes', 'most-fifties', 'most-centuries',
            'fours-innings', 'sixes-innings', 'best-strike-rates', 'highest-scores',
            'top-wickets', 'four-wickets', 'five-wickets', 'maidens', 'best-bowling-avg',
            'best-figures', 'best-economies', 'team-runs', 'team-wickets', 'team-fifties',
            'team-centuries', 'highest-team-totals'
        ];

        $tournaments = Tournament::where('status', '!=', 'draft')->orderBy('id', 'desc')->get();
        foreach ($tournaments as $t) {
            $slug = $t->slug;
            $tourUrl = route('tournament.public.slug', ['slug' => $slug, 'id' => $t->id]);
            $banner = $t->banner_url ?: ($t->poster_image ?: url('/images/logo.png'));
            $lastmod = $this->nowIso();

            // Main Local Series Page
            $urls[] = [
                'loc' => $tourUrl,
                'lastmod' => $lastmod,
                'priority' => '0.85',
                'changefreq' => 'daily',
                'image' => $banner,
                'image_title' => $t->name . ' Local Cricket Tournament Schedule & Points Table'
            ];

            // 21 Stat Categories
            foreach ($statCategories as $statKey) {
                $statUrl = route('series.stat.slug', ['slug' => $slug, 'id' => $t->id, 'stat' => $statKey]);
                $statTitle = ucwords(str_replace('-', ' ', $statKey)) . ' - ' . $t->name;
                $urls[] = [
                    'loc' => $statUrl,
                    'lastmod' => $lastmod,
                    'priority' => '0.80',
                    'changefreq' => 'daily',
                    'image' => $banner,
                    'image_title' => $statTitle
                ];
            }
        }

        // 3. Local Cricket Matches
        $localMatches = CricketMatch::with(['team1', 'team2', 'tournament'])
            ->where(function ($q) {
                $q->where('is_api_match', false)->orWhereNotNull('tournament_id');
            })
            ->where('is_approved', true)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($localMatches as $m) {
            $eff = $m->effective_status;
            $priority = ($eff === 'live') ? '0.95' : (($eff === 'upcoming') ? '0.85' : '0.75');
            $changefreq = ($eff === 'live') ? 'always' : (($eff === 'upcoming') ? 'hourly' : 'daily');
            $img = $m->team1?->logo ?: ($m->team2?->logo ?: url('/images/logo.png'));
            $tName = $m->tournament?->name ? ' (' . $m->tournament->name . ')' : '';
            $title = ($m->team1?->name ?? 'Team 1') . ' vs ' . ($m->team2?->name ?? 'Team 2') . $tName . ' Local Cricket Match';

            $urls[] = [
                'loc' => $m->url,
                'lastmod' => $this->formatIso($m->match_date),
                'priority' => $priority,
                'changefreq' => $changefreq,
                'image' => $img,
                'image_title' => $title
            ];
        }

        return $this->xmlResponse('sitemaps.urlset', compact('urls'));
    }
}
