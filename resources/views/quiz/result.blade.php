@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    <!-- Score Header Card -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/60 border border-slate-800 rounded-3xl p-8 backdrop-blur-xl shadow-2xl text-center">
        <!-- Glow highlight -->
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <span class="text-xs uppercase font-bold tracking-wider px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                Quiz Complete
            </span>

            <h1 class="heading-font text-3xl sm:text-4xl font-extrabold text-white">
                {{ $quiz->title }}
            </h1>

            <!-- Big Circular Score Display -->
            <div class="flex items-center justify-center my-6">
                <div class="relative flex flex-col items-center justify-center w-36 h-36 rounded-full border-4 {{ $attempt->percentage >= 80 ? 'border-emerald-500 bg-emerald-950/30 text-emerald-400' : ($attempt->percentage >= 50 ? 'border-amber-500 bg-amber-950/30 text-amber-400' : 'border-rose-500 bg-rose-950/30 text-rose-400') }} shadow-2xl">
                    <span class="heading-font text-4xl font-black">{{ $attempt->score }}<span class="text-xl font-normal text-slate-400">/{{ $attempt->total }}</span></span>
                    <span class="text-xs font-bold tracking-wide mt-1">{{ $attempt->percentage }}%</span>
                </div>
            </div>

            <p class="text-sm text-slate-300 max-w-md mx-auto">
                @if($attempt->percentage === 100)
                    🏆 Outstanding! Perfect score. You've mastered this material!
                @elseif($attempt->percentage >= 80)
                    🎉 Great job! You have a solid grasp of these concepts.
                @elseif($attempt->percentage >= 50)
                    👍 Good effort! Review the explanations below to improve your score.
                @else
                    📚 Keep practicing! Study the explanations below and try again.
                @endif
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a href="{{ route('quiz.show', $quiz) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                    Retake Quiz
                </a>
                <a href="{{ route('quiz.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">
                    Create New Quiz
                </a>
                <a href="{{ route('history.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 transition">
                    View History
                </a>
            </div>
        </div>
    </div>

    <!-- Question-by-Question Review -->
    <div class="space-y-4">
        <h2 class="heading-font text-xl font-bold text-white flex items-center gap-2">
            <span>Detailed Review & Explanations</span>
        </h2>

        <div class="space-y-4">
            @foreach($attempt->details as $i => $row)
                <div class="p-6 rounded-2xl border {{ $row['correct'] ? 'border-emerald-500/30 bg-emerald-950/10' : 'border-rose-500/30 bg-rose-950/10' }} backdrop-blur-sm space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-md {{ $row['correct'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }} text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                {{ $i + 1 }}
                            </span>
                            <h3 class="text-base font-semibold text-slate-100">
                                {{ $row['question'] }}
                            </h3>
                        </div>

                        <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-full border {{ $row['correct'] ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border-rose-500/30 text-rose-400' }}">
                            {{ $row['correct'] ? 'Correct' : 'Incorrect' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800">
                            <span class="text-xs uppercase font-medium text-slate-400 block mb-1">Your Answer</span>
                            <span class="font-medium {{ $row['correct'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $row['yours'] ?: '(No answer provided)' }}
                            </span>
                        </div>

                        @if(!$row['correct'])
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800">
                                <span class="text-xs uppercase font-medium text-slate-400 block mb-1">Correct Answer</span>
                                <span class="font-medium text-emerald-400">
                                    {{ $row['right'] }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if(!empty($row['explanation']))
                        <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs text-slate-300 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-semibold text-indigo-300">Explanation:</span>
                                {{ $row['explanation'] }}
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
