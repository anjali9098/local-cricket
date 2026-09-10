@extends(isset($isLocal) && $isLocal ? 'layouts.local' : 'layouts.admin')

@section('content')
<main class="min-h-[85vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg">

        {{-- Title --}}
        <div class="text-center mb-6">
            <h1 class="text-xl sm:text-2xl font-black {{ isset($isLocal) && $isLocal ? 'text-white' : 'text-slate-900' }} uppercase tracking-tight m-0">CHOOSE OPENING PLAYERS</h1>
            <p class="text-xs sm:text-sm {{ isset($isLocal) && $isLocal ? 'text-gray-400' : 'text-slate-500 font-semibold' }} mt-1.5 flex items-center justify-center gap-2 flex-wrap">
                <span>Innings 1</span> &bull; 
                <span>Batting: <strong class="text-sky-500">{{ $battingTeam->name }}</strong></span> &bull; 
                <span>Bowling: <strong class="text-emerald-500">{{ $bowlingTeam->name }}</strong></span>
            </p>
        </div>

        @if($battingTeam->players->isEmpty() || $bowlingTeam->players->isEmpty())
            <div class="{{ isset($isLocal) && $isLocal ? 'bg-amber-500/10 border-amber-500/30' : 'bg-amber-50 border-amber-300' }} border rounded-2xl p-4 sm:p-5 mb-5 text-center">
                <div class="font-black {{ isset($isLocal) && $isLocal ? 'text-amber-400' : 'text-amber-800' }} text-sm mb-1 flex items-center justify-center gap-1.5">
                    <span>⚠️</span> Players Not Found in Squads!
                </div>
                <p class="text-xs {{ isset($isLocal) && $isLocal ? 'text-gray-300' : 'text-slate-600' }} mb-3">
                    To start this match, players must be added to both teams. Please add players first:
                </p>
                <div class="flex gap-2 flex-wrap justify-center">
                    @if($battingTeam->players->isEmpty())
                        <a href="{{ isset($isLocal) && $isLocal ? route('local.manage-tournament', $match->tournament_id) : route('admin.players') }}" target="_blank" class="text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg transition-all">
                            + Add Players for {{ $battingTeam->name }}
                        </a>
                    @endif
                    @if($bowlingTeam->players->isEmpty())
                        <a href="{{ isset($isLocal) && $isLocal ? route('local.manage-tournament', $match->tournament_id) : route('admin.players') }}" target="_blank" class="text-xs font-bold {{ isset($isLocal) && $isLocal ? 'bg-[#161b22] border-[#30363d] text-white' : 'bg-slate-100 border-slate-300 text-slate-800' }} border px-3 py-1.5 rounded-lg transition-all">
                            + Add Players for {{ $bowlingTeam->name }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Form Card --}}
        <div class="{{ isset($isLocal) && $isLocal ? 'bg-[#0d1117] border-[#30363d]' : 'bg-white border-slate-200 shadow-xl' }} border rounded-2xl p-6 sm:p-8">
            <form method="POST" action="{{ isset($isLocal) && $isLocal ? route('local.start-innings', $match->id) : route('admin.start-innings', $match->id) }}" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="batting_team_id" value="{{ $battingTeam->id }}">
                <input type="hidden" name="bowling_team_id" value="{{ $bowlingTeam->id }}">

                {{-- Striker --}}
                <div>
                    <label class="block text-xs font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-300' : 'text-slate-700' }} uppercase tracking-wider mb-1.5">
                        Striker <span class="{{ isset($isLocal) && $isLocal ? 'text-gray-400' : 'text-slate-500 font-semibold' }} lowercase">({{ $battingTeam->name }})</span> *
                    </label>
                    <select name="striker_id" required class="w-full {{ isset($isLocal) && $isLocal ? 'bg-[#161b22] border-[#30363d] text-white' : 'bg-slate-50 border-slate-300 text-slate-900 focus:bg-white' }} border rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-500 outline-none">
                        <option value="" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-gray-400' : 'bg-white text-slate-400' }}">-- Choose Striker --</option>
                        @foreach($battingTeam->players as $player)
                            <option value="{{ $player->id }}" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">{{ $player->name }} ({{ $player->role }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Non-striker --}}
                <div>
                    <label class="block text-xs font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-300' : 'text-slate-700' }} uppercase tracking-wider mb-1.5">
                        Non-striker <span class="{{ isset($isLocal) && $isLocal ? 'text-gray-400' : 'text-slate-500 font-semibold' }} lowercase">({{ $battingTeam->name }})</span> *
                    </label>
                    <select name="non_striker_id" required class="w-full {{ isset($isLocal) && $isLocal ? 'bg-[#161b22] border-[#30363d] text-white' : 'bg-slate-50 border-slate-300 text-slate-900 focus:bg-white' }} border rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-500 outline-none">
                        <option value="" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-gray-400' : 'bg-white text-slate-400' }}">-- Choose Non-striker --</option>
                        @foreach($battingTeam->players as $player)
                            <option value="{{ $player->id }}" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">{{ $player->name }} ({{ $player->role }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Opening Bowler --}}
                <div>
                    <label class="block text-xs font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-300' : 'text-slate-700' }} uppercase tracking-wider mb-1.5">
                        Opening Bowler <span class="{{ isset($isLocal) && $isLocal ? 'text-gray-400' : 'text-slate-500 font-semibold' }} lowercase">({{ $bowlingTeam->name }})</span> *
                    </label>
                    <select name="bowler_id" required class="w-full {{ isset($isLocal) && $isLocal ? 'bg-[#161b22] border-[#30363d] text-white' : 'bg-slate-50 border-slate-300 text-slate-900 focus:bg-white' }} border rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-500 outline-none">
                        <option value="" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-gray-400' : 'bg-white text-slate-400' }}">-- Choose Opening Bowler --</option>
                        @foreach($bowlingTeam->players as $player)
                            <option value="{{ $player->id }}" class="{{ isset($isLocal) && $isLocal ? 'bg-[#161b22] text-white' : 'bg-white text-slate-900' }}">{{ $player->name }} ({{ $player->role }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 px-4 rounded-xl text-sm transition-all shadow-lg shadow-blue-500/25 mt-2">
                    Start Innings & Open Scorer
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="javascript:history.back()" class="text-xs sm:text-sm font-bold {{ isset($isLocal) && $isLocal ? 'text-gray-400 hover:text-white' : 'text-slate-500 hover:text-slate-900' }} transition-colors">← Go Back</a>
        </div>
    </div>
</main>
@endsection
