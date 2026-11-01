<x-layouts.cashier>
    <x-slot:title>Riwayat Transaksi Saya</x-slot:title>

    <div class="space-y-5">
        <!-- Header Section -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="font-black text-base sm:text-lg text-slate-900">Riwayat Transaksi Saya</h1>
                <p class="text-xs text-slate-500 font-medium">Semua transaksi yang diproses menggunakan akun kasir ini.</p>
            </div>
            <a href="{{ route('cashier.pos') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition duration-150 shrink-0">
                Input POS
            </a>
        </div>

        <!-- Quick Summary Metrics -->
        <div class="grid grid-cols-3 gap-2.5 sm:gap-4 text-xs">
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/90 shadow-xs">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Omzet</span>
                <div class="font-black text-slate-900 text-sm sm:text-lg mt-1">
                    Rp{{ number_format($totalPersonalSales, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/90 shadow-xs">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Unit</span>
                <div class="font-black text-slate-900 text-sm sm:text-lg mt-1">
                    {{ $totalPersonalUnits }} <span class="text-xs font-semibold text-slate-500">item</span>
                </div>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/90 shadow-xs">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Transaksi</span>
                <div class="font-black text-slate-900 text-sm sm:text-lg mt-1">
                    {{ $totalPersonalTx }} <span class="text-xs font-semibold text-slate-500">struk</span>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('cashier.history') }}" class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Cari nomor struk (TRX-...)"
                class="flex-1 px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium placeholder:text-slate-400"
            >
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition duration-150 cursor-pointer">
                Cari
            </button>
            @if ($search)
                <a href="{{ route('cashier.history') }}" class="px-3.5 py-2.5 bg-slate-100 text-slate-700 text-xs sm:text-sm rounded-xl hover:bg-slate-200 font-semibold flex items-center transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Transactions List -->
        <div class="space-y-3">
            @forelse ($transactions as $tx)
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 space-y-3 hover:border-slate-300 transition duration-150">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="font-mono font-black text-xs sm:text-sm text-indigo-700">{{ $tx->invoice_code }}</span>
                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                                {{ $tx->created_at->format('d/m/Y H:i') }} • {{ $tx->shift->shift_number }} (Hari {{ $tx->shift->eventDay->day_name }})
                            </div>
                        </div>

                        <div class="text-right">
                            @if ($tx->status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    BERHASIL
                                </span>
                            @elseif ($tx->status === 'voided')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                    VOID
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    KOREKSI
                                </span>
                            @endif
                            <div class="text-[10px] text-slate-400 font-mono uppercase mt-0.5 font-bold">{{ $tx->payment_method }}</div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-1.5 text-xs">
                        @foreach ($tx->items as $it)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-700">
                                    {{ $it->product_name_snapshot }} 
                                    <strong class="text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded-md ml-1">×{{ $it->quantity }}</strong>
                                </span>
                                <span class="text-slate-900 font-bold">{{ $it->formatted_subtotal }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Total Pembayaran ({{ $tx->total_items }} item)</span>
                        <span class="text-base font-black {{ $tx->status === 'voided' ? 'text-slate-400 line-through' : 'text-slate-900' }}">
                            {{ $tx->formatted_total }}
                        </span>
                    </div>

                    @if ($tx->notes)
                        <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <strong>Catatan:</strong> {{ $tx->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/90 p-10 text-center text-slate-400 text-xs shadow-xs">
                    Belum ada riwayat transaksi yang tersimpan.
                </div>
            @endforelse

            <div class="pt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-layouts.cashier>
