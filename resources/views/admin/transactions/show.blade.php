<x-layouts.app>
    <x-slot:title>Detail Transaksi {{ $transaction->invoice_code }}</x-slot:title>
    <x-slot:header>Rincian Struk & Audit Transaksi</x-slot:header>

    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.transactions.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                ← Kembali ke Riwayat Transaksi
            </a>

            @if (! $transaction->isVoided())
                <form action="{{ route('admin.transactions.void', $transaction) }}" method="POST" onsubmit="return prompt('Masukkan alasan pembatalan transaksi:') ? true : false">
                    @csrf
                </form>
            @endif
        </div>

        <!-- Receipt Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 bg-slate-50">
                <div>
                    <span class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Nomor Invoice</span>
                    <h2 class="text-xl font-mono font-bold text-slate-900 mt-0.5">{{ $transaction->invoice_code }}</h2>
                </div>

                <div class="flex items-center gap-2">
                    @if ($transaction->status === 'completed')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            TRANSAKSI BERHASIL
                        </span>
                    @elseif ($transaction->status === 'voided')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            DIBATALKAN (VOID)
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            DIKOREKSI
                        </span>
                    @endif
                </div>
            </div>

            <!-- Transaction Metadata -->
            <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs border-b border-slate-100">
                <div>
                    <span class="text-slate-400 block font-medium">Tanggal & Waktu:</span>
                    <strong class="text-slate-800 text-sm">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</strong>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Petugas / Kasir:</span>
                    <strong class="text-slate-800 text-sm">{{ $transaction->cashier->name }}</strong>
                    <div class="text-slate-500 text-[11px]">(@ {{ $transaction->cashier->username }})</div>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Shift & Hari:</span>
                    <strong class="text-slate-800 text-sm">{{ $transaction->shift->eventDay->day_name }} — {{ $transaction->shift->shift_number }}</strong>
                    <div class="text-slate-500 text-[11px]">{{ $transaction->shift->name }}</div>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Metode Pembayaran:</span>
                    <strong class="text-slate-800 text-sm uppercase">{{ $transaction->payment_method }}</strong>
                </div>
            </div>

            <!-- Itemized Table -->
            <div class="p-6">
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Daftar Barang Belanjaan (Snapshot Master Data)</h3>
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                        <tr>
                            <th class="py-2.5">No</th>
                            <th class="py-2.5">Nama Produk Saat Transaksi</th>
                            <th class="py-2.5 text-right">Harga Satuan Snapshot</th>
                            <th class="py-2.5 text-center">Jumlah</th>
                            <th class="py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaction->items as $idx => $item)
                            <tr>
                                <td class="py-3 text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 font-bold text-slate-900">{{ $item->product_name_snapshot }}</td>
                                <td class="py-3 text-right text-slate-600">{{ $item->formatted_unit_price }}</td>
                                <td class="py-3 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                                <td class="py-3 text-right font-extrabold text-slate-900">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200">
                        <tr>
                            <td colspan="3" class="py-3 text-right font-bold text-slate-600 uppercase text-xs">Total Pembelian:</td>
                            <td class="py-3 text-center font-bold text-slate-900">{{ $transaction->total_items }} unit</td>
                            <td class="py-3 text-right text-base sm:text-lg font-black text-indigo-700">{{ $transaction->formatted_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($transaction->notes)
                <div class="p-6 border-t border-slate-100 bg-slate-50 text-xs">
                    <span class="font-bold text-slate-700">Catatan Kasir:</span> {{ $transaction->notes }}
                </div>
            @endif
        </div>

        <!-- Corrections / Void Audit History -->
        @if ($transaction->corrections->isNotEmpty())
            <div class="bg-white rounded-2xl border border-rose-200 shadow-xs p-5 space-y-3">
                <h3 class="font-bold text-rose-900 text-sm flex items-center gap-2">
                    <span>⚠️</span> Catatan Audit Koreksi / Pembatalan
                </h3>
                <div class="space-y-3">
                    @foreach ($transaction->corrections as $cor)
                        <div class="p-3.5 rounded-xl bg-rose-50/50 border border-rose-100 text-xs text-rose-900 space-y-1">
                            <div class="flex items-center justify-between font-bold">
                                <span>Aksi: {{ strtoupper($cor->action) }} oleh {{ $cor->correctedByUser->name }}</span>
                                <span class="text-rose-600 font-mono">{{ $cor->created_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div>
                                <strong>Alasan:</strong> {{ $cor->reason }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
