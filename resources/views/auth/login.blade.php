@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-slate-900/70 border border-slate-800/80 rounded-2xl p-8 backdrop-blur-xl shadow-2xl shadow-indigo-950/30">
        <div class="text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 items-center justify-center shadow-lg shadow-indigo-500/25 ring-1 ring-white/20 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="heading-font text-2xl font-bold tracking-tight text-white">Welcome back</h2>
            <p class="mt-2 text-sm text-slate-400">Log in to access your saved PDFs and quiz history</p>
        </div>

        <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
                <div class="relative">
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition text-sm @error('email') border-rose-500 @enderror" 
                           placeholder="you@example.com">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                </div>
                <input id="password" 
                       name="password" 
                       type="password" 
                       autocomplete="current-password" 
                       required 
                       class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition text-sm @error('password') border-rose-500 @enderror" 
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" 
                           name="remember" 
                           type="checkbox" 
                           class="h-4 w-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    <label for="remember" class="ml-2 block text-xs text-slate-400">Remember me for 30 days</label>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-lg shadow-indigo-600/30 transition duration-200 transform active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                Sign in to your account
            </button>

            <div class="text-center pt-2 space-y-3">
                <p class="text-xs text-slate-400">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition">Create an account</a>
                </p>

                <div class="relative py-2">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-800"></div></div>
                    <div class="relative flex justify-center text-xs"><span class="px-2 bg-slate-900 text-slate-500 uppercase">Or</span></div>
                </div>

                <a href="{{ route('quiz.index') }}" 
                   class="block w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-800 border border-slate-700/60 transition text-center">
                    Continue as Guest &rarr;
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
