<x-layouts.app>
    <x-slot:title>Laporan Shift & Harian</x-slot:title>
    <x-slot:header>Laporan Rincian Shift 1, Shift 2, & Total Harian</x-slot:header>

    <div class="space-y-4" x-data="{ activeTab: 'total' }">
        <!-- Day Selector & Export Header -->
        <div class="bg-white rounded-lg border border-zinc-200 p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                @foreach ($eventDays as $d)
                    <a 
                        href="{{ route('admin.reports.daily', ['event_day_id' => $d->id]) }}"
                        class="px-3 py-1.5 rounded text-xs font-semibold whitespace-nowrap {{ $activeDay->id === $d->id ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' }}"
                    >
                        Hari {{ $d->day_name }}
                    </a>
                @endforeach
            </div>

            <a 
                href="{{ route('admin.reports.export-daily', $activeDay) }}"
                class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded transition text-center"
            >
                Download Excel (Hari {{ $activeDay->day_name }})
            </a>
        </div>

        <!-- Day Overview & Tabs -->
        <div class="bg-white rounded-lg border border-zinc-200 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-zinc-900">Hari {{ $activeDay->day_name }} ({{ $activeDay->event_date->format('l, d F Y') }})</h2>
                    <span class="text-xs text-zinc-500">{{ $dailyReport['daily_total']['transaction_count'] }} Transaksi Terproses</span>
                </div>
                <div class="sm:text-right">
                    <span class="text-[11px] text-zinc-400 block uppercase tracking-wider font-semibold">Total Pendapatan Harian</span>
                    <span class="text-xl font-bold text-zinc-900">
                        Rp{{ number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex items-center gap-1.5 mt-3 text-xs">
                <button 
                    type="button" 
                    @click="activeTab = 'total'"
                    :class="activeTab === 'total' ? 'bg-zinc-900 text-white font-bold' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                    class="px-3 py-1.5 rounded transition"
                >
                    Total Harian (Shift 1 + Shift 2)
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'shift1'"
                    :class="activeTab === 'shift1' ? 'bg-zinc-900 text-white font-bold' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                    class="px-3 py-1.5 rounded transition"
                >
                    Shift 1 (Pagi)
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'shift2'"
                    :class="activeTab === 'shift2' ? 'bg-zinc-900 text-white font-bold' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                    class="px-3 py-1.5 rounded transition"
                >
                    Shift 2 (Sore)
                </button>
            </div>
        </div>

        <!-- 1. Total Harian (Shift 1 + Shift 2) -->
        <div x-show="activeTab === 'total'" class="space-y-3">
            <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">Tabel Rekap Penjualan 6 Produk (Shift 1 + Shift 2)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                            <tr>
                                <th class="py-2 px-3">No</th>
                                <th class="py-2 px-3">Nama Produk</th>
                                <th class="py-2 px-3">Kategori</th>
                                <th class="py-2 px-3 text-right">Harga Satuan (Rp)</th>
                                <th class="py-2 px-3 text-center">Qty Shift 1</th>
                                <th class="py-2 px-3 text-center">Qty Shift 2</th>
                                <th class="py-2 px-3 text-center font-bold">Total Qty</th>
                                <th class="py-2 px-3 text-right">Subtotal Omzet (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach ($dailyReport['daily_total']['items'] as $idx => $it)
                                <tr class="hover:bg-zinc-50">
                                    <td class="py-2 px-3 text-zinc-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="py-2 px-3 font-semibold text-zinc-900">{{ $it['product_name'] }}</td>
                                    <td class="py-2 px-3 text-zinc-500">{{ $it['category'] }}</td>
                                    <td class="py-2 px-3 text-right text-zinc-700">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-center text-zinc-700">{{ $it['quantity_s1'] }}</td>
                                    <td class="py-2 px-3 text-center text-zinc-700">{{ $it['quantity_s2'] }}</td>
                                    <td class="py-2 px-3 text-center font-bold text-zinc-900">{{ $it['total_quantity'] }}</td>
                                    <td class="py-2 px-3 text-right font-bold text-zinc-900">Rp{{ number_format($it['total_subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-zinc-200 bg-zinc-50 font-bold text-xs">
                            <tr>
                                <td colspan="4" class="py-2.5 px-3 text-right uppercase text-[11px] text-zinc-500">GRAND TOTAL HARI INI:</td>
                                <td class="py-2.5 px-3 text-center text-zinc-800">{{ $dailyReport['shift1']['total_quantity'] ?? 0 }}</td>
                                <td class="py-2.5 px-3 text-center text-zinc-800">{{ $dailyReport['shift2']['total_quantity'] ?? 0 }}</td>
                                <td class="py-2.5 px-3 text-center font-black text-zinc-900">{{ $dailyReport['daily_total']['total_quantity'] }} unit</td>
                                <td class="py-2.5 px-3 text-right font-black text-sm text-zinc-900">
                                    Rp{{ number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. Shift 1 Detail -->
        <div x-show="activeTab === 'shift1'" class="space-y-3" style="display: none;">
            <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">Rincian Shift 1 (Pagi)</span>
                    <span class="text-xs text-zinc-500">{{ $dailyReport['shift1']['transaction_count'] ?? 0 }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                            <tr>
                                <th class="py-2 px-3">No</th>
                                <th class="py-2 px-3">Nama Produk</th>
                                <th class="py-2 px-3 text-right">Harga Satuan (Rp)</th>
                                <th class="py-2 px-3 text-center">Jumlah Terjual</th>
                                <th class="py-2 px-3 text-right">Total Penjualan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @if (!empty($dailyReport['shift1']['items']))
                                @foreach ($dailyReport['shift1']['items'] as $idx => $it)
                                    <tr class="hover:bg-zinc-50">
                                        <td class="py-2 px-3 text-zinc-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-3 font-semibold text-zinc-900">{{ $it['product_name'] }}</td>
                                        <td class="py-2 px-3 text-right text-zinc-700">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                        <td class="py-2 px-3 text-center font-bold text-zinc-800">{{ $it['quantity'] }} unit</td>
                                        <td class="py-2 px-3 text-right font-bold text-zinc-900">Rp{{ number_format($it['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-zinc-200 bg-zinc-50 font-bold text-xs">
                            <tr>
                                <td colspan="3" class="py-2.5 px-3 text-right uppercase text-[11px] text-zinc-500">TOTAL SHIFT 1:</td>
                                <td class="py-2.5 px-3 text-center font-black text-zinc-900">{{ $dailyReport['shift1']['total_quantity'] ?? 0 }} unit</td>
                                <td class="py-2.5 px-3 text-right font-black text-sm text-zinc-900">
                                    Rp{{ number_format($dailyReport['shift1']['total_revenue'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Shift 2 Detail -->
        <div x-show="activeTab === 'shift2'" class="space-y-3" style="display: none;">
            <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">Rincian Shift 2 (Sore)</span>
                    <span class="text-xs text-zinc-500">{{ $dailyReport['shift2']['transaction_count'] ?? 0 }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                            <tr>
                                <th class="py-2 px-3">No</th>
                                <th class="py-2 px-3">Nama Produk</th>
                                <th class="py-2 px-3 text-right">Harga Satuan (Rp)</th>
                                <th class="py-2 px-3 text-center">Jumlah Terjual</th>
                                <th class="py-2 px-3 text-right">Total Penjualan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @if (!empty($dailyReport['shift2']['items']))
                                @foreach ($dailyReport['shift2']['items'] as $idx => $it)
                                    <tr class="hover:bg-zinc-50">
                                        <td class="py-2 px-3 text-zinc-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-3 font-semibold text-zinc-900">{{ $it['product_name'] }}</td>
                                        <td class="py-2 px-3 text-right text-zinc-700">Rp{{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                        <td class="py-2 px-3 text-center font-bold text-zinc-800">{{ $it['quantity'] }} unit</td>
                                        <td class="py-2 px-3 text-right font-bold text-zinc-900">Rp{{ number_format($it['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-zinc-200 bg-zinc-50 font-bold text-xs">
                            <tr>
                                <td colspan="3" class="py-2.5 px-3 text-right uppercase text-[11px] text-zinc-500">TOTAL SHIFT 2:</td>
                                <td class="py-2.5 px-3 text-center font-black text-zinc-900">{{ $dailyReport['shift2']['total_quantity'] ?? 0 }} unit</td>
                                <td class="py-2.5 px-3 text-right font-black text-sm text-zinc-900">
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
