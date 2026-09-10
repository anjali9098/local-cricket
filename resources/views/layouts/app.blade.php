<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'CricketKaScore — Live Scores, Series & Local Cricket' }}</title>
    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Tailwind CSS (Full Responsive & Utility System) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                container: false
            },
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'cricket-dark': '#000000',
                        'cricket-card': '#0d1117',
                        'cricket-card-hover': '#161b22',
                        'cricket-border': '#30363d',
                        'cricket-blue': '#2563eb',
                        'cricket-sky': '#38bdf8',
                        'cricket-green': '#22c55e',
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-theme');
        }
    </script>
</head>
<body>

    @if(!request()->is('admin*'))
    <!-- Main Lovable Navbar -->
    <nav class="main-navbar">
        <div class="navbar-container">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="brand-logo" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <img src="{{ asset('images/logo.png') }}" alt="CricketKaScore" class="w-8 h-8 sm:w-9 sm:h-9 object-contain rounded-lg bg-white p-0.5 shadow-sm">
                <div class="brand-text font-black text-base sm:text-lg tracking-tight" style="color: var(--text-main);">CRICKET<span style="color: #38bdf8;">KASCORE</span></div>
            </a>

            <!-- Nav Links (Hidden on mobile/tablet, accessible in side menu) -->
            <ul class="nav-menu hidden lg:flex">
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('live') }}" class="nav-link {{ request()->routeIs('live') ? 'active' : '' }}">Live</a></li>
                <li><a href="{{ route('matches') }}" class="nav-link {{ request()->routeIs('matches') ? 'active' : '' }}">Matches</a></li>
                <li><a href="{{ route('stats') }}" class="nav-link {{ request()->routeIs('stats') ? 'active' : '' }}">Stats</a></li>
                <li><a href="{{ route('tournaments') }}" class="nav-link {{ request()->routeIs('tournaments') ? 'active' : '' }}">Tournaments</a></li>
                <li><a href="{{ route('news') }}" class="nav-link {{ request()->routeIs('news') && (!request('type') || request('type') === 'news') ? 'active' : '' }}">News</a></li>
                <li><a href="{{ route('local.dashboard') }}" class="nav-link {{ request()->routeIs('local.*') ? 'active' : '' }}">Local Cricket</a></li>
                <li><a href="{{ route('compare') }}" class="nav-link {{ request()->routeIs('compare') ? 'active' : '' }}">Compare</a></li>
            </ul>

            <!-- Actions & Three-Dots Menu -->
            <div class="navbar-right" style="display: flex; align-items: center; gap: 6px;">
                <!-- Live Search Trigger Button -->
                <button type="button" onclick="openSearchModal()" class="search-trigger" title="Live Search" style="width: 34px; height: 34px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
                <button type="button" onclick="toggleTheme()" class="theme-toggle" title="Toggle Light/Dark Mode" style="background:none; border:none; cursor:pointer; font-size:1.1rem; color:var(--text-muted); padding:0 4px;">
                    <span id="theme-icon">☀️</span>
                </button>

                <!-- Three-Dots (Kebab) Side Drawer Trigger Button -->
                <button type="button" id="sideMenuTriggerBtn" onclick="openSideMenu()" class="nav-more-btn" title="Open Menu" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="5" r="2.2"></circle>
                        <circle cx="12" cy="12" r="2.2"></circle>
                        <circle cx="12" cy="19" r="2.2"></circle>
                    </svg>
                </button>

                @auth
                    <a href="{{ route('local.dashboard') }}" class="user-avatar-btn" title="Dashboard ({{ Auth::user()->name }})">
                        <div class="user-avatar-img">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline; margin: 0;">
                        @csrf
                        <button type="submit" class="navbar-logout-btn" title="Logout" style="height: 32px; padding: 0 8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-signin" style="padding: 6px 14px; font-size: 0.8rem;">Sign in</a>
                @endauth
            </div>
        </div>

        <!-- Mobile Scrollable Category / Nav Bar (Visible on < 1024px / Mobile & Tablet) -->
        <div class="mobile-nav-strip lg:hidden">
            <div class="mobile-nav-scroll" id="mobileNavScroll">
                <a href="{{ route('home') }}" class="mobile-nav-pill {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('live') }}" class="mobile-nav-pill {{ request()->routeIs('live') ? 'active' : '' }}">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>Live
                </a>
                <a href="{{ route('matches') }}" class="mobile-nav-pill {{ request()->routeIs('matches*') ? 'active' : '' }}">Matches</a>
                <a href="{{ route('stats') }}" class="mobile-nav-pill {{ request()->routeIs('stats') ? 'active' : '' }}">Stats</a>
                <a href="{{ route('tournaments') }}" class="mobile-nav-pill {{ request()->routeIs('tournaments*') ? 'active' : '' }}">Tournaments</a>
                <a href="{{ route('news') }}" class="mobile-nav-pill {{ request()->routeIs('news*') && (!request('type') || request('type') === 'news') ? 'active' : '' }}">News</a>
                <a href="{{ route('local.dashboard') }}" class="mobile-nav-pill {{ request()->routeIs('local.*') ? 'active' : '' }}">Local Cricket</a>
                <a href="{{ route('compare') }}" class="mobile-nav-pill {{ request()->routeIs('compare') ? 'active' : '' }}">Compare</a>
            </div>
        </div>
    </nav>
    @endif

    <!-- Offcanvas Side Drawer Backdrop -->
    <div id="sideMenuBackdrop" onclick="closeSideMenu()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(6px); z-index: 99998; opacity: 0; transition: opacity 0.3s ease;"></div>

    <!-- Offcanvas Side Drawer Panel -->
    <div id="sideMenuDrawer" style="position: fixed; top: 0; right: -100%; width: 360px; max-width: 88vw; height: 100vh; background: var(--bg-card); border-left: 1px solid var(--border-color); z-index: 99999; box-shadow: -15px 0 40px rgba(0, 0, 0, 0.6); display: flex; flex-direction: column; transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1); overflow-y: auto;">
        
        <!-- Drawer Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 24px 28px 18px; border-bottom: 1px solid var(--border-color);">
            <h2 style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">
                Menu
            </h2>
            <button type="button" onclick="closeSideMenu()" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer; line-height: 1; padding: 2px 6px; border-radius: 6px; transition: color 0.2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'" title="Close Menu">&times;</button>
        </div>

        <!-- Drawer Content -->
        <div style="padding: 28px 24px; display: flex; flex-direction: column; gap: 26px; flex: 1;">
            
            <!-- 📁 CATEGORIES (2-Column Grid as in Screenshot 1) -->
            <div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 16px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    <span>CATEGORIES</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px;">
                    <a href="{{ route('news', ['type' => 'fantasy']) }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Fantasy Tips</span>
                    </a>
                    <a href="{{ route('news', ['type' => 'prediction']) }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Match Predictions</span>
                    </a>
                    <a href="{{ route('news', ['type' => 'preview']) }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Match Previews</span>
                    </a>
                    <a href="{{ route('news', ['type' => 'article']) }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Latest Articles</span>
                    </a>
                    <a href="{{ route('teams') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Popular Teams</span>
                    </a>
                    <a href="{{ route('players') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Players</span>
                    </a>
                    <a href="{{ route('venues') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Venues</span>
                    </a>
                    <a href="{{ route('webstories.all') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Web Stories</span>
                    </a>
                    <a href="{{ route('glossary.all') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Glossary Terms</span>
                    </a>
                    <a href="{{ route('stats') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span>Rankings</span>
                    </a>
                    <a href="{{ route('player.birthdays') }}" class="side-menu-link">
                        <span class="side-arrow">&rsaquo;</span>
                        <span> Player Birthdays</span>
                    </a>
                </div>
            </div>

            <div style="height: 1px; background: var(--border-color); border: none; margin: 0; opacity: 0.6;"></div>

            <!-- 📄 PAGES (2-Column Grid as in Screenshot 1) -->
            <div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 16px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>PAGES</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px;">
                    <a href="{{ route('home') }}" class="side-menu-link">
                        <span style="font-size: 0.95rem;">ⓘ</span>
                        <span>About Us</span>
                    </a>
                    <a href="{{ route('home') }}" class="side-menu-link">
                        <span style="font-size: 0.95rem;">📞</span>
                        <span>Contact Us</span>
                    </a>
                    <a href="{{ route('home') }}" class="side-menu-link">
                        <span style="font-size: 0.95rem;">🛡️</span>
                        <span>Privacy Policy</span>
                    </a>
                    <a href="{{ route('home') }}" class="side-menu-link">
                        <span style="font-size: 0.95rem;">📜</span>
                        <span>Terms &amp; Cond.</span>
                    </a>
                </div>
            </div>

            <div style="height: 1px; background: var(--border-color); border: none; margin: 0; opacity: 0.6;"></div>

            <!-- 🌐 FOLLOW US (Social Circles as in Screenshot 1) -->
            <div>
                <div style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 14px;">
                    FOLLOW US
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="#" class="side-social-icon" title="Facebook">FB</a>
                    <a href="#" class="side-social-icon" title="Twitter / X">X</a>
                    <a href="#" class="side-social-icon" title="Instagram">IG</a>
                    <a href="#" class="side-social-icon" title="YouTube">YT</a>
                </div>
            </div>

            <div style="height: 1px; background: var(--border-color); border: none; margin: 0; opacity: 0.6;"></div>

            <!-- 👤 ACCOUNT / LOGOUT IN SIDE MENU -->
            @auth
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--bg-card-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                        <div class="user-avatar-img" style="width: 34px; height: 34px; font-size: 0.95rem;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->name }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Logged in User</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('local.dashboard') }}" style="flex: 1; padding: 9px 12px; background: #2563eb; color: white; border-radius: 8px; font-weight: 700; font-size: 0.82rem; text-align: center; text-decoration: none;">Local Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" style="padding: 9px 16px; background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer;">Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('login') }}" style="flex: 1; padding: 10px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-align: center; text-decoration: none;">Sign In</a>
                    <a href="{{ route('register') }}" style="flex: 1; padding: 10px; background: #2563eb; color: white; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-align: center; text-decoration: none;">Register</a>
                </div>
            @endauth

        </div>
    </div>

    <!-- Real-Time Universal Live Search Modal -->
    <div id="searchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(11, 15, 23, 0.82); z-index:9999; backdrop-filter:blur(8px); align-items:flex-start; justify-content:center; padding-top:60px; padding-bottom:40px; overflow-y:auto;" onclick="if(event.target===this) closeSearchModal();">
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:18px; max-width:720px; width:94%; padding:24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: modalSlideDown 0.2s ease;" onclick="event.stopPropagation();">
            
            <!-- Modal Header -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:1.3rem;">🔍</span>
                    <h3 style="font-size:1.1rem; font-weight:900; color:var(--text-main); margin:0; letter-spacing:-0.01em;">
                        Universal Cricket Search
                    </h3>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <kbd style="font-size:0.7rem; font-weight:800; background:var(--bg-card-secondary); color:var(--text-dim); padding:3px 7px; border-radius:5px; border:1px solid var(--border-color);">ESC</kbd>
                    <button type="button" onclick="closeSearchModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; line-height:1; padding:0 4px;" title="Close (Esc)">&times;</button>
                </div>
            </div>

            <!-- Search Input Form -->
            <form id="globalSearchForm" action="{{ route('search') }}" method="GET" style="position:relative; margin-bottom:14px;" onsubmit="handleSearchSubmit(event)">
                <input type="text" id="liveSearchInput" name="q" oninput="handleLiveSearchInput()" placeholder="Search players, teams, matches, series, news, articles, venues..." style="width:100%; padding:14px 100px 14px 44px; background:var(--bg-card-secondary); border:2px solid var(--border-color); border-radius:12px; color:var(--text-main); font-size:1rem; font-weight:600; outline:none; transition:border-color 0.2s;" autofocus autocomplete="off">
                <div style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:1.05rem; pointer-events:none;">
                    🔍
                </div>
                <button type="button" id="searchClearBtn" onclick="clearLiveSearch()" style="display:none; position:absolute; right:74px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-dim); font-size:1.1rem; cursor:pointer; padding:4px;">
                    &times;
                </button>
                <button type="submit" style="position:absolute; right:6px; top:50%; transform:translateY(-50%); padding:8px 14px; background:#0284c7; color:white; border:none; border-radius:8px; font-weight:800; font-size:0.82rem; cursor:pointer;">
                    Search
                </button>
            </form>

            <!-- Quick Category Filter Chips Inside Modal -->
            <div id="modalCategoryFilter" style="display:flex; gap:6px; overflow-x:auto; padding-bottom:10px; margin-bottom:12px; scrollbar-width:none;">
                <button type="button" onclick="setSearchFilter('all', this)" class="modal-filter-chip active" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:none; cursor:pointer; background:#0284c7; color:white; white-space:nowrap;">All</button>
                <button type="button" onclick="setSearchFilter('players', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">🏏 Players</button>
                <button type="button" onclick="setSearchFilter('teams', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">🛡️ Teams</button>
                <button type="button" onclick="setSearchFilter('matches', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">⚡ Matches</button>
                <button type="button" onclick="setSearchFilter('tournaments', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">🏆 Series</button>
                <button type="button" onclick="setSearchFilter('articles', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">📝 Articles</button>
                <button type="button" onclick="setSearchFilter('news', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">📰 News</button>
                <button type="button" onclick="setSearchFilter('predictions', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">🎯 Predictions</button>
                <button type="button" onclick="setSearchFilter('venues', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">🏟️ Venues</button>
                <button type="button" onclick="setSearchFilter('web_stories', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">📱 Stories</button>
                <button type="button" onclick="setSearchFilter('glossary', this)" class="modal-filter-chip" style="padding:5px 12px; border-radius:16px; font-size:0.75rem; font-weight:700; border:1px solid var(--border-color); cursor:pointer; background:var(--bg-card-secondary); color:var(--text-muted); white-space:nowrap;">📖 Glossary</button>
            </div>

            <!-- Results Container -->
            <div id="searchModalResults" style="max-height:420px; overflow-y:auto; padding-right:4px;">
                <div style="text-align:center; padding:28px 16px; color:var(--text-muted);">
                    <div style="font-size:2rem; margin-bottom:8px;">🏏</div>
                    <p style="font-size:0.88rem; font-weight:600; margin-bottom:12px;">Type any player, team, match, series or article to get instant results...</p>
                    <div style="display:flex; justify-content:center; flex-wrap:wrap; gap:6px;">
                        <span onclick="applyQuickSearch('India')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">India</span>
                        <span onclick="applyQuickSearch('Virat Kohli')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Virat Kohli</span>
                        <span onclick="applyQuickSearch('Rohit Sharma')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Rohit Sharma</span>
                        <span onclick="applyQuickSearch('Mumbai Indians')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Mumbai Indians</span>
                        <span onclick="applyQuickSearch('T20')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">T20</span>
                        <span onclick="applyQuickSearch('Yorker')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Yorker</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer Info -->
            <div id="searchModalFooter" style="display:none; margin-top:14px; padding-top:12px; border-top:1px solid var(--border-color); align-items:center; justify-content:space-between;">
                <span id="searchResultCountText" style="font-size:0.78rem; font-weight:700; color:var(--text-muted);"></span>
                <a id="searchViewAllLink" href="#" style="font-size:0.82rem; font-weight:800; color:#38bdf8; text-decoration:none;">
                    View all results on search page &rarr;
                </a>
            </div>

        </div>
    </div>

    @yield('content')

    <!-- Site Footer -->
    @if(!request()->is('admin*'))
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="{{ route('home') }}" class="brand-logo" style="margin-bottom: 12px; display:inline-flex;">
                        <div class="brand-icon-box" style="width:28px;height:28px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">🏏</div>
                        <div class="brand-text" style="font-size:1.1rem;">CRICKET<span>KASCORE</span></div>
                    </a>
                    <p>
                        International, domestic, league and local gully cricket in one place.
                        Score ball-by-ball and watch career stats build automatically on Laravel framework.
                    </p>
                </div>

                <div>
                    <h4 class="footer-heading">Cricket Hub</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('players') }}">Popular Players</a></li>
                        <li><a href="{{ route('teams') }}">Popular Teams</a></li>
                        <li><a href="{{ route('venues') }}">Cricket Venues</a></li>
                        <li><a href="{{ route('webstories.all') }}">Web Stories</a></li>
                        <li><a href="{{ route('glossary.all') }}">Cricket Glossary</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Features</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('local.dashboard') }}">CrickArena Local Hub</a></li>
                        <li><a href="{{ route('stats') }}">Rankings & Stats</a></li>
                        <li><a href="{{ route('news') }}">Predictions & Fantasy</a></li>
                        <li><a href="{{ route('compare') }}">Compare Players ⚡</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Account & Admin</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('local.dashboard') }}">My Tournaments</a></li>
                        <li><a href="{{ route('login') }}">Sign In / Register</a></li>
                        <li><a href="{{ route('admin.dashboard') }}">Super Admin Panel</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom-bar">
                <p>&copy; {{ date('Y') }} CricketKaScore &bull; Every Ball Counts.</p>
                <div style="display:flex;gap:16px;font-size:0.8rem;color:var(--text-dim);">
                    <a href="{{ route('local.dashboard') }}">Local Scorer</a>
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar (For Native App Feel) -->
    <nav class="mobile-bottom-bar" aria-label="Mobile Navigation">
        <a href="{{ route('home') }}" class="mobile-bottom-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Home</span>
        </a>
        <a href="{{ route('live') }}" class="mobile-bottom-item {{ request()->routeIs('live') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
            <span>Live</span>
        </a>
        <a href="{{ route('matches') }}" class="mobile-bottom-item {{ request()->routeIs('matches*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span>Matches</span>
        </a>
        <a href="{{ route('local.dashboard') }}" class="mobile-bottom-item {{ request()->routeIs('local.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.45 1-1 1H7c-.55 0-1-.45-1-1v-2.34"></path><path d="M18 14.66V17c0 .55-.45 1-1 1h-2c-.55 0-1-.45-1-1v-2.34"></path><path d="M12 2v20"></path></svg>
            <span>Local</span>
        </a>
        <button type="button" onclick="openSideMenu()" class="mobile-bottom-item" style="background:none;border:none;cursor:pointer;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            <span>Menu</span>
        </button>
    </nav>
    @endif

    <script>
    // Universal Live Search Engine
    let searchDebounceTimer = null;
    let currentSearchCategory = 'all';
    let lastSearchResults = null;

    function openSearchModal() {
        const modal = document.getElementById('searchModal');
        if (modal) {
            modal.style.display = 'flex';
            const input = document.getElementById('liveSearchInput');
            if (input) {
                setTimeout(() => {
                    input.focus();
                    input.select();
                }, 80);
            }
        }
    }

    function closeSearchModal() {
        const modal = document.getElementById('searchModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function clearLiveSearch() {
        const input = document.getElementById('liveSearchInput');
        if (input) {
            input.value = '';
            document.getElementById('searchClearBtn').style.display = 'none';
            input.focus();
            renderDefaultSearchState();
        }
    }

    function applyQuickSearch(term) {
        const input = document.getElementById('liveSearchInput');
        if (input) {
            input.value = term;
            handleLiveSearchInput();
        }
    }

    function setSearchFilter(cat, btn) {
        currentSearchCategory = cat;
        document.querySelectorAll('.modal-filter-chip').forEach(b => {
            b.style.background = 'var(--bg-card-secondary)';
            b.style.color = 'var(--text-muted)';
            b.style.border = '1px solid var(--border-color)';
        });
        btn.style.background = '#0284c7';
        btn.style.color = 'white';
        btn.style.border = 'none';

        if (lastSearchResults) {
            renderSearchResults(lastSearchResults, document.getElementById('liveSearchInput').value.trim());
        }
    }

    function handleLiveSearchInput() {
        const query = document.getElementById('liveSearchInput').value.trim();
        const clearBtn = document.getElementById('searchClearBtn');
        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'block' : 'none';
        }

        clearTimeout(searchDebounceTimer);

        if (query.length === 0) {
            renderDefaultSearchState();
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            performLiveSearch(query);
        }, 180);
    }

    function handleSearchSubmit(e) {
        const query = document.getElementById('liveSearchInput').value.trim();
        if (!query) {
            e.preventDefault();
        }
    }

    function renderDefaultSearchState() {
        const resultsDiv = document.getElementById('searchModalResults');
        const footer = document.getElementById('searchModalFooter');
        if (footer) footer.style.display = 'none';

        resultsDiv.innerHTML = `
            <div style="text-align:center; padding:28px 16px; color:var(--text-muted);">
                <div style="font-size:2rem; margin-bottom:8px;">🏏</div>
                <p style="font-size:0.88rem; font-weight:600; margin-bottom:12px;">Type any player, team, match, series or article to get instant results...</p>
                <div style="display:flex; justify-content:center; flex-wrap:wrap; gap:6px;">
                    <span onclick="applyQuickSearch('India')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">India</span>
                    <span onclick="applyQuickSearch('Virat Kohli')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Virat Kohli</span>
                    <span onclick="applyQuickSearch('Rohit Sharma')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Rohit Sharma</span>
                    <span onclick="applyQuickSearch('Mumbai Indians')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Mumbai Indians</span>
                    <span onclick="applyQuickSearch('T20')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">T20</span>
                    <span onclick="applyQuickSearch('Yorker')" style="cursor:pointer; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:0.75rem; font-weight:700; color:var(--text-main); padding:4px 10px; border-radius:12px;">Yorker</span>
                </div>
            </div>
        `;
    }

    function performLiveSearch(query) {
        const resultsDiv = document.getElementById('searchModalResults');
        resultsDiv.innerHTML = `
            <div style="text-align:center; padding:30px; color:var(--text-dim); font-size:0.9rem; display:flex; align-items:center; justify-content:center; gap:8px;">
                <span style="display:inline-block; width:16px; height:16px; border:2px solid #0284c7; border-top-color:transparent; border-radius:50%; animation:spin 0.6s linear infinite;"></span>
                Searching CricketKaScore...
            </div>
        `;

        fetch('/api/search?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                lastSearchResults = data.results;
                renderSearchResults(data.results, query, data.total_count);
            })
            .catch(err => {
                resultsDiv.innerHTML = '<p style="color:#ef4444; font-size:0.85rem; text-align:center; padding:20px;">Failed to fetch results. Please try again.</p>';
            });
    }

    function renderSearchResults(results, query, totalCount) {
        const resultsDiv = document.getElementById('searchModalResults');
        const footer = document.getElementById('searchModalFooter');
        const countText = document.getElementById('searchResultCountText');
        const viewAllLink = document.getElementById('searchViewAllLink');

        let itemsToRender = [];

        if (currentSearchCategory === 'all') {
            itemsToRender = results.all || [];
        } else if (results[currentSearchCategory]) {
            itemsToRender = results[currentSearchCategory];
        }

        if (itemsToRender.length === 0) {
            resultsDiv.innerHTML = `
                <div style="text-align:center; padding:30px 16px;">
                    <div style="font-size:1.8rem; margin-bottom:6px;">🔍</div>
                    <div style="font-size:0.95rem; font-weight:800; color:var(--text-main);">No results found for "${query}"</div>
                    <p style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Try searching by another player, team or keyword, or press Enter for full search.</p>
                </div>
            `;
            if (footer) footer.style.display = 'none';
            return;
        }

        let html = '<div style="display:flex; flex-direction:column; gap:8px;">';

        itemsToRender.forEach(item => {
            const badgeBg = item.type === 'player' ? '#e0f2fe' : (item.type === 'team' ? '#f0fdf4' : (item.type === 'match' ? '#fef2f2' : (item.type === 'tournament' ? '#fef9c3' : '#f3e8ff')));
            const badgeColor = item.type === 'player' ? '#0369a1' : (item.type === 'team' ? '#15803d' : (item.type === 'match' ? '#b91c1c' : (item.type === 'tournament' ? '#854d0e' : '#6b21a8')));

            let imgHtml = '';
            if (item.image) {
                imgHtml = `<img src="${item.image}" alt="${item.title}" style="width:38px; height:38px; border-radius:8px; object-fit:cover; flex-shrink:0;">`;
            } else if (item.initials) {
                imgHtml = `<div style="width:38px; height:38px; border-radius:8px; background:var(--bg-card-secondary); border:1px solid var(--border-color); color:#38bdf8; font-weight:800; font-size:0.85rem; display:flex; align-items:center; justify-content:center; flex-shrink:0;">${item.initials}</div>`;
            } else {
                const icon = item.type === 'match' ? '⚡' : (item.type === 'tournament' ? '🏆' : (item.type === 'venue' ? '🏟️' : (item.type === 'story' ? '📱' : (item.type === 'glossary' ? '📖' : '📰'))));
                imgHtml = `<div style="width:38px; height:38px; border-radius:8px; background:var(--bg-card-secondary); border:1px solid var(--border-color); font-size:1.1rem; display:flex; align-items:center; justify-content:center; flex-shrink:0;">${icon}</div>`;
            }

            html += `
                <a href="${item.url}" style="text-decoration:none; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 14px; background:var(--bg-card-secondary); border:1px solid var(--border-color); border-radius:10px; transition:all 0.15s;"
                   onmouseover="this.style.background='rgba(56, 189, 248, 0.08)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.background='var(--bg-card-secondary)'; this.style.borderColor='var(--border-color)';">
                    <div style="display:flex; align-items:center; gap:12px; overflow:hidden;">
                        ${imgHtml}
                        <div style="overflow:hidden;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="font-weight:800; font-size:0.9rem; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    ${item.title}
                                </span>
                                <span style="font-size:0.65rem; font-weight:800; padding:1px 6px; border-radius:4px; background:${badgeBg}; color:${badgeColor}; white-space:nowrap;">
                                    ${item.badge || item.type_label}
                                </span>
                            </div>
                            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                ${item.subtitle || ''}
                            </div>
                        </div>
                    </div>
                    <span style="color:var(--text-dim); font-size:0.9rem; font-weight:700; flex-shrink:0;">&rarr;</span>
                </a>
            `;
        });

        html += '</div>';
        resultsDiv.innerHTML = html;

        if (footer && viewAllLink) {
            footer.style.display = 'flex';
            if (countText) {
                countText.innerText = `${itemsToRender.length} result${itemsToRender.length > 1 ? 's' : ''} shown`;
            }
            viewAllLink.href = `/search?q=${encodeURIComponent(query)}&type=${currentSearchCategory}`;
        }
    }

    // Three-Dots (Kebab) Dropdown Handler
    function toggleMoreMenu(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        const dropdown = document.getElementById('moreMenuDropdown');
        const btn = document.getElementById('moreMenuBtn');
        if (!dropdown) return;
        
        const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';
        if (isHidden) {
            dropdown.style.display = 'block';
            if (btn) {
                btn.setAttribute('aria-expanded', 'true');
                btn.style.background = 'rgba(56, 189, 248, 0.15)';
                btn.style.borderColor = '#38bdf8';
                btn.style.color = '#38bdf8';
            }
        } else {
            dropdown.style.display = 'none';
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
                btn.style.background = 'var(--bg-card-secondary)';
                btn.style.borderColor = 'var(--border-color)';
                btn.style.color = 'var(--text-main)';
            }
        }
    }

    function closeMoreMenu() {
        const dropdown = document.getElementById('moreMenuDropdown');
        const btn = document.getElementById('moreMenuBtn');
        if (dropdown && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
                btn.style.background = 'var(--bg-card-secondary)';
                btn.style.borderColor = 'var(--border-color)';
                btn.style.color = 'var(--text-main)';
            }
        }
    }

    // Close more menu when clicking outside
    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('moreMenuDropdown');
        const btn = document.getElementById('moreMenuBtn');
        if (dropdown && !dropdown.contains(e.target) && e.target !== btn && !btn?.contains(e.target)) {
            closeMoreMenu();
        }
    });

    // Offcanvas Side Drawer (Matching Screenshot 1)
    function openSideMenu() {
        const drawer = document.getElementById('sideMenuDrawer');
        const backdrop = document.getElementById('sideMenuBackdrop');
        if (drawer && backdrop) {
            backdrop.style.display = 'block';
            setTimeout(() => { backdrop.style.opacity = '1'; }, 10);
            drawer.style.right = '0';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSideMenu() {
        const drawer = document.getElementById('sideMenuDrawer');
        const backdrop = document.getElementById('sideMenuBackdrop');
        if (drawer && backdrop) {
            drawer.style.right = '-100%';
            backdrop.style.opacity = '0';
            setTimeout(() => { backdrop.style.display = 'none'; }, 300);
            document.body.style.overflow = '';
        }
    }

    // Keyboard Shortcuts (Ctrl+K or / to open search, Esc to close modals & menus)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openSearchModal();
        } else if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            openSearchModal();
        } else if (e.key === 'Escape') {
            closeSearchModal();
            closeMoreMenu();
            closeSideMenu();
        }
    });

    function toggleTheme() {
        const isLight = document.body.classList.toggle('light-theme');
        document.documentElement.classList.toggle('light-theme', isLight);
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        const themeIcon = document.getElementById('theme-icon');
        if (themeIcon) themeIcon.innerText = isLight ? '🌙' : '☀️';
    }

    // Loop Engineering: Reusable Password Visibility Toggle Engine
    function initPasswordToggles() {
        const toggleButtons = document.querySelectorAll('.password-toggle-btn');
        toggleButtons.forEach(btn => {
            if (btn.dataset.toggleInitialized === 'true') return;
            btn.dataset.toggleInitialized = 'true';

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const input = targetId ? document.getElementById(targetId) : this.closest('.password-input-wrapper')?.querySelector('input');
                if (!input) return;

                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');

                const eyeShow = this.querySelector('.eye-show');
                const eyeHide = this.querySelector('.eye-hide');

                if (eyeShow && eyeHide) {
                    if (isPassword) {
                        eyeShow.style.display = 'none';
                        eyeHide.style.display = 'block';
                        this.setAttribute('title', 'Hide password');
                    } else {
                        eyeShow.style.display = 'block';
                        eyeHide.style.display = 'none';
                        this.setAttribute('title', 'Show password');
                    }
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const themeIcon = document.getElementById('theme-icon');
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-theme');
            document.documentElement.classList.add('light-theme');
            if (themeIcon) themeIcon.innerText = '🌙';
        } else {
            document.body.classList.remove('light-theme');
            document.documentElement.classList.remove('light-theme');
            if (themeIcon) themeIcon.innerText = '☀️';
        }

        // Initialize all password visibility toggles
        initPasswordToggles();

        // Auto-center active mobile nav pill into view (scrolling ONLY the nav strip container, NOT the window)
        const mobileNavScroll = document.getElementById('mobileNavScroll');
        const activeNavPill = document.querySelector('.mobile-nav-pill.active');
        if (mobileNavScroll && activeNavPill) {
            const pillLeft = activeNavPill.offsetLeft;
            const pillWidth = activeNavPill.offsetWidth;
            const containerWidth = mobileNavScroll.clientWidth;
            mobileNavScroll.scrollTo({
                left: pillLeft - (containerWidth / 2) + (pillWidth / 2),
                behavior: 'smooth'
            });
        }
    });
    </script>
</body>
</html>
