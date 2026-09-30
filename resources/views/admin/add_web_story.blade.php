@extends('layouts.admin')

@section('content')
<div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Action Toolbar matching Series Style -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left buttons & filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>
            <a href="{{ route('admin.story') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                &lsaquo; Back
            </a>

            <!-- Tag / Category Filter -->
            <select id="filter-story-tag" onchange="filterStoryTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 140px;">
                <option value="">All Categories</option>
                <option value="cricket">Cricket</option>
                <option value="ipl">IPL 2026</option>
                <option value="international">International</option>
                <option value="domestic">Domestic</option>
                <option value="trending">Trending</option>
            </select>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <div class="admin-search-wrapper">
                    <input type="text" id="story-search-input" class="admin-search-input" oninput="filterStoryTable()" onkeyup="filterStoryTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterStoryTable();}" placeholder="Search web stories..." style="width: 220px;">
                    <button type="button" class="admin-search-clear-btn" title="Clear search">&times;</button>
                </div>
                <button type="button" onclick="filterStoryTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetStorySearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Reset
                </button>
            </div>
        </div>

        <!-- Right: + Add New Button -->
        <div>
            <button type="button" onclick="toggleStoryForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> {{ $editItem ? 'Edit Mode Active' : 'Add Web Story' }}
            </button>
        </div>
    </div>

    <!-- Add / Edit Web Story Form Panel (Matching Screenshot 1 & 2) -->
    <div id="story-form-container" style="display: {{ $editItem ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>📱</span> {{ $editItem ? 'Edit Web Story: ' . $editItem->title : 'Add New Web Story' }}
                </h3>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 3px;">
                    Manage story details, cover poster, and dynamically add/reorder as many slides with descriptions and CTA links.
                </div>
            </div>
            <button type="button" onclick="{{ $editItem ? "window.location.href='" . route('admin.story') . "'" : "toggleStoryForm()" }}" style="background: transparent; border: none; font-size: 1.4rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <form id="webStoryForm" method="POST" action="{{ $editItem ? route('admin.story.update', $editItem->id) : route('admin.story.post') }}" enctype="multipart/form-data" onsubmit="return handleStoryFormSubmit(event)" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf

            <!-- Hidden field for JSON slides -->
            <input type="hidden" name="slides_json" id="slides_json_input" value="">

            <!-- SECTION 1: STORY DETAILS -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
                
                <!-- Category Dropdown (Screenshot 1) -->
                <div style="max-width: 320px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Category
                    </label>
                    @php
                        $curCat = old('category', $editItem->category ?? ($editItem->tag ?? 'Cricket'));
                    @endphp
                    <select name="category" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; background: white; font-weight: 600;">
                        <option value="Cricket" {{ strcasecmp($curCat, 'Cricket') === 0 ? 'selected' : '' }}>Cricket</option>
                        <option value="IPL 2026" {{ strcasecmp($curCat, 'IPL 2026') === 0 ? 'selected' : '' }}>IPL 2026</option>
                        <option value="International" {{ strcasecmp($curCat, 'International') === 0 ? 'selected' : '' }}>International</option>
                        <option value="Domestic" {{ strcasecmp($curCat, 'Domestic') === 0 ? 'selected' : '' }}>Domestic</option>
                        <option value="Trending" {{ strcasecmp($curCat, 'Trending') === 0 ? 'selected' : '' }}>Trending</option>
                        <option value="Women's Cricket" {{ strcasecmp($curCat, "Women's Cricket") === 0 ? 'selected' : '' }}>Women's Cricket</option>
                    </select>
                </div>

                <!-- Title [Around 50-70 Characters] (Screenshot 1) -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Title <span style="font-weight: 600; color: #64748b;">[Around 50-70 Characters]</span> <span style="color:#ef4444;">*</span>
                        </label>
                        <span id="title-char-count" style="font-size: 0.75rem; color: #64748b; font-weight: 700;">0 chars</span>
                    </div>
                    <input type="text" id="story_title" name="title" value="{{ old('title', $editItem->title ?? '') }}" required placeholder="e.g. England vs Pakistan 2nd Test: Sonny Baker Replaces Brydon Carse" onkeyup="handleTitleInput(this.value)" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem; color: #0f172a; outline: none; box-sizing: border-box; font-weight: 600;">
                </div>

                <!-- Page URL (Screenshot 1) -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Page URL <span style="font-size: 0.75rem; color: #0284c7; font-weight: 600;">(Auto-generated slug)</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="text" id="story_slug" name="slug" value="{{ old('slug', $editItem->slug ?? '') }}" placeholder="england-vs-pakistan-2nd-test-sonny-baker-replaces-brydon-carse" style="flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #334155; outline: none; box-sizing: border-box; background: #fafafa; font-family: monospace;">
                        @if($editItem)
                            <a href="{{ route('webstories.show', $editItem->id) }}" target="_blank" style="padding: 8px 12px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 4px; color: #166534; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <span>Preview</span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Meta Description (Screenshot 1) -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Meta Description
                        </label>
                        <span id="meta-char-count" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">0 chars</span>
                    </div>
                    <textarea id="story_meta" name="meta_description" rows="2" onkeyup="updateMetaCount(this.value)" placeholder="England name Sonny Baker in the squad for the 2nd Test vs Pakistan as Brydon Carse is left out..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; resize: vertical;">{{ old('meta_description', $editItem->meta_description ?? '') }}</textarea>
                </div>

                <!-- Keywords (Screenshot 1) -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Keywords
                    </label>
                    <input type="text" name="keywords" value="{{ old('keywords', $editItem->keywords ?? '') }}" placeholder="England vs Pakistan 2nd Test, England Test Squad, Sonny Baker, Brydon Carse..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <!-- ROW: Story Cover Poster + Author + Display Order (Screenshot 1) -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>🖼️</span> Story Cover Poster (Thumbnail for Front Page & Cards)
                    </div>

                    <div style="display: grid; grid-template-columns: auto 1fr 1fr; gap: 16px; align-items: center; flex-wrap: wrap;">
                        <!-- Cover Preview Thumbnail -->
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img id="story-cover-preview" src="{{ old('image_url', $editItem->image_url ?? '') }}" alt="Cover" style="width: 60px; height: 85px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1; background: #0f172a; display: {{ !empty(old('image_url', $editItem->image_url ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                            <div id="story-cover-empty-placeholder" style="width: 60px; height: 85px; background: #e2e8f0; border: 1px dashed #94a3b8; border-radius: 4px; display: {{ empty(old('image_url', $editItem->image_url ?? '')) ? 'flex' : 'none' }}; align-items: center; justify-content: center; font-size: 0.7rem; color: #64748b; font-weight: 700; text-align: center; padding: 4px;">
                                No Cover
                            </div>
                        </div>

                        <!-- Choose File & Modal Button -->
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                                <label style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #0284c7; color: white; border-radius: 4px; font-weight: 700; font-size: 0.78rem; cursor: pointer;">
                                    <span>📁 Choose File</span>
                                    <input type="file" name="poster_file" accept="image/*" onchange="previewCoverFile(this)" style="display: none;">
                                </label>
                                <button type="button" onclick="openGlobalPosterUploader('story_cover_input', 'story-cover-preview', 'web_stories', 'story-cover-badge')" style="padding: 6px 12px; background: #0f172a; color: white; border: none; border-radius: 4px; font-size: 0.78rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>✂️ Standard Cropper</span>
                                </button>
                                <span id="story-cover-badge" style="display: none;"></span>
                            </div>
                            <input type="text" id="story_cover_input" name="image_url" value="{{ old('image_url', $editItem->image_url ?? '') }}" oninput="updateCoverUrlPreview(this.value)" placeholder="Image URL (auto-filled on upload or fallback to Slide #1)" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem; outline: none; background: white;">
                        </div>

                        <!-- Author & Display Order -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.8rem; color: #334155;">Author</label>
                                <input type="text" name="author" value="{{ old('author', $editItem->author ?? 'Admin') }}" placeholder="Admin" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.8rem; color: #334155;">Display Order</label>
                                <input type="number" name="display_order" value="{{ old('display_order', $editItem->display_order ?? 1) }}" min="1" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none;">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECTION 2: DYNAMIC STORY SLIDES (Matching Screenshot 1 & 2) -->
            <div style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; background: #ffffff;">
                
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;">
                    <div>
                        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <span>🎬</span> Story Slides
                            <span id="slidesCountBadge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: 800;">0 Slides</span>
                        </h4>
                        <div style="font-size: 0.76rem; color: #64748b; margin-top: 3px;">
                            Add as many slides as you want. Previous slides automatically collapse so the view stays clean; click <strong>Edit</strong> to view or change any slide.
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <button type="button" onclick="collapseAllSlides()" style="background: white; border: 1px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.78rem; padding: 7px 12px; border-radius: 4px; cursor: pointer; transition: all 0.2s;" title="Collapse all slides">
                            📁 Collapse All
                        </button>
                        <button type="button" onclick="expandAllSlides()" style="background: white; border: 1px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.78rem; padding: 7px 12px; border-radius: 4px; cursor: pointer; transition: all 0.2s;" title="Expand all slides">
                            📂 Expand All
                        </button>
                        <button type="button" onclick="addNewSlide()" style="background: #0284c7; color: white; border: none; font-weight: 800; font-size: 0.85rem; padding: 8px 18px; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(2,132,199,0.3); transition: background 0.15s;">
                            <span style="font-size: 1.1rem; line-height: 1;">+</span> Add New Slide
                        </button>
                    </div>
                </div>

                <!-- Dynamic Slides Container -->
                <div id="slidesListContainer" style="display: flex; flex-direction: column; gap: 14px;">
                    <!-- Javascript will render slide rows here -->
                </div>

                <!-- Bottom Add Slide Button -->
                <div style="margin-top: 14px; text-align: center;">
                    <button type="button" onclick="addNewSlide()" style="background: #f8fafc; border: 2px dashed #94a3b8; color: #0284c7; font-weight: 800; font-size: 0.86rem; padding: 10px 24px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f0f9ff'; this.style.borderColor='#0284c7';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#94a3b8';">
                        <span style="font-size: 1.2rem; line-height: 1;">+</span> Click Here to Add Another Slide
                    </button>
                </div>

            </div>

            <!-- ROW: Enable Checkbox & Submit Buttons -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.88rem; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $editItem->is_enabled ?? true) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #0284c7;">
                    Publish &amp; Enable this Web Story
                </label>

                <div style="display: flex; align-items: center; gap: 10px;">
                    @if($editItem)
                        <a href="{{ route('admin.story') }}" style="background: #f1f5f9; color: #475569; font-weight: 700; padding: 9px 20px; border-radius: 4px; text-decoration: none; font-size: 0.88rem; border: 1px solid #cbd5e1; display: inline-flex; align-items: center;">
                            Cancel
                        </a>
                    @endif
                    <button type="submit" id="btnSubmitStory" style="background: #0284c7; color: white; font-weight: 800; padding: 10px 32px; border-radius: 4px; border: none; font-size: 0.92rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 6px rgba(2,132,199,0.35);">
                        {{ $editItem ? 'UPDATE WEB STORY' : 'PUBLISH WEB STORY' }}
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Existing Web Stories List Table (Matching Exact Series Style) -->
    <div id="story-table-container" style="display: {{ $editItem ? 'none' : 'block' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        @if($webStories->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="story-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                            <th style="padding: 10px 12px; width: 65px;">ORDER</th>
                            <th style="padding: 10px 12px; width: 80px;">POSTER</th>
                            <th style="padding: 10px 14px; min-width: 250px;">TITLE</th>
                            <th style="padding: 10px 14px; min-width: 180px;">PAGE LINK</th>
                            <th style="padding: 10px 12px; width: 90px;">SLIDES</th>
                            <th style="padding: 10px 12px; width: 110px;">CATEGORY</th>
                            <th style="padding: 10px 14px; min-width: 160px;">ADD/UPDATE</th>
                            <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($webStories as $item)
                            <tr class="tbl-story-row" data-tag="{{ strtolower($item->category ?? ($item->tag ?? 'cricket')) }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
                                <!-- # EDIT -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <a href="{{ route('admin.story', ['edit' => $item->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                            #{{ $loop->iteration }}
                                        </a>
                                        <a href="{{ route('admin.story', ['edit' => $item->id]) }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;" title="Edit Web Story">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    </div>
                                </td>

                                <!-- ORDER -->
                                <td style="padding: 12px 10px; vertical-align: middle; color: #334155; font-weight: 700;">
                                    {{ $item->display_order ?: $item->id }}
                                </td>

                                <!-- POSTER / COVER -->
                                <td style="padding: 10px 12px; vertical-align: middle;">
                                    @if(!empty($item->image_url))
                                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width: 44px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                                        <div style="width: 44px; height: 60px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: none; align-items: center; justify-content: center; font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-align: center;">No Cover</div>
                                    @else
                                        <div style="width: 44px; height: 60px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-align: center;">No Cover</div>
                                    @endif
                                </td>

                                <!-- TITLE -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem; line-height: 1.35;">
                                        <a href="{{ route('admin.story', ['edit' => $item->id]) }}" style="color: #0f172a; text-decoration: none;" onmouseover="this.style.color='#0284c7';" onmouseout="this.style.color='#0f172a';">
                                            {{ $item->title }}
                                        </a>
                                    </div>
                                    <div style="font-size: 0.76rem; color: #64748b; margin-top: 3px;">
                                        By {{ $item->author ?? 'Admin' }}
                                    </div>
                                </td>

                                <!-- PAGE LINK -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <a href="{{ route('webstories.show', $item->id) }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;">
                                        /web-story/{{ $item->id }}
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </a>
                                </td>

                                <!-- SLIDES -->
                                <td style="padding: 12px 12px; vertical-align: middle;">
                                    <span style="background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">
                                        {{ count($item->slides ?? []) }} Slides
                                    </span>
                                </td>

                                <!-- CATEGORY -->
                                <td style="padding: 12px 12px; vertical-align: middle;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em;">
                                        {{ strtoupper($item->category ?? ($item->tag ?? 'CRICKET')) }}
                                    </span>
                                </td>

                                <!-- ADD/UPDATE -->
                                <td style="padding: 12px 14px; vertical-align: middle; color: #475569; font-size: 0.8rem;">
                                    {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d M Y, h:i A') : ($item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('d M Y, h:i A') : 'Active') }}
                                </td>

                                <!-- ACTION -->
                                <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                    <form method="POST" action="{{ route('admin.story.delete', $item->id) }}" onsubmit="return confirm('Are you sure you want to delete this web story?');" style="margin: 0; display: inline;">
                                        @csrf
                                        <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-weight: 800; font-size: 0.78rem; padding: 4px 10px; border-radius: 4px; cursor: pointer; transition: all 0.2s;">
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
            <div id="story-table-pagination"></div>
        @else
            <div id="no-stories-msg" style="text-align: center; padding: 40px; color: #94a3b8; font-weight: 600;">
                No web stories added yet. Click "+ Add Web Story" above to publish your first interactive visual story!
            </div>
        @endif
    </div>

</div>

<!-- JavaScript for Dynamic Slides, Live Filter, Search, and Validation -->
<script>
// Initial slides data (from backend if editing)
const initialSlidesData = @json($editItem && !empty($editItem->slides) ? $editItem->slides : []);

let slidesState = [];

document.addEventListener('DOMContentLoaded', () => {
    // If editing and has slides, load them (collapsed by default); else start with 1 blank open slide
    if (initialSlidesData && initialSlidesData.length > 0) {
        initialSlidesData.forEach(s => {
            if (typeof s === 'string') {
                slidesState.push({ image: s, heading: '', description: '', cta_text: '', cta_url: '', isOpen: false });
            } else if (typeof s === 'object' && s !== null) {
                slidesState.push({
                    image: s.image || s.url || '',
                    heading: s.heading || s.title || '',
                    description: s.description || s.desc || '',
                    cta_text: s.cta_text || s.ctaText || '',
                    cta_url: s.cta_url || s.ctaUrl || '',
                    isOpen: false
                });
            }
        });
    } else {
        slidesState.push({ image: '', heading: '', description: '', cta_text: '', cta_url: '', isOpen: true });
    }

    renderAllSlides();

    // Init title and meta counts
    const titleVal = document.getElementById('story_title')?.value || '';
    handleTitleInput(titleVal);
    const metaVal = document.getElementById('story_meta')?.value || '';
    updateMetaCount(metaVal);

    // Initialize Table Manager
    storyTableManager = new AdminTableManager({
        tableId: 'story-table',
        rowSelector: '.tbl-story-row',
        searchInputId: 'story-search-input',
        filterSelectId: 'filter-story-tag',
        filterDataAttr: 'tag',
        paginationContainerId: 'story-table-pagination',
        perPage: 10,
        colSpan: 9,
        noResultsMsg: 'No matching web stories found.'
    });
});

function renderAllSlides() {
    const container = document.getElementById('slidesListContainer');
    if (!container) return;
    container.innerHTML = '';

    const countBadge = document.getElementById('slidesCountBadge');
    if (countBadge) countBadge.innerText = `${slidesState.length} Slides`;

    slidesState.forEach((slide, idx) => {
        const slideCard = document.createElement('div');
        slideCard.className = 'story-slide-card';
        slideCard.dataset.slideIndex = idx;
        const isOpen = slide.isOpen === true;

        if (!isOpen) {
            // Collapsed row (clean, compact, shows summary only)
            slideCard.style.cssText = 'background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; position: relative; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.02); cursor: pointer;';
            slideCard.setAttribute('onclick', `toggleSlideOpen(${idx})`);
            slideCard.setAttribute('onmouseover', "this.style.borderColor='#0284c7'; this.style.background='#f8fafc';");
            slideCard.setAttribute('onmouseout', "this.style.borderColor='#cbd5e1'; this.style.background='white';");

            slideCard.innerHTML = `
                <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                    <span style="background: #0f172a; color: white; font-weight: 800; font-size: 0.75rem; padding: 4px 10px; border-radius: 4px; white-space: nowrap;">
                        Slide #${idx + 1}
                    </span>
                    ${slide.image ? `
                        <img src="${escapeHtml(slide.image)}" alt="Slide #${idx + 1}" style="width: 34px; height: 46px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1; flex-shrink: 0;" onerror="this.style.display='none';">
                    ` : `
                        <div style="width: 34px; height: 46px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: #94a3b8; flex-shrink: 0;">
                            📷
                        </div>
                    `}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${slide.heading ? escapeHtml(slide.heading) : '<span style="color:#94a3b8; font-style:italic;">(Untitled Slide - Click Edit to Add Content)</span>'}
                            </span>
                            ${slide.cta_text ? `
                                <span style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.68rem; padding: 2px 7px; border-radius: 3px; white-space: nowrap;">
                                    CTA: ${escapeHtml(slide.cta_text)}
                                </span>
                            ` : ''}
                        </div>
                        <div style="font-size: 0.78rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                            ${slide.description ? escapeHtml(slide.description) : '<span style="color:#cbd5e1;">No description added</span>'}
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;" onclick="event.stopPropagation();">
                    <button type="button" onclick="toggleSlideOpen(${idx})" title="Edit Slide" style="background: #0284c7; color: white; border: none; border-radius: 4px; padding: 5px 14px; cursor: pointer; font-size: 0.78rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(2,132,199,0.25);">
                        ✏️ Edit
                    </button>
                    ${idx > 0 ? `<button type="button" onclick="moveSlideUp(${idx})" title="Move Up" style="background: white; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; color: #475569;">▲</button>` : ''}
                    ${idx < slidesState.length - 1 ? `<button type="button" onclick="moveSlideDown(${idx})" title="Move Down" style="background: white; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; color: #475569;">▼</button>` : ''}
                    <button type="button" onclick="removeSlide(${idx})" title="Delete Slide" style="background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; border-radius: 4px; padding: 5px 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800;">
                        🗑️
                    </button>
                </div>
            `;
        } else {
            // Expanded edit view
            slideCard.style.cssText = 'background: #f8fafc; border: 2px solid #0284c7; border-radius: 8px; padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; position: relative; transition: all 0.2s; box-shadow: 0 4px 12px rgba(2,132,199,0.08);';

            const charLen = (slide.description || '').length;
            const charColor = charLen > 150 ? '#dc2626' : (charLen >= 120 ? '#16a34a' : '#64748b');

            slideCard.innerHTML = `
                <!-- Slide Header Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #0284c7; color: white; font-weight: 800; font-size: 0.78rem; padding: 3px 10px; border-radius: 4px;">
                            Slide #${idx + 1} (Editing)
                        </span>
                        <span id="slide_header_title_${idx}" style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">
                            ${slide.heading ? escapeHtml(slide.heading) : 'Untitled Slide'}
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <button type="button" onclick="toggleSlideOpen(${idx})" title="Collapse Slide" style="background: white; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 12px; cursor: pointer; font-size: 0.78rem; font-weight: 700; color: #334155; display: inline-flex; align-items: center; gap: 4px;">
                            ▲ Close
                        </button>
                        ${idx > 0 ? `<button type="button" onclick="moveSlideUp(${idx})" title="Move Up" style="background: white; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; color: #475569;">▲</button>` : ''}
                        ${idx < slidesState.length - 1 ? `<button type="button" onclick="moveSlideDown(${idx})" title="Move Down" style="background: white; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; color: #475569;">▼</button>` : ''}
                        <button type="button" onclick="removeSlide(${idx})" title="Delete Slide" style="background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                            🗑️ Remove
                        </button>
                    </div>
                </div>

                <!-- Slide Body Grid: Left Image, Right Text & CTA -->
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 16px; align-items: flex-start;">
                    
                    <!-- Left: Slide Image Box -->
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="width: 100%; aspect-ratio: 9/16; max-height: 180px; background: #0f172a; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; position: relative; display: flex; align-items: center; justify-content: center;">
                            <img id="slide_preview_${idx}" src="${slide.image || ''}" alt="Slide Image" style="width: 100%; height: 100%; object-fit: cover; display: ${slide.image ? 'block' : 'none'};" onerror="this.style.display='none';">
                            <div id="slide_placeholder_${idx}" style="display: ${slide.image ? 'none' : 'flex'}; flex-direction: column; align-items: center; justify-content: center; gap: 4px; color: #94a3b8; font-size: 0.72rem; text-align: center; padding: 6px;">
                                <span style="font-size: 1.4rem;">📷</span>
                                <span>No Slide Image</span>
                            </div>
                        </div>

                        <!-- Upload Controls -->
                        <label style="display: block; text-align: center; padding: 5px 8px; background: #0284c7; color: white; border-radius: 4px; font-weight: 700; font-size: 0.74rem; cursor: pointer;">
                            <span>📁 Choose Image</span>
                            <input type="file" accept="image/*" onchange="handleSlideFileSelect(event, ${idx})" style="display: none;">
                        </label>

                        <button type="button" onclick="openSlideModalCropper(${idx})" style="padding: 5px 8px; background: #0f172a; color: white; border: none; border-radius: 4px; font-size: 0.74rem; font-weight: 700; cursor: pointer;">
                            ✂️ Crop / Zoom
                        </button>

                        <input type="text" id="slide_img_input_${idx}" value="${escapeHtml(slide.image || '')}" oninput="handleSlideImageInput(this.value, ${idx})" placeholder="Image URL..." style="width: 100%; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 0.72rem; outline: none; background: white; box-sizing: border-box;">
                    </div>

                    <!-- Right: Heading, Description (120-150 chars), CTA Text & URL -->
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Heading -->
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.8rem; color: #1e293b;">
                                Slide Heading
                            </label>
                            <input type="text" value="${escapeHtml(slide.heading || '')}" oninput="handleSlideHeadingInput(${idx}, this.value)" placeholder="e.g. Virat Kohli Delivers in the Final" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; font-weight: 600;">
                        </div>

                        <!-- Description with 120-150 chars recommendation -->
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                <label style="font-weight: 700; font-size: 0.8rem; color: #1e293b;">
                                    Slide Description <span style="font-weight: 600; color: #64748b;">(Recommended: 120-150 characters)</span>
                                </label>
                                <span id="slide_desc_count_${idx}" style="font-size: 0.74rem; font-weight: 700; color: ${charColor};">
                                    ${charLen} / 150 chars
                                </span>
                            </div>
                            <textarea rows="2" maxlength="200" oninput="handleSlideDescChange(${idx}, this)" placeholder="Brief description (120-150 characters)..." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box; resize: vertical;">${escapeHtml(slide.description || '')}</textarea>
                        </div>

                        <!-- CTA Text & CTA URL -->
                        <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 10px; background: #ffffff; padding: 8px 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
                            <div>
                                <label style="display: block; margin-bottom: 3px; font-weight: 700; font-size: 0.75rem; color: #475569;">
                                    CTA Button Text (ctaText)
                                </label>
                                <input type="text" value="${escapeHtml(slide.cta_text || '')}" oninput="handleSlideFieldChange(${idx}, 'cta_text', this.value)" placeholder="e.g. Match Details, Read More" style="width: 100%; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 0.8rem; outline: none; box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 3px; font-weight: 700; font-size: 0.75rem; color: #475569;">
                                    CTA Link URL (ctaUrl)
                                </label>
                                <input type="url" value="${escapeHtml(slide.cta_url || '')}" oninput="handleSlideFieldChange(${idx}, 'cta_url', this.value)" placeholder="e.g. /matches/india-vs-south-africa" style="width: 100%; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 0.8rem; outline: none; box-sizing: border-box;">
                            </div>
                        </div>

                        <!-- Done / Collapse Button at bottom of card -->
                        <div style="display: flex; justify-content: flex-end; padding-top: 6px;">
                            <button type="button" onclick="toggleSlideOpen(${idx})" style="background: #0f172a; color: white; border: none; padding: 6px 16px; border-radius: 4px; font-weight: 700; font-size: 0.78rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                <span>✓</span> Done / Close Slide #${idx + 1}
                            </button>
                        </div>

                    </div>

                </div>
            `;
        }
        container.appendChild(slideCard);
    });

    syncSlidesJsonInput();
}

function addNewSlide() {
    // 1. Collapse all existing slides so the view stays neat & clean!
    slidesState.forEach(s => s.isOpen = false);

    // 2. Add new blank slide as OPEN
    slidesState.push({
        image: '',
        heading: '',
        description: '',
        cta_text: '',
        cta_url: '',
        isOpen: true
    });
    renderAllSlides();

    // 3. Scroll smoothly to the new slide & focus on its heading
    setTimeout(() => {
        const cards = document.querySelectorAll('.story-slide-card');
        if (cards.length > 0) {
            const lastCard = cards[cards.length - 1];
            lastCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const headingInput = lastCard.querySelector('input[placeholder*="Virat Kohli"], input[type="text"]');
            if (headingInput) headingInput.focus();
        }
    }, 60);
}

function toggleSlideOpen(idx) {
    if (slidesState[idx]) {
        slidesState[idx].isOpen = !slidesState[idx].isOpen;
        renderAllSlides();
        if (slidesState[idx].isOpen) {
            setTimeout(() => {
                const el = document.querySelector(`.story-slide-card[data-slide-index="${idx}"]`);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 60);
        }
    }
}

function collapseAllSlides() {
    slidesState.forEach(s => s.isOpen = false);
    renderAllSlides();
}

function expandAllSlides() {
    slidesState.forEach(s => s.isOpen = true);
    renderAllSlides();
}

function handleSlideHeadingInput(idx, value) {
    handleSlideFieldChange(idx, 'heading', value);
    const headerTitle = document.getElementById(`slide_header_title_${idx}`);
    if (headerTitle) {
        headerTitle.innerText = value.trim() ? value : 'Untitled Slide';
    }
}

function removeSlide(idx) {
    if (slidesState.length <= 1) {
        if (!confirm('This is the only slide. Do you want to clear it?')) return;
        slidesState[0] = { image: '', heading: '', description: '', cta_text: '', cta_url: '', isOpen: true };
        renderAllSlides();
        return;
    }
    slidesState.splice(idx, 1);
    renderAllSlides();
}

function moveSlideUp(idx) {
    if (idx <= 0) return;
    const temp = slidesState[idx];
    slidesState[idx] = slidesState[idx - 1];
    slidesState[idx - 1] = temp;
    renderAllSlides();
}

function moveSlideDown(idx) {
    if (idx >= slidesState.length - 1) return;
    const temp = slidesState[idx];
    slidesState[idx] = slidesState[idx + 1];
    slidesState[idx + 1] = temp;
    renderAllSlides();
}

function handleSlideFieldChange(idx, field, value) {
    if (slidesState[idx]) {
        slidesState[idx][field] = value;
        syncSlidesJsonInput();
    }
}

function handleSlideDescChange(idx, textarea) {
    const val = textarea.value;
    handleSlideFieldChange(idx, 'description', val);
    const countEl = document.getElementById(`slide_desc_count_${idx}`);
    if (countEl) {
        const len = val.length;
        countEl.innerText = `${len} / 150 chars`;
        countEl.style.color = len > 150 ? '#dc2626' : (len >= 120 ? '#16a34a' : '#64748b');
    }
}

function handleSlideImageInput(url, idx) {
    if (slidesState[idx]) {
        slidesState[idx].image = url;
        const prev = document.getElementById(`slide_preview_${idx}`);
        const ph = document.getElementById(`slide_placeholder_${idx}`);
        if (prev) {
            prev.src = url;
            prev.style.display = url ? 'block' : 'none';
        }
        if (ph) {
            ph.style.display = url ? 'none' : 'flex';
        }
        syncSlidesJsonInput();
    }
}

function handleSlideFileSelect(event, idx) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        handleSlideImageInput(dataUrl, idx);
        const input = document.getElementById(`slide_img_input_${idx}`);
        if (input) input.value = dataUrl;

        // If cover image is not yet set, automatically use this slide image as cover!
        const coverInput = document.getElementById('story_cover_input');
        if (coverInput && (!coverInput.value || coverInput.value.trim() === '')) {
            updateCoverUrlPreview(dataUrl);
            coverInput.value = dataUrl;
        }
    };
    reader.readAsDataURL(file);
}

