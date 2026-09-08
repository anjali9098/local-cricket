@extends('layouts.admin')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px;">
    
    <!-- Header -->
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0; color: #0f172a; letter-spacing: -0.02em;">
                {{ $editItem ? 'Edit Fantasy Tip' : 'Add Fantasy Tip' }}
            </h1>
            <p style="font-size: 0.95rem; color: #64748b; margin: 4px 0 0 0;">Manage fantasy cricket tips to display on global homepage.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; font-weight: 700; color: #64748b; font-size: 0.9rem; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; background: white;">Back to Dashboard</a>
    </div>

    <!-- Form Panel -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <form method="POST" action="{{ $editItem ? route('admin.fantasy.update', $editItem->id) : route('admin.fantasy.post') }}" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Tip Title *</label>
                <input type="text" name="title" value="{{ old('title', $editItem->title ?? '') }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; outline: none; box-sizing: border-box;" placeholder="">
            </div>
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Summary / Players *</label>
                <textarea name="summary" rows="4" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; outline: none; box-sizing: border-box; resize: vertical;" placeholder="">{{ old('summary', $editItem->summary ?? '') }}</textarea>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" style="background: #0ea5e9; color: white; font-weight: 700; padding: 12px 32px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(14,165,233,0.2);">
                    {{ $editItem ? 'Update Fantasy Tip' : 'Add Fantasy Tip' }}
                </button>
                @if($editItem)
                    <a href="{{ route('admin.fantasy') }}" style="text-decoration: none; font-weight: 700; color: #64748b; font-size: 0.9rem; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; background: white;">Cancel Edit</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data List Panel -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span>Existing Fantasy Tips</span>
                <span style="font-size: 0.8rem; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 12px; font-weight: 700;">{{ $fantasyTips->count() }} Total</span>
            </h3>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="text" id="fantasy-search-input" oninput="filterFantasyTable()" onkeyup="filterFantasyTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterFantasyTable();}" placeholder="Search fantasy tips..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
                <button type="button" onclick="filterFantasyTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetFantasySearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Refresh
                </button>
            </div>
        </div>

        @if($fantasyTips->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="fantasy-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f1f5f9; color: #64748b; font-weight: 700;">
                            <th style="padding: 12px 16px;">Title</th>
                            <th style="padding: 12px 16px;">Summary</th>
                            <th style="padding: 12px 16px; text-align: right; width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fantasyTips as $item)
                            <tr class="tbl-fantasy-row" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                <td style="padding: 16px; font-weight: 700; color: #0f172a;">{{ $item->title }}</td>
                                <td style="padding: 16px; color: #475569; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item->summary }}</td>
                                <td style="padding: 16px; text-align: right; display: flex; justify-content: flex-end; gap: 8px; align-items: center;">
                                    <a href="{{ route('home') }}" target="_blank" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 6px 12px; border-radius: 6px; text-decoration: none;">View</a>
                                    <a href="{{ route('admin.fantasy', ['edit' => $item->id]) }}" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.8rem; padding: 6px 12px; border-radius: 6px; text-decoration: none;">Edit</a>
                                    <form method="POST" action="{{ route('admin.fantasy.delete', $item->id) }}" onsubmit="return confirm('Are you sure you want to delete this fantasy tip?');" style="display:inline; margin:0;">
                                        @csrf
                                        <button type="submit" style="background: #fee2e2; color: #b91c1c; border: none; font-weight: 700; font-size: 0.8rem; padding: 6px 12px; border-radius: 6px; cursor: pointer;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="fantasy-table-pagination"></div>
        @else
            <div style="text-align: center; padding: 40px; color: #94a3b8; font-weight: 600;">No fantasy tips added yet. Fill the form above to add your first fantasy tip!</div>
        @endif
    </div>

</div>

<script>
let fantasyTableManager;
document.addEventListener('DOMContentLoaded', () => {
    fantasyTableManager = new AdminTableManager({
        tableId: 'fantasy-table',
        rowSelector: '.tbl-fantasy-row',
        searchInputId: 'fantasy-search-input',
        paginationContainerId: 'fantasy-table-pagination',
        perPage: 10,
        colSpan: 3,
        noResultsMsg: 'No matching fantasy tips found.'
    });
});

function filterFantasyTable() {
    if (fantasyTableManager) fantasyTableManager.applyFilter(1);
}

function resetFantasySearch() {
    if (fantasyTableManager) fantasyTableManager.reset();
}
</script>
@endsection
