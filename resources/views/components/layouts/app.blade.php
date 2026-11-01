<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — Stand-PKTOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-800 selection:bg-indigo-500 selection:text-white" x-data="{ sidebarOpen: false }">
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
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 md:hidden"
            style="display: none;"
        ></div>

        <!-- Sidebar Navigation -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out shrink-0 shadow-lg md:shadow-none"
        >
            <!-- Brand -->
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <div class="font-black text-base tracking-tight text-slate-900">STAND PKTOS</div>
                    <div class="text-[11px] text-slate-500 font-medium">Sistem Penjualan Stand Acara</div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>📊</span>
                    <span>Dashboard Utama</span>
                </a>

                <div class="pt-3 pb-1 px-3.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Penjualan & Shift</div>

                <a href="{{ route('admin.shifts.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.shifts.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>⏱</span>
                    <span>Manajemen Shift</span>
                </a>

                <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.transactions.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>🧾</span>
                    <span>Riwayat Transaksi</span>
                </a>

                <a href="{{ route('cashier.pos') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition font-bold shadow-xs">
                    <span class="flex items-center gap-2">
                        <span>🛒</span>
                        <span>Buka Kasir POS</span>
                    </span>
                    <span class="text-xs">↗</span>
                </a>

                <div class="pt-3 pb-1 px-3.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Laporan</div>

                <a href="{{ route('admin.reports.daily') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.reports.daily') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>📅</span>
                    <span>Laporan Shift & Harian</span>
                </a>

                <a href="{{ route('admin.reports.five-days') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.reports.five-days') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>📈</span>
                    <span>Rekapitulasi 5 Hari</span>
                </a>

                <div class="pt-3 pb-1 px-3.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Data Master</div>

                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.products.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>🏷</span>
                    <span>Katalog & Harga</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>👥</span>
                    <span>Akun Petugas</span>
                </a>

                <a href="{{ route('admin.event-days.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.event-days.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>🗓</span>
                    <span>Jadwal Hari Acara</span>
                </a>

                <a href="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>📋</span>
                    <span>Log Aktivitas</span>
                </a>
            </nav>

            <!-- User Footer & Logout -->
            <div class="p-3.5 border-t border-slate-200 flex items-center justify-between text-xs bg-slate-50/50">
                <div class="truncate pr-2">
                    <div class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-slate-500">Administrator</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition font-bold cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Topbar Header (Responsive, Roomy, Mobile-Friendly) -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-6 py-3 shadow-xs">
                @php
                    $openShift = \App\Models\Shift::with('eventDay')->where('status', 'open')->first();
                @endphp

                <!-- Desktop / Tablet Header Layout -->
                <div class="hidden sm:flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <button @click="sidebarOpen = true" class="md:hidden px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold flex items-center gap-1.5">
                            <span>☰</span>
                            <span>Menu</span>
                        </button>
                        <h1 class="text-base font-extrabold text-slate-900 truncate">{{ $header ?? $title ?? 'Dashboard' }}</h1>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        @if ($openShift)
                            <div class="text-xs px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Shift Open:</span>
                                <span>Hari {{ $openShift->eventDay->day_name }} — {{ $openShift->shift_number }}</span>
                            </div>
                        @else
                            <div class="text-xs px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 font-medium">
                                Shift Tutup
                            </div>
                        @endif

                        <a href="{{ route('cashier.pos') }}" target="_blank" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1">
                            <span>Kasir POS</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>

                <!-- Mobile Header Layout (Streamlined, Non-Cramped) -->
                <div class="sm:hidden flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <button @click="sidebarOpen = true" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold flex items-center gap-1 shrink-0">
                            <span>☰</span>
                            <span>Menu</span>
                        </button>
                        <span class="font-extrabold text-xs text-slate-900 truncate">
                            {{ $header ?? $title ?? 'Dashboard' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        @if ($openShift)
                            <div class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $openShift->shift_number }}</span>
                            </div>
                        @endif

                        <a href="{{ route('cashier.pos') }}" target="_blank" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-[11px] font-bold transition shadow-xs">
                            POS ↗
                        </a>
                    </div>
                </div>
            </header>

            <!-- Alerts Banner & Main Content -->
            <main class="flex-1 p-4 sm:p-6 max-w-6xl w-full mx-auto space-y-5">
                @if (session('success'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-medium shadow-xs">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-medium shadow-xs">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('info'))
                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs sm:text-sm font-medium shadow-xs">
                        {{ session('info') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
