<x-layouts.app>
    <x-slot:title>Rekapitulasi 5 Hari Penjualan</x-slot:title>
    <x-slot:header>Rekapitulasi Penjualan 5 Hari</x-slot:header>

    <div class="space-y-6">
        <!-- Header & Export Action -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-block text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Laporan Komprehensif</span>
                <h2 class="text-base sm:text-lg font-black text-slate-900">Rekapitulasi Akhir Penjualan Stand PKTOS</h2>
                <p class="text-xs text-slate-500 font-medium">Akumulasi seluruh transaksi dari 5 hari acara dan 10 sesi shift.</p>
            </div>

            <a 
                href="{{ route('admin.reports.export-five-days') }}"
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition duration-150 shadow-xs text-center shrink-0 flex items-center justify-center cursor-pointer"
            >
                Download Excel (Rekap 5 Hari)
            </a>
        </div>

        <!-- 3 Grand KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md mb-2">
                        Total Omzet 5 Hari
                    </span>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Rp{{ number_format($recap['total_revenue_5_days'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    Penjualan bersih (struk berhasil)
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md mb-2">
                        Total Unit Terjual
                    </span>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ number_format($recap['total_units_5_days'], 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">unit</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    Seluruh 6 jenis barang stand
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-sky-800 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-md mb-2">
                        Total Transaksi Valid
                    </span>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ number_format($recap['total_transactions_5_days'], 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">struk</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-2 pt-2 border-t border-slate-100">
                    Total struk belanja tercatat
                </div>
            </div>
        </div>

        <!-- 1. Daily Breakdown Table -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">1. Ringkasan Harian (Senin — Jumat)</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Hari Acara</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4 text-center">Transaksi</th>
                            <th class="py-3 px-4 text-center">Unit Terjual</th>
                            <th class="py-3 px-4 text-right">Pendapatan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recap['daily_summary'] as $ds)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-4 font-bold text-slate-900">
                                    Hari {{ $ds['day']->day_name }}
                                </td>
                                <td class="py-2.5 px-4 text-slate-500 font-mono">
                                    {{ $ds['day']->event_date->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 text-center text-slate-700 font-medium">
                                    {{ $ds['tx_count'] }}
                                </td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-900">
                                    {{ $ds['units'] }} unit
                                </td>
                                <td class="py-2.5 px-4 text-right font-black text-slate-900">
                                    Rp{{ number_format($ds['revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-right">
                                    <a 
                                        href="{{ route('admin.reports.daily', ['event_day_id' => $ds['day']->id]) }}"
                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl text-xs transition"
                                    >
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50/80 font-bold text-xs">
                        <tr>
                            <td colspan="2" class="py-3 px-4 uppercase text-[11px] text-slate-500">TOTAL 5 HARI:</td>
                            <td class="py-3 px-4 text-center text-slate-800 font-black">{{ $recap['total_transactions_5_days'] }} trx</td>
                            <td class="py-3 px-4 text-center text-slate-900 font-black">{{ $recap['total_units_5_days'] }} unit</td>
                            <td class="py-3 px-4 text-right font-black text-sm sm:text-base text-emerald-800">
                                Rp{{ number_format($recap['total_revenue_5_days'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. Products Ranking & Cashiers (Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Peringkat Produk Terlaris -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">2. Peringkat Produk Terlaris</h3>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($recap['products_ranking'] as $idx => $pr)
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 transition">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-400 text-xs">{{ $idx + 1 }}.</span>
                                <div>
                                    <strong class="text-slate-900 text-xs sm:text-sm block">{{ $pr->product_name_snapshot }}</strong>
                                    <div class="text-[11px] text-slate-400 font-medium">Rp{{ number_format($pr->total_revenue, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <span class="font-black text-slate-900 text-xs sm:text-sm">{{ $pr->total_units }} unit</span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">Belum ada penjualan.</div>
                    @endforelse
                </div>
            </div>

            <!-- Rekap Petugas -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">3. Rekapitulasi Berdasarkan Petugas</h3>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($recap['cashiers_recap'] as $cr)
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 transition">
                            <div>
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $cr->user_name }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">@ {{ $cr->username }} • {{ $cr->tx_count }} transaksi</div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-slate-900 text-xs sm:text-sm">Rp{{ number_format($cr->total_revenue, 0, ',', '.') }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $cr->total_units }} unit</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">Belum ada data kasir.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 4. Shift 1 vs Shift 2 -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5">
            <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800 mb-3">4. Perbandingan Shift 1 vs Shift 2 (Akumulasi 5 Hari)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <span class="inline-block text-[10px] font-bold uppercase text-sky-800 bg-sky-100 px-2 py-0.5 rounded-md mb-2">Shift 1 (Pagi)</span>
                    <div class="text-lg sm:text-xl font-black text-slate-900 mt-1">
                        Rp{{ number_format($recap['shifts_comparison']['shift1_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">
                        {{ $recap['shifts_comparison']['shift1_units'] }} unit ({{ $recap['shifts_comparison']['shift1_tx_count'] }} transaksi)
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <span class="inline-block text-[10px] font-bold uppercase text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md mb-2">Shift 2 (Sore)</span>
                    <div class="text-lg sm:text-xl font-black text-slate-900 mt-1">
                        Rp{{ number_format($recap['shifts_comparison']['shift2_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">
                        {{ $recap['shifts_comparison']['shift2_units'] }} unit ({{ $recap['shifts_comparison']['shift2_tx_count'] }} transaksi)
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
