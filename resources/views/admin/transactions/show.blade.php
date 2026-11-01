<x-layouts.app>
    <x-slot:title>Detail Transaksi {{ $transaction->invoice_code }}</x-slot:title>
    <x-slot:header>Rincian Struk & Audit Transaksi</x-slot:header>

    <div class="max-w-3xl space-y-5">
        <div>
            <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center text-xs text-slate-500 hover:text-indigo-600 font-bold transition">
                Kembali ke Riwayat Transaksi
            </a>
        </div>

        <!-- Receipt Box -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kode Invoice</span>
                    <h2 class="text-lg sm:text-xl font-mono font-black text-indigo-700">{{ $transaction->invoice_code }}</h2>
                </div>

                <div>
                    @if ($transaction->status === 'completed')
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">BERHASIL</span>
                    @elseif ($transaction->status === 'voided')
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">VOID</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">KOREKSI</span>
                    @endif
                </div>
            </div>

            <!-- Metadata Grid -->
            <div class="grid grid-cols-2 gap-4 text-xs border-b border-slate-100 pb-4">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Waktu Transaksi</span>
                    <strong class="text-slate-900 font-bold">{{ $transaction->created_at->format('d/m/Y H:i:s') }} WIB</strong>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Petugas Kasir</span>
                    <strong class="text-slate-900 font-bold">{{ $transaction->cashier->name }} (@ {{ $transaction->cashier->username }})</strong>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Hari & Shift</span>
                    <strong class="text-slate-900 font-bold">Hari {{ $transaction->shift->eventDay->day_name }} — {{ $transaction->shift->shift_number }}</strong>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Metode Pembayaran</span>
                    <strong class="text-slate-900 font-bold uppercase font-mono">{{ $transaction->payment_method }}</strong>
                </div>
            </div>

            <!-- Items Table -->
            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Item Penjualan:</span>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold bg-slate-50">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Nama Produk (Snapshot)</th>
                                <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($transaction->items as $idx => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900">{{ $item->product_name_snapshot }}</td>
                                    <td class="py-2.5 px-3 text-right text-slate-700">{{ $item->formatted_unit_price }}</td>
                                    <td class="py-2.5 px-3 text-center font-black text-slate-900">{{ $item->quantity }}</td>
                                    <td class="py-2.5 px-3 text-right font-black text-slate-900">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold">
                            <tr>
                                <td colspan="3" class="py-3 px-3 text-right uppercase text-[11px] text-slate-500">TOTAL TRANSAKSI:</td>
                                <td class="py-3 px-3 text-center text-slate-900 font-black">{{ $transaction->total_items }} unit</td>
                                <td class="py-3 px-3 text-right text-base font-black text-slate-900 {{ $transaction->isVoided() ? 'line-through text-slate-400' : '' }}">{{ $transaction->formatted_total }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if ($transaction->notes)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700">
                    <strong class="text-slate-900">Catatan Transaksi:</strong> {{ $transaction->notes }}
                </div>
            @endif
        </div>

        <!-- Void Log -->
        @if ($transaction->corrections->isNotEmpty())
            <div class="bg-rose-50/70 rounded-2xl border border-rose-200 p-5 space-y-3 text-xs shadow-xs">
                <span class="font-extrabold text-rose-800 uppercase text-xs tracking-wider block">Riwayat Pembatalan Transaksi (Void)</span>
                @foreach ($transaction->corrections as $cor)
                    <div class="p-3.5 bg-white rounded-xl border border-rose-200 text-rose-950 space-y-1 shadow-2xs">
                        <div class="flex items-center justify-between font-bold text-xs text-rose-900">
                            <span>Dibatalkan oleh: {{ $cor->correctedByUser->name }}</span>
                            <span class="font-mono text-[11px]">{{ $cor->created_at->format('d/m/Y H:i:s') }} WIB</span>
                        </div>
                        <div class="text-xs text-slate-700 mt-1">
                            <strong>Alasan Pembatalan:</strong> {{ $cor->reason }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
