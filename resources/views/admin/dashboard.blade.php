<x-layouts.app>
    <x-slot:title>Dashboard Penjualan Stand</x-slot:title>
    <x-slot:header>Ringkasan Eksekutif Penjualan Stand</x-slot:header>

    <div class="space-y-6">
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Revenue -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Omzet Stand</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">💰</span>
                </div>
                <div class="text-2xl font-black text-slate-900 mt-2">
                    Rp{{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <span class="text-emerald-600 font-bold">5 Hari Acara</span>
                    <span>• Keseluruhan shift</span>
                </div>
            </div>

            <!-- Today's Revenue -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Penjualan Hari Ini</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">📅</span>
                </div>
                <div class="text-2xl font-black text-indigo-600 mt-2">
                    Rp{{ number_format($todayRevenue, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    <strong>{{ $todayTransactions }}</strong> transaksi ({{ $todayUnits }} item)
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Transaksi Valid</span>
                    <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm">🧾</span>
                </div>
                <div class="text-2xl font-black text-slate-900 mt-2">
                    {{ number_format($totalTransactions, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Semua transaksi aktif
                </div>
            </div>

            <!-- Total Units Sold -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Barang Terjual</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">📦</span>
                </div>
                <div class="text-2xl font-black text-slate-900 mt-2">
                    {{ number_format($totalItemsSold, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-500">unit</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Akumulasi produk laku
                </div>
            </div>
        </div>

        <!-- Shift Aktif & Petugas Bertugas Section -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $activeShift ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-base">
                        {{ $activeShift ? '⚡' : '⏸' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base text-slate-900">
                                {{ $activeShift ? 'Shift Sedang Aktif: ' . $activeShift->shift_number . ' (' . $activeShift->name . ')' : 'Tidak Ada Shift yang Sedang Open' }}
                            </h3>
                            @if ($activeShift)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">OPEN</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">STANDBY</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if ($activeShift)
                                Hari {{ $activeShift->eventDay->day_name }} ({{ $activeShift->eventDay->event_date->format('d M Y') }}) • Dibuka {{ $activeShift->opened_at?->format('H:i') }}
                            @else
                                Buka shift pada menu Manajemen Shift untuk mengizinkan kasir bertransaksi.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($activeShift)
                        <form action="{{ route('admin.shifts.close', $activeShift) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menutup shift ini? Kasir tidak akan dapat bertransaksi setelah shift ditutup.')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                🛑 Tutup Shift Sekarang
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                        Kelola Shift →
                    </a>
                </div>
            </div>

            <!-- Petugas On Duty -->
            @if ($activeShift)
                <div class="pt-4">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-2">Petugas yang Ditugaskan di Shift Ini:</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($activeShift->cashiers as $c)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="font-bold text-slate-800">{{ $c->name }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">(@ {{ $c->username }})</span>
                            </div>
                        @empty
                            <span class="text-xs text-amber-600">Belum ada kasir yang ditugaskan ke shift ini.</span>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <!-- 5-Day Trend & Shift Comparison Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- 5-Day Summary Cards (Col 7) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Performa 5 Hari Acara</h3>
                        <p class="text-xs text-slate-500">Rekapitulasi penjualan stand Senin s/d Jumat</p>
                    </div>
                    <a href="{{ route('admin.reports.five-days') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        Laporan Lengkap →
                    </a>
                </div>

                <div class="space-y-3">
                    @foreach ($recap['daily_summary'] as $ds)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 text-center font-bold text-xs uppercase px-2 py-1 rounded-lg {{ $ds['day']->day_name === 'Senin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ substr($ds['day']->day_name, 0, 3) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">Hari {{ $ds['day']->day_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $ds['day']->event_date->format('d/m/Y') }} • {{ $ds['tx_count'] }} Transaksi</div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-extrabold text-sm text-slate-900">Rp{{ number_format($ds['revenue'], 0, ',', '.') }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">{{ $ds['units'] }} unit terjual</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shift Comparison & Best Selling Products (Col 5) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Shift 1 vs Shift 2 -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <h3 class="font-bold text-slate-900 text-base mb-1">Perbandingan Shift 1 & 2</h3>
                    <p class="text-xs text-slate-500 mb-4">Kontribusi pendapatan antar shift</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-xl bg-indigo-50 border border-indigo-100">
                            <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block">Shift 1 (Pagi)</span>
                            <div class="text-base sm:text-lg font-black text-indigo-900 mt-1">
                                Rp{{ number_format($recap['shifts_comparison']['shift1_revenue'], 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-indigo-700 mt-0.5">
                                {{ $recap['shifts_comparison']['shift1_units'] }} unit ({{ $recap['shifts_comparison']['shift1_tx_count'] }} trx)
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-100">
                            <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block">Shift 2 (Sore)</span>
                            <div class="text-base sm:text-lg font-black text-amber-900 mt-1">
                                Rp{{ number_format($recap['shifts_comparison']['shift2_revenue'], 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-amber-700 mt-0.5">
                                {{ $recap['shifts_comparison']['shift2_units'] }} unit ({{ $recap['shifts_comparison']['shift2_tx_count'] }} trx)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Terlaris -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <h3 class="font-bold text-slate-900 text-base mb-1">Top Produk Terlaris</h3>
                    <p class="text-xs text-slate-500 mb-3">Peringkat produk berdasarkan unit terjual</p>

                    <div class="divide-y divide-slate-100">
                        @forelse ($recap['products_ranking']->take(4) as $idx => $pr)
                            <div class="py-2.5 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 text-slate-400 font-bold">#{{ $idx + 1 }}</span>
                                    <span class="font-bold text-slate-800">{{ $pr->product_name_snapshot }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-extrabold text-slate-900">{{ $pr->total_units }} unit</span>
                                    <div class="text-[10px] text-slate-400">Rp{{ number_format($pr->total_revenue, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-400 text-xs">Belum ada data penjualan produk.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Transactions Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Transaksi Penjualan Terbaru</h3>
                    <p class="text-xs text-slate-500">Mencatat aktivitas kasir secara real-time</p>
                </div>
                <a href="{{ route('admin.transactions.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Lihat Semua ({{ $totalTransactions }}) →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Shift</th>
                            <th class="py-3 px-4">Item Terjual</th>
                            <th class="py-3 px-4 text-right">Total (Rp)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentTransactions as $tx)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-700">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->invoice_code }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $tx->created_at->format('d/m H:i') }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800">
                                    {{ $tx->cashier->name }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $tx->shift->eventDay->day_name }} — {{ $tx->shift->shift_number }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $tx->total_items }} unit
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-slate-900">
                                    {{ $tx->formatted_total }}
                                </td>
                                <td class="py-3 px-4 text-center">
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
