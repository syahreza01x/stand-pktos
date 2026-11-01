<x-layouts.app>
    <x-slot:title>Laporan Shift & Harian</x-slot:title>
    <x-slot:header>Laporan Rincian Shift 1, Shift 2, & Total Harian</x-slot:header>

    <div class="space-y-6" x-data="{ activeTab: 'total' }">
        <!-- Day Selector & Export Header -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- 5-Day Pill Navigation -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                @foreach ($eventDays as $d)
                    <a 
                        href="{{ route('admin.reports.daily', ['event_day_id' => $d->id]) }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeDay->id === $d->id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        Hari {{ $d->day_name }}
                    </a>
                @endforeach
            </div>

            <!-- Export to Excel Button -->
            <a 
                href="{{ route('admin.reports.export-daily', $activeDay) }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel Hari {{ $activeDay->day_name }}</span>
            </a>
        </div>

        <!-- Day Overview Header -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase text-indigo-600">Laporan Penjualan Stand</span>
                    <h2 class="text-xl font-black text-slate-900 mt-0.5">Hari {{ $activeDay->day_name }} ({{ $activeDay->event_date->format('l, d F Y') }})</h2>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Total Omzet Harian (Shift 1 + Shift 2)</span>
                    <span class="text-2xl font-black text-emerald-600">
                        Rp{{ number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Tab Switchers -->
            <div class="flex items-center gap-2 mt-4">
                <button 
                    type="button" 
                    @click="activeTab = 'total'"
                    :class="activeTab === 'total' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition"
                >
                    3. Total Hari Ini (Gabungan)
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'shift1'"
                    :class="activeTab === 'shift1' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition"
                >
                    1. Rincian Shift 1 (Pagi)
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'shift2'"
                    :class="activeTab === 'shift2' ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition"
                >
                    2. Rincian Shift 2 (Sore)
                </button>
            </div>
        </div>

        <!-- 1. TAB: TOTAL HARI (Shift 1 + Shift 2) -->
        <div x-show="activeTab === 'total'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Rekapitulasi Total Harian (Shift 1 + Shift 2)</h3>
                        <p class="text-xs text-slate-500">Penjumlahan otomatis dari seluruh transaksi valid hari {{ $activeDay->day_name }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $dailyReport['daily_total']['transaction_count'] }} Transaksi Terproses
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50/50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Produk Stand</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Harga Satuan (Rp)</th>
                                <th class="py-3 px-4 text-center bg-indigo-50/50">Qty Shift 1</th>
                                <th class="py-3 px-4 text-center bg-amber-50/50">Qty Shift 2</th>
                                <th class="py-3 px-4 text-center font-bold">Total Unit</th>
                                <th class="py-3 px-4 text-right">Subtotal Omzet (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($dailyReport['daily_total']['items'] as $idx => $it)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $it['product_name'] }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $it['category'] }}</td>
                                    <td class="py-3 px-4 text-right font-medium text-slate-700">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center bg-indigo-50/30 font-medium text-indigo-900">{{ $it['quantity_s1'] }}</td>
                                    <td class="py-3 px-4 text-center bg-amber-50/30 font-medium text-amber-900">{{ $it['quantity_s2'] }}</td>
                                    <td class="py-3 px-4 text-center font-black text-slate-900 text-sm">{{ $it['total_quantity'] }}</td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900">Rp{{ number_format($it['total_subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50/80 font-bold">
                            <tr>
                                <td colspan="4" class="py-4 px-4 text-right uppercase text-xs text-slate-700">GRAND TOTAL HARI INI:</td>
                                <td class="py-4 px-4 text-center text-indigo-700 font-extrabold">{{ $dailyReport['shift1']['total_quantity'] ?? 0 }}</td>
                                <td class="py-4 px-4 text-center text-amber-700 font-extrabold">{{ $dailyReport['shift2']['total_quantity'] ?? 0 }}</td>
                                <td class="py-4 px-4 text-center text-sm font-black text-slate-900">{{ $dailyReport['daily_total']['total_quantity'] }} unit</td>
                                <td class="py-4 px-4 text-right text-base sm:text-lg font-black text-emerald-700">
                                    Rp{{ number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. TAB: RINCIAN SHIFT 1 -->
        <div x-show="activeTab === 'shift1'" class="space-y-4" style="display: none;">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-indigo-50/50 border-b border-indigo-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Rincian Shift 1 (Pagi)</h3>
                        <p class="text-xs text-slate-500">6 Produk, Kuantitas Terjual, dan Pendapatan Shift 1</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-800">
                        {{ $dailyReport['shift1']['transaction_count'] ?? 0 }} Transaksi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-right">Harga Satuan (Rp)</th>
                                <th class="py-3 px-4 text-center">Jumlah Terjual</th>
                                <th class="py-3 px-4 text-right">Total Penjualan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if (!empty($dailyReport['shift1']['items']))
                                @foreach ($dailyReport['shift1']['items'] as $idx => $it)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3 px-4 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 font-bold text-slate-900">{{ $it['product_name'] }}</td>
                                        <td class="py-3 px-4 text-right font-medium text-slate-700">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $it['quantity'] }} unit</td>
                                        <td class="py-3 px-4 text-right font-extrabold text-slate-900">Rp{{ number_format($it['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50/80 font-bold">
                            <tr>
                                <td colspan="3" class="py-3.5 px-4 text-right uppercase text-xs text-slate-700">TOTAL SHIFT 1:</td>
                                <td class="py-3.5 px-4 text-center font-black text-slate-900">{{ $dailyReport['shift1']['total_quantity'] ?? 0 }} unit</td>
                                <td class="py-3.5 px-4 text-right text-base font-black text-indigo-700">
                                    Rp{{ number_format($dailyReport['shift1']['total_revenue'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. TAB: RINCIAN SHIFT 2 -->
        <div x-show="activeTab === 'shift2'" class="space-y-4" style="display: none;">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-amber-50/50 border-b border-amber-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Rincian Shift 2 (Sore)</h3>
                        <p class="text-xs text-slate-500">6 Produk, Kuantitas Terjual, dan Pendapatan Shift 2</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800">
                        {{ $dailyReport['shift2']['transaction_count'] ?? 0 }} Transaksi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-right">Harga Satuan (Rp)</th>
                                <th class="py-3 px-4 text-center">Jumlah Terjual</th>
                                <th class="py-3 px-4 text-right">Total Penjualan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if (!empty($dailyReport['shift2']['items']))
                                @foreach ($dailyReport['shift2']['items'] as $idx => $it)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3 px-4 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 font-bold text-slate-900">{{ $it['product_name'] }}</td>
                                        <td class="py-3 px-4 text-right font-medium text-slate-700">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $it['quantity'] }} unit</td>
                                        <td class="py-3 px-4 text-right font-extrabold text-slate-900">Rp{{ number_format($it['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50/80 font-bold">
                            <tr>
                                <td colspan="3" class="py-3.5 px-4 text-right uppercase text-xs text-slate-700">TOTAL SHIFT 2:</td>
                                <td class="py-3.5 px-4 text-center font-black text-slate-900">{{ $dailyReport['shift2']['total_quantity'] ?? 0 }} unit</td>
                                <td class="py-3.5 px-4 text-right text-base font-black text-amber-700">
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
