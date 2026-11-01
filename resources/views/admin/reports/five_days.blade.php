<x-layouts.app>
    <x-slot:title>Rekapitulasi 5 Hari Penjualan Stand</x-slot:title>
    <x-slot:header>Rekapitulasi Keseluruhan 5 Hari (Senin — Jumat)</x-slot:header>

    <div class="space-y-6">
        <!-- Top Action & Export Header -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase text-indigo-600">Laporan Akhir Acara Stand Organisasi</span>
                <h2 class="text-xl font-black text-slate-900 mt-0.5">Rekapitulasi 5 Hari Penjualan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Akumulasi seluruh transaksi dari 5 hari acara dan 10 shift.</p>
            </div>

            <a 
                href="{{ route('admin.reports.export-five-days') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition shrink-0 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Download Rekap Excel (Lengkap)</span>
            </a>
        </div>

        <!-- 3 Grand KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider block">Total Pendapatan 5 Hari</span>
                <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">
                    Rp{{ number_format($recap['total_revenue_5_days'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-500 mt-1">Akumulasi omzet bersih (exclude void)</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider block">Total Unit Terjual 5 Hari</span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                    {{ number_format($recap['total_units_5_days'], 0, ',', '.') }} <span class="text-base font-bold text-slate-500">unit</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Total produk yang berhasil dibeli pelanggan</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider block">Total Transaksi Selesai</span>
                <div class="text-2xl sm:text-3xl font-black text-indigo-600 mt-2">
                    {{ number_format($recap['total_transactions_5_days'], 0, ',', '.') }} <span class="text-base font-bold text-slate-500">struk</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Jumlah pelanggan terlayani</div>
            </div>
        </div>

        <!-- 1. Daily Breakdown Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-base">1. Ringkasan Harian (Senin — Jumat)</h3>
                <p class="text-xs text-slate-500">Total unit barang terjual dan pendapatan harian</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50/50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Hari Acara</th>
                            <th class="py-3 px-4">Tanggal Pelaksanaan</th>
                            <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                            <th class="py-3 px-4 text-center">Unit Terjual</th>
                            <th class="py-3 px-4 text-right">Total Pendapatan (Rp)</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recap['daily_summary'] as $ds)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                                    Hari {{ $ds['day']->day_name }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $ds['day']->event_date->format('d F Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ $ds['tx_count'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-black text-slate-900">
                                    {{ $ds['units'] }} unit
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">
                                    Rp{{ number_format($ds['revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a 
                                        href="{{ route('admin.reports.daily', ['event_day_id' => $ds['day']->id]) }}"
                                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition"
                                    >
                                        Buka Rincian →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold">
                        <tr>
                            <td colspan="2" class="py-4 px-4 uppercase text-xs text-slate-700">TOTAL 5 HARI:</td>
                            <td class="py-4 px-4 text-center text-indigo-700">{{ $recap['total_transactions_5_days'] }} trx</td>
                            <td class="py-4 px-4 text-center text-slate-900 font-black">{{ $recap['total_units_5_days'] }} unit</td>
                            <td class="py-4 px-4 text-right text-base font-black text-emerald-700">
                                Rp{{ number_format($recap['total_revenue_5_days'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. Product Rankings & Cashier Performance (Side by side) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Ranking Produk Terlaris -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-base">2. Peringkat Produk Terlaris</h3>
                    <p class="text-xs text-slate-500">Penjualan per produk diurutkan dari volume unit terbanyak</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recap['products_ranking'] as $idx => $pr)
                        <div class="p-3.5 flex items-center justify-between text-xs sm:text-sm hover:bg-slate-50/60 transition">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg font-bold text-xs flex items-center justify-center {{ $idx === 0 ? 'bg-amber-100 text-amber-800' : ($idx === 1 ? 'bg-slate-200 text-slate-700' : ($idx === 2 ? 'bg-orange-100 text-orange-800' : 'bg-slate-50 text-slate-400')) }}">
                                    #{{ $idx + 1 }}
                                </span>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $pr->product_name_snapshot }}</div>
                                    <div class="text-[11px] text-slate-400">Total Omzet: Rp{{ number_format($pr->total_revenue, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="font-black text-slate-900">{{ $pr->total_units }} unit</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">Belum ada penjualan produk.</div>
                    @endforelse
                </div>
            </div>

            <!-- Rekap Petugas / Kasir -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-base">3. Rekapitulasi Berdasarkan Petugas</h3>
                    <p class="text-xs text-slate-500">Total penjualan dan produktivitas masing-masing kasir</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recap['cashiers_recap'] as $cr)
                        <div class="p-3.5 flex items-center justify-between text-xs sm:text-sm hover:bg-slate-50/60 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center">
                                    👤
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $cr->user_name }}</div>
                                    <div class="text-[11px] text-slate-400">@ {{ $cr->username }} • {{ $cr->tx_count }} transaksi</div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-black text-slate-900">Rp{{ number_format($cr->total_revenue, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-slate-500">{{ $cr->total_units }} unit barang</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">Belum ada data kasir.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Shift 1 vs Shift 2 Comparison -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <h3 class="font-bold text-slate-900 text-base mb-1">4. Perbandingan Total Shift 1 vs Shift 2 (5 Hari)</h3>
            <p class="text-xs text-slate-500 mb-4">Menganalisis perputaran omzet pagi hari dibandingkan sore hari</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider block">Shift 1 (Pagi)</span>
                        <div class="text-xl font-black text-indigo-950 mt-1">
                            Rp{{ number_format($recap['shifts_comparison']['shift1_revenue'], 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-indigo-700 mt-0.5">
                            {{ $recap['shifts_comparison']['shift1_units'] }} unit terjual ({{ $recap['shifts_comparison']['shift1_tx_count'] }} transaksi)
                        </div>
                    </div>
                    <div class="text-3xl">🌅</div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-700 uppercase tracking-wider block">Shift 2 (Sore)</span>
                        <div class="text-xl font-black text-amber-950 mt-1">
                            Rp{{ number_format($recap['shifts_comparison']['shift2_revenue'], 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-amber-700 mt-0.5">
                            {{ $recap['shifts_comparison']['shift2_units'] }} unit terjual ({{ $recap['shifts_comparison']['shift2_tx_count'] }} transaksi)
                        </div>
                    </div>
                    <div class="text-3xl">🌇</div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
