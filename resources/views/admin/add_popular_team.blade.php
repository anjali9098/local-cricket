@extends('layouts.admin')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0; color: #0f172a; letter-spacing: -0.02em;">Add Popular Team</h1>
            <p style="font-size: 0.95rem; color: #64748b; margin: 4px 0 0 0;">Add teams to feature in the Popular Teams list on homepage.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; font-weight: 700; color: #64748b; font-size: 0.9rem; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; background: white;">Back to Dashboard</a>
    </div>

    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <form method="POST" action="{{ route('admin.popular.post') }}" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            @csrf
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Team Name *</label>
                <input type="text" name="name" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; outline: none; box-sizing: border-box;">
            </div>
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">City</label>
                <input type="text" name="city" style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; outline: none; box-sizing: border-box;">
            </div>
            <div style="grid-column: 1 / -1;">
                <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Color Code</label>
                <input type="color" name="color_code" value="#2563eb" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 8px; height: 46px; box-sizing: border-box; background: white;">
            </div>
            <div style="grid-column: 1 / -1; margin-top: 10px;">
                <button type="submit" style="background: #0ea5e9; color: white; font-weight: 700; padding: 12px 32px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(14,165,233,0.2);">
                    Add Popular Team
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
