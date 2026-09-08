<?php
$file = 'resources/views/layouts/admin.blade.php';
$content = file_get_contents($file);

$toastScript = <<<HTML
        <!-- Content Area -->
        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;">
        @if(session('success'))
            <div class="toast-alert" style="background: #10b981; color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: space-between; min-width: 300px; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s ease-out;">
                <span>? {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8;">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="toast-alert" style="background: #ef4444; color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: space-between; min-width: 300px; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s ease-out;">
                <span>? {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8;">&times;</button>
            </div>
        @endif
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
HTML;

$content = str_replace("        <!-- Content Area -->\n        <main class=\"admin-content\">\n            @yield('content')\n        </main>\n    </div>", $toastScript, $content);
file_put_contents($file, $content);
echo "Admin Toast Done\n";

$localFile = 'resources/views/layouts/local.blade.php';
$localContent = file_get_contents($localFile);
$localContent = str_replace("    @yield('content')\n\n</body>", "    @yield('content')\n\n    " . str_replace("        <!-- Content Area -->\n        <main class=\"admin-content\">\n            @yield('content')\n        </main>\n    </div>\n\n    ", "", $toastScript) . "\n</body>", $localContent);
file_put_contents($localFile, $localContent);
echo "Local Toast Done\n";

// Remove old inline blocks from admin/dashboard.blade.php and admin/manage-tournament.blade.php
function removeOldAlerts($filepath) {
    if (file_exists($filepath)) {
        $text = file_get_contents($filepath);
        $text = preg_replace('/@if\(session\(\'success\'\)\).*?@endif/s', '', $text, 1);
        $text = preg_replace('/@if\(session\(\'error\'\)\).*?@endif/s', '', $text, 1);
        file_put_contents($filepath, $text);
    }
}
removeOldAlerts('resources/views/admin/dashboard.blade.php');
removeOldAlerts('resources/views/admin/manage-tournament.blade.php');
removeOldAlerts('resources/views/local/dashboard.blade.php');
removeOldAlerts('resources/views/local/manage-tournament.blade.php');
echo "Alerts Removed";
