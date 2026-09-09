@extends('layouts.admin')

@section('content')
<div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Action Toolbar matching Screenshot -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left buttons & filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>

            <!-- Category Filter -->
            <select id="filter-category" onchange="filterSeriesTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 100px;">
                <option value="">All</option>
                <option value="international">International</option>
                <option value="t20 leagues">T20 Leagues</option>
                <option value="domestic">Domestic</option>
                <option value="women">Women's</option>
            </select>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="text" id="series-search-input" oninput="filterSeriesTable()" onkeyup="filterSeriesTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterSeriesTable();}" placeholder="Search series..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
                <button type="button" onclick="filterSeriesTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetSeriesSearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Right: + Add New Series Button -->
        <div>
            <button type="button" onclick="toggleSeriesForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add New Series
            </button>
        </div>
    </div>

    <!-- Add / Edit Series Form Panel (Strictly Red-Ticked Fields Only) -->
    <div id="series-form-container" style="display: {{ $editItem ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                {{ $editItem ? '✏️ Edit Series: ' . $editItem->name : '➕ Add New Series' }}
            </h3>
            <button type="button" onclick="toggleSeriesForm()" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <form method="POST" action="{{ $editItem ? route('admin.series.update', $editItem->id) : route('admin.series.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- ROW 1: Series Name & Page Unique URL | Short Name | Series Year | Display Order -->
            <div style="display: grid; grid-template-columns: 2.2fr 1fr 1fr 1fr; gap: 16px; align-items: flex-start;">
                
                <!-- Series Name & Unique URL -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Series Name <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="series_name" name="name" value="{{ old('name', $editItem->name ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                    
                    <input type="text" id="series_slug" name="slug" value="{{ old('slug', $editItem->slug ?? '') }}" placeholder="" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                </div>

                <!-- Short Name -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Short Name
                    </label>
                    <input type="text" name="short_name" value="{{ old('short_name', $editItem->short_name ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <!-- Series Year -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Series Year
                    </label>
                    <input type="text" name="year" value="{{ old('year', $editItem->year ?? '2026') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <!-- Display Order -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Display Order
                    </label>
                    <input type="number" name="display_order" value="{{ old('display_order', $editItem->display_order ?? '') }}" min="1" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 2: Short Meta Description -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                    Short Meta Description
                </label>
                <input type="text" name="meta_description" value="{{ old('meta_description', $editItem->meta_description ?? '') }}" placeholder="" maxlength="200" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
            </div>

            <!-- ROW 3: Start Date | End Date | Select Categories | Match Formats -->
            <div style="display: grid; grid-template-columns: 1.1fr 1.1fr 1.6fr 1.8fr; gap: 16px; align-items: center;">
                <!-- Start Date -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Start Date
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date', $editItem->start_date ?? date('Y-m-d')) }}" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <!-- End Date -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        End Date
                    </label>
                    <input type="date" name="end_date" value="{{ old('end_date', $editItem->end_date ?? date('Y-m-d')) }}" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <!-- Select Categories -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Select Categories
                    </label>
                    @php $curCat = old('category', $editItem->category ?? 'International'); @endphp
                    <select name="category" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                        <option value="International" {{ strcasecmp($curCat, 'International') === 0 ? 'selected' : '' }}>International</option>
                        <option value="T20 Leagues" {{ strcasecmp($curCat, 'T20 Leagues') === 0 || strcasecmp($curCat, 'IPL') === 0 ? 'selected' : '' }}>T20 Leagues</option>
                        <option value="Domestic" {{ strcasecmp($curCat, 'Domestic') === 0 ? 'selected' : '' }}>Domestic</option>
                        <option value="Women" {{ strcasecmp($curCat, 'Women') === 0 ? 'selected' : '' }}>Women's</option>
                    </select>
                </div>

                <!-- Match Formats Checkboxes -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Match Formats
                    </label>
                    @php 
                        $formatsList = explode(',', $editItem->match_formats ?? ($editItem->format ?? 'T20'));
                    @endphp
                    <div style="display: flex; align-items: center; gap: 14px; padding: 6px 0; font-size: 0.85rem; font-weight: 600; color: #334155;">
                        <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" name="match_formats[]" value="T10" {{ in_array('T10', $formatsList) ? 'checked' : '' }}> T10
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" name="match_formats[]" value="T20" {{ in_array('T20', $formatsList) || empty($editItem) ? 'checked' : '' }}> T20
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" name="match_formats[]" value="ODI" {{ in_array('ODI', $formatsList) ? 'checked' : '' }}> ODI
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" name="match_formats[]" value="TEST" {{ in_array('TEST', $formatsList) || in_array('Test', $formatsList) ? 'checked' : '' }}> TEST
                        </label>
                    </div>
                </div>
            </div>

            <!-- ROW 4: Teams ❓ | Venues ❓ | Hosting Country | Total Matches -->
            <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1.2fr 1fr; gap: 16px;">
                <!-- Teams -->
                <div>
                    <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Teams 
                        <button type="button" onclick="openTeamsModal()" title="View & select all teams or add new" style="display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px; background:#0284c7; color:white; border-radius:50%; font-size:0.7rem; font-weight:bold; cursor:pointer; border:none; line-height:1;">?</button>
                    </label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="text" id="series_teams_list" name="teams_list" value="{{ old('teams_list', $editItem->teams_list ?? '') }}" placeholder="Select or type teams..." style="width: 100%; padding: 8px 105px 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                        <button type="button" onclick="openTeamsModal()" style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.76rem; font-weight: 700; border-radius: 4px; padding: 4px 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; transition: all 0.15s;" onmouseover="this.style.background='#0284c7'; this.style.color='#ffffff';" onmouseout="this.style.background='#f0f9ff'; this.style.color='#0284c7';">
                            + Select / Add
                        </button>
                    </div>
                </div>

                <!-- Venues -->
                <div>
                    <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Venues 
                        <button type="button" onclick="openVenuesModal()" title="View & select all venues or add new" style="display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px; background:#0284c7; color:white; border-radius:50%; font-size:0.7rem; font-weight:bold; cursor:pointer; border:none; line-height:1;">?</button>
                    </label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="text" id="series_venues_list" name="venues_list" value="{{ old('venues_list', $editItem->venues_list ?? '') }}" placeholder="Select or type venues..." style="width: 100%; padding: 8px 105px 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                        <button type="button" onclick="openVenuesModal()" style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.76rem; font-weight: 700; border-radius: 4px; padding: 4px 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; transition: all 0.15s;" onmouseover="this.style.background='#0284c7'; this.style.color='#ffffff';" onmouseout="this.style.background='#f0f9ff'; this.style.color='#0284c7';">
                            + Select / Add
                        </button>
                    </div>
                </div>

                <!-- Hosting Country -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Hosting Country
                    </label>
                    <input type="text" name="hosting_country" value="{{ old('hosting_country', $editItem->hosting_country ?? ($editItem->city ?? 'India')) }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <!-- Total Matches -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Total Matches
                    </label>
                    <input type="number" name="total_matches" value="{{ old('total_matches', $editItem->total_matches ?? 10) }}" min="1" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 5: Series Full Description in HTML -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                    Series Full Description in HTML
                </label>
                <textarea name="full_description" rows="4" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; resize: vertical;">{{ old('full_description', $editItem->full_description ?? ($editItem->description ?? '')) }}</textarea>
            </div>

            <!-- ROW 6: Series Image Poster | Enable | SUBMIT -->
            <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                
                <!-- Series Image Poster -->
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Series Image Poster / Banner
                        </label>
                        <span id="series-poster-badge" style="display: none;"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="file" name="poster_image_file" accept="image/*" onchange="previewAndConvertImage(this, 'series_poster_image_input', 'series-poster-preview', 'series-poster-badge')" style="font-size: 0.82rem; color: #475569;">
                        <input type="text" id="series_poster_image_input" name="poster_image" value="{{ old('poster_image', $editItem->poster_image ?? ($editItem->banner_url ?? '')) }}" oninput="previewUrlImage(this, 'series-poster-preview')" placeholder="Image URL or auto-filled from upload" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                        <img id="series-poster-preview" src="{{ old('poster_image', $editItem->poster_image ?? ($editItem->banner_url ?? '')) }}" alt="Poster" style="height: 38px; border-radius: 4px; border: 1px solid #cbd5e1; display: {{ !empty(old('poster_image', $editItem->poster_image ?? ($editItem->banner_url ?? ''))) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                    </div>
                </div>

                <!-- Enable & SUBMIT Button -->
                <div style="display: flex; align-items: center; gap: 16px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.88rem; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $editItem->is_enabled ?? true) ? 'checked' : '' }} style="width: 15px; height: 15px; accent-color: #0284c7;">
                        Enable
                    </label>

                    <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                        SUBMIT
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Existing Series List Table (Matching Exact Screenshot) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        @if($tournaments->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="series-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                            <th style="padding: 10px 12px; width: 65px;">ORDER</th>
                            <th style="padding: 10px 12px; width: 90px;">POSTER</th>
                            <th style="padding: 10px 14px; min-width: 220px;">NAME</th>
                            <th style="padding: 10px 14px; min-width: 240px;">PAGE LINK</th>
                            <th style="padding: 10px 12px;">FORMATS</th>
                            <th style="padding: 10px 12px; white-space: nowrap;">TM-VN-MT</th>
                            <th style="padding: 10px 14px; min-width: 180px;">ADD/UPDATE</th>
                            <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tournaments as $t)
                            <tr class="tbl-series-row" data-category="{{ strtolower($t->category ?? '') }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
                                <!-- # EDIT -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <a href="{{ route('admin.series', ['edit' => $t->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                            {{ $t->id }}
                                        </a>
                                        <a href="{{ route('admin.series', ['edit' => $t->id]) }}" title="Edit Series" style="color: #0284c7; text-decoration: none;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 2px;">
                                        <span>{{ $t->views_count ?? 0 }}</span>
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </div>
                                </td>

                                <!-- ORDER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <input type="number" value="{{ $t->display_order ?? 1 }}" min="1" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; text-align: center; color: #1e293b; outline: none;">
                                </td>

                                <!-- POSTER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    @php
                                        $poster = $t->poster_image ?: $t->banner_url;
                                    @endphp
                                    @if(!empty($poster))
                                        <img src="{{ $poster }}" alt="Poster" style="width: 65px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block;">
                                    @else
                                        <div style="width: 65px; height: 38px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                                            No Poster
                                        </div>
                                    @endif
                                </td>

                                <!-- NAME -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 3px;">
                                        {{ $t->name }}
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 700;">
                                        <a href="{{ route('admin.manage-tournament', $t->id) }}" style="color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;">
                                            Schedule
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                        <a href="{{ route('admin.tournament.preview', $t->id) }}" target="_blank" style="color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;">
                                            PT
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                        <a href="{{ route('admin.manage-tournament', $t->id) }}" style="color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;">
                                            Player Stats
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                </td>

                                <!-- PAGE LINK -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="margin-bottom: 2px;">
                                        <a href="{{ route('admin.tournament.preview', $t->id) }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 3px;">
                                            {{ $t->slug ?: \Illuminate\Support\Str::slug($t->name) }}
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                                        From {{ $t->start_date ? date('Y-m-d', strtotime($t->start_date)) : '2026-08-30' }} TO {{ $t->end_date ? date('Y-m-d', strtotime($t->end_date)) : '2026-09-12' }}
                                    </div>
                                </td>

                                <!-- FORMATS -->
                                <td style="padding: 12px 12px; vertical-align: middle; font-weight: 700; color: #1e293b;">
                                    {{ $t->match_formats ?: ($t->format ?? 'T20') }}
                                </td>

                                <!-- TM-VN-MT -->
                                <td style="padding: 12px 12px; vertical-align: middle; font-weight: 800; color: #0284c7; white-space: nowrap;">
                                    {{ $t->teams->count() ?: 10 }} - {{ $t->venues_list ? count(explode(',', $t->venues_list)) : 2 }} - {{ $t->total_matches ?: ($t->matches->count() ?: 24) }}
                                </td>

                                <!-- ADD/UPDATE -->
                                <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.75rem; color: #475569; line-height: 1.4;">
                                    <div>{{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('Y-m-d H:i:s') : '2026-08-31 05:02:24' }} - <strong>{{ $t->user->name ?? 'Admin' }}</strong></div>
                                    <div>{{ $t->updated_at ? \Carbon\Carbon::parse($t->updated_at)->format('Y-m-d H:i:s') : '2026-09-02 10:51:33' }} - <strong>{{ $t->user->name ?? 'Admin' }}</strong></div>
                                </td>

                                <!-- ACTION -->
                                <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                    <form method="POST" action="{{ route('admin.delete-tournament', $t->id) }}" onsubmit="return confirm('Delete series \'{{ addslashes($t->name) }}\'?');" style="display:inline; margin:0;">
                                        @csrf
                                        <button type="submit" style="background: #fee2e2; color: #b91c1c; border: none; font-weight: 700; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- 10-item Pagination Container -->
            <div id="series-table-pagination"></div>
        @else
            <div style="text-align: center; padding: 48px; color: #94a3b8; font-weight: 600;">
                No series available yet. Click <strong>+ Add New Series</strong> above to add one!
            </div>
        @endif
    </div>

