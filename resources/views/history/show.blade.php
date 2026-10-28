@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    <!-- Header Breadcrumb & Controls -->
    <div class="flex items-center justify-between">
        <a href="{{ route('history.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
            &larr; Back to History
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('quiz.show', $attempt->quiz) }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md transition">
                Retake This Quiz
            </a>
        </div>
    </div>

    <!-- Attempt Overview Card -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-full {{ $attempt->quiz->type === 'mcq' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-purple-500/10 text-purple-400 border border-purple-500/20' }}">
                        {{ $attempt->quiz->type === 'mcq' ? 'Multiple Choice' : 'Fill in Blank' }}
                    </span>
                    @if(!empty($attempt->quiz->provider))
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $attempt->quiz->provider === 'gemini' ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/20' }}">
                            {{ $attempt->quiz->provider === 'gemini' ? '✨ Gemini' : '🦙 Ollama' }}
                        </span>
                    @endif
                    <span class="text-xs text-slate-400">&bull; Attempted on {{ $attempt->created_at->format('M d, Y \a\t h:i A') }}</span>
                </div>
                <h1 class="heading-font text-2xl font-bold text-white">{{ $attempt->quiz->title }}</h1>
                @if($attempt->quiz->document)
                    <p class="text-xs text-slate-400 mt-1">Source: <span class="text-slate-300 font-medium">{{ $attempt->quiz->document->original_name }}</span></p>
                @endif
            </div>

            <!-- Score Pill -->
            <div class="flex items-center gap-3 p-3.5 rounded-2xl border {{ $attempt->percentage >= 80 ? 'border-emerald-500/30 bg-emerald-950/20 text-emerald-400' : ($attempt->percentage >= 50 ? 'border-amber-500/30 bg-amber-950/20 text-amber-400' : 'border-rose-500/30 bg-rose-950/20 text-rose-400') }} self-start sm:self-auto">
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Score</span>
                    <span class="heading-font text-2xl font-black">{{ $attempt->score }}<span class="text-sm font-normal text-slate-400">/{{ $attempt->total }}</span></span>
                </div>
                <span class="text-base font-bold px-2 py-1 rounded-lg bg-slate-900/80">{{ $attempt->percentage }}%</span>
            </div>
        </div>
    </div>

    <!-- Question-by-Question Review -->
    <div class="space-y-4">
        <h3 class="heading-font text-lg font-bold text-white">Submitted Answers & Review</h3>

        <div class="space-y-4">
            @foreach($attempt->details as $i => $row)
                <div class="p-6 rounded-2xl border {{ $row['correct'] ? 'border-emerald-500/30 bg-emerald-950/10' : 'border-rose-500/30 bg-rose-950/10' }} backdrop-blur-sm space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-md {{ $row['correct'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }} text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                {{ $i + 1 }}
                            </span>
                            <h4 class="text-base font-semibold text-slate-100">
                                {{ $row['question'] }}
                            </h4>
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
