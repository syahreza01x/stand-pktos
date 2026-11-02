<!DOCTYPE html>
<html lang="id" class="bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kasir' }} — Stand-PKTOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 antialiased text-slate-800 flex flex-col pb-20 md:pb-8 selection:bg-indigo-500 selection:text-white">
    <!-- Cashier Top Header (Clean, Minimal, To The Point) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-3.5 py-2.5 sm:px-4 sm:py-3 shadow-xs">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <a href="{{ route('cashier.dashboard') }}" class="font-black text-sm tracking-tight text-slate-900 shrink-0">
                    STAND PKTOS
                </a>
                <span class="text-slate-300 text-xs">|</span>
                <span class="text-xs text-slate-600 font-semibold truncate max-w-[150px] sm:max-w-none">
                    {{ auth()->user()->name }}
                </span>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-1.5 shrink-0">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-[11px] text-indigo-700 hover:text-indigo-900 font-bold px-2 py-1 bg-indigo-50 border border-indigo-200 rounded-lg transition shadow-2xs">
                        Admin
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-2 py-1 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition font-bold cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 py-4 sm:px-6 sm:py-6">
        @if (session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-medium shadow-xs">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-medium shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation Bar (Pure Text, Simple & Minimalist) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-40 py-2.5 px-3 shadow-lg">
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <a 
                href="{{ route('cashier.pos') }}" 
                class="py-2.5 px-2 rounded-xl transition font-bold block {{ request()->routeIs('cashier.pos') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Input POS
            </a>
            <a 
                href="{{ route('cashier.history') }}" 
                class="py-2.5 px-2 rounded-xl transition font-bold block {{ request()->routeIs('cashier.history') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Riwayat Saya
            </a>
            <a 
                href="{{ route('cashier.dashboard') }}" 
                class="py-2.5 px-2 rounded-xl transition font-bold block {{ request()->routeIs('cashier.dashboard') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                Info Shift
            </a>
        </div>
    </nav>
</body>
</html>
