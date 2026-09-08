@extends('layouts.admin')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">

    <!-- Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0; color: #0f172a; letter-spacing: -0.02em;">NOTIFICATIONS & REQUESTS</h1>
            <p style="font-size: 0.95rem; color: #64748b; margin: 4px 0 0 0;">Manage local tournament creation approvals and deletion requests.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; font-weight: 700; color: #64748b; font-size: 0.9rem; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; background: white;">Back to Dashboard</a>
    </div>

    <!-- Main Container -->
    <div style="display: flex; flex-direction: column; gap: 32px;">

        <!-- Section 1: Approval Requests -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #d97706; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                📂 Tournament Approval Requests ({{ $pendingApprovals->count() }})
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($pendingApprovals as $t)
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px 20px; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 1rem; margin-bottom: 4px;">{{ $t->name }}</div> 
                            <div style="color: #64748b; font-size: 0.85rem;">
                                <strong>Format:</strong> {{ $t->format }} &bull; 
                                <strong>City:</strong> {{ $t->city ?? 'Local' }} &bull; 
                                <strong>Created by:</strong> <span style="color: #0284c7; font-weight: 600;">{{ $t->user->email ?? 'Local User' }}</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <form method="POST" action="{{ route('admin.approve-tournament', $t->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1);">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.delete-tournament', $t->id) }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to reject/delete this tournament?');">
                                @csrf
                                <button type="submit" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);">
                                    Reject & Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 32px; color: #94a3b8; font-weight: 600;">
                        No pending tournament approval requests.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Section 2: Deletion Requests -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #b91c1c; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                🗑️ Tournament Deletion Requests ({{ $pendingDeletions->count() }})
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($pendingDeletions as $t)
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #fff5f5; border: 1px solid #fee2e2; padding: 16px 20px; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 800; color: #991b1b; font-size: 1rem; margin-bottom: 4px;">{{ $t->name }}</div> 
                            <div style="color: #64748b; font-size: 0.85rem;">
                                <strong>Format:</strong> {{ $t->format }} &bull; 
                                <strong>City:</strong> {{ $t->city ?? 'Local' }} &bull; 
                                <strong>Requested by:</strong> <span style="color: #b91c1c; font-weight: 600;">{{ $t->user->email ?? 'Local User' }}</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <form method="POST" action="{{ route('admin.delete-tournament', $t->id) }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to permanently delete this tournament?');">
                                @csrf
                                <button type="submit" style="background: #b91c1c; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer; box-shadow: 0 2px 4px rgba(185, 28, 28, 0.1);">
                                    Approve Deletion
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.reject-deletion', $t->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" style="background: #64748b; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                    Reject Deletion
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 32px; color: #94a3b8; font-weight: 600;">
                        No pending tournament deletion requests.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