function openSlideModalCropper(idx) {
    const targetInputId = `slide_img_input_${idx}`;
    const previewImgId = `slide_preview_${idx}`;
    openGlobalPosterUploader(targetInputId, previewImgId, 'web_stories');

    // Watch for input changes when modal inserts the cropped image
    const input = document.getElementById(targetInputId);
    if (input) {
        const checkVal = () => {
            handleSlideImageInput(input.value, idx);
        };
        input.addEventListener('input', checkVal, { once: true });
        input.addEventListener('change', checkVal, { once: true });
    }
}

function syncSlidesJsonInput() {
    const hidden = document.getElementById('slides_json_input');
    if (hidden) {
        hidden.value = JSON.stringify(slidesState);
    }
}

function previewCoverFile(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        updateCoverUrlPreview(e.target.result);
        const coverInp = document.getElementById('story_cover_input');
        if (coverInp) coverInp.value = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

function updateCoverUrlPreview(url) {
    const prev = document.getElementById('story-cover-preview');
    const ph = document.getElementById('story-cover-empty-placeholder');
    if (prev) {
        prev.src = url || '';
        prev.style.display = url ? 'block' : 'none';
    }
    if (ph) {
        ph.style.display = url ? 'none' : 'flex';
    }
}

function handleTitleInput(text) {
    const countEl = document.getElementById('title-char-count');
    if (countEl) {
        const len = text.length;
        countEl.innerText = `${len} chars`;
        countEl.style.color = (len >= 50 && len <= 70) ? '#16a34a' : '#64748b';
    }
    autoSlugify(text);
}

function updateMetaCount(text) {
    const countEl = document.getElementById('meta-char-count');
    if (countEl) {
        countEl.innerText = `${text.length} chars`;
    }
}

function autoSlugify(text) {
    const slugInput = document.getElementById('story_slug');
    if (slugInput) {
        slugInput.value = text.toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
}

function toggleStoryForm() {
    const container = document.getElementById('story-form-container');
    const tableContainer = document.getElementById('story-table-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        if (tableContainer) tableContainer.style.display = 'none';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        container.style.display = 'none';
        if (tableContainer) tableContainer.style.display = 'block';
    }
}

function handleStoryFormSubmit(e) {
    syncSlidesJsonInput();
    // Validate that at least one slide has an image or title
    const hasAnyContent = slidesState.some(s => s.image || s.heading || s.description);
    const coverUrl = document.getElementById('story_cover_input')?.value || '';
    if (!hasAnyContent && !coverUrl) {
        alert('Please add at least one slide with an image or title.');
        e.preventDefault();
        return false;
    }
    return true;
}

function escapeHtml(string) {
    if (!string) return '';
    return String(string)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Table Filter & Reset
let storyTableManager;
function filterStoryTable() {
    if (storyTableManager) storyTableManager.applyFilter(1);
}

function resetStorySearch() {
    if (storyTableManager) storyTableManager.reset();
}
</script>
@endsection