</div>

<!-- ========================================================
     TEAMS SELECTION & QUICK-ADD MODAL
     ======================================================== -->
<div id="teams-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden; animation: fadeIn 0.2s ease;">
        
        <!-- Modal Header -->
        <div style="padding: 16px 20px; background: #0f172a; color: white; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">🛡️</span>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800;">Select Teams (With Generated IDs)</h3>
            </div>
            <button type="button" onclick="closeTeamsModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.5rem; line-height: 1; cursor: pointer;">&times;</button>
        </div>

        <!-- Modal Search & Quick Add Toggle -->
        <div style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <input type="text" id="modal-team-search" oninput="filterModalTeams()" placeholder="Search teams by name, short name or ID..." style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; outline: none;">
            <button type="button" onclick="toggleQuickTeamForm()" id="btn-toggle-quick-team" style="padding: 8px 14px; background: #0284c7; color: white; border: none; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
                + Add New Team
            </button>
        </div>

        <!-- Quick Add Team Form Container (Hidden by default) -->
        <div id="quick-team-form" style="display: none; background: #f0f9ff; border-bottom: 2px solid #bae6fd; padding: 14px 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 0.88rem; font-weight: 800; color: #0369a1;">⚡ Quick Create Team</h4>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; align-items: flex-end;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Team Name *</label>
                    <input type="text" id="quick_team_name" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #93c5fd; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Short Code</label>
                    <input type="text" id="quick_team_short" placeholder="" maxlength="5" style="width: 100%; padding: 6px 10px; border: 1px solid #93c5fd; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box; text-transform: uppercase;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Type</label>
                    <select id="quick_team_type" style="width: 100%; padding: 6px 8px; border: 1px solid #93c5fd; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box; background: white;">
                        <option value="International">International</option>
                        <option value="League">League / Franchise</option>
                        <option value="Domestic">Domestic</option>
                        <option value="Club">Club / Local</option>
                    </select>
                </div>
                <button type="button" onclick="submitQuickTeam()" id="btn-save-quick-team" style="padding: 7px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: 700; font-size: 0.82rem; cursor: pointer; height: 32px;">
                    Save
                </button>
            </div>
            <div id="quick-team-msg" style="margin-top: 6px; font-size: 0.78rem; font-weight: 600;"></div>
        </div>

        <!-- Teams List Box -->
        <div id="modal-teams-list" style="padding: 16px 20px; overflow-y: auto; flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
            @foreach($allTeams ?? [] as $tm)
                <label class="modal-team-item" data-search="{{ strtolower($tm->name . ' ' . $tm->short_name . ' id:' . $tm->id) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: white; transition: all 0.15s;" onmouseover="this.style.borderColor='#0284c7'; this.style.background='#f8fafc';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='white';">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" class="team-checkbox" value="{{ $tm->name }}" data-id="{{ $tm->id }}" data-short="{{ $tm->short_name }}" style="width: 16px; height: 16px; accent-color: #0284c7;">
                        <div>
                            <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a;">{{ $tm->name }}</div>
                            <div style="font-size: 0.74rem; color: #64748b; font-weight: 600;">
                                Code: <strong>{{ $tm->short_name ?? '-' }}</strong> &bull; {{ $tm->team_type ?? 'Team' }}
                            </div>
                        </div>
                    </div>
                    <span style="font-size: 0.72rem; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px;">
                        ID: {{ $tm->id }}
                    </span>
                </label>
            @endforeach
        </div>

        <!-- Modal Footer -->
        <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;">
            <span id="teams-selected-count" style="font-size: 0.82rem; font-weight: 700; color: #475569;">0 teams selected</span>
            <div style="display: flex; gap: 8px;">
                <button type="button" onclick="closeTeamsModal()" style="padding: 7px 16px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; font-weight: 700; font-size: 0.85rem; color: #475569; cursor: pointer;">
                    Cancel
                </button>
                <button type="button" onclick="insertSelectedTeams()" style="padding: 7px 18px; border: none; border-radius: 6px; background: #0284c7; font-weight: 800; font-size: 0.85rem; color: white; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                    Insert Selected Teams &rarr;
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================
     VENUES SELECTION & QUICK-ADD MODAL
     ======================================================== -->
