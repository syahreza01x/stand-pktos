<x-layouts.app>
    <x-slot:title>Detail Transaksi {{ $transaction->invoice_code }}</x-slot:title>
    <x-slot:header>Rincian Invoice & Audit Transaksi</x-slot:header>

    <div class="max-w-2xl space-y-4">
        <div>
            <a href="{{ route('admin.transactions.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 font-medium">
                ← Kembali ke Riwayat Transaksi
            </a>
        </div>

        <!-- Receipt Box -->
        <div class="bg-white rounded-lg border border-zinc-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
                <div>
                    <span class="text-[10px] text-zinc-400 uppercase tracking-wider block">Kode Invoice</span>
                    <h2 class="text-base font-mono font-bold text-zinc-900">{{ $transaction->invoice_code }}</h2>
                </div>

                <div>
                    @if ($transaction->status === 'completed')
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">BERHASIL</span>
                    @elseif ($transaction->status === 'voided')
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">VOID</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">KOREKSI</span>
                    @endif
                </div>
            </div>

            <!-- Metadata Grid -->
            <div class="grid grid-cols-2 gap-3 text-xs border-b border-zinc-100 pb-3">
                <div>
                    <span class="text-zinc-400 text-[11px] block">Waktu Transaksi:</span>
                    <strong class="text-zinc-800">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</strong>
                </div>
                <div>
                    <span class="text-zinc-400 text-[11px] block">Petugas Kasir:</span>
                    <strong class="text-zinc-800">{{ $transaction->cashier->name }} (@ {{ $transaction->cashier->username }})</strong>
                </div>
                <div>
                    <span class="text-zinc-400 text-[11px] block">Hari & Shift:</span>
                    <strong class="text-zinc-800">{{ $transaction->shift->eventDay->day_name }} — {{ $transaction->shift->shift_number }}</strong>
                </div>
                <div>
                    <span class="text-zinc-400 text-[11px] block">Metode Pembayaran:</span>
                    <strong class="text-zinc-800 uppercase font-mono">{{ $transaction->payment_method }}</strong>
                </div>
            </div>

            <!-- Items -->
            <div>
                <span class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider block mb-2">Item Belanja:</span>
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2">No</th>
                            <th class="py-2">Produk Snapshot</th>
                            <th class="py-2 text-right">Harga Satuan</th>
                            <th class="py-2 text-center">Qty</th>
                            <th class="py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($transaction->items as $idx => $item)
                            <tr>
                                <td class="py-2 text-zinc-400">{{ $idx + 1 }}</td>
                                <td class="py-2 font-medium text-zinc-900">{{ $item->product_name_snapshot }}</td>
                                <td class="py-2 text-right text-zinc-600">{{ $item->formatted_unit_price }}</td>
                                <td class="py-2 text-center font-bold text-zinc-800">{{ $item->quantity }}</td>
                                <td class="py-2 text-right font-bold text-zinc-900">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-zinc-200 font-bold">
                        <tr>
                            <td colspan="3" class="py-2.5 text-right uppercase text-[11px] text-zinc-500">TOTAL:</td>
                            <td class="py-2.5 text-center text-zinc-900">{{ $transaction->total_items }}</td>
                            <td class="py-2.5 text-right text-sm font-black text-zinc-900">{{ $transaction->formatted_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($transaction->notes)
                <div class="p-2.5 bg-zinc-50 rounded text-xs text-zinc-600">
                    <strong>Catatan:</strong> {{ $transaction->notes }}
                </div>
            @endif
        </div>

        <!-- Void Log -->
        @if ($transaction->corrections->isNotEmpty())
            <div class="bg-white rounded-lg border border-rose-200 p-4 space-y-2 text-xs">
                <span class="font-bold text-rose-800 uppercase text-[10px] tracking-wider block">Catatan Pembatalan (Void):</span>
                @foreach ($transaction->corrections as $cor)
                    <div class="p-2.5 bg-rose-50 rounded text-rose-900">
                        <div class="flex justify-between font-bold text-[11px]">
                            <span>Dibatalkan oleh: {{ $cor->correctedByUser->name }}</span>
                            <span>{{ $cor->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="mt-1">
                            <strong>Alasan:</strong> {{ $cor->reason }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
