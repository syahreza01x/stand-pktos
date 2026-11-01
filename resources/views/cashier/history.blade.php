<x-layouts.cashier>
    <x-slot:title>Riwayat Transaksi Saya</x-slot:title>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-sm sm:text-base text-zinc-900">Riwayat Penjualan Anda</h2>
                <p class="text-xs text-zinc-500">Semua transaksi yang diproses menggunakan akun kasir ini.</p>
            </div>
            <a href="{{ route('cashier.pos') }}" class="px-3 py-1.5 bg-zinc-900 hover:bg-black text-white rounded text-xs font-semibold transition">
                + Input Baru
            </a>
        </div>

        <!-- Quick Summary Metrics -->
        <div class="grid grid-cols-3 gap-2 text-xs">
            <div class="bg-white p-2.5 rounded border border-zinc-200">
                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block">Total Omzet</span>
                <div class="font-bold text-zinc-900 text-sm mt-0.5">
                    Rp{{ number_format($totalPersonalSales, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-2.5 rounded border border-zinc-200">
                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block">Total Unit</span>
                <div class="font-bold text-zinc-900 text-sm mt-0.5">
                    {{ $totalPersonalUnits }} item
                </div>
            </div>
            <div class="bg-white p-2.5 rounded border border-zinc-200">
                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block">Transaksi</span>
                <div class="font-bold text-zinc-900 text-sm mt-0.5">
                    {{ $totalPersonalTx }} struk
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('cashier.history') }}" class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Cari kode invoice (TRX-...)"
                class="flex-1 px-3 py-1.5 text-xs rounded border border-zinc-300 bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900"
            >
            <button type="submit" class="px-3 py-1.5 bg-zinc-900 text-white font-medium text-xs rounded hover:bg-black transition">
                Cari
            </button>
            @if ($search)
                <a href="{{ route('cashier.history') }}" class="px-3 py-1.5 bg-zinc-200 text-zinc-700 text-xs rounded hover:bg-zinc-300 flex items-center">
                    Reset
                </a>
            @endif
        </form>

        <!-- Transactions List -->
        <div class="space-y-2.5">
            @forelse ($transactions as $tx)
                <div class="bg-white rounded-lg border border-zinc-200 p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2">
                        <div>
                            <span class="font-mono font-bold text-xs text-zinc-900">{{ $tx->invoice_code }}</span>
                            <div class="text-[11px] text-zinc-400 mt-0.5">
                                {{ $tx->created_at->format('d/m/Y H:i') }} • {{ $tx->shift->shift_number }} ({{ $tx->shift->eventDay->day_name }})
                            </div>
                        </div>

                        <div class="text-right">
                            @if ($tx->status === 'completed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    BERHASIL
                                </span>
                            @elseif ($tx->status === 'voided')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                    VOID
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    KOREKSI
                                </span>
                            @endif
                            <div class="text-[10px] text-zinc-400 uppercase mt-0.5">{{ $tx->payment_method }}</div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-1 text-xs">
                        @foreach ($tx->items as $it)
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-zinc-700">{{ $it->product_name_snapshot }} <strong class="text-zinc-900">×{{ $it->quantity }}</strong></span>
                                <span class="text-zinc-900 font-medium">{{ $it->formatted_subtotal }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2 border-t border-zinc-100 flex items-center justify-between">
                        <span class="text-xs text-zinc-500">Total ({{ $tx->total_items }} unit)</span>
                        <span class="text-sm font-bold {{ $tx->status === 'voided' ? 'text-zinc-400 line-through' : 'text-zinc-900' }}">
                            {{ $tx->formatted_total }}
                        </span>
                    </div>

                    @if ($tx->notes)
                        <div class="text-[11px] text-zinc-500 bg-zinc-50 p-2 rounded">
                            Catatan: {{ $tx->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-lg border border-zinc-200 p-8 text-center text-zinc-400 text-xs">
                    Belum ada riwayat transaksi yang tersimpan.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-layouts.cashier>
