<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'POS Kasir' }} — Stand-PKTOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-800 flex flex-col pb-20 md:pb-6 selection:bg-indigo-500 selection:text-white">
    <!-- Cashier Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 py-3 shadow-xs">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('cashier.dashboard') }}" class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-base shadow-xs">
                    🎪
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900 text-sm sm:text-base leading-tight">Stand-PKTOS</span>
                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700">Kasir</span>
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium">Petugas: <strong class="text-slate-800">{{ auth()->user()->name }}</strong></div>
                </div>
            </div>

            <!-- Active Shift Badge & User Action -->
            <div class="flex items-center gap-2">
                @php
                    $myActiveShift = \App\Models\Shift::with('eventDay')
                        ->where('status', 'open')
                        ->where(function($q) {
                            if (!auth()->user()->isAdmin()) {
                                $q->whereHas('cashiers', fn($sub) => $sub->where('users.id', auth()->id()));
                            }
                        })
                        ->first();
                @endphp

                @if ($myActiveShift)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="truncate max-w-[120px] sm:max-w-none">{{ $myActiveShift->eventDay->day_name }} — {{ $myActiveShift->shift_number }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Shift Terkunci</span>
                    </div>
                @endif

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 bg-indigo-50 rounded-lg">Admin ↗</a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-3 sm:p-5">
        @if (session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation Bar (Thumb Friendly) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40 py-2 px-6 flex items-center justify-around shadow-lg">
        <a href="{{ route('cashier.pos') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('cashier.pos') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="text-[10px]">POS Kasir</span>
        </a>

        <a href="{{ route('cashier.history') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('cashier.history') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span class="text-[10px]">Riwayat Saya</span>
        </a>

        <a href="{{ route('cashier.dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('cashier.dashboard') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[10px]">Shift & Profil</span>
        </a>
    </nav>
</body>
</html>
