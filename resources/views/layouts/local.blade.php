<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Local Dashboard — CricketKaScore' }}</title>
    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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
    <style>
        :root {
            /* Default variables (Dark Mode) */
            --local-bg: #000000;
            --local-text: #f8fafc;
            --local-green: #38bdf8;
            --local-border: #30363d;
            
            --bg-card: #0d1117;
            --bg-card-hover: #161b22;
            --bg-card-secondary: #161b22;
            --border-color: #30363d;
            --text-main: #f8fafc;
            --text-dim: #8b949e;
            --text-muted: #6e7681;
            --primary: #2563eb;
            --radius-lg: 12px;
            --shadow-md: 0 4px 12px rgba(0,0,0,0.25);
            --font-heading: 'Inter', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        /* Light Theme Overrides */
        body.light-theme, html.light-theme {
            --local-bg: #f8fafc;
            --local-text: #0f172a;
            --local-green: #2563eb;
            --local-border: #e2e8f0;
            
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --bg-card-secondary: #f1f5f9;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-dim: #64748b;
            --text-muted: #94a3b8;
            --primary: #2563eb;
            --shadow-md: 0 4px 6px rgba(0,0,0,0.02);
        }
        
        body {
            background-color: var(--local-bg);
            color: var(--local-text);
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .local-navbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .local-navbar-container {
            width: 100%;
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 16px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        @media (min-width: 640px) {
            .local-navbar-container {
                height: 68px;
                padding: 0 24px;
            }
        }
        
        .local-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--local-text);
            font-weight: 900;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
        }
        
        .local-nav-center {
            display: flex;
            gap: 28px;
        }
        
        .local-nav-center a {
            text-decoration: none;
            color: var(--text-dim);
            font-weight: 700;
            font-size: 0.92rem;
            transition: color 0.2s;
        }
        
        .local-nav-center a:hover, .local-nav-center a.active {
            color: #38bdf8;
        }
        
        .local-nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .local-btn-dash {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--local-text);
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .local-btn-logout {
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .local-btn-logout:hover {
            background: var(--bg-card-hover);
        }
        
        .cricket-table th {
            background: var(--bg-card-secondary) !important;
            color: var(--text-main) !important;
            border-bottom: 1px solid var(--border-color) !important;
            font-weight: 800 !important;
        }
        .cricket-table td {
            color: var(--text-main) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }
        .cricket-table tr:hover td {
            background: var(--bg-card-hover) !important;
        }
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-main) !important;
        }
        
        /* Premium Tab Buttons Overrides */
        .series-tab {
            background: var(--bg-card-secondary) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-dim) !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            padding: 8px 18px !important;
            border-radius: 8px !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .series-tab:hover {
            color: var(--text-main) !important;
            background: var(--bg-card-hover) !important;
        }
        .series-tab.active {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        /* Modal & Form Elements Theme Support */
        #createModal > div {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
        }
        #createModal h2, #createModal h3, #createModal label {
            color: var(--text-main) !important;
        }
        #createModal input, #createModal select, #createModal textarea {
            background: var(--bg-card-secondary) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
        }
        #createModal input::placeholder, #createModal textarea::placeholder {
            color: var(--text-dim) !important;
        }

        /* Light Theme Card and Utility Overrides for Local Views */
        body.light-theme .bg-\[\#0d1117\],
        body.light-theme .bg-\[\#000000\],
        body.light-theme .bg-black {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
        }
        body.light-theme .bg-\[\#161b22\],
        body.light-theme .bg-\[\#161b22\]\/50,
        body.light-theme .bg-\[\#21262d\] {
            background-color: var(--bg-card-secondary) !important;
            border-color: var(--border-color) !important;
        }
        body.light-theme .border-\[\#30363d\] {
            border-color: var(--border-color) !important;
        }
        body.light-theme .text-white:not(.bg-blue-600 *):not(.bg-emerald-600 *):not(.bg-red-600 *):not(.badge-live *):not(.series-tab.active *):not(.bg-blue-600):not(.bg-emerald-600):not(.bg-red-600):not(.badge-live):not(.series-tab.active) {
            color: var(--text-main) !important;
        }
        body.light-theme .text-gray-300,
        body.light-theme .text-gray-400 {
            color: var(--text-dim) !important;
        }
    </style>
</head>
<body class="min-h-screen pb-8">

    <!-- Local Admin Navbar -->
    <nav class="local-navbar">
        <div class="local-navbar-container">
            <a href="{{ route('home') }}" class="local-brand">
                <img src="{{ asset('images/logo.png') }}" alt="CricketKaScore" class="w-8 h-8 sm:w-9 sm:h-9 object-contain rounded-lg bg-white p-0.5 shadow-sm">
                <div class="font-black text-base sm:text-lg tracking-tight" style="color: var(--local-text, var(--text-main, #ffffff));">CRICKET<span style="color: #38bdf8;">KASCORE</span></div>
            </a>

            <!-- Desktop Nav: Only Home and Dashboard -->
            <div class="local-nav-center hidden md:flex">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('local.dashboard') }}" class="{{ request()->routeIs('local.*') ? 'active' : '' }}">Dashboard</a>
            </div>

            <div class="local-nav-right">
                <button type="button" onclick="toggleTheme()" class="theme-toggle text-lg text-gray-400 hover:text-white p-1.5 transition-colors" title="Toggle Light/Dark Mode">
                    <span id="theme-icon">☀️</span>
                </button>
                @auth
                    <div class="flex items-center gap-2">
                        <span class="hidden sm:inline font-bold text-xs sm:text-sm text-gray-300">{{ Auth::user()->name }}</span>
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600/30 text-sky-400 font-bold text-xs flex items-center justify-center border border-blue-500/40">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0 flex items-center">
                        @csrf
                        <button type="submit" class="local-btn-logout w-8 h-8 sm:w-9 sm:h-9 p-0 rounded-lg flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/5 border border-[#30363d] transition-all" title="Logout">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-bold text-xs sm:text-sm text-gray-300 hover:text-white px-2.5 py-1.5">Sign In</a>
                    <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-3.5 py-1.5 rounded-lg font-bold text-xs sm:text-sm transition-all shadow-sm">Register</a>
                @endauth
            </div>
        </div>

        <!-- Mobile Dedicated Nav Bar: ONLY Home and Dashboard -->
        <div class="md:hidden flex items-center gap-2 px-3 py-2 border-t border-[var(--border-color)] bg-[var(--bg-card)]">
            <a href="{{ route('home') }}" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-lg font-bold text-xs transition-all {{ request()->routeIs('home') ? 'bg-blue-600 text-white shadow-sm' : 'bg-[var(--bg-card-secondary)] text-[var(--text-main)] hover:bg-[var(--bg-card-hover)] border border-[var(--border-color)]' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Home</span>
            </a>
            <a href="{{ route('local.dashboard') }}" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-lg font-bold text-xs transition-all {{ request()->routeIs('local.*') ? 'bg-blue-600 text-white shadow-sm' : 'bg-[var(--bg-card-secondary)] text-[var(--text-main)] hover:bg-[var(--bg-card-hover)] border border-[var(--border-color)]' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Dashboard</span>
            </a>
        </div>
    </nav>

    @yield('content')

    <!-- Toast Notification -->
    <div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;">
        @if(session('success'))
            <div class="toast-alert" style="background: #10b981; color: white; padding: 14px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: space-between; min-width: 280px; font-weight: 700; font-size: 0.9rem; transform: translateY(100px); opacity: 0; transition: all 0.3s ease-out;">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8; margin-left: 12px;">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="toast-alert" style="background: #ef4444; color: white; padding: 14px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: space-between; min-width: 280px; font-weight: 700; font-size: 0.9rem; transform: translateY(100px); opacity: 0; transition: all 0.3s ease-out;">
                <span>✗ {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8; margin-left: 12px;">&times;</button>
            </div>
        @endif
    </div>
    
    <script>
        function toggleTheme() {
            const isLight = document.body.classList.toggle('light-theme');
            document.documentElement.classList.toggle('light-theme', isLight);
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) themeIcon.innerText = isLight ? '🌙' : '☀️';
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

            const toasts = document.querySelectorAll('.toast-alert');
            toasts.forEach(toast => {
                setTimeout(() => {
                    toast.style.transform = 'translateY(0)';
                    toast.style.opacity = '1';
                }, 100);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(20px)';
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            });
        });
    </script>
</body>
</html>
