<x-layouts.cashier>
    <x-slot:title>Riwayat Transaksi Saya</x-slot:title>

    <div class="space-y-4">
        <!-- Header & Summary Cards -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 text-base sm:text-lg">Riwayat Transaksi Anda</h2>
                <p class="text-xs text-slate-500">Daftar transaksi yang diproses menggunakan akun ini.</p>
            </div>
            <a href="{{ route('cashier.pos') }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                + Input Baru
            </a>
        </div>

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-3 gap-2.5">
            <div class="bg-white p-3 rounded-xl border border-slate-200">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total Omzet</span>
                <div class="font-black text-slate-900 text-sm sm:text-base mt-0.5">
                    Rp{{ number_format($totalPersonalSales, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total Unit</span>
                <div class="font-black text-slate-900 text-sm sm:text-base mt-0.5">
                    {{ $totalPersonalUnits }} item
                </div>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Transaksi</span>
                <div class="font-black text-slate-900 text-sm sm:text-base mt-0.5">
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
                placeholder="Cari kode invoice (misal: TRX-...)"
                class="flex-1 px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-medium text-xs rounded-xl hover:bg-slate-900 transition">
                Cari
            </button>
            @if ($search)
                <a href="{{ route('cashier.history') }}" class="px-3 py-2 bg-slate-200 text-slate-700 text-xs rounded-xl hover:bg-slate-300 flex items-center">
                    Reset
                </a>
            @endif
        </form>

        <!-- Transactions List -->
        <div class="space-y-3">
            @forelse ($transactions as $tx)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <div>
                            <span class="font-mono font-bold text-xs text-indigo-700">{{ $tx->invoice_code }}</span>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $tx->created_at->format('d M Y, H:i') }} • {{ $tx->shift->shift_number }} ({{ $tx->shift->eventDay->day_name }})
                            </div>
                        </div>

                        <div class="text-right">
                            @if ($tx->status === 'completed')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    BERHASIL
                                </span>
                            @elseif ($tx->status === 'voided')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                    DIBATALKAN
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    DIKOREKSI
                                </span>
                            @endif
                            <div class="text-[10px] text-slate-500 uppercase mt-0.5 font-semibold">{{ $tx->payment_method }}</div>
                        </div>
                    </div>

                    <!-- Items itemized -->
                    <div class="space-y-1.5 text-xs text-slate-700">
                        @foreach ($tx->items as $it)
                            <div class="flex justify-between items-center text-[11px] sm:text-xs">
                                <span>{{ $it->product_name_snapshot }} <span class="text-slate-400 font-bold">× {{ $it->quantity }}</span></span>
                                <span class="font-medium text-slate-800">{{ $it->formatted_subtotal }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Total Belanja ({{ $tx->total_items }} unit)</span>
                        <span class="text-sm sm:text-base font-black {{ $tx->status === 'voided' ? 'text-slate-400 line-through' : 'text-slate-900' }}">
                            {{ $tx->formatted_total }}
                        </span>
                    </div>

                    @if ($tx->notes)
                        <div class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg italic">
                            Catatan: {{ $tx->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Belum ada riwayat transaksi yang tercatat.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-layouts.cashier>
