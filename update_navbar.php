<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// Replace nav menu
$oldMenu = '/<ul class="nav-menu">.*?<\/ul>/is';
$newMenu = '<ul class="nav-menu">
                <li><a href="{{ route(\'home\') }}" class="nav-link {{ request()->routeIs(\'home\') ? \'active\' : \'\' }}">Home</a></li>
                <li><a href="#" class="nav-link">Leaderboard</a></li>
            </ul>';
$content = preg_replace($oldMenu, $newMenu, $content);

// Replace navbar right
$oldRight = '/<div class="navbar-right">.*?<\/div>\s*<\/div>\s*<\/nav>/is';
$newRight = '<div class="navbar-right" style="display:flex; align-items:center; gap:20px;">
                @auth
                    @if(Auth::user()->role === \'admin\' || Auth::user()->role === \'superadmin\')
                        <a href="{{ route(\'admin.dashboard\') }}" class="nav-link" style="display:flex; align-items:center; gap:6px; font-weight:600; color:var(--text-main); text-decoration:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route(\'local.dashboard\') }}" class="nav-link" style="display:flex; align-items:center; gap:6px; font-weight:600; color:var(--text-main); text-decoration:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            Dashboard
                        </a>
                    @endif
                    <form method="POST" action="{{ route(\'logout\') }}" style="display:inline; margin:0;">
                        @csrf
                        <button type="submit" style="background:none; border:1px solid #e2e8f0; border-radius:8px; padding:6px 10px; cursor:pointer; color:var(--text-main); display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
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