<div id="venues-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden; animation: fadeIn 0.2s ease;">
        
        <!-- Modal Header -->
        <div style="padding: 16px 20px; background: #0f172a; color: white; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">🏟️</span>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800;">Select Venues / Stadiums (With Generated IDs)</h3>
            </div>
            <button type="button" onclick="closeVenuesModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.5rem; line-height: 1; cursor: pointer;">&times;</button>
        </div>

        <!-- Modal Search & Quick Add Toggle -->
        <div style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <input type="text" id="modal-venue-search" oninput="filterModalVenues()" placeholder="Search venues by stadium name, city, or ID..." style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; outline: none;">
            <button type="button" onclick="toggleQuickVenueForm()" id="btn-toggle-quick-venue" style="padding: 8px 14px; background: #0284c7; color: white; border: none; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
                + Add New Venue
            </button>
        </div>

        <!-- Quick Add Venue Form Container (Hidden by default) -->
        <div id="quick-venue-form" style="display: none; background: #f0fdf4; border-bottom: 2px solid #bbf7d0; padding: 14px 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 0.88rem; font-weight: 800; color: #15803d;">⚡ Quick Create Venue / Stadium</h4>
            <div style="display: grid; grid-template-columns: 2fr 1.2fr 1fr 1fr auto; gap: 10px; align-items: flex-end;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Stadium Name *</label>
                    <input type="text" id="quick_venue_name" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #86efac; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">City</label>
                    <input type="text" id="quick_venue_city" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #86efac; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Country</label>
                    <input type="text" id="quick_venue_country" value="India" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #86efac; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Capacity</label>
                    <input type="text" id="quick_venue_capacity" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #86efac; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;">
                </div>
                <button type="button" onclick="submitQuickVenue()" id="btn-save-quick-venue" style="padding: 7px 16px; background: #16a34a; color: white; border: none; border-radius: 4px; font-weight: 700; font-size: 0.82rem; cursor: pointer; height: 32px;">
                    Save
                </button>
            </div>
            <div id="quick-venue-msg" style="margin-top: 6px; font-size: 0.78rem; font-weight: 600;"></div>
        </div>

        <!-- Venues List Box -->
        <div id="modal-venues-list" style="padding: 16px 20px; overflow-y: auto; flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
            @foreach($allVenues ?? [] as $vn)
                <label class="modal-venue-item" data-search="{{ strtolower($vn->name . ' ' . $vn->city . ' ' . $vn->country . ' id:' . $vn->id) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: white; transition: all 0.15s;" onmouseover="this.style.borderColor='#16a34a'; this.style.background='#f8fafc';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='white';">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" class="venue-checkbox" value="{{ $vn->name }}" data-id="{{ $vn->id }}" data-city="{{ $vn->city }}" style="width: 16px; height: 16px; accent-color: #16a34a;">
                        <div>
                            <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a;">{{ $vn->name }}</div>
                            <div style="font-size: 0.74rem; color: #64748b; font-weight: 600;">
                                📍 {{ $vn->city ? $vn->city . ', ' : '' }}{{ $vn->country ?? 'India' }}
                                @if($vn->capacity) &bull; Cap: {{ $vn->capacity }} @endif
                            </div>
                        </div>
                    </div>
                    <span style="font-size: 0.72rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px;">
                        ID: {{ $vn->id }}
                    </span>
                </label>
            @endforeach
        </div>

        <!-- Modal Footer -->
        <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;">
            <span id="venues-selected-count" style="font-size: 0.82rem; font-weight: 700; color: #475569;">0 venues selected</span>
            <div style="display: flex; gap: 8px;">
                <button type="button" onclick="closeVenuesModal()" style="padding: 7px 16px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; font-weight: 700; font-size: 0.85rem; color: #475569; cursor: pointer;">
                    Cancel
                </button>
                <button type="button" onclick="insertSelectedVenues()" style="padding: 7px 18px; border: none; border-radius: 6px; background: #16a34a; font-weight: 800; font-size: 0.85rem; color: white; cursor: pointer; box-shadow: 0 2px 4px rgba(22,163,74,0.3);">
                    Insert Selected Venues &rarr;
                </button>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleSeriesForm() {
    const container = document.getElementById('series-form-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        const nameInput = document.getElementById('series_name');
        if (nameInput) {
            nameInput.focus();
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        container.style.display = 'none';
    }
}

function autoSlugify(text) {
    const slugInput = document.getElementById('series_slug');
    if (slugInput && (!slugInput.dataset.manual || slugInput.value === '')) {
        slugInput.value = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    }
}

document.getElementById('series_slug')?.addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Initialize Table Manager for Series
let seriesTableManager;
document.addEventListener('DOMContentLoaded', () => {
    seriesTableManager = new AdminTableManager({
        tableId: 'series-table',
        rowSelector: '.tbl-series-row',
        searchInputId: 'series-search-input',
        filterSelectId: 'filter-category',
        filterDataAttr: 'category',
        paginationContainerId: 'series-table-pagination',
        perPage: 10,
        colSpan: 9,
        noResultsMsg: 'No matching series found.'
    });
});

function filterSeriesTable() {
    if (seriesTableManager) seriesTableManager.applyFilter(1);
}

function resetSeriesSearch() {
    if (seriesTableManager) seriesTableManager.reset();
}

/* ========================================================
   TEAMS MODAL LOGIC
   ======================================================== */
function openTeamsModal() {
    document.getElementById('teams-modal').style.display = 'flex';
    syncSelectedTeamsCheckboxes();
}

function closeTeamsModal() {
    document.getElementById('teams-modal').style.display = 'none';
}

function toggleQuickTeamForm() {
    const form = document.getElementById('quick-team-form');
    const btn = document.getElementById('btn-toggle-quick-team');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        btn.innerText = '✕ Close Form';
        document.getElementById('quick_team_name').focus();
    } else {
        form.style.display = 'none';
        btn.innerText = '+ Add New Team';
    }
}

