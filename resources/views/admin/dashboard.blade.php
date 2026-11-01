<x-layouts.app>
    <x-slot:title>Dashboard Penjualan</x-slot:title>
    <x-slot:header>Ringkasan Penjualan</x-slot:header>

    <div class="space-y-6">
        <!-- Top 4 Metrics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Metric 1: Total Omzet -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-slate-300 transition duration-150 flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-md mb-2">
                        Total Omzet Stand
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                        Rp{{ number_format($totalRevenue, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    5 hari acara (bersih)
                </div>
            </div>

            <!-- Metric 2: Hari Ini -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-slate-300 transition duration-150 flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-indigo-800 bg-indigo-50 border border-indigo-200/80 px-2 py-0.5 rounded-md mb-2">
                        Penjualan Hari Ini
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                        Rp{{ number_format($todayRevenue, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    {{ $todayTransactions }} trx • {{ $todayUnits }} unit
                </div>
            </div>

            <!-- Metric 3: Transaksi Valid -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-slate-300 transition duration-150 flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-sky-800 bg-sky-50 border border-sky-200/80 px-2 py-0.5 rounded-md mb-2">
                        Total Transaksi
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ number_format($totalTransactions, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    Struk berhasil diproses
                </div>
            </div>

            <!-- Metric 4: Total Unit -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-slate-300 transition duration-150 flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-800 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-md mb-2">
                        Total Terjual
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ number_format($totalItemsSold, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">unit</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    Akumulasi seluruh produk
                </div>
            </div>
        </div>

        <!-- Shift Aktif & Status Stand -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-extrabold text-sm sm:text-base text-slate-900">
                            {{ $activeShift ? 'Shift Aktif: ' . $activeShift->shift_number . ' (' . $activeShift->name . ')' : 'Stand Sedang Tutup' }}
                        </h2>
                        @if ($activeShift)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                OPEN
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                CLOSED
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">
                        @if ($activeShift)
                            Hari {{ $activeShift->eventDay->day_name }} ({{ $activeShift->eventDay->event_date->format('d/m/Y') }}) • Dibuka pukul {{ $activeShift->opened_at?->format('H:i') ?? '08:00' }} WIB
                        @else
                            Buka shift pada menu Manajemen Shift untuk mengizinkan kasir menginput transaksi penjualan stand.
                        @endif
                    </div>
                </div>

                <!-- Action Buttons (No Icons) -->
                <div class="flex items-center gap-2 shrink-0">
                    @if ($activeShift)
                        <form action="{{ route('admin.shifts.close', $activeShift) }}" method="POST" onsubmit="return confirm('Tutup shift ini? Kasir tidak akan dapat bertransaksi setelah shift ditutup.')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-xs cursor-pointer">
                                Tutup Shift
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 text-indigo-700 border border-indigo-200 hover:border-indigo-300 font-bold text-xs sm:text-sm rounded-xl transition shadow-xs">
                        Kelola Shift
                    </a>
                </div>
            </div>

            <!-- Petugas On Duty Badges -->
            @if ($activeShift)
                <div class="pt-3.5 flex flex-wrap items-center gap-2">
                    <span class="text-slate-400 font-bold text-[10px] uppercase tracking-wider">Petugas On-Duty:</span>
                    @forelse ($activeShift->cashiers as $c)
                        <span class="inline-flex items-center px-3 py-1 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-800 text-xs font-semibold">
                            <span>{{ $c->name }}</span>
                            <span class="text-slate-400 text-[10px] ml-1">(@ {{ $c->username }})</span>
                        </span>
                    @empty
                        <span class="text-slate-400 text-xs italic">Belum ada petugas kasir ditugaskan di shift ini.</span>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- 5-Day Overview & Shift Comparison -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- 5-Day Summary (Col 7) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700">Ringkasan 5 Hari Acara</h3>
                        <p class="text-[11px] text-slate-400">Akumulasi penjualan tiap hari acara</p>
                    </div>
                    <a href="{{ route('admin.reports.five-days') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                        Laporan Detail
                    </a>
                </div>

                <div class="space-y-2">
                    @foreach ($recap['daily_summary'] as $ds)
                        <div class="p-3 rounded-xl border transition flex items-center justify-between {{ $ds['day']->status === 'active' ? 'bg-emerald-50/50 border-emerald-300 ring-2 ring-emerald-500/10' : 'bg-slate-50/70 border-slate-200/80 hover:bg-slate-100/50' }}">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-xs sm:text-sm text-slate-900">Hari {{ $ds['day']->day_name }}</span>
                                        <span class="text-slate-400 text-[11px] font-mono">({{ $ds['day']->event_date->format('d/m/Y') }})</span>
                                        @if ($ds['day']->status === 'active')
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                HARI INI
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 font-medium">{{ $ds['tx_count'] }} transaksi</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-xs sm:text-sm text-slate-900">Rp{{ number_format($ds['revenue'], 0, ',', '.') }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $ds['units'] }} unit</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shift 1 vs Shift 2 & Best Seller (Col 5) -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Shift 1 vs Shift 2 -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700 mb-3">Perbandingan Shift 1 vs Shift 2</h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="inline-block text-[10px] uppercase text-sky-800 bg-sky-100 font-bold px-2 py-0.5 rounded-md mb-1.5">Shift 1 (Pagi)</span>
                            <div class="font-black text-slate-900 text-sm sm:text-base">
                                Rp{{ number_format($recap['shifts_comparison']['shift1_revenue'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1 font-medium">
                                {{ $recap['shifts_comparison']['shift1_units'] }} unit ({{ $recap['shifts_comparison']['shift1_tx_count'] }} trx)
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="inline-block text-[10px] uppercase text-amber-800 bg-amber-100 font-bold px-2 py-0.5 rounded-md mb-1.5">Shift 2 (Sore)</span>
                            <div class="font-black text-slate-900 text-sm sm:text-base">
                                Rp{{ number_format($recap['shifts_comparison']['shift2_revenue'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1 font-medium">
                                {{ $recap['shifts_comparison']['shift2_units'] }} unit ({{ $recap['shifts_comparison']['shift2_tx_count'] }} trx)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Terlaris -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700 mb-3">Produk Paling Laris</h3>
                    <div class="divide-y divide-slate-100 text-xs">
                        @forelse ($recap['products_ranking']->take(4) as $idx => $pr)
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-400 text-xs">{{ $idx + 1 }}.</span>
                                    <span class="font-bold text-slate-800">{{ $pr->product_name_snapshot }}</span>
                                </div>
                                <span class="font-black text-slate-900">{{ $pr->total_units }} unit</span>
                            </div>
                        @empty
                            <div class="py-2 text-slate-400 text-xs italic">Belum ada data penjualan tercatat.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Transactions -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">Transaksi Terbaru</h3>
                </div>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                    Lihat Semua Transaksi
                </a>
            </div>

            <!-- Mobile Cards View (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
                @forelse ($recentTransactions as $tx)
                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.transactions.show', $tx) }}" class="font-mono font-bold text-xs text-indigo-700 hover:underline">
                                {{ $tx->invoice_code }}
                            </a>
                            @if ($tx->status === 'completed')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">BERHASIL</span>
                            @elseif ($tx->status === 'voided')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">VOID</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">KOREKSI</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-600 flex items-center justify-between">
                            <span>{{ $tx->cashier->name }} • {{ $tx->shift->eventDay->day_name }}</span>
                            <span class="text-slate-400 font-mono text-[11px]">{{ $tx->created_at->format('d/m H:i') }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-medium">{{ $tx->total_items }} unit</span>
                            <span class="font-black text-sm text-slate-900">{{ $tx->formatted_total }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 text-xs">Belum ada transaksi tersimpan.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Shift</th>
                            <th class="py-3 px-4">Item</th>
                            <th class="py-3 px-4 text-right">Total (Rp)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentTransactions as $tx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-4 font-mono font-bold text-indigo-700">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->invoice_code }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m H:i') }}</td>
                                <td class="py-2.5 px-4 text-slate-800 font-medium">{{ $tx->cashier->name }}</td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $tx->shift->eventDay->day_name }} — {{ $tx->shift->shift_number }}</td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $tx->total_items }} unit</td>
                                <td class="py-2.5 px-4 text-right font-black text-slate-900">{{ $tx->formatted_total }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    @if ($tx->status === 'completed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">BERHASIL</span>
                                    @elseif ($tx->status === 'voided')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">VOID</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">KOREKSI</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada transaksi tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
