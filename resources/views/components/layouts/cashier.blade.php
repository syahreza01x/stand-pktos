<!DOCTYPE html>
<html lang="id" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kasir' }} — Stand-PKTOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-zinc-800 flex flex-col pb-16 md:pb-6">
    <!-- Cashier Top Navigation Bar -->
    <header class="bg-white border-b border-zinc-200 sticky top-0 z-30 px-4 py-2.5">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div>
                <a href="{{ route('cashier.dashboard') }}" class="font-bold text-sm tracking-tight text-zinc-900 block">
                    STAND PKTOS
                </a>
                <div class="text-[11px] text-zinc-500">
                    Petugas: <strong class="text-zinc-800">{{ auth()->user()->name }}</strong>
                </div>
            </div>

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
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                        {{ $myActiveShift->eventDay->day_name }} — {{ $myActiveShift->shift_number }} (OPEN)
                    </span>
                @else
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-zinc-200 text-zinc-700">
                        Shift Tutup
                    </span>
                @endif

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-xs text-zinc-600 hover:text-zinc-900 px-2 py-1 rounded bg-zinc-100">
                        Admin ↗
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-2.5 py-1 text-xs text-zinc-500 hover:text-rose-600 hover:bg-zinc-100 rounded transition font-medium">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-3xl w-full mx-auto p-3 sm:p-5">
        @if (session('success'))
            <div class="mb-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-3 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation Bar (Minimalist Tabs) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-zinc-200 z-40 py-2 px-4 flex items-center justify-around text-xs">
        <a href="{{ route('cashier.pos') }}" class="py-1 px-3 rounded {{ request()->routeIs('cashier.pos') ? 'bg-zinc-900 text-white font-bold' : 'text-zinc-600 hover:text-zinc-900' }}">
            Input POS
        </a>
        <a href="{{ route('cashier.history') }}" class="py-1 px-3 rounded {{ request()->routeIs('cashier.history') ? 'bg-zinc-900 text-white font-bold' : 'text-zinc-600 hover:text-zinc-900' }}">
            Riwayat Saya
        </a>
        <a href="{{ route('cashier.dashboard') }}" class="py-1 px-3 rounded {{ request()->routeIs('cashier.dashboard') ? 'bg-zinc-900 text-white font-bold' : 'text-zinc-600 hover:text-zinc-900' }}">
            Info Shift
        </a>
    </nav>
</body>
</html>
