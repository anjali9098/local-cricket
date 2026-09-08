<?php
$file = 'resources/views/layouts/admin.blade.php';
$content = file_get_contents($file);

// Remove the top header entirely
$content = preg_replace('/<header class="admin-header">.*?<\/header>/s', '', $content);

// Move the logout button to the sidebar
$sidebarEnd = '        </nav>
    </aside>';
    
$logoutHtml = '
            <!-- Logout / Profile -->
            <div style="margin-top: auto; padding: 16px; border-top: 1px solid #1e293b;">
                <div style="color: white; font-weight: 700; font-size: 0.9rem; margin-bottom: 2px;">{{ Auth::user()->name }}</div>
                <div style="color: var(--admin-primary); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 12px;">Super Admin</div>
                <form method="POST" action="{{ route(\'logout\') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: transparent; border: 1px solid #334155; color: #94a3b8; width: 100%; padding: 8px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.2s;">Logout</button>
                </form>
            </div>
        </nav>
    </aside>';

$content = str_replace($sidebarEnd, $logoutHtml, $content);
file_put_contents($file, $content);
echo "Layout updated";
