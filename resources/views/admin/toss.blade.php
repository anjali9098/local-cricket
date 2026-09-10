@extends(isset($isLocal) && $isLocal ? 'layouts.local' : 'layouts.admin')

@section('content')
<main class="min-h-[85vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- Coin Icon + Title --}}
        <div class="text-center mb-6">
            <div class="text-4xl sm:text-5xl mb-3 animate-bounce">🪙</div>
            <h1 class="text-2xl sm:text-3xl font-black {{ isset($isLocal) && $isLocal ? 'text-white' : 'text-slate-900' }} tracking-tight uppercase m-0">MATCH TOSS</h1>
            <p class="text-xs sm:text-sm {{ isset($isLocal) && $isLocal ? 'text-gray-400' : 'text-slate-500 font-semibold' }} mt-1">
                {{ $match->team1?->name ?? 'Team 1' }} <span class="text-sky-500 font-bold">vs</span> {{ $match->team2?->name ?? 'Team 2' }}
            </p>
        </div>

        {{-- Toss Form Card --}}
        <div class="{{ isset($isLocal) && $isLocal ? 'bg-[#0d1117] border-[#30363d]' : 'bg-white border-slate-200 shadow-xl' }} border rounded-2xl p-6 sm:p-8">
            <form method="POST" action="{{ isset($isLocal) && $isLocal ? route('local.save-toss', $match->id) : route('admin.save-toss', $match->id) }}" class="flex flex-col gap-5">
                @csrf

                {{-- Toss Winner --}}
                <div>
                    <label class="block text-xs font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-300' : 'text-slate-700' }} uppercase tracking-wider mb-1.5">Toss winner *</label>
                    <select name="toss_winner_id" required class="w-full {{ isset($isLocal) && $isLocal ? 'bg-[#161b22] border-[#30363d] text-white' : 'bg-slate-50 border-slate-300 text-slate-900 focus:bg-white' }} border rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-500 outline-none">
                        <option value="" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-gray-400' : 'bg-white text-slate-400' }}">Choose team that won toss...</option>
                        <option value="{{ $match->team1_id }}" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">{{ $match->team1?->name ?? 'Team 1' }}</option>
                        <option value="{{ $match->team2_id }}" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">{{ $match->team2?->name ?? 'Team 2' }}</option>
                    </select>
                </div>

                {{-- Decision --}}
                <div>
                    <label class="block text-xs font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-300' : 'text-slate-700' }} uppercase tracking-wider mb-1.5">Elected Decision *</label>
                    <select name="decision" required class="w-full {{ isset($isLocal) && $isLocal ? 'bg-[#161b22] border-[#30363d] text-white' : 'bg-slate-50 border-slate-300 text-slate-900 focus:bg-white' }} border rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-500 outline-none">
                        <option value="bat" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">🏏 Elected to Bat First</option>
                        <option value="field" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">⚾ Elected to Field First</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 px-4 rounded-xl text-sm transition-all shadow-lg shadow-blue-500/25 mt-2">
                    Confirm & Start Match
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="javascript:history.back()" class="text-xs sm:text-sm font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-400 hover:text-white' : 'text-slate-500 hover:text-slate-900' }} transition-colors">← Go Back</a>
        </div>
    </div>
</main>
@endsection
