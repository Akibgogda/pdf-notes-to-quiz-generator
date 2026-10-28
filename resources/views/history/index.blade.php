@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    <!-- Header & Statistics Cards -->
    <div class="space-y-6">
        <div>
            <h1 class="heading-font text-3xl font-extrabold text-white">Quiz History & Progress</h1>
            <p class="text-sm text-slate-400 mt-1">Review past quiz attempts, track performance metrics, and retake study tests.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Stat 1 -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 backdrop-blur-sm shadow-md">
                <span class="text-xs uppercase font-medium text-slate-400 block mb-1">Total Attempts</span>
                <span class="heading-font text-2xl sm:text-3xl font-bold text-white">{{ $totalAttempts }}</span>
            </div>

            <!-- Stat 2 -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 backdrop-blur-sm shadow-md">
                <span class="text-xs uppercase font-medium text-slate-400 block mb-1">Average Score</span>
                <span class="heading-font text-2xl sm:text-3xl font-bold {{ $averageScore >= 80 ? 'text-emerald-400' : ($averageScore >= 50 ? 'text-amber-400' : 'text-slate-200') }}">
                    {{ $averageScore }}%
                </span>
            </div>

            <!-- Stat 3 -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 backdrop-blur-sm shadow-md">
                <span class="text-xs uppercase font-medium text-slate-400 block mb-1">Saved PDFs</span>
                <span class="heading-font text-2xl sm:text-3xl font-bold text-indigo-400">{{ $totalDocuments }}</span>
            </div>

            <!-- Stat 4 -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 backdrop-blur-sm shadow-md">
                <span class="text-xs uppercase font-medium text-slate-400 block mb-1">Quizzes Created</span>
                <span class="heading-font text-2xl sm:text-3xl font-bold text-purple-400">{{ $totalQuizzes }}</span>
            </div>
        </div>
    </div>

    <!-- Attempts Table / Cards -->
    @if($attempts->count() > 0)
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden backdrop-blur-sm shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="heading-font text-base font-bold text-white">Recent Attempts</h3>
                <span class="text-xs text-slate-400">{{ $attempts->total() }} recorded {{ Str::plural('attempt', $attempts->total()) }}</span>
            </div>

            <div class="divide-y divide-slate-800/80">
                @foreach($attempts as $att)
                    <div class="p-5 sm:px-6 hover:bg-slate-850/50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $att->quiz->type === 'mcq' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-purple-500/10 text-purple-400 border border-purple-500/20' }}">
                                    {{ $att->quiz->type === 'mcq' ? 'MCQ' : 'Fill Blank' }}
                                </span>
                                @if(!empty($att->quiz->provider))
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $att->quiz->provider === 'gemini' ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/20' }}">
                                        {{ $att->quiz->provider === 'gemini' ? '✨ Gemini' : '🦙 Ollama' }}
                                    </span>
                                @endif

                                <span class="text-xs text-slate-400">
                                    {{ $att->created_at->format('M d, Y &bull; h:i A') }}
                                </span>
                            </div>

                            <h4 class="text-base font-bold text-slate-100 truncate">
                                {{ $att->quiz->title }}
                            </h4>

                            @if($att->quiz->document)
                                <p class="text-xs text-slate-400 flex items-center gap-1 truncate">
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    {{ $att->quiz->document->original_name }}
                                </p>
                            @endif
                        </div>

                        <!-- Score & Actions -->
                        <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0">
                            <!-- Score Pill -->
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border {{ $att->percentage >= 80 ? 'border-emerald-500/30 bg-emerald-950/20 text-emerald-400' : ($att->percentage >= 50 ? 'border-amber-500/30 bg-amber-950/20 text-amber-400' : 'border-rose-500/30 bg-rose-950/20 text-rose-400') }}">
                                <span class="heading-font text-base font-bold">{{ $att->score }}/{{ $att->total }}</span>
                                <span class="text-xs font-semibold">({{ $att->percentage }}%)</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('history.show', $att) }}" 
                                   class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-200 hover:text-white hover:bg-slate-700 transition">
                                    Review
                                </a>

                                <a href="{{ route('quiz.show', $att->quiz) }}" 
                                   class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600/80 text-white hover:bg-indigo-500 transition">
                                    Retake
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-slate-800">
                {{ $attempts->links() }}
            </div>
        </div>
    @else
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="heading-font text-lg font-bold text-white">No quiz history yet</h3>
            <p class="text-sm text-slate-400 max-w-md mx-auto">Take your first quiz generated from your notes or uploaded PDF to see your test results and analytics recorded here.</p>
            <a href="{{ route('quiz.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition inline-block">
                Generate your first quiz
            </a>
        </div>
    @endif

</div>
@endsection
