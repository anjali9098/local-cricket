<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

$oldMenu = '/<ul class="nav-menu">.*?<\/ul>/is';
$newMenu = '<ul class="nav-menu">
                <li><a href="{{ route(\'home\') }}" class="nav-link {{ request()->routeIs(\'home\') ? \'active\' : \'\' }}">Home</a></li>
                <li><a href="{{ route(\'live\') }}" class="nav-link {{ request()->routeIs(\'live\') ? \'active\' : \'\' }}">Live</a></li>
                <li><a href="{{ route(\'matches\') }}" class="nav-link {{ request()->routeIs(\'matches\') ? \'active\' : \'\' }}">Matches</a></li>
                <li><a href="{{ route(\'stats\') }}" class="nav-link {{ request()->routeIs(\'stats\') ? \'active\' : \'\' }}">Stats</a></li>
                <li><a href="{{ route(\'tournaments\') }}" class="nav-link {{ request()->routeIs(\'tournaments\') ? \'active\' : \'\' }}">Tournaments</a></li>
                <li><a href="{{ route(\'news\') }}" class="nav-link {{ request()->routeIs(\'news\') ? \'active\' : \'\' }}">News</a></li>
                <li><a href="{{ route(\'local.dashboard\') }}" class="nav-link {{ request()->routeIs(\'local.*\') ? \'active\' : \'\' }}">Local Cricket</a></li>
                <li><a href="{{ route(\'compare\') }}" class="nav-link {{ request()->routeIs(\'compare\') ? \'active\' : \'\' }}">Compare</a></li>
            </ul>';
$content = preg_replace($oldMenu, $newMenu, $content);

$oldRight = '/<div class="navbar-right".*?<\/div>\s*<\/div>\s*<\/nav>/is';
$newRight = '<div class="navbar-right">
                <button type="button" onclick="openSearchModal()" class="search-trigger" title="Live Search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
                <button type="button" onclick="toggleTheme()" class="theme-toggle" title="Toggle Light/Dark Mode" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:var(--text-muted); padding:0 8px; margin-right: 12px;">
                    <span id="theme-icon">??</span>
                </button>

                @auth
                    @if(Auth::user()->role === \'admin\' || Auth::user()->role === \'superadmin\')
                        <a href="{{ route(\'admin.dashboard\') }}" class="tag-badge primary" style="padding: 6px 12px; font-weight:700;">Super Admin</a>
                    @endif
                    <a href="{{ route(\'local.dashboard\') }}" class="user-avatar-btn">
                        <div class="user-avatar-img">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route(\'logout\') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="tag-badge" style="padding: 6px 10px; background:transparent; border:1px solid var(--border-color); color:var(--text-muted); cursor:pointer;">Logout</button>
                    </form>
                @else
                    <a href="{{ route(\'login\') }}" class="btn-signin">Sign in</a>
                @endauth
            </div>
        </div>
    </nav>';
$content = preg_replace($oldRight, $newRight, $content);

file_put_contents($file, $content);
echo "Done";
