@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-10">

    <!-- Hero Title -->
    <div class="text-center space-y-3">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
            Powered by {{ config('services.ai_provider') === 'gemini' ? 'Google Gemini AI' : 'Local AI & Ollama' }}
        </div>
        <h1 class="heading-font text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white">
            Transform Notes into <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-purple-400 bg-clip-text text-transparent">Interactive Quizzes</span>
        </h1>
        <p class="text-slate-400 text-base max-w-2xl mx-auto">
            Upload your lecture notes, textbook chapters, or research papers in PDF or text format. Our AI extracts key concepts and generates customized practice tests.
        </p>
    </div>

    <!-- Main Generator Card -->
    <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl shadow-indigo-950/20">
        <form method="POST" action="{{ route('quiz.generate') }}" enctype="multipart/form-data" id="quizForm">
            @csrf

            @guest
                <div class="mb-6 p-4 rounded-xl border border-amber-500/30 bg-amber-950/20 text-amber-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                        <span><strong>Guest Mode active:</strong> Quizzes are processed in your session without database persistence. <a href="{{ route('register') }}" class="underline font-semibold hover:text-white">Sign up</a> to permanently save PDFs and test history!</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-200 hover:text-white font-medium">Log in</a>
                        <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">Sign up</a>
                    </div>
                </div>
            @endguest

            <!-- Step 1: Input Source Selection -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 text-xs flex items-center justify-center font-bold">1</span>
                        Choose Source Material
                    </label>

                    @if($savedDocuments->count() > 0)
                        <span class="text-xs text-slate-400">
                            {{ $savedDocuments->count() }} stored {{ Str::plural('PDF', $savedDocuments->count()) }} available
                        </span>
                    @endif
                </div>

                <!-- Input Type Switcher Tabs -->
                <div class="grid grid-cols-3 gap-2 p-1.5 bg-slate-950/80 rounded-xl border border-slate-800" id="sourceTabs">
                    <button type="button" 
                            onclick="switchTab('upload')" 
                            id="tab-upload" 
                            class="py-2.5 px-3 rounded-lg text-xs sm:text-sm font-medium transition-all text-white bg-indigo-600 shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>Upload File</span>
                    </button>

                    <button type="button" 
                            onclick="switchTab('saved')" 
                            id="tab-saved" 
                            class="py-2.5 px-3 rounded-lg text-xs sm:text-sm font-medium transition-all text-slate-400 hover:text-slate-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>Saved PDFs ({{ $savedDocuments->count() }})</span>
                    </button>

                    <button type="button" 
                            onclick="switchTab('text')" 
                            id="tab-text" 
                            class="py-2.5 px-3 rounded-lg text-xs sm:text-sm font-medium transition-all text-slate-400 hover:text-slate-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Paste Notes</span>
                    </button>
                </div>

                <!-- Pane 1: File Upload (PDF / TXT) -->
                <div id="pane-upload" class="space-y-3">
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-indigo-500/60 rounded-xl p-8 text-center bg-slate-950/40 hover:bg-slate-950/70 transition group cursor-pointer" onclick="document.getElementById('fileInput').click()">
                        <input type="file" 
                               name="file" 
                               id="fileInput" 
                               accept=".pdf,.txt" 
                               class="hidden" 
                               onchange="handleFileSelect(this)">

                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 group-hover:bg-indigo-500/20 transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-200">
                            <span class="text-indigo-400">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-slate-400 mt-1">PDF or TXT files (up to 10MB). Automatically stored in your library!</p>
                        
                        <div id="fileSelectedBadge" class="hidden mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-900/50 border border-indigo-500/30 text-xs font-medium text-indigo-200">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span id="fileNameDisplay">file.pdf</span>
                        </div>
                    </div>
                </div>

                <!-- Pane 2: Choose from Saved PDFs -->
                <div id="pane-saved" class="hidden space-y-3">
                    @guest
                        <div class="p-6 text-center border border-slate-800 rounded-xl bg-slate-950/40 space-y-2">
                            <p class="text-sm font-semibold text-slate-200">PDF Library Requires an Account</p>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto">Sign in or create a free account to save your textbook chapters and notes, and quickly generate quizzes from them anytime.</p>
                            <div class="flex items-center justify-center gap-2 pt-2">
                                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition">Sign in</a>
                                <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">Create Account</a>
                            </div>
                        </div>
                    @else
                        @if($savedDocuments->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                            @foreach($savedDocuments as $doc)
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 hover:bg-slate-900 hover:border-indigo-500/50 cursor-pointer transition">
                                    <input type="radio" 
                                           name="document_id" 
                                           value="{{ $doc->id }}" 
                                           {{ (old('document_id') == $doc->id || $selectedDocumentId == $doc->id) ? 'checked' : '' }}
                                           class="mt-1 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs uppercase font-bold px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                                {{ strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)) }}
                                            </span>
                                            <p class="text-sm font-semibold text-slate-200 truncate">{{ $doc->title }}</p>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $doc->original_name }} &bull; {{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center border border-slate-800 rounded-xl bg-slate-950/40 space-y-2">
                            <p class="text-sm text-slate-300">You haven't uploaded any PDFs yet.</p>
                            <p class="text-xs text-slate-500">Upload a PDF using the "Upload File" tab to automatically save it for future quizzes!</p>
                            <button type="button" onclick="switchTab('upload')" class="mt-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300">Switch to Upload &rarr;</button>
                        </div>
                    @endif
                    @endguest
                </div>

                <!-- Pane 3: Raw Notes Textarea -->
                <div id="pane-text" class="hidden space-y-2">
                    <textarea name="notes" 
                              id="notesArea" 
                              rows="6" 
                              placeholder="Paste text notes, summary, study guide or textbook excerpts here (minimum 50 characters)..." 
                              class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition text-sm font-mono">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-6 border-slate-800">

            <!-- Step 2: Quiz Format & Configuration -->
            <div class="space-y-6">
                <label class="text-sm font-semibold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 text-xs flex items-center justify-center font-bold">2</span>
                    Quiz Configuration
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- MCQ Option -->
                    <label class="relative flex items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-950/50 hover:bg-slate-900 hover:border-indigo-500/50 cursor-pointer transition group">
                        <input type="radio" name="type" value="mcq" checked class="text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                        <div>
                            <span class="block text-sm font-semibold text-white group-hover:text-indigo-300 transition">Multiple Choice</span>
                            <span class="block text-xs text-slate-400 mt-0.5">4 selectable options with one correct answer and explanation.</span>
                        </div>
                    </label>

                    <!-- Fill Blanks Option -->
                    <label class="relative flex items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-950/50 hover:bg-slate-900 hover:border-indigo-500/50 cursor-pointer transition group">
                        <input type="radio" name="type" value="fill" {{ old('type') === 'fill' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                        <div>
                            <span class="block text-sm font-semibold text-white group-hover:text-indigo-300 transition">Fill in the Blanks</span>
                            <span class="block text-xs text-slate-400 mt-0.5">Test recall by typing the missing keyword or term.</span>
                        </div>
                    </label>
                </div>

                <!-- AI Engine / Model Selection -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            AI Model / Engine
                        </label>
                        <span class="text-[11px] text-slate-400">Default: {{ config('services.ai_provider') === 'gemini' ? 'Google Gemini' : 'Ollama' }}</span>
                    </div>

                    @php
                        $defaultProvider = old('provider', config('services.ai_provider', 'gemini'));
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Google Gemini Option -->
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 hover:bg-slate-900 hover:border-indigo-500/50 cursor-pointer transition group">
                            <input type="radio" 
                                   name="provider" 
                                   value="gemini" 
                                   {{ $defaultProvider === 'gemini' ? 'checked' : '' }} 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-white group-hover:text-indigo-300 transition">
                                        ✨ Google Gemini
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Cloud & Fast</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">High-speed reasoning via {{ config('services.gemini.model', 'gemini-3.5-flash-lite') }}. Completes in ~1-2s.</p>
                            </div>
                        </label>

                        <!-- Ollama Option -->
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 hover:bg-slate-900 hover:border-indigo-500/50 cursor-pointer transition group">
                            <input type="radio" 
                                   name="provider" 
                                   value="ollama" 
                                   {{ $defaultProvider === 'ollama' ? 'checked' : '' }} 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-white group-hover:text-indigo-300 transition">
                                        🦙 Ollama
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Local & Offline</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Runs on your local server via {{ config('services.ollama.model', 'qwen2.5:3b') }}. 100% private.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Number of Questions -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-slate-950/50 border border-slate-800">
                    <div>
                        <span class="text-sm font-medium text-slate-200">Number of Questions</span>
                        <p class="text-xs text-slate-400">Choose how many questions to generate (1 - 10)</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @foreach([3, 5, 8, 10] as $count)
                            <button type="button" 
                                    onclick="setQuestionCount({{ $count }})" 
                                    class="q-count-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition {{ $count === 5 ? 'bg-indigo-600 text-white border-indigo-500' : 'bg-slate-900 text-slate-300 border-slate-800 hover:bg-slate-800' }}"
                                    data-count="{{ $count }}">
                                {{ $count }}
                            </button>
                        @endforeach
                        <input type="hidden" name="n" id="questionCountInput" value="5">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8">
                <button type="submit" 
                        id="generateBtn"
                        class="w-full py-4 px-6 rounded-xl text-base font-semibold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-xl shadow-indigo-600/30 transition duration-200 transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Generate AI Quiz</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Quizzes Quick Access -->
    @if($recentQuizzes->count() > 0)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="heading-font text-lg font-bold text-white">Recent Quizzes</h3>
                <a href="{{ route('history.index') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View All History &rarr;</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($recentQuizzes as $q)
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/50 hover:border-slate-700 transition flex items-center justify-between">
                        <div class="min-w-0 pr-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    {{ $q->type === 'mcq' ? 'Multiple Choice' : 'Fill in Blank' }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $q->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="text-sm font-semibold text-slate-200 truncate mt-1">{{ $q->title }}</h4>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $q->question_count }} questions</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('quiz.show', $q) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600/20 text-indigo-300 hover:bg-indigo-600/30 border border-indigo-500/30 transition">
                                Retake
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
    function switchTab(tab) {
        ['upload', 'saved', 'text'].forEach(t => {
            const btn = document.getElementById('tab-' + t);
            const pane = document.getElementById('pane-' + t);
            if (t === tab) {
                btn.className = "py-2.5 px-3 rounded-lg text-xs sm:text-sm font-medium transition-all text-white bg-indigo-600 shadow-sm flex items-center justify-center gap-2";
                pane.classList.remove('hidden');
            } else {
                btn.className = "py-2.5 px-3 rounded-lg text-xs sm:text-sm font-medium transition-all text-slate-400 hover:text-slate-200 flex items-center justify-center gap-2";
                pane.classList.add('hidden');
            }
        });
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const badge = document.getElementById('fileSelectedBadge');
            const nameDisplay = document.getElementById('fileNameDisplay');
            nameDisplay.innerText = input.files[0].name;
            badge.classList.remove('hidden');
        }
    }

    function setQuestionCount(count) {
        document.getElementById('questionCountInput').value = count;
        document.querySelectorAll('.q-count-btn').forEach(btn => {
            if (parseInt(btn.getAttribute('data-count')) === count) {
                btn.className = "q-count-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition bg-indigo-600 text-white border-indigo-500";
            } else {
                btn.className = "q-count-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition bg-slate-900 text-slate-300 border-slate-800 hover:bg-slate-800";
            }
        });
    }

    // Check if document was passed via query
    @if(!empty($selectedDocumentId))
        switchTab('saved');
    @endif

    // Loading indicator on submit
    document.getElementById('quizForm').addEventListener('submit', function() {
        const btn = document.getElementById('generateBtn');
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generating quiz with AI... (this may take up to a minute)
        `;
        btn.disabled = true;
    });
</script>
@endsection
