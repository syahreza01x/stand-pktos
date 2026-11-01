<!DOCTYPE html>
<html lang="id" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — Stand-PKTOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-zinc-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition-opacity ease-linear duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-40 md:hidden"
            style="display: none;"
        ></div>

        <!-- Sidebar Navigation -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed md:static inset-y-0 left-0 z-50 w-60 bg-white border-r border-zinc-200 flex flex-col transition-transform duration-150 ease-in-out shrink-0"
        >
            <!-- Brand -->
            <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm tracking-tight text-zinc-900">STAND PKTOS</div>
                    <div class="text-[11px] text-zinc-500">Sistem Penjualan Stand</div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-zinc-400 hover:text-zinc-700 text-xs font-semibold px-2 py-1">
                    Tutup
                </button>
            </div>

            <!-- Navigation Links (Simple, Clean, Minimalist) -->
            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto text-xs font-medium">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Dashboard
                </a>

                <div class="pt-3 pb-1 px-3 text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">Penjualan & Shift</div>

                <a href="{{ route('admin.shifts.index') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.shifts.*') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Manajemen Shift
                </a>

                <a href="{{ route('admin.transactions.index') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.transactions.*') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Riwayat Transaksi
                </a>

                <a href="{{ route('cashier.pos') }}" target="_blank" class="block px-3 py-2 rounded-lg text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition font-medium">
                    Buka Kasir POS ↗
                </a>

                <div class="pt-3 pb-1 px-3 text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">Laporan</div>

                <a href="{{ route('admin.reports.daily') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.reports.daily') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Laporan Shift & Harian
                </a>

                <a href="{{ route('admin.reports.five-days') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.reports.five-days') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Rekapitulasi 5 Hari
                </a>

                <div class="pt-3 pb-1 px-3 text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">Data Stand</div>

                <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.products.*') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Produk & Harga
                </a>

                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Akun Petugas
                </a>

                <a href="{{ route('admin.event-days.index') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.event-days.*') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Hari Acara
                </a>

                <a href="{{ route('admin.activity-logs.index') }}" class="block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.activity-logs.*') ? 'bg-zinc-900 text-white font-semibold' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    Log Aktivitas
                </a>
            </nav>

            <!-- User Footer & Logout -->
            <div class="p-3 border-t border-zinc-200 flex items-center justify-between text-xs">
                <div class="truncate pr-2">
                    <div class="font-semibold text-zinc-900 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-zinc-500">Administrator</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-2 py-1 text-xs text-zinc-500 hover:text-rose-600 hover:bg-zinc-100 rounded transition font-medium">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Topbar Header -->
            <header class="bg-white border-b border-zinc-200 sticky top-0 z-30 px-4 sm:px-6 py-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button @click="sidebarOpen = true" class="md:hidden px-2 py-1 rounded border border-zinc-300 text-zinc-700 text-xs">
                        Menu
                    </button>
                    <h1 class="text-sm sm:text-base font-bold text-zinc-900">{{ $header ?? $title ?? 'Dashboard' }}</h1>
                </div>

                <!-- Shift Status Indicator -->
                <div class="flex items-center gap-2">
                    @php
                        $openShift = \App\Models\Shift::with('eventDay')->where('status', 'open')->first();
                    @endphp
                    @if ($openShift)
                        <div class="text-[11px] px-2.5 py-1 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium">
                            <span class="font-bold">Shift Open:</span> {{ $openShift->eventDay->day_name }} — {{ $openShift->shift_number }}
                        </div>
                    @else
                        <div class="text-[11px] px-2.5 py-1 rounded bg-zinc-100 border border-zinc-200 text-zinc-600 font-medium">
                            Shift Tutup
                        </div>
                    @endif

                    <a href="{{ route('cashier.pos') }}" class="px-3 py-1 bg-zinc-900 hover:bg-black text-white rounded text-xs font-medium transition">
                        Kasir POS
                    </a>
                </div>
            </header>

            <!-- Alerts Banner -->
            <main class="flex-1 p-4 sm:p-6 max-w-6xl w-full mx-auto space-y-5">
                @if (session('success'))
                    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-xs font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('info'))
                    <div class="p-3 rounded-lg bg-zinc-100 border border-zinc-200 text-zinc-800 text-xs font-medium">
                        {{ session('info') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
