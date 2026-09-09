@extends('layouts.admin')

@section('content')
<div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Action Toolbar -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left buttons & filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>

            <!-- Type Filter -->
            <select id="filter-pred-type" onchange="filterPredictionTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 140px;">
                <option value="">All Content</option>
                <option value="prediction">Match Prediction</option>
                <option value="fantasy">Fantasy Tips</option>
                <option value="preview">Match Preview</option>
            </select>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="text" id="pred-search-input" oninput="filterPredictionTable()" onkeyup="filterPredictionTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterPredictionTable();}" placeholder="Search predictions..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
                <button type="button" onclick="filterPredictionTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetPredictionSearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Right: + Add New Button -->
        <div>
            <button type="button" onclick="togglePredictionForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add Prediction / Tips
            </button>
        </div>
    </div>

    <!-- Add / Edit Prediction & Fantasy Tips Form Panel -->
    <div id="pred-form-container" style="display: {{ $editItem ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                {{ $editItem ? '✏️ Edit ' . ($editType === 'FANTASY' ? 'Fantasy Tip' : 'Prediction') . ': ' . $editItem->title : '🎯 Add Prediction & Fantasy Tips' }}
            </h3>
            <button type="button" onclick="togglePredictionForm()" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <form method="POST" action="{{ $editItem ? ($editType === 'FANTASY' ? route('admin.fantasy.update', $editItem->id) : route('admin.prediction.update', $editItem->id)) : route('admin.prediction.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- DUAL OPTION SELECTOR (Match Prediction vs Fantasy Tips vs Match Preview) -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 800; font-size: 0.85rem; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;">
                    SELECT CONTENT TYPE <span style="color:#ef4444;">*</span>
                </label>
                <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="type" value="PREDICTION" {{ old('type', $editType ?? 'PREDICTION') === 'PREDICTION' || old('type', $editType ?? '') === 'MATCH PREDICTION' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                        <span>🎯 MATCH PREVIEW AND PREDICTION</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="type" value="FANTASY" {{ old('type', $editType ?? '') === 'FANTASY' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                        <span>⭐ FANTASY TIPS</span>
                    </label>
                </div>
            </div>

            <!-- ROW 1: Title & Slug | Display Order -->
            <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 16px; align-items: flex-start;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Title <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="pred_title" name="title" value="{{ old('title', $editItem->title ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                    
                    <input type="text" id="pred_slug" name="slug" value="{{ old('slug', $editItem->slug ?? '') }}" placeholder="" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Display Order
                    </label>
                    <input type="number" name="display_order" value="{{ old('display_order', $editItem->display_order ?? '') }}" min="1" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 2: Short Meta Description & Keywords -->
            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Short Meta Description
                    </label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $editItem->meta_description ?? ($editItem->summary ?? '')) }}" placeholder="" maxlength="255" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Keywords
                    </label>
                    <input type="text" name="keywords" value="{{ old('keywords', $editItem->keywords ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 3: Full Description / Content in HTML -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                    Full Content &amp; Analysis Points <span style="color:#ef4444;">*</span>
                </label>
                <textarea name="summary" rows="5" required placeholder="" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; resize: vertical;">{{ old('summary', $editItem->summary ?? ($editItem->full_content ?? '')) }}</textarea>
            </div>

            <!-- ROW 4: Poster Image | Enable | SUBMIT -->
            <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                
                <!-- Poster Image -->
                <div style="flex: 1; min-width: 280px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Poster Image
                    </label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="file" name="poster_file" accept="image/*" style="font-size: 0.82rem; color: #475569;">
                        <input type="text" name="poster_image" value="{{ old('poster_image', $editItem->poster_image ?? '') }}" placeholder="" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                        @if($editItem && ($editItem->poster_image))
                            <img src="{{ $editItem->poster_image }}" alt="Poster" style="height: 32px; border-radius: 4px; border: 1px solid #cbd5e1;">
                        @endif
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

    <!-- Existing Predictions & Fantasy Tips List Table (Series Design) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        @if($predictions->isNotEmpty() || $fantasyTips->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="pred-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                            <th style="padding: 10px 12px; width: 65px;">ORDER</th>
                            <th style="padding: 10px 12px; width: 90px;">POSTER</th>
                            <th style="padding: 10px 14px; min-width: 250px;">NAME / TITLE</th>
                            <th style="padding: 10px 14px; min-width: 200px;">PAGE LINK</th>
                            <th style="padding: 10px 12px; width: 140px;">TYPE</th>
                            <th style="padding: 10px 14px; min-width: 180px;">ADD/UPDATE</th>
                            <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Predictions Loop -->
                        @foreach($predictions as $item)
                            <tr class="tbl-pred-row" data-type="prediction" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
                                <!-- # EDIT -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <a href="{{ route('admin.prediction', ['edit' => $item->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                            {{ $item->id }}
                                        </a>
                                        <a href="{{ route('admin.prediction', ['edit' => $item->id]) }}" title="Edit Prediction" style="color: #0284c7; text-decoration: none;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 2px;">
                                        <span>0</span>
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </div>
                                </td>

                                <!-- ORDER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <input type="number" value="{{ $item->display_order ?? 1 }}" min="1" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; text-align: center; color: #1e293b; outline: none;">
                                </td>

                                <!-- POSTER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    @if(!empty($item->poster_image))
                                        <img src="{{ $item->poster_image }}" alt="Poster" style="width: 65px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block;">
                                    @else
                                        <div style="width: 65px; height: 38px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                                            No Poster
                                        </div>
                                    @endif
                                </td>

                                <!-- NAME / TITLE -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 3px;">
                                        {{ $item->title }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; line-height: 1.4;">
                                        {{ Str::limit($item->summary, 80) }}
                                    </div>
                                </td>

                                <!-- PAGE LINK -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="margin-bottom: 2px;">
                                        <a href="{{ route('home') }}#predictions" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 3px;">
                                            {{ $item->slug ?: \Illuminate\Support\Str::slug($item->title) }}
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                                        Keywords: {{ $item->keywords ?: 'Match Prediction' }}
                                    </div>
                                </td>

                                <!-- TYPE -->
                                <td style="padding: 12px 12px; vertical-align: middle;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">
                                        🎯 PREDICTION
                                    </span>
                                </td>

                                <!-- ADD/UPDATE -->
                                <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.75rem; color: #475569; line-height: 1.4;">
                                    <div>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                    <div>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                </td>

                                <!-- ACTION -->
                                <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                    <form method="POST" action="{{ route('admin.prediction.delete', $item->id) }}" onsubmit="return confirm('Delete this prediction?');" style="display:inline; margin:0;">
                                        @csrf
                                        <button type="submit" style="background: #fee2e2; color: #b91c1c; border: none; font-weight: 700; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        <!-- Fantasy Tips Loop -->
                        @foreach($fantasyTips as $item)
                            <tr class="tbl-pred-row" data-type="fantasy" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
                                <!-- # EDIT -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <a href="{{ route('admin.prediction', ['edit' => $item->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                            {{ $item->id }}
                                        </a>
                                        <a href="{{ route('admin.prediction', ['edit' => $item->id]) }}" title="Edit Fantasy Tip" style="color: #0284c7; text-decoration: none;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 2px;">
                                        <span>0</span>
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </div>
                                </td>

                                <!-- ORDER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <input type="number" value="{{ $item->display_order ?? 1 }}" min="1" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; text-align: center; color: #1e293b; outline: none;">
                                </td>

                                <!-- POSTER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    @if(!empty($item->poster_image))
                                        <img src="{{ $item->poster_image }}" alt="Poster" style="width: 65px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block;">
                                    @else
                                        <div style="width: 65px; height: 38px; background: #fef3c7; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #b45309; font-weight: 600;">
                                            ⭐ Tip
                                        </div>
                                    @endif
                                </td>

                                <!-- NAME / TITLE -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 3px;">
                                        {{ $item->title }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; line-height: 1.4;">
                                        {{ Str::limit($item->summary, 80) }}
                                    </div>
                                </td>

                                <!-- PAGE LINK -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="margin-bottom: 2px;">
                                        <a href="{{ route('home') }}#predictions" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 3px;">
                                            {{ $item->slug ?: \Illuminate\Support\Str::slug($item->title) }}
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                                        Keywords: {{ $item->keywords ?: 'Fantasy Tips, Dream11' }}
                                    </div>
                                </td>

                                <!-- TYPE -->
                                <td style="padding: 12px 12px; vertical-align: middle;">
                                    <span style="background: #fef3c7; color: #b45309; padding: 4px 8px; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">
                                        ⭐ FANTASY TIP
                                    </span>
                                </td>

                                <!-- ADD/UPDATE -->
                                <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.75rem; color: #475569; line-height: 1.4;">
                                    <div>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                    <div>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                </td>

                                <!-- ACTION -->
                                <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                    <form method="POST" action="{{ route('admin.fantasy.delete', $item->id) }}" onsubmit="return confirm('Delete this fantasy tip?');" style="display:inline; margin:0;">
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
            <div id="prediction-table-pagination"></div>
        @else
            <div style="text-align: center; padding: 48px; color: #94a3b8; font-weight: 600;">
                No predictions or fantasy tips available yet. Click <strong>+ Add Prediction / Tips</strong> above to add one!
            </div>
        @endif
    </div>

</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function togglePredictionForm() {
    const container = document.getElementById('pred-form-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        const titleInput = document.getElementById('pred_title');
        if (titleInput) {
            titleInput.focus();
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        container.style.display = 'none';
    }
}

function autoSlugify(text) {
    const slugInput = document.getElementById('pred_slug');
    if (slugInput && (!slugInput.dataset.manual || slugInput.value === '')) {
        slugInput.value = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    }
}

document.getElementById('pred_slug')?.addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Initialize Table Manager for Predictions & Fantasy Tips
let predTableManager;
document.addEventListener('DOMContentLoaded', () => {
    predTableManager = new AdminTableManager({
        tableId: 'prediction-table',
        rowSelector: '.tbl-pred-row',
        searchInputId: 'pred-search-input',
        filterSelectId: 'filter-pred-type',
        filterDataAttr: 'type',
        paginationContainerId: 'prediction-table-pagination',
        perPage: 10,
        colSpan: 8,
        noResultsMsg: 'No matching predictions or tips found.'
    });
});

function filterPredictionTable() {
    if (predTableManager) predTableManager.applyFilter(1);
}

function resetPredictionSearch() {
    if (predTableManager) predTableManager.reset();
}
</script>
@endsection
