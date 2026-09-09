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

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="text" id="venue-search-input" oninput="filterVenueTable()" onkeyup="filterVenueTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterVenueTable();}" placeholder="Search venues / stadiums..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
                <button type="button" onclick="filterVenueTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetVenueSearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Right: + Add New Button -->
        <div>
            <button type="button" onclick="toggleVenueForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add New Venue
            </button>
        </div>
    </div>

    <!-- Add / Edit Venue Form Panel -->
    <div id="venue-form-container" style="display: {{ $editItem ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                {{ $editItem ? '✏️ Edit Venue: ' . $editItem->name : '🏟️ Add New Cricket Stadium / Venue' }}
            </h3>
            <button type="button" onclick="toggleVenueForm()" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <form method="POST" action="{{ $editItem ? route('admin.venues.update', $editItem->id) : route('admin.venues.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- ROW 1: Venue Name & Slug | City | Country | Display Order -->
            <!-- ROW 1: Venue Name & Slug | City | Country | Display Order -->
            <div style="display: grid; grid-template-columns: 2.2fr 1fr 1fr 0.8fr; gap: 16px; align-items: flex-start;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Stadium / Venue Name <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="venue_name" name="name" value="{{ old('name', $editItem->name ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                    
                    <input type="text" id="venue_slug" name="slug" value="{{ old('slug', $editItem->slug ?? '') }}" placeholder="" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        City
                    </label>
                    <input type="text" name="city" value="{{ old('city', $editItem->city ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Country
                    </label>
                    <input type="text" name="country" value="{{ old('country', $editItem->country ?? 'India') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Display Order
                    </label>
                    <input type="number" name="display_order" value="{{ old('display_order', $editItem->display_order ?? '') }}" min="1" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 2: Capacity | Keywords | Description -->
            <div style="display: grid; grid-template-columns: 1fr 1.2fr 1.8fr; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Capacity
                    </label>
                    <input type="text" name="capacity" value="{{ old('capacity', $editItem->capacity ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Keywords
                    </label>
                    <input type="text" name="keywords" value="{{ old('keywords', $editItem->keywords ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Ground &amp; Pitch Description
                    </label>
                    <input type="text" name="description" value="{{ old('description', $editItem->description ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 3: Venue Photo / Poster | SUBMIT -->
            <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                
                <!-- Poster Image -->
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Venue Photo / Poster
                        </label>
                        <span id="venue-poster-badge" style="display: none;"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="file" name="image_file" accept="image/*" onchange="previewAndConvertImage(this, 'venue_image_url_input', 'venue-poster-preview', 'venue-poster-badge')" style="font-size: 0.82rem; color: #475569;">
                        <input type="text" id="venue_image_url_input" name="image_url" value="{{ old('image_url', $editItem->image_url ?? '') }}" oninput="previewUrlImage(this, 'venue-poster-preview')" placeholder="Image URL or auto-filled from upload" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                        <img id="venue-poster-preview" src="{{ old('image_url', $editItem->image_url ?? '') }}" alt="Venue" style="height: 38px; border-radius: 4px; border: 1px solid #cbd5e1; display: {{ !empty(old('image_url', $editItem->image_url ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                    </div>
                </div>

                <!-- SUBMIT Button -->
                <div>
                    <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                        SUBMIT
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Existing Venues List Table (Matching Exact Series Style) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        @if($venues->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="venue-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                            <th style="padding: 10px 12px; width: 65px;">ORDER</th>
                            <th style="padding: 10px 12px; width: 90px;">POSTER</th>
                            <th style="padding: 10px 14px; min-width: 220px;">STADIUM NAME</th>
                            <th style="padding: 10px 14px; min-width: 220px;">PAGE LINK / CITY</th>
                            <th style="padding: 10px 12px;">CAPACITY</th>
                            <th style="padding: 10px 14px; min-width: 180px;">ADD/UPDATE</th>
                            <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venues as $item)
                            <tr class="tbl-venue-row" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
                                <!-- # EDIT -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <a href="{{ route('admin.venues', ['edit' => $item->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                            {{ $item->id }}
                                        </a>
                                        <a href="{{ route('admin.venues', ['edit' => $item->id]) }}" title="Edit Venue" style="color: #0284c7; text-decoration: none;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                        Country: <strong>{{ $item->country ?: 'India' }}</strong>
                                    </div>
                                </td>

                                <!-- ORDER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <input type="number" value="{{ $item->display_order ?? 1 }}" min="1" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; text-align: center; color: #1e293b; outline: none;">
                                </td>

                                <!-- POSTER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    @if(!empty($item->image_url))
                                        <img src="{{ $item->image_url }}" alt="Venue" style="width: 65px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                                        <div style="width: 65px; height: 38px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: none; align-items: center; justify-content: center; font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                                            🏟️ Stadium
                                        </div>
                                    @else
                                        <div style="width: 65px; height: 38px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                                            🏟️ Stadium
                                        </div>
                                    @endif
                                </td>

                                <!-- STADIUM NAME -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 3px;">
                                        {{ $item->name }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                                        {{ Str::limit($item->description ?: 'Pitch report & ground details', 60) }}
                                    </div>
                                </td>

                                <!-- PAGE LINK / CITY -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="margin-bottom: 2px;">
                                        <span style="color: #0284c7; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 3px;">
                                            {{ $item->slug ?: \Illuminate\Support\Str::slug($item->name) }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                                        {{ $item->city ? $item->city . ', ' : '' }}{{ $item->country ?: 'India' }}
                                    </div>
                                </td>

                                <!-- CAPACITY -->
                                <td style="padding: 12px 12px; vertical-align: middle; font-weight: 700; color: #1e293b;">
                                    <span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                        {{ $item->capacity ? number_format((int)filter_var($item->capacity, FILTER_SANITIZE_NUMBER_INT)) . ' Seats' : 'N/A' }}
                                    </span>
                                </td>

                                <!-- ADD/UPDATE -->
                                <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.75rem; color: #475569; line-height: 1.4;">
                                    <div>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                    <div>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                </td>

                                <!-- ACTION -->
                                <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                    <form method="POST" action="{{ route('admin.venues.delete', $item->id) }}" onsubmit="return confirm('Delete venue \'{{ addslashes($item->name) }}\'?');" style="display:inline; margin:0;">
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
            <div id="venue-table-pagination"></div>
        @else
            <div style="text-align: center; padding: 48px; color: #94a3b8; font-weight: 600;">
                No venues available yet. Click <strong>+ Add New Venue</strong> above to add one!
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
function toggleVenueForm() {
    const container = document.getElementById('venue-form-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        const nameInput = document.getElementById('venue_name');
        if (nameInput) {
            nameInput.focus();
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        container.style.display = 'none';
    }
}

function autoSlugify(text) {
    const slugInput = document.getElementById('venue_slug');
    if (slugInput && (!slugInput.dataset.manual || slugInput.value === '')) {
        slugInput.value = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    }
}

document.getElementById('venue_slug')?.addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Initialize Table Manager for Venues
let venueTableManager;
document.addEventListener('DOMContentLoaded', () => {
    venueTableManager = new AdminTableManager({
        tableId: 'venue-table',
        rowSelector: '.tbl-venue-row',
        searchInputId: 'venue-search-input',
        paginationContainerId: 'venue-table-pagination',
        perPage: 10,
        colSpan: 8,
        noResultsMsg: 'No matching venues found.'
    });
});

function filterVenueTable() {
    if (venueTableManager) venueTableManager.applyFilter(1);
}

function resetVenueSearch() {
    if (venueTableManager) venueTableManager.reset();
}
</script>
@endsection
