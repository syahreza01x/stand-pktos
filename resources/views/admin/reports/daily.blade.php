<x-layouts.app>
    <x-slot:title>Laporan Shift & Harian</x-slot:title>
    <x-slot:header>Laporan Rincian Shift & Harian</x-slot:header>

    <div class="space-y-6" x-data="{ activeTab: 'total' }">
        <!-- Day Selector & Export Header -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                @foreach ($eventDays as $d)
                    <a 
                        href="{{ route('admin.reports.daily', ['event_day_id' => $d->id]) }}"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition duration-150 {{ $activeDay->id === $d->id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        Hari {{ $d->day_name }}
                    </a>
                @endforeach
            </div>

            <a 
                href="{{ route('admin.reports.export-daily', $activeDay) }}"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition duration-150 shadow-xs text-center flex items-center justify-center shrink-0 cursor-pointer"
            >
                Download Excel (Hari {{ $activeDay->day_name }})
            </a>
        </div>

        <!-- Day Overview & Tabs -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <span class="inline-block text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Laporan Penjualan</span>
                    <h2 class="text-base sm:text-lg font-black text-slate-900">Hari {{ $activeDay->day_name }} ({{ $activeDay->event_date->format('l, d F Y') }})</h2>
                    <span class="text-xs text-slate-500 font-medium">{{ $dailyReport['daily_total']['transaction_count'] }} Transaksi Terproses</span>
                </div>
                <div class="sm:text-right bg-slate-50 sm:bg-transparent p-3 sm:p-0 rounded-xl">
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Total Omzet Harian</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-700">
                        Rp{{ number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Rincian Metode Pembayaran Harian (Cash, QRIS, Transfer) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">1. Uang Tunai (Cash Stand)</span>
                    <div class="text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['daily_total']['payments']['cash']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-emerald-700 font-medium block mt-0.5">
                        {{ $dailyReport['daily_total']['payments']['cash']['count'] ?? 0 }} transaksi tunai
                    </span>
                </div>

                <div class="p-3.5 rounded-xl border border-sky-200 bg-sky-50/50">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-800 block">2. QRIS Stand (ShopeePay)</span>
                    <div class="text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['daily_total']['payments']['qris']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-sky-700 font-medium block mt-0.5">
                        {{ $dailyReport['daily_total']['payments']['qris']['count'] ?? 0 }} transaksi QRIS
                    </span>
                </div>

                <div class="p-3.5 rounded-xl border border-purple-200 bg-purple-50/50">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-800 block">3. Transfer Bank (TF)</span>
                    <div class="text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['daily_total']['payments']['transfer']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-purple-700 font-medium block mt-0.5">
                        {{ $dailyReport['daily_total']['payments']['transfer']['count'] ?? 0 }} transaksi transfer
                    </span>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto text-xs font-bold pb-1 sm:pb-0">
                <button 
                    type="button" 
                    @click="activeTab = 'total'"
                    :class="activeTab === 'total' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap"
                >
                    Total Harian (Shift 1 + 2)
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'shift1'"
                    :class="activeTab === 'shift1' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap"
                >
                    Shift 1 (Pagi)
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'shift2'"
                    :class="activeTab === 'shift2' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap"
                >
                    Shift 2 (Sore)
                </button>
            </div>
        </div>

        <!-- 1. Total Harian (Shift 1 + Shift 2) -->
        <div x-show="activeTab === 'total'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">Tabel Rekap Penjualan Produk (Shift 1 + Shift 2)</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Harga Satuan</th>
                                <th class="py-3 px-4 text-center">Qty Shift 1</th>
                                <th class="py-3 px-4 text-center">Qty Shift 2</th>
                                <th class="py-3 px-4 text-center font-bold">Total Qty</th>
                                <th class="py-3 px-4 text-right">Subtotal Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($dailyReport['daily_total']['items'] as $idx => $it)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-4 font-bold text-slate-900">{{ $it['product_name'] }}</td>
                                    <td class="py-2.5 px-4">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $it['category'] === 'Makanan' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                            {{ $it['category'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-slate-700 font-medium">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-4 text-center text-slate-700 font-semibold">{{ $it['quantity_s1'] }}</td>
                                    <td class="py-2.5 px-4 text-center text-slate-700 font-semibold">{{ $it['quantity_s2'] }}</td>
                                    <td class="py-2.5 px-4 text-center font-black text-slate-900 bg-slate-50">{{ $it['total_quantity'] }}</td>
                                    <td class="py-2.5 px-4 text-right font-black text-slate-900">Rp{{ number_format($it['total_subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50/80 font-bold text-xs">
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-right uppercase text-[11px] text-slate-500">GRAND TOTAL HARI INI:</td>
                                <td class="py-3 px-4 text-center text-slate-800 font-black">{{ $dailyReport['shift1']['total_quantity'] ?? 0 }}</td>
                                <td class="py-3 px-4 text-center text-slate-800 font-black">{{ $dailyReport['shift2']['total_quantity'] ?? 0 }}</td>
                                <td class="py-3 px-4 text-center font-black text-slate-900 bg-slate-100">{{ $dailyReport['daily_total']['total_quantity'] }} unit</td>
                                <td class="py-3 px-4 text-right font-black text-sm sm:text-base text-emerald-800">
                                    Rp{{ number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. Shift 1 Detail -->
        <div x-show="activeTab === 'shift1'" class="space-y-4" style="display: none;">
            <!-- Shift 1 Payment Breakdown -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/50 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Tunai (Shift 1)</span>
                    <div class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['shift1']['payments']['cash']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-emerald-700 font-medium block mt-0.5">
                        {{ $dailyReport['shift1']['payments']['cash']['count'] ?? 0 }} transaksi tunai
                    </span>
                </div>
                <div class="bg-white p-3.5 rounded-2xl border border-sky-200 bg-sky-50/50 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-800 block">QRIS (Shift 1)</span>
                    <div class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['shift1']['payments']['qris']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-sky-700 font-medium block mt-0.5">
                        {{ $dailyReport['shift1']['payments']['qris']['count'] ?? 0 }} transaksi QRIS
                    </span>
                </div>
                <div class="bg-white p-3.5 rounded-2xl border border-purple-200 bg-purple-50/50 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-800 block">Transfer (Shift 1)</span>
                    <div class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['shift1']['payments']['transfer']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-purple-700 font-medium block mt-0.5">
                        {{ $dailyReport['shift1']['payments']['transfer']['count'] ?? 0 }} transaksi transfer
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">Rincian Shift 1 (Pagi)</h3>
                    <span class="text-xs text-slate-500 font-medium">{{ $dailyReport['shift1']['transaction_count'] ?? 0 }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-right">Harga Satuan</th>
                                <th class="py-3 px-4 text-center">Jumlah Terjual</th>
                                <th class="py-3 px-4 text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if (!empty($dailyReport['shift1']['items']))
                                @foreach ($dailyReport['shift1']['items'] as $idx => $it)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">{{ $it['product_name'] }}</td>
                                        <td class="py-2.5 px-4 text-right text-slate-700 font-medium">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                        <td class="py-2.5 px-4 text-center font-black text-slate-900">{{ $it['quantity'] }} unit</td>
                                        <td class="py-2.5 px-4 text-right font-black text-slate-900">Rp{{ number_format($it['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold text-xs">
                            <tr>
                                <td colspan="3" class="py-3 px-4 text-right uppercase text-[11px] text-slate-500">TOTAL SHIFT 1:</td>
                                <td class="py-3 px-4 text-center font-black text-slate-900">{{ $dailyReport['shift1']['total_quantity'] ?? 0 }} unit</td>
                                <td class="py-3 px-4 text-right font-black text-sm text-emerald-800">
                                    Rp{{ number_format($dailyReport['shift1']['total_revenue'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Shift 2 Detail -->
        <div x-show="activeTab === 'shift2'" class="space-y-4" style="display: none;">
            <!-- Shift 2 Payment Breakdown -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/50 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Tunai (Shift 2)</span>
                    <div class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['shift2']['payments']['cash']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-emerald-700 font-medium block mt-0.5">
                        {{ $dailyReport['shift2']['payments']['cash']['count'] ?? 0 }} transaksi tunai
                    </span>
                </div>
                <div class="bg-white p-3.5 rounded-2xl border border-sky-200 bg-sky-50/50 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-800 block">QRIS (Shift 2)</span>
                    <div class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['shift2']['payments']['qris']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-sky-700 font-medium block mt-0.5">
                        {{ $dailyReport['shift2']['payments']['qris']['count'] ?? 0 }} transaksi QRIS
                    </span>
                </div>
                <div class="bg-white p-3.5 rounded-2xl border border-purple-200 bg-purple-50/50 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-800 block">Transfer (Shift 2)</span>
                    <div class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                        Rp{{ number_format($dailyReport['shift2']['payments']['transfer']['total'] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-purple-700 font-medium block mt-0.5">
                        {{ $dailyReport['shift2']['payments']['transfer']['count'] ?? 0 }} transaksi transfer
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">Rincian Shift 2 (Sore)</h3>
                    <span class="text-xs text-slate-500 font-medium">{{ $dailyReport['shift2']['transaction_count'] ?? 0 }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-right">Harga Satuan</th>
                                <th class="py-3 px-4 text-center">Jumlah Terjual</th>
                                <th class="py-3 px-4 text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if (!empty($dailyReport['shift2']['items']))
                                @foreach ($dailyReport['shift2']['items'] as $idx => $it)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">{{ $it['product_name'] }}</td>
                                        <td class="py-2.5 px-4 text-right text-slate-700 font-medium">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                        <td class="py-2.5 px-4 text-center font-black text-slate-900">{{ $it['quantity'] }} unit</td>
                                        <td class="py-2.5 px-4 text-right font-black text-slate-900">Rp{{ number_format($it['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold text-xs">
                            <tr>
                                <td colspan="3" class="py-3 px-4 text-right uppercase text-[11px] text-slate-500">TOTAL SHIFT 2:</td>
                                <td class="py-3 px-4 text-center font-black text-slate-900">{{ $dailyReport['shift2']['total_quantity'] ?? 0 }} unit</td>
                                <td class="py-3 px-4 text-right font-black text-sm text-emerald-800">
                                    Rp{{ number_format($dailyReport['shift2']['total_revenue'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
