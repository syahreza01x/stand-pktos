<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kasir' }} — Stand-PKTOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-800 flex flex-col pb-24 md:pb-8 selection:bg-indigo-500 selection:text-white">
    <!-- Cashier Top Header (Roomy, Clean, Non-Cramped) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 py-3 shadow-xs">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('cashier.dashboard') }}" class="font-black text-sm sm:text-base tracking-tight text-slate-900 block truncate">
                    STAND PKTOS
                </a>
                <div class="text-xs text-slate-500 truncate font-medium">
                    Petugas: <strong class="text-slate-800">{{ auth()->user()->name }}</strong>
                </div>
            </div>

            <!-- Shift Status & Actions -->
            <div class="flex items-center gap-2 shrink-0">
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
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <span class="truncate max-w-[130px] sm:max-w-none">{{ $myActiveShift->eventDay->day_name }} • {{ $myActiveShift->shift_number }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                        <span>Shift Tutup</span>
                    </div>
                @endif

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-xs text-indigo-700 hover:text-indigo-900 font-bold px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition shadow-xs">
                        Admin ↗
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition font-bold cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container with Generous Padding -->
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

    <!-- Mobile Bottom Navigation Bar (Roomy, Comfortable, Thumb-Friendly) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-40 py-2.5 px-3 shadow-lg">
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <a 
                href="{{ route('cashier.pos') }}" 
                class="py-2.5 px-2 rounded-xl transition font-bold flex flex-col items-center gap-0.5 {{ request()->routeIs('cashier.pos') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                <span>🛒</span>
                <span>Input POS</span>
            </a>
            <a 
                href="{{ route('cashier.history') }}" 
                class="py-2.5 px-2 rounded-xl transition font-bold flex flex-col items-center gap-0.5 {{ request()->routeIs('cashier.history') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                <span>📜</span>
                <span>Riwayat Saya</span>
            </a>
            <a 
                href="{{ route('cashier.dashboard') }}" 
                class="py-2.5 px-2 rounded-xl transition font-bold flex flex-col items-center gap-0.5 {{ request()->routeIs('cashier.dashboard') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                <span>⏱</span>
                <span>Info Shift</span>
            </a>
        </div>
    </nav>
</body>
</html>
