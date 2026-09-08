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

            <!-- Tag Filter -->
            <select id="filter-story-tag" onchange="filterStoryTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 130px;">
                <option value="">All Tags</option>
                <option value="story">STORY</option>
                <option value="ipl">IPL 2026</option>
                <option value="match">MATCH</option>
                <option value="trending">TRENDING</option>
            </select>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="text" id="story-search-input" oninput="filterStoryTable()" onkeyup="filterStoryTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterStoryTable();}" placeholder="Search web stories..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
                <button type="button" onclick="filterStoryTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetStorySearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Right: + Add New Button -->
        <div>
            <button type="button" onclick="toggleStoryForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add Web Story
            </button>
        </div>
    </div>

    <!-- Add / Edit Web Story Form Panel -->
    <div id="story-form-container" style="display: {{ $editItem ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                {{ $editItem ? '✏️ Edit Web Story: ' . $editItem->title : '⚡ Add New Web Story' }}
            </h3>
            <button type="button" onclick="toggleStoryForm()" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <form method="POST" action="{{ $editItem ? route('admin.story.update', $editItem->id) : route('admin.story.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- ROW 1: Title & Slug | Tag | Display Order -->
            <div style="display: grid; grid-template-columns: 2.2fr 1fr 0.8fr; gap: 16px; align-items: flex-start;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Story Title <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="story_title" name="title" value="{{ old('title', $editItem->title ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                    
                    <input type="text" id="story_slug" name="slug" value="{{ old('slug', $editItem->slug ?? '') }}" placeholder="" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Tag / Category
                    </label>
                    <input type="text" name="tag" value="{{ old('tag', $editItem->tag ?? 'STORY') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Display Order
                    </label>
                    <input type="number" name="display_order" value="{{ old('display_order', $editItem->display_order ?? '') }}" min="1" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 2: Author, Publish Date & Keywords -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Author (Optional)
                    </label>
                    <input type="text" name="author" value="{{ old('author', $editItem->author ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Publish Date
                    </label>
                    <input type="date" name="publish_date" value="{{ old('publish_date', isset($editItem->created_at) ? \Carbon\Carbon::parse($editItem->created_at)->format('Y-m-d') : date('Y-m-d')) }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Keywords (Comma separated)
                    </label>
                    <input type="text" name="keywords" value="{{ old('keywords', $editItem->keywords ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 3: Multi-Slide Image Upload / Image URL -->
            <div style="border: 1px dashed #cbd5e1; border-radius: 6px; padding: 16px 20px; background: #f8fafc; display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Upload Story Images (Supports Multiple Slides)
                    </label>
                    <input type="file" name="images[]" multiple accept="image/*" style="font-size: 0.82rem; color: #475569;">
                    <p style="font-size: 0.76rem; color: #64748b; margin: 4px 0 0 0;">Select one or multiple images from your device. Each image becomes a full-screen story slide.</p>
                </div>

                <div style="border-top: 1px solid #e2e8f0; padding-top: 12px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        OR Single Cover Image URL / Fallback
                    </label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="text" name="image_url" value="{{ old('image_url', $editItem->image_url ?? '') }}" placeholder="" style="flex: 1; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; box-sizing: border-box; background: white;">
                        @if($editItem && $editItem->image_url)
                            <img src="{{ $editItem->image_url }}" alt="Cover" style="height: 36px; width: 28px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;">
                        @endif
                    </div>
                </div>
            </div>

            <!-- ROW 4: Enable & SUBMIT -->
            <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.88rem; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $editItem->is_enabled ?? true) ? 'checked' : '' }} style="width: 15px; height: 15px; accent-color: #0284c7;">
                        Enable
                    </label>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                        {{ $editItem ? 'UPDATE WEB STORY' : 'SUBMIT' }}
                    </button>
                    @if($editItem)
                        <a href="{{ route('admin.story') }}" style="background: #f1f5f9; color: #475569; font-weight: 700; padding: 9px 20px; border-radius: 4px; text-decoration: none; font-size: 0.88rem; border: 1px solid #cbd5e1; display: inline-flex; align-items: center;">
                            Cancel
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    <!-- Existing Web Stories List Table (Matching Exact Series Style) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
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
                            <th style="padding: 10px 12px; width: 100px;">TAG</th>
                            <th style="padding: 10px 14px; min-width: 160px;">ADD/UPDATE</th>
                            <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($webStories as $item)
                            <tr class="tbl-story-row" data-tag="{{ strtolower($item->tag ?? 'story') }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
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
                                    <img src="{{ $item->image_url ?: 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=80&h=110&q=80' }}" alt="{{ $item->title }}" style="width: 44px; height: 58px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                                </td>

                                <!-- TITLE -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem; line-height: 1.35;">
                                        {{ $item->title }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 3px;">
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

                                <!-- TAG -->
                                <td style="padding: 12px 12px; vertical-align: middle;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em;">
                                        {{ strtoupper($item->tag ?? 'STORY') }}
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

<!-- JavaScript for Live Filter, Search, and Auto Slugify -->
<script>
function toggleStoryForm() {
    const container = document.getElementById('story-form-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        container.style.display = 'none';
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

// Initialize Table Manager for Web Stories
let storyTableManager;
document.addEventListener('DOMContentLoaded', () => {
    storyTableManager = new AdminTableManager({
        tableId: 'story-table',
        rowSelector: '.tbl-story-row',
        searchInputId: 'story-search-input',
        filterSelectId: 'filter-story-tag',
        filterDataAttr: 'tag',
        paginationContainerId: 'story-table-pagination',
        perPage: 10,
        colSpan: 8,
        noResultsMsg: 'No matching web stories found.'
    });
});

function filterStoryTable() {
    if (storyTableManager) storyTableManager.applyFilter(1);
}

function resetStorySearch() {
    if (storyTableManager) storyTableManager.reset();
}
</script>
@endsection
