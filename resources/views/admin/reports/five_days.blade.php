<x-layouts.app>
    <x-slot:title>Rekapitulasi 5 Hari Penjualan</x-slot:title>
    <x-slot:header>Rekapitulasi Penjualan 5 Hari (Senin — Jumat)</x-slot:header>

    <div class="space-y-4">
        <!-- Header & Export Action -->
        <div class="bg-white rounded-lg border border-zinc-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-zinc-900">Rekapitulasi Akhir Penjualan Stand PKTOS</h2>
                <p class="text-xs text-zinc-500">Akumulasi seluruh transaksi dari 5 hari acara dan 10 shift.</p>
            </div>

            <a 
                href="{{ route('admin.reports.export-five-days') }}"
                class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded transition text-center shrink-0 cursor-pointer"
            >
                Download Excel (Rekap 5 Hari)
            </a>
        </div>

        <!-- 3 Grand KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white p-3.5 rounded-lg border border-zinc-200">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 block">Total Omzet 5 Hari</span>
                <div class="text-xl font-bold text-zinc-900 mt-1">
                    Rp{{ number_format($recap['total_revenue_5_days'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Penjualan bersih (exclude void)</div>
            </div>

            <div class="bg-white p-3.5 rounded-lg border border-zinc-200">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 block">Total Unit Terjual</span>
                <div class="text-xl font-bold text-zinc-900 mt-1">
                    {{ number_format($recap['total_units_5_days'], 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">unit</span>
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Seluruh jenis barang</div>
            </div>

            <div class="bg-white p-3.5 rounded-lg border border-zinc-200">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 block">Total Transaksi Valid</span>
                <div class="text-xl font-bold text-zinc-900 mt-1">
                    {{ number_format($recap['total_transactions_5_days'], 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">struk</span>
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Transaksi selesai</div>
            </div>
        </div>

        <!-- 1. Daily Breakdown Table -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200">
                <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">1. Ringkasan Harian (Senin — Jumat)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Hari Acara</th>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3 text-center">Transaksi</th>
                            <th class="py-2.5 px-3 text-center">Unit Terjual</th>
                            <th class="py-2.5 px-3 text-right">Pendapatan (Rp)</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($recap['daily_summary'] as $ds)
                            <tr class="hover:bg-zinc-50">
                                <td class="py-2.5 px-3 font-bold text-zinc-900">
                                    Hari {{ $ds['day']->day_name }}
                                </td>
                                <td class="py-2.5 px-3 text-zinc-500 font-mono">
                                    {{ $ds['day']->event_date->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-3 text-center text-zinc-700">
                                    {{ $ds['tx_count'] }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-zinc-900">
                                    {{ $ds['units'] }} unit
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-zinc-900">
                                    Rp{{ number_format($ds['revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <a 
                                        href="{{ route('admin.reports.daily', ['event_day_id' => $ds['day']->id]) }}"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-[11px] transition"
                                    >
                                        Buka Rincian →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-zinc-200 bg-zinc-50 font-bold text-xs">
                        <tr>
                            <td colspan="2" class="py-2.5 px-3 uppercase text-[11px] text-zinc-500">TOTAL 5 HARI:</td>
                            <td class="py-2.5 px-3 text-center text-zinc-800">{{ $recap['total_transactions_5_days'] }} trx</td>
                            <td class="py-2.5 px-3 text-center text-zinc-900 font-black">{{ $recap['total_units_5_days'] }} unit</td>
                            <td class="py-2.5 px-3 text-right font-black text-sm text-zinc-900">
                                Rp{{ number_format($recap['total_revenue_5_days'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. Products Ranking & Cashiers (Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Peringkat Produk Terlaris -->
            <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">2. Peringkat Produk Terlaris</span>
                </div>

                <div class="divide-y divide-zinc-100 text-xs">
                    @forelse ($recap['products_ranking'] as $idx => $pr)
                        <div class="p-2.5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-zinc-400 mr-1.5">{{ $idx + 1 }}.</span>
                                <strong class="text-zinc-900">{{ $pr->product_name_snapshot }}</strong>
                                <div class="text-[11px] text-zinc-400">Rp{{ number_format($pr->total_revenue, 0, ',', '.') }}</div>
                            </div>
                            <span class="font-bold text-zinc-900">{{ $pr->total_units }} unit</span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-zinc-400 text-xs">Belum ada penjualan.</div>
                    @endforelse
                </div>
            </div>

            <!-- Rekap Petugas -->
            <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">3. Rekapitulasi Berdasarkan Petugas</span>
                </div>

                <div class="divide-y divide-zinc-100 text-xs">
                    @forelse ($recap['cashiers_recap'] as $cr)
                        <div class="p-2.5 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-zinc-900">{{ $cr->user_name }}</div>
                                <div class="text-[11px] text-zinc-400">@ {{ $cr->username }} • {{ $cr->tx_count }} transaksi</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-zinc-900">Rp{{ number_format($cr->total_revenue, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-zinc-500">{{ $cr->total_units }} unit</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-zinc-400 text-xs">Belum ada data kasir.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 4. Shift 1 vs Shift 2 -->
        <div class="bg-white rounded-lg border border-zinc-200 p-4">
            <span class="font-bold text-xs uppercase tracking-wider text-zinc-700 block mb-2">4. Perbandingan Shift 1 vs Shift 2 (5 Hari)</span>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-zinc-50 rounded border border-zinc-100">
                    <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider block">Shift 1 (Pagi)</span>
                    <div class="text-base font-bold text-zinc-900 mt-1">
                        Rp{{ number_format($recap['shifts_comparison']['shift1_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">
                        {{ $recap['shifts_comparison']['shift1_units'] }} unit ({{ $recap['shifts_comparison']['shift1_tx_count'] }} trx)
                    </div>
                </div>

                <div class="p-3 bg-zinc-50 rounded border border-zinc-100">
                    <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider block">Shift 2 (Sore)</span>
                    <div class="text-base font-bold text-zinc-900 mt-1">
                        Rp{{ number_format($recap['shifts_comparison']['shift2_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">
                        {{ $recap['shifts_comparison']['shift2_units'] }} unit ({{ $recap['shifts_comparison']['shift2_tx_count'] }} trx)
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