function filterModalTeams() {
    const query = document.getElementById('modal-team-search').value.toLowerCase().trim();
    const items = document.querySelectorAll('.modal-team-item');
    items.forEach(item => {
        const s = item.dataset.search || '';
        item.style.display = (query === '' || s.includes(query)) ? 'flex' : 'none';
    });
}

function syncSelectedTeamsCheckboxes() {
    const inputVal = document.getElementById('series_teams_list').value;
    const currentList = inputVal.split(',').map(s => s.trim().toLowerCase());
    let count = 0;
    document.querySelectorAll('.team-checkbox').forEach(cb => {
        if (currentList.includes(cb.value.toLowerCase())) {
            cb.checked = true;
            count++;
        }
    });
    updateTeamsSelectedCount();
}

function updateTeamsSelectedCount() {
    const checked = document.querySelectorAll('.team-checkbox:checked').length;
    document.getElementById('teams-selected-count').innerText = checked + ' teams selected';
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('team-checkbox')) {
        updateTeamsSelectedCount();
    }
    if (e.target.classList.contains('venue-checkbox')) {
        updateVenuesSelectedCount();
    }
});

function insertSelectedTeams() {
    const checked = Array.from(document.querySelectorAll('.team-checkbox:checked')).map(cb => cb.value);
    document.getElementById('series_teams_list').value = checked.join(', ');
    closeTeamsModal();
}

