@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    <!-- Header & Quick Upload Card -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="heading-font text-3xl font-extrabold text-white">Stored Documents & PDFs</h1>
            <p class="text-sm text-slate-400 mt-1">Manage all your uploaded study files and generate new quizzes anytime without re-uploading.</p>
        </div>

        <button type="button" 
                onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition self-start md:self-auto">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Upload New PDF</span>
        </button>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="max-w-lg w-full bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="heading-font text-lg font-bold text-white">Upload Document to Library</h3>
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Title (Optional)</label>
                    <input type="text" name="title" placeholder="e.g. Chapter 4 - Cell Division" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Select File (.pdf, .txt)</label>
                    <input type="file" name="file" accept=".pdf,.txt" required class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                    <p class="text-[11px] text-slate-500 mt-1">Minimum 50 characters of readable text. Up to 10MB.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md">
                        Upload & Store
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents List / Grid -->
    @if($documents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($documents as $doc)
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 backdrop-blur-sm hover:border-slate-700 transition flex flex-col justify-between space-y-4 shadow-lg group">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-800 text-slate-300">
                                {{ strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)) }}
                            </span>
                        </div>

                        <div class="mt-3">
                            <h3 class="text-base font-bold text-white group-hover:text-indigo-300 transition truncate" title="{{ $doc->title }}">
                                {{ $doc->title }}
                            </h3>
                            <p class="text-xs text-slate-400 truncate mt-0.5" title="{{ $doc->original_name }}">{{ $doc->original_name }}</p>
                        </div>

                        <div class="flex items-center gap-4 text-xs text-slate-400 mt-4 pt-3 border-t border-slate-800/80">
                            <span>{{ number_format($doc->file_size / 1024, 1) }} KB</span>
                            <span>&bull;</span>
                            <span>{{ $doc->quizzes_count }} {{ Str::plural('quiz', $doc->quizzes_count) }} generated</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between gap-2 pt-2">
                        <a href="{{ route('quiz.index', ['document_id' => $doc->id]) }}" 
                           class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold text-white bg-indigo-600/90 hover:bg-indigo-500 text-center shadow-sm transition">
                            Generate Quiz
                        </a>

                        <a href="{{ route('documents.download', $doc) }}" 
                           title="Download original file"
                           class="p-2 rounded-xl text-slate-400 hover:text-white bg-slate-800/80 hover:bg-slate-700 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>

                        <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Delete this stored document? Existing quizzes will be preserved.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    title="Delete document"
                                    class="p-2 rounded-xl text-slate-400 hover:text-rose-400 bg-slate-800/80 hover:bg-rose-500/10 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $documents->links() }}
        </div>
    @else
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="heading-font text-lg font-bold text-white">No documents stored yet</h3>
            <p class="text-sm text-slate-400 max-w-md mx-auto">Upload textbook chapters, articles, or research papers as PDF to store them securely and generate practice quizzes.</p>
            <button type="button" 
                    onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition inline-block">
                Upload your first PDF
            </button>
        </div>
    @endif

</div>
@endsection
