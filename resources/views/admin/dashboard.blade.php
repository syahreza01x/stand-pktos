<x-layouts.app>
    <x-slot:title>Dashboard Penjualan</x-slot:title>
    <x-slot:header>Ringkasan Penjualan Stand PKTOS</x-slot:header>

    <div class="space-y-5">
        <!-- Top 4 Metrics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-lg border border-zinc-200">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 block">Total Omzet Stand</span>
                <div class="text-xl sm:text-2xl font-bold text-zinc-900 mt-1">
                    Rp{{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">5 hari acara (bersih)</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-zinc-200">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 block">Penjualan Hari Ini</span>
                <div class="text-xl sm:text-2xl font-bold text-zinc-900 mt-1">
                    Rp{{ number_format($todayRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">{{ $todayTransactions }} transaksi ({{ $todayUnits }} unit)</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-zinc-200">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 block">Total Transaksi Valid</span>
                <div class="text-xl sm:text-2xl font-bold text-zinc-900 mt-1">
                    {{ number_format($totalTransactions, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Struk berhasil</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-zinc-200">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 block">Total Unit Terjual</span>
                <div class="text-xl sm:text-2xl font-bold text-zinc-900 mt-1">
                    {{ number_format($totalItemsSold, 0, ',', '.') }} <span class="text-xs font-normal text-zinc-500">unit</span>
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Akumulasi produk laku</div>
            </div>
        </div>

        <!-- Shift Aktif & Petugas Bertugas -->
        <div class="bg-white rounded-lg border border-zinc-200 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-100 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-zinc-900">
                            {{ $activeShift ? 'Shift Aktif: ' . $activeShift->shift_number . ' (' . $activeShift->name . ')' : 'Tidak Ada Shift yang Sedang Buka' }}
                        </span>
                        @if ($activeShift)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">OPEN</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 text-zinc-600">TUTUP</span>
                        @endif
                    </div>
                    <div class="text-xs text-zinc-500 mt-0.5">
                        @if ($activeShift)
                            Hari {{ $activeShift->eventDay->day_name }} • Dibuka jam {{ $activeShift->opened_at?->format('H:i') }}
                        @else
                            Buka shift pada menu Manajemen Shift untuk mengizinkan kasir menginput transaksi.
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($activeShift)
                        <form action="{{ route('admin.shifts.close', $activeShift) }}" method="POST" onsubmit="return confirm('Tutup shift ini? Kasir tidak akan dapat bertransaksi setelah shift ditutup.')">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded transition cursor-pointer">
                                Tutup Shift
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.shifts.index') }}" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold text-xs rounded transition">
                        Kelola Shift →
                    </a>
                </div>
            </div>

            <!-- Petugas On Duty -->
            @if ($activeShift)
                <div class="pt-3 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-zinc-400 font-semibold text-[11px] uppercase">Petugas On-Duty:</span>
                    @forelse ($activeShift->cashiers as $c)
                        <span class="px-2 py-1 rounded bg-zinc-100 text-zinc-800 font-medium">
                            {{ $c->name }} (@ {{ $c->username }})
                        </span>
                    @empty
                        <span class="text-zinc-500 italic">Belum ada petugas ditugaskan ke shift ini.</span>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- 5-Day Overview & Shift Comparison -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- 5-Day Summary (Col 7) -->
            <div class="lg:col-span-7 bg-white rounded-lg border border-zinc-200 p-4">
                <div class="flex items-center justify-between mb-3 border-b border-zinc-100 pb-2">
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-700">Ringkasan 5 Hari Acara</h3>
                    </div>
                    <a href="{{ route('admin.reports.five-days') }}" class="text-xs text-zinc-500 hover:text-zinc-900 font-medium">
                        Laporan Detail →
                    </a>
                </div>

                <div class="space-y-2">
                    @foreach ($recap['daily_summary'] as $ds)
                        <div class="p-2.5 rounded border border-zinc-100 bg-zinc-50 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-zinc-900">Hari {{ $ds['day']->day_name }}</span>
                                <span class="text-zinc-400 text-[11px] ml-1">({{ $ds['day']->event_date->format('d/m/Y') }})</span>
                                <div class="text-[11px] text-zinc-500">{{ $ds['tx_count'] }} transaksi</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-zinc-900">Rp{{ number_format($ds['revenue'], 0, ',', '.') }}</div>
                                <div class="text-[11px] text-zinc-500">{{ $ds['units'] }} unit</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shift 1 vs Shift 2 & Best Seller (Col 5) -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Shift 1 vs Shift 2 -->
                <div class="bg-white rounded-lg border border-zinc-200 p-4">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-700 mb-2">Shift 1 vs Shift 2</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded bg-zinc-50 border border-zinc-100">
                            <span class="text-[10px] uppercase text-zinc-400 font-semibold block">Shift 1 (Pagi)</span>
                            <div class="font-bold text-zinc-900 text-sm mt-0.5">
                                Rp{{ number_format($recap['shifts_comparison']['shift1_revenue'], 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-zinc-500 mt-0.5">
                                {{ $recap['shifts_comparison']['shift1_units'] }} unit ({{ $recap['shifts_comparison']['shift1_tx_count'] }} trx)
                            </div>
                        </div>

                        <div class="p-2.5 rounded bg-zinc-50 border border-zinc-100">
                            <span class="text-[10px] uppercase text-zinc-400 font-semibold block">Shift 2 (Sore)</span>
                            <div class="font-bold text-zinc-900 text-sm mt-0.5">
                                Rp{{ number_format($recap['shifts_comparison']['shift2_revenue'], 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-zinc-500 mt-0.5">
                                {{ $recap['shifts_comparison']['shift2_units'] }} unit ({{ $recap['shifts_comparison']['shift2_tx_count'] }} trx)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Terlaris -->
                <div class="bg-white rounded-lg border border-zinc-200 p-4">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-700 mb-2">Produk Terlaris</h3>
                    <div class="divide-y divide-zinc-100 text-xs">
                        @forelse ($recap['products_ranking']->take(4) as $idx => $pr)
                            <div class="py-1.5 flex items-center justify-between">
                                <span class="text-zinc-800"><strong class="text-zinc-400 mr-1.5">{{ $idx + 1 }}.</strong> {{ $pr->product_name_snapshot }}</span>
                                <span class="font-bold text-zinc-900">{{ $pr->total_units }} unit</span>
                            </div>
                        @empty
                            <div class="py-2 text-zinc-400 text-xs">Belum ada data penjualan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Transactions -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
                <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">Transaksi Terbaru</span>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 font-medium">
                    Lihat Semua →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Invoice</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Kasir</th>
                            <th class="py-2.5 px-3">Shift</th>
                            <th class="py-2.5 px-3">Item</th>
                            <th class="py-2.5 px-3 text-right">Total (Rp)</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($recentTransactions as $tx)
                            <tr class="hover:bg-zinc-50">
                                <td class="py-2 px-3 font-mono font-bold text-zinc-900">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->invoice_code }}
                                    </a>
                                </td>
                                <td class="py-2 px-3 text-zinc-500 whitespace-nowrap">{{ $tx->created_at->format('d/m H:i') }}</td>
                                <td class="py-2 px-3 text-zinc-800">{{ $tx->cashier->name }}</td>
                                <td class="py-2 px-3 text-zinc-600">{{ $tx->shift->eventDay->day_name }} — {{ $tx->shift->shift_number }}</td>
                                <td class="py-2 px-3 text-zinc-600">{{ $tx->total_items }} unit</td>
                                <td class="py-2 px-3 text-right font-bold text-zinc-900">{{ $tx->formatted_total }}</td>
                                <td class="py-2 px-3 text-center">
                                    @if ($tx->status === 'completed')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BERHASIL</span>
                                    @elseif ($tx->status === 'voided')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">VOID</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">KOREKSI</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-zinc-400 text-xs">
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
