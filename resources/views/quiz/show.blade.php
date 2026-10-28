@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    <!-- Quiz Header Banner -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                        {{ $quiz->type === 'mcq' ? 'Multiple Choice' : 'Fill in the Blank' }}
                    </span>
                    @if(!empty($quiz->provider))
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $quiz->provider === 'gemini' ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/20' }}">
                            {{ $quiz->provider === 'gemini' ? '✨ Gemini' : '🦙 Ollama' }}
                        </span>
                    @endif
                    <span class="text-xs text-slate-400">&bull; {{ $quiz->question_count }} Questions</span>
                </div>
                <h1 class="heading-font text-2xl sm:text-3xl font-bold text-white">{{ $quiz->title }}</h1>
                @if($quiz->document)
                    <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Source Document: <span class="text-slate-300 font-medium">{{ $quiz->document->original_name }}</span>
                    </p>
                @endif
            </div>

            <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white bg-slate-800/80 hover:bg-slate-800 transition shrink-0">
                &larr; Back to Generator
            </a>
        </div>
    </div>

    <!-- Quiz Questions Form -->
    <form method="POST" action="{{ route('quiz.submit', $quiz) }}" id="submitQuizForm" class="space-y-6">
        @csrf

        @foreach($quiz->questions as $i => $q)
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 sm:p-7 backdrop-blur-xl hover:border-slate-700/80 transition shadow-lg">
                <div class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-400 text-sm font-bold flex items-center justify-center shrink-0 mt-0.5">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base sm:text-lg font-semibold text-slate-100 leading-relaxed">
                            {{ $q['question'] }}
                        </h3>

                        <div class="mt-4">
                            @if($quiz->type === 'mcq')
                                <div class="grid grid-cols-1 gap-2.5">
                                    @foreach($q['options'] as $optIndex => $opt)
                                        <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:bg-indigo-950/20 hover:border-indigo-500/40 cursor-pointer transition group">
                                            <input type="radio" 
                                                   name="answers[{{ $i }}]" 
                                                   value="{{ $optIndex }}" 
                                                   required
                                                   class="text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                                            <span class="text-sm text-slate-300 group-hover:text-white transition">
                                                {{ $opt }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-2">
                                    <input type="text" 
                                           name="answers[{{ $i }}]" 
                                           required 
                                           autocomplete="off"
                                           placeholder="Type your answer here..."
                                           class="w-full px-4 py-3 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition text-sm">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Submit Button -->
        <div class="sticky bottom-6 z-20 bg-slate-950/90 backdrop-blur-md p-4 rounded-2xl border border-slate-800 shadow-2xl flex items-center justify-between gap-4">
            <span class="text-xs text-slate-400">Answer all questions to see your score and detailed explanations</span>

            <button type="submit" 
                    id="submitBtn"
                    class="py-3 px-6 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-600/30 transition transform active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Submit Answers</span>
            </button>
        </div>
    </form>

</div>

<script>
    document.getElementById('submitQuizForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = 'Submitting & Scoring...';
        btn.disabled = true;
    });
</script>
@endsection
