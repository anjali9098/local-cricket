<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — CricketKaScore</title>
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
        // Universal Image Preview and Client-side Base64 Compressor
        function previewAndConvertImage(fileInput, targetInputId, previewImgId, statusBadgeId, maxDimension = 700, quality = 0.85) {
            if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
            const file = fileInput.files[0];
            const targetInput = targetInputId ? document.getElementById(targetInputId) : null;
            const previewImg = previewImgId ? document.getElementById(previewImgId) : null;
            const statusBadge = statusBadgeId ? document.getElementById(statusBadgeId) : null;

            if (statusBadge) {
                statusBadge.innerText = '⏳ Converting...';
                statusBadge.style.display = 'inline-block';
                statusBadge.style.background = '#fef3c7';
                statusBadge.style.color = '#b45309';
                statusBadge.style.padding = '2px 8px';
                statusBadge.style.borderRadius = '4px';
                statusBadge.style.fontSize = '0.75rem';
                statusBadge.style.fontWeight = '700';
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const rawData = e.target.result;
                
                // Instant preview right away
                if (previewImg) {
                    previewImg.onerror = function() { this.style.display = 'none'; };
                    previewImg.src = rawData;
                    previewImg.style.display = 'block';
                }
                if (targetInput) {
                    targetInput.value = rawData;
                }

                const isSvgOrGif = file.type && (file.type.toLowerCase().includes('svg') || file.type.toLowerCase().includes('gif'));
                if (isSvgOrGif) {
                    if (statusBadge) {
                        statusBadge.innerText = '✓ Ready (' + Math.round(rawData.length / 1024) + ' KB)';
                        statusBadge.style.background = '#dcfce7';
                        statusBadge.style.color = '#16a34a';
                    }
                    return;
                }

                const img = new Image();
                img.onload = function() {
                    try {
                        let width = img.width;
                        let height = img.height;

                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        const optimizedBase64 = canvas.toDataURL('image/jpeg', quality);

                        if (targetInput) targetInput.value = optimizedBase64;
                        if (previewImg) {
                            previewImg.src = optimizedBase64;
                            previewImg.style.display = 'block';
                        }
                        if (statusBadge) {
                            statusBadge.innerText = '✓ Ready (' + Math.round(optimizedBase64.length / 1024) + ' KB)';
                            statusBadge.style.background = '#dcfce7';
                            statusBadge.style.color = '#16a34a';
                        }
                    } catch (err) {
                        console.warn('Canvas optimization fallback to raw:', err);
                        if (targetInput) targetInput.value = rawData;
                        if (statusBadge) {
                            statusBadge.innerText = '✓ Ready (' + Math.round(rawData.length / 1024) + ' KB)';
                            statusBadge.style.background = '#dcfce7';
                            statusBadge.style.color = '#16a34a';
                        }
                    }
                };
                img.onerror = function() {
                    if (targetInput) targetInput.value = rawData;
                    if (statusBadge) {
                        statusBadge.innerText = '✓ Ready (' + Math.round(rawData.length / 1024) + ' KB)';
                        statusBadge.style.background = '#dcfce7';
                        statusBadge.style.color = '#16a34a';
                    }
                };
                img.src = rawData;
            };
            reader.onerror = function() {
                if (statusBadge) {
                    statusBadge.innerText = '✕ Error reading file';
                    statusBadge.style.background = '#fee2e2';
                    statusBadge.style.color = '#b91c1c';
                }
            };
            reader.readAsDataURL(file);
        }

        function previewUrlImage(urlInput, previewImgId) {
            const previewImg = document.getElementById(previewImgId);
            if (!previewImg) return;
            const url = (urlInput.value || '').trim();
            if (url) {
                previewImg.onerror = function() { this.style.display = 'none'; };
                previewImg.src = url;
                previewImg.style.display = 'block';
            } else {
                previewImg.style.display = 'none';
            }
        }

        function previewAndConvertMultiImages(fileInput, targetContainerId, previewContainerId) {
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
            const previewBox = document.getElementById(previewContainerId);
            const hiddenContainer = document.getElementById(targetContainerId);
            if (hiddenContainer) hiddenContainer.innerHTML = '';
            if (previewBox) previewBox.innerHTML = '';

            Array.from(fileInput.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const rawData = e.target.result;
                    const img = new Image();
                    img.onload = function() {
                        const maxDim = 800;
                        let width = img.width, height = img.height;
                        if (width > maxDim || height > maxDim) {
                            if (width > height) { height = Math.round((height * maxDim) / width); width = maxDim; }
                            else { width = Math.round((width * maxDim) / height); height = maxDim; }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width; canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        const b64 = canvas.toDataURL('image/jpeg', 0.85);

                        if (hiddenContainer) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'slides_base64[]';
                            input.value = b64;
                            hiddenContainer.appendChild(input);
                        }
                        if (previewBox) {
                            const imgThumb = document.createElement('img');
                            imgThumb.src = b64;
                            imgThumb.style.cssText = 'width: 55px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;';
                            previewBox.appendChild(imgThumb);
                        }
                    };
                    img.onerror = function() {
                        if (hiddenContainer) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'slides_base64[]';
                            input.value = rawData;
                            hiddenContainer.appendChild(input);
                        }
                        if (previewBox) {
                            const imgThumb = document.createElement('img');
                            imgThumb.src = rawData;
                            imgThumb.style.cssText = 'width: 55px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;';
                            previewBox.appendChild(imgThumb);
                        }
                    };
                    img.src = rawData;
                };
                reader.readAsDataURL(file);
            });
        }
    </script>
    <style>
        :root {
            --admin-sidebar-bg: #0b0f17;
            --admin-sidebar-hover: #1e293b;
            --admin-sidebar-text: #94a3b8;
            --admin-sidebar-active: #ffffff;
            
            --admin-bg: #f8fafc;
            --admin-card: #ffffff;
            --admin-text-main: #0f172a;
            --admin-text-muted: #64748b;
            --admin-border: #e2e8f0;
            --admin-primary: #0ea5e9;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--admin-bg);
            color: var(--admin-text-main);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background-color: var(--admin-sidebar-bg);
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1e293b;
            overflow-y: auto;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .admin-sidebar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 2px;
        }

        .admin-brand {
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            color: white;
            font-weight: 900;
            font-size: 1.2rem;
            text-decoration: none;
            gap: 10px;
            border-bottom: 1px solid #1e293b;
            flex-shrink: 0;
        }

        .admin-nav {
            flex: 1;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            text-decoration: none;
            color: var(--admin-sidebar-text);
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .admin-nav-link:hover, .admin-nav-link.active {
            background-color: var(--admin-sidebar-hover);
            color: var(--admin-sidebar-active);
        }

        .admin-nav-link svg {
            stroke: currentColor;
        }

        /* Sub Navigation */
        .admin-subnav-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 12px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            user-select: none;
        }
        .admin-subnav-label:hover {
            color: #94a3b8;
            background: rgba(255,255,255,0.03);
        }
        .admin-subnav-label .chevron {
            transition: transform 0.3s ease;
            font-size: 0.6rem;
        }
        .admin-subnav-label.expanded .chevron {
            transform: rotate(180deg);
        }

        .admin-subnav {
            display: none;
            flex-direction: column;
            gap: 2px;
            padding: 4px 0 4px 8px;
            border-left: 2px solid #1e293b;
            margin-left: 20px;
            animation: slideDown 0.3s ease;
        }
        .admin-subnav.show {
            display: flex;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .admin-subnav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            text-decoration: none;
            color: #64748b;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.82rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        .admin-subnav-item:hover, .admin-subnav-item.active {
            background: rgba(14, 165, 233, 0.08);
            color: #38bdf8;
        }
        .admin-subnav-item .subnav-icon {
            font-size: 0.85rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        /* Main Content wrapper */
        .admin-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Navbar */
        .admin-header {
            height: 70px;
            background-color: var(--admin-card);
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 32px;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-logout-btn {
            background: transparent;
            border: 1px solid var(--admin-border);
            color: var(--admin-text-muted);
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
        }

        /* Content Scroll Area */
        .admin-content {
            flex: 1;
            overflow-y: auto;
            padding: 40px;
        }

        /* Enforce proper table layout for all admin data tables */
        .admin-content table {
            display: table !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .admin-content thead {
            display: table-header-group !important;
        }
        .admin-content tbody {
            display: table-row-group !important;
        }
        .admin-content tr {
            display: table-row !important;
        }
        .admin-content th,
        .admin-content td {
            display: table-cell !important;
            box-sizing: border-box !important;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a href="{{ route('home') }}" class="admin-brand" style="display:flex; align-items:center; gap:12px; text-decoration:none; padding: 14px 20px;">
            <img src="{{ asset('images/logo.png') }}" alt="CricketKaScore" style="height: 38px; width: 38px; object-fit: contain; border-radius: 8px; background: white; padding: 2px; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
            <div>
                <div style="font-weight:900; font-size:0.95rem; color:white; line-height:1.1; letter-spacing:-0.02em;">CRICKET<span style="color:#38bdf8;">KASCORE</span></div>
                <div style="font-size:0.65rem; color:#94a3b8; font-weight:800; letter-spacing:1px; text-transform:uppercase;">SUPER ADMIN</div>
            </div>
        </a>
        
        <nav class="admin-nav">

            <!-- Content Management Sub-Navigation -->
            <div class="admin-subnav-label expanded" onclick="toggleSubnav(this)">
                <span>📋 Content Management</span>
                <span class="chevron">▼</span>
            </div>
            <div class="admin-subnav show" id="content-subnav">
                <a href="{{ route('admin.match') }}" class="admin-subnav-item {{ request()->routeIs('admin.match') ? 'active' : '' }}">
                    <span class="subnav-icon">📺</span> Live & Upcoming Matches
                </a>
                <a href="{{ route('admin.series') }}" class="admin-subnav-item {{ request()->routeIs('admin.series') ? 'active' : '' }}">
                    <span class="subnav-icon">🏆</span> Series
                </a>
                <a href="{{ route('admin.match-preview') }}" class="admin-subnav-item {{ request()->routeIs('admin.match-preview*') ? 'active' : '' }}">
                    <span class="subnav-icon">⚡</span> Match Preview
                </a>
                <a href="{{ route('admin.prediction') }}" class="admin-subnav-item {{ request()->routeIs('admin.prediction*') || request()->routeIs('admin.fantasy*') ? 'active' : '' }}">
                    <span class="subnav-icon">🎯</span> Prediction & Fantasy Tips
                </a>
                <a href="{{ route('admin.article') }}" class="admin-subnav-item {{ request()->routeIs('admin.article') ? 'active' : '' }}">
                    <span class="subnav-icon">📰</span> Latest Articles
                </a>
                <a href="{{ route('admin.news') }}" class="admin-subnav-item {{ request()->routeIs('admin.news') ? 'active' : '' }}">
                    <span class="subnav-icon">📢</span> Latest News
                </a>
                <a href="{{ route('admin.popular') }}" class="admin-subnav-item {{ request()->routeIs('admin.popular*') || request()->routeIs('admin.teams*') ? 'active' : '' }}">
                    <span class="subnav-icon">🏏</span> Most Popular Teams
                </a>
                <a href="{{ route('admin.ranking') }}" class="admin-subnav-item {{ request()->routeIs('admin.ranking') ? 'active' : '' }}">
                    <span class="subnav-icon">📊</span> Team Rankings
                </a>
                <a href="{{ route('admin.story') }}" class="admin-subnav-item {{ request()->routeIs('admin.story') ? 'active' : '' }}">
                    <span class="subnav-icon">📖</span> Web Stories
                </a>
                <a href="{{ route('admin.glossary') }}" class="admin-subnav-item {{ request()->routeIs('admin.glossary') ? 'active' : '' }}">
                    <span class="subnav-icon">📚</span> Glossary Terms
                </a>
            </div>

            <!-- Cricket Data Sub-Navigation -->
            <div class="admin-subnav-label" onclick="toggleSubnav(this)" style="margin-top: 6px;">
                <span>🏏 Cricket Data</span>
                <span class="chevron">▶</span>
            </div>
            <div class="admin-subnav" id="cricket-data-subnav">
                <a href="{{ route('admin.players') }}" class="admin-subnav-item {{ request()->routeIs('admin.players*') ? 'active' : '' }}">
                    <span class="subnav-icon">👤</span> Players
                </a>
                <a href="{{ route('admin.venues') }}" class="admin-subnav-item {{ request()->routeIs('admin.venues*') ? 'active' : '' }}">
                    <span class="subnav-icon">🏟️</span> Venues
                </a>
            </div>

            <a href="{{ route('home') }}" class="admin-nav-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Go to Website
            </a>

            @php
                $navApprovalsCount = \App\Models\Tournament::where('category', 'local')->where('is_approved', false)->count();
                $navDeletionsCount = \App\Models\Tournament::where('category', 'local')->where('delete_requested', true)->count();
                $totalNavNotifications = $navApprovalsCount + $navDeletionsCount;
            @endphp
            <a href="{{ route('admin.notifications') }}" class="admin-nav-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="display: flex; align-items: center;">
                    <span style="font-size: 1.1rem; margin-right: 8px;">🔔</span>
                    Notifications
                </span>
                @if($totalNavNotifications > 0)
                    <span style="background: #ef4444; color: white; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 12px; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                        {{ $totalNavNotifications }}
                    </span>
                @endif
            </a>

            <!-- Logout / Profile -->
            <div style="margin-top: auto; padding: 16px; border-top: 1px solid #1e293b;">
                <div style="color: white; font-weight: 700; font-size: 0.9rem; margin-bottom: 2px;">{{ Auth::user()->name ?? 'Super Admin' }}</div>
                <div style="color: var(--admin-primary); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 12px;">Super Admin</div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: transparent; border: 1px solid #334155; color: #94a3b8; width: 100%; padding: 8px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.2s;">Logout</button>
                </form>
            </div>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <div class="admin-wrapper">
        <!-- Top Header -->
        

        <!-- Content Area -->
        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;">
        @if(session('success'))
            <div class="toast-alert" style="background: #10b981; color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: space-between; min-width: 300px; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s ease-out;">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8;">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="toast-alert" style="background: #ef4444; color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: space-between; min-width: 300px; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s ease-out;">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8;">&times;</button>
            </div>
        @endif
    </div>
    
    <script>
        function toggleSubnav(label) {
            label.classList.toggle('expanded');
            const subnav = label.nextElementSibling;
            subnav.classList.toggle('show');
        }

        /* ==========================================================
           UNIVERSAL ADMIN TABLE MANAGER (SMART FILTERING & SEARCH)
           ========================================================== */
        class AdminTableManager {
            constructor(config) {
                this.tableId = config.tableId;
                this.rowSelector = config.rowSelector || (config.rowClass ? `.${config.rowClass}` : `#${config.tableId} tbody tr:not(.no-filter-row)`);
                this.searchInputId = config.searchInputId;
                this.filterSelects = config.filterSelects || (config.filterSelectId ? [{ id: config.filterSelectId, attr: config.filterDataAttr }] : []);
                this.paginationContainerId = config.paginationContainerId || `${config.tableId}-pagination`;
                this.perPage = config.perPage || 10;
                this.currentPage = 1;
                this.colSpan = config.colSpan || 8;
                this.noResultsMsg = config.noResultsMsg || 'No matching records found.';
                this.extraFilter = config.extraFilter || null;

                this.init();
            }

            init() {
                const searchInput = document.getElementById(this.searchInputId);
                if (searchInput) {
                    searchInput.addEventListener('input', () => this.applyFilter(1));
                    searchInput.addEventListener('keyup', () => this.applyFilter(1));
                    searchInput.addEventListener('change', () => this.applyFilter(1));
                    searchInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') { e.preventDefault(); this.applyFilter(1); }
                    });
                }

                this.filterSelects.forEach(f => {
                    const el = document.getElementById(f.id);
                    if (el) {
                        el.addEventListener('change', () => this.applyFilter(1));
                        el.addEventListener('input', () => this.applyFilter(1));
                    }
                });

                // Attach to global window object
                window[`${this.tableId}_manager`] = this;

                this.applyFilter(1);
            }

            reset() {
                const searchInput = document.getElementById(this.searchInputId);
                if (searchInput) searchInput.value = '';

                this.filterSelects.forEach(f => {
                    const el = document.getElementById(f.id);
                    if (el) el.value = '';
                });

                this.applyFilter(1);
            }

            matchFilter(filterVal, rowVal, rowText) {
                if (!filterVal) return true;
                filterVal = filterVal.toLowerCase().trim();
                rowVal = (rowVal || '').toLowerCase().trim();

                // 1. Direct or substring match
                if (rowVal === filterVal || rowVal.includes(filterVal)) return true;

                // 2. Normalized (remove hyphens, spaces, underscores)
                const normFilter = filterVal.replace(/[-_\s]+/g, '');
                const normRow = rowVal.replace(/[-_\s]+/g, '');
                if (normRow && (normRow === normFilter || normRow.includes(normFilter) || normFilter.includes(normRow))) return true;

                // 3. Cricket specific role synonyms
                if (normFilter.includes('wicket') || normFilter.includes('keeper') || normFilter === 'wk') {
                    if (normRow.includes('wk') || normRow.includes('keeper') || normRow.includes('wicket')) return true;
                }
                if (normFilter === 'batsman' || normFilter === 'batter') {
                    if (normRow.includes('bat') || normRow.includes('batter')) return true;
                }
                if (normFilter.includes('allround') || normFilter.includes('all-round')) {
                    if (normRow.includes('all') && (normRow.includes('round') || normRow.includes('rounder'))) return true;
                }
                if (normFilter === 'bowler' || normFilter === 'bowling') {
                    if (normRow.includes('bowl')) return true;
                }

                // 4. Fallback check inside the whole row text
                if (rowText && (rowText.includes(filterVal) || rowText.includes(normFilter))) return true;

                return false;
            }

            applyFilter(page = 1) {
                this.currentPage = page;
                const searchInput = document.getElementById(this.searchInputId);
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const queryTokens = query.split(/\s+/).filter(Boolean);

                const rows = Array.from(document.querySelectorAll(this.rowSelector));
                const totalRows = rows.length;

                const filters = this.filterSelects.map(f => {
                    const el = document.getElementById(f.id);
                    return {
                        val: el ? el.value.toLowerCase().trim() : '',
                        attr: f.attr
                    };
                });

                const matchedRows = rows.filter(row => {
                    const inputVals = Array.from(row.querySelectorAll('input, select, textarea')).map(i => i.value || '').join(' ');
                    const dataVals = Object.values(row.dataset || {}).join(' ');
                    const rowText = (row.textContent + ' ' + inputVals + ' ' + dataVals).toLowerCase();

                    // Search tokens match (all tokens must match somewhere in row)
                    const matchesQuery = queryTokens.length === 0 || queryTokens.every(tok => rowText.includes(tok));

                    // Filter dropdowns match
                    const matchesFilters = filters.every(f => {
                        if (!f.val) return true;
                        const rowVal = (row.getAttribute(`data-${f.attr}`) || row.dataset[f.attr] || '').toLowerCase();
                        return this.matchFilter(f.val, rowVal, rowText);
                    });

                    let matchesExtra = true;
                    if (typeof this.extraFilter === 'function') {
                        matchesExtra = this.extraFilter(row);
                    }

                    return matchesQuery && matchesFilters && matchesExtra;
                });

                const filteredCount = matchedRows.length;
                const totalPages = Math.max(1, Math.ceil(filteredCount / this.perPage));
                if (this.currentPage > totalPages) this.currentPage = totalPages;
                if (this.currentPage < 1) this.currentPage = 1;

                const startIndex = (this.currentPage - 1) * this.perPage;
                const endIndex = startIndex + this.perPage;

                // Hide all rows first
                rows.forEach(r => r.style.display = 'none');

                // Show only paginated rows
                matchedRows.slice(startIndex, endIndex).forEach(r => r.style.display = '');

                // Handle No Results element (Supports both Table tbody and Div grids)
                let noResultsEl = document.getElementById(`no-results-${this.tableId}`);
                const tableEl = document.getElementById(this.tableId);
                const tbody = tableEl ? tableEl.querySelector('tbody') : null;

                if (!noResultsEl && tableEl) {
                    if (tbody) {
                        noResultsEl = document.createElement('tr');
                        noResultsEl.id = `no-results-${this.tableId}`;
                        noResultsEl.className = 'no-filter-row';
                        noResultsEl.innerHTML = `<td colspan="${this.colSpan}" style="text-align: center; padding: 36px 20px; color: #94a3b8; font-weight: 600; font-size: 0.95rem;">${this.noResultsMsg}</td>`;
                        tbody.appendChild(noResultsEl);
                    } else {
                        noResultsEl = document.createElement('div');
                        noResultsEl.id = `no-results-${this.tableId}`;
                        noResultsEl.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 36px 20px; color: #94a3b8; font-weight: 600; font-size: 0.95rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px;';
                        noResultsEl.innerText = this.noResultsMsg;
                        tableEl.appendChild(noResultsEl);
                    }
                }
                if (noResultsEl) {
                    noResultsEl.style.display = (filteredCount === 0 && totalRows > 0) ? '' : 'none';
                }

                // Render Pagination
                this.renderPagination(filteredCount, totalRows, totalPages, startIndex, endIndex);
            }

            renderPagination(filteredCount, totalRows, totalPages, startIndex, endIndex) {
                const container = document.getElementById(this.paginationContainerId);
                if (!container) return;

                if (totalRows === 0) {
                    container.innerHTML = '';
                    return;
                }

                const startDisplay = filteredCount > 0 ? startIndex + 1 : 0;
                const endDisplay = Math.min(endIndex, filteredCount);

                let html = `
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 12px 18px; background: white; border-top: 1px solid #e2e8f0; font-size: 0.85rem; color: #475569;">
                    <div>
                        Showing <strong>${startDisplay}</strong> to <strong>${endDisplay}</strong> of <strong>${filteredCount}</strong> entries
                        ${filteredCount < totalRows ? `<span style="color: #94a3b8; margin-left: 4px;">(filtered from ${totalRows} total)</span>` : ''}
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px;">
                `;

                // Previous button
                const prevDisabled = this.currentPage <= 1;
                html += `
                    <button type="button" ${prevDisabled ? 'disabled' : ''} onclick="window['${this.tableId}_manager'].applyFilter(${this.currentPage - 1})"
                        style="padding: 5px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: ${prevDisabled ? '#f1f5f9' : 'white'}; color: ${prevDisabled ? '#94a3b8' : '#1e293b'}; font-weight: 700; cursor: ${prevDisabled ? 'not-allowed' : 'pointer'}; font-size: 0.82rem; transition: background 0.15s;">
                        &laquo; Prev
                    </button>
                `;

                // Page numbers
                let startPage = Math.max(1, this.currentPage - 2);
                let endPage = Math.min(totalPages, this.currentPage + 2);
                if (totalPages <= 5) {
                    startPage = 1;
                    endPage = totalPages;
                }

                if (startPage > 1) {
                    html += `<button type="button" onclick="window['${this.tableId}_manager'].applyFilter(1)" style="padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #1e293b; font-weight: 600; cursor: pointer; font-size: 0.82rem;">1</button>`;
                    if (startPage > 2) html += `<span style="padding: 0 4px; color: #94a3b8; font-size: 0.8rem;">...</span>`;
                }

                for (let p = startPage; p <= endPage; p++) {
                    const isActive = p === this.currentPage;
                    html += `
                        <button type="button" onclick="window['${this.tableId}_manager'].applyFilter(${p})"
                            style="padding: 5px 11px; border: 1px solid ${isActive ? '#0284c7' : '#cbd5e1'}; border-radius: 4px; background: ${isActive ? '#0284c7' : 'white'}; color: ${isActive ? 'white' : '#1e293b'}; font-weight: 700; cursor: pointer; font-size: 0.82rem; box-shadow: ${isActive ? '0 1px 3px rgba(2,132,199,0.3)' : 'none'};">
                            ${p}
                        </button>
                    `;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) html += `<span style="padding: 0 4px; color: #94a3b8; font-size: 0.8rem;">...</span>`;
                    html += `<button type="button" onclick="window['${this.tableId}_manager'].applyFilter(${totalPages})" style="padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #1e293b; font-weight: 600; cursor: pointer; font-size: 0.82rem;">${totalPages}</button>`;
                }

                // Next button
                const nextDisabled = this.currentPage >= totalPages;
                html += `
                    <button type="button" ${nextDisabled ? 'disabled' : ''} onclick="window['${this.tableId}_manager'].applyFilter(${this.currentPage + 1})"
                        style="padding: 5px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: ${nextDisabled ? '#f1f5f9' : 'white'}; color: ${nextDisabled ? '#94a3b8' : '#1e293b'}; font-weight: 700; cursor: ${nextDisabled ? 'not-allowed' : 'pointer'}; font-size: 0.82rem; transition: background 0.15s;">
                        Next &raquo;
                    </button>
                `;

                html += `
                    </div>
                </div>
                `;

                container.innerHTML = html;
            }
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

            // Auto-scroll to section if ?section= param is present
            const params = new URLSearchParams(window.location.search);
            const section = params.get('section');
            if (section) {
                const el = document.getElementById('section-' + section);
                if (el) {
                    setTimeout(() => {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        el.style.outline = '2px solid var(--admin-primary)';
                        el.style.outlineOffset = '4px';
                        setTimeout(() => { el.style.outline = 'none'; }, 2000);
                    }, 300);
                }
            }
        });
    </script>

</body>
</html>
