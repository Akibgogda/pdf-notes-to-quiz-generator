<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Notes to Quiz') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|outfit:500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .heading-font {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .bg-mesh {
            background-color: #030712;
            background-image: 
                radial-gradient(at 15% 15%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 85% 10%, rgba(168, 85, 247, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 90%, rgba(59, 130, 246, 0.12) 0px, transparent 50%);
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-mesh text-slate-200 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('quiz.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/25 ring-1 ring-white/20 group-hover:scale-105 transition-all duration-200">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="heading-font text-lg font-bold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
                                QuizCraft
                            </span>
                            <span class="text-[10px] tracking-wider uppercase font-semibold text-indigo-400">PDF to Quiz AI</span>
                        </div>
                    </a>

                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('quiz.index') }}" 
                           class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('quiz.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                            Create Quiz
                        </a>
                        <a href="{{ route('documents.index') }}" 
                           class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('documents.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                            My PDFs
                        </a>
                        <a href="{{ route('history.index') }}" 
                           class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('history.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                            Quiz History
                        </a>
                    </nav>
                </div>

                <!-- Right Nav / Profile -->
                <div class="flex items-center gap-3">
                    @auth
                        <div class="flex items-center gap-3">
                            <div class="hidden sm:flex flex-col text-right">
                                <span class="text-sm font-semibold text-slate-200 leading-tight">{{ Auth::user()->name }}</span>
                                <span class="text-xs text-slate-400">{{ Auth::user()->email }}</span>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm font-bold text-white shadow-md ring-2 ring-slate-800">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        title="Sign out"
                                        class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-all">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] font-medium text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                Guest Mode
                            </span>
                            <a href="{{ route('login') }}" class="px-3.5 py-1.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white transition">Log in</a>
                            <a href="{{ route('register') }}" class="px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-md transition">Sign up</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Sub-navigation -->
        <div class="md:hidden flex border-t border-slate-800/60 bg-slate-900/60 px-4 py-2 gap-2 overflow-x-auto">
            <a href="{{ route('quiz.index') }}" class="px-3 py-1 rounded text-xs font-medium {{ request()->routeIs('quiz.*') ? 'bg-indigo-500/20 text-indigo-300' : 'text-slate-400' }}">Create Quiz</a>
            <a href="{{ route('documents.index') }}" class="px-3 py-1 rounded text-xs font-medium {{ request()->routeIs('documents.*') ? 'bg-indigo-500/20 text-indigo-300' : 'text-slate-400' }}">My PDFs</a>
            <a href="{{ route('history.index') }}" class="px-3 py-1 rounded text-xs font-medium {{ request()->routeIs('history.*') ? 'bg-indigo-500/20 text-indigo-300' : 'text-slate-400' }}">Quiz History</a>
        </div>
    </header>

    <!-- Notification Toasts / Banners -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-6 flex items-center gap-3 p-4 rounded-xl border border-emerald-500/30 bg-emerald-950/40 text-emerald-300 shadow-lg shadow-emerald-950/20">
                <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium">{{ session('status') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl border border-rose-500/30 bg-rose-950/40 text-rose-300 shadow-lg shadow-rose-950/20">
                <div class="flex items-center gap-2 mb-2 font-semibold text-rose-200">
                    <svg class="w-5 h-5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Please correct the following:</span>
                </div>
                <ul class="list-disc list-inside text-sm space-y-1 text-rose-300/90 pl-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950/60 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} QuizCraft. AI-powered PDF study assistant.</p>
            <div class="flex items-center gap-4 text-slate-400">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ config('services.ai_provider') === 'gemini' ? 'Google Gemini AI Ready' : 'Ollama AI Ready' }}
                </span>
            </div>
        </div>
    </footer>

</body>
</html>