function submitQuickTeam() {
    const name = document.getElementById('quick_team_name').value.trim();
    const shortName = document.getElementById('quick_team_short').value.trim();
    const type = document.getElementById('quick_team_type').value;
    const msgEl = document.getElementById('quick-team-msg');
    const saveBtn = document.getElementById('btn-save-quick-team');

    if (!name) {
        msgEl.style.color = '#ef4444';
        msgEl.innerText = 'Please enter a team name.';
        return;
    }

    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';
    msgEl.innerText = '';

    fetch("{{ route('admin.teams.quick-add') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name: name, short_name: shortName, team_type: type })
    })
    .then(r => r.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save';
        if (data.success && data.team) {
            msgEl.style.color = '#16a34a';
            msgEl.innerText = '✓ ' + data.message + ' (ID: ' + data.team.id + ')';
            
            // Add new element to list and check it
            const list = document.getElementById('modal-teams-list');
            const newLabel = document.createElement('label');
            newLabel.className = 'modal-team-item';
            newLabel.dataset.search = (data.team.name + ' ' + (data.team.short_name || '') + ' id:' + data.team.id).toLowerCase();
            newLabel.style = 'display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border: 1.5px solid #0284c7; border-radius: 8px; cursor: pointer; background: #f0f9ff;';
            newLabel.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" class="team-checkbox" value="${data.team.name}" data-id="${data.team.id}" data-short="${data.team.short_name || ''}" checked style="width: 16px; height: 16px; accent-color: #0284c7;">
                    <div>
                        <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a;">${data.team.name}</div>
                        <div style="font-size: 0.74rem; color: #64748b; font-weight: 600;">Code: <strong>${data.team.short_name || '-'}</strong> &bull; ${data.team.team_type || 'Team'}</div>
                    </div>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px;">ID: ${data.team.id}</span>
            `;
            list.insertBefore(newLabel, list.firstChild);
            
            // Clear inputs
            document.getElementById('quick_team_name').value = '';
            document.getElementById('quick_team_short').value = '';
            updateTeamsSelectedCount();
        } else {
            msgEl.style.color = '#ef4444';
            msgEl.innerText = data.message || 'Error creating team.';
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save';
        msgEl.style.color = '#ef4444';
        msgEl.innerText = 'Network error occurred.';
    });
}

/* ========================================================
   VENUES MODAL LOGIC
   ======================================================== */
function openVenuesModal() {
    document.getElementById('venues-modal').style.display = 'flex';
    syncSelectedVenuesCheckboxes();
}

function closeVenuesModal() {
    document.getElementById('venues-modal').style.display = 'none';
}

function toggleQuickVenueForm() {
    const form = document.getElementById('quick-venue-form');
    const btn = document.getElementById('btn-toggle-quick-venue');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        btn.innerText = '✕ Close Form';
        document.getElementById('quick_venue_name').focus();
    } else {
        form.style.display = 'none';
        btn.innerText = '+ Add New Venue';
    }
}

function filterModalVenues() {
    const query = document.getElementById('modal-venue-search').value.toLowerCase().trim();
    const items = document.querySelectorAll('.modal-venue-item');
    items.forEach(item => {
        const s = item.dataset.search || '';
        item.style.display = (query === '' || s.includes(query)) ? 'flex' : 'none';
    });
}

function syncSelectedVenuesCheckboxes() {
    const inputVal = document.getElementById('series_venues_list').value;
    const currentList = inputVal.split(',').map(s => s.trim().toLowerCase());
    let count = 0;
    document.querySelectorAll('.venue-checkbox').forEach(cb => {
        if (currentList.includes(cb.value.toLowerCase())) {
            cb.checked = true;
            count++;
        }
    });
    updateVenuesSelectedCount();
}

function updateVenuesSelectedCount() {
    const checked = document.querySelectorAll('.venue-checkbox:checked').length;
    document.getElementById('venues-selected-count').innerText = checked + ' venues selected';
}

function insertSelectedVenues() {
    const checked = Array.from(document.querySelectorAll('.venue-checkbox:checked')).map(cb => cb.value);
    document.getElementById('series_venues_list').value = checked.join(', ');
    closeVenuesModal();
}

function submitQuickVenue() {
    const name = document.getElementById('quick_venue_name').value.trim();
    const city = document.getElementById('quick_venue_city').value.trim();
    const country = document.getElementById('quick_venue_country').value.trim();
    const capacity = document.getElementById('quick_venue_capacity').value.trim();
    const msgEl = document.getElementById('quick-venue-msg');
    const saveBtn = document.getElementById('btn-save-quick-venue');

    if (!name) {
        msgEl.style.color = '#ef4444';
        msgEl.innerText = 'Please enter a stadium name.';
        return;
    }

    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';
    msgEl.innerText = '';

    fetch("{{ route('admin.venues.quick-add') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name: name, city: city, country: country, capacity: capacity })
    })
    .then(r => r.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save';
        if (data.success && data.venue) {
            msgEl.style.color = '#16a34a';
            msgEl.innerText = '✓ ' + data.message + ' (ID: ' + data.venue.id + ')';
            
            // Add new element to list and check it
            const list = document.getElementById('modal-venues-list');
            const newLabel = document.createElement('label');
            newLabel.className = 'modal-venue-item';
            newLabel.dataset.search = (data.venue.name + ' ' + (data.venue.city || '') + ' id:' + data.venue.id).toLowerCase();
            newLabel.style = 'display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border: 1.5px solid #16a34a; border-radius: 8px; cursor: pointer; background: #f0fdf4;';
            newLabel.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" class="venue-checkbox" value="${data.venue.name}" data-id="${data.venue.id}" data-city="${data.venue.city || ''}" checked style="width: 16px; height: 16px; accent-color: #16a34a;">
                    <div>
                        <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a;">${data.venue.name}</div>
                        <div style="font-size: 0.74rem; color: #64748b; font-weight: 600;">📍 ${data.venue.city ? data.venue.city + ', ' : ''}${data.venue.country || 'India'} ${data.venue.capacity ? '&bull; Cap: ' + data.venue.capacity : ''}</div>
                    </div>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px;">ID: ${data.venue.id}</span>
            `;
            list.insertBefore(newLabel, list.firstChild);
            
            // Clear inputs
            document.getElementById('quick_venue_name').value = '';
            document.getElementById('quick_venue_city').value = '';
            document.getElementById('quick_venue_capacity').value = '';
            updateVenuesSelectedCount();
        } else {
            msgEl.style.color = '#ef4444';
            msgEl.innerText = data.message || 'Error creating venue.';
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save';
        msgEl.style.color = '#ef4444';
        msgEl.innerText = 'Network error occurred.';
    });
}
</script>
@endsection
