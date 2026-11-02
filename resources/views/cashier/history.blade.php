<x-layouts.cashier>
    <x-slot:title>Riwayat Transaksi Saya</x-slot:title>

    <div class="space-y-5">
        <!-- Header Section -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="font-black text-base sm:text-lg text-slate-900">Riwayat Transaksi Saya</h1>
                <p class="text-xs text-slate-500 font-medium">Rekap dan riwayat transaksi akun kasir shift ini.</p>
            </div>
            <a href="{{ route('cashier.pos') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition duration-150 shrink-0">
                Input POS
            </a>
        </div>

        <!-- Rekonsiliasi Kas / Metode Pembayaran Box -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <div>
                    <h3 class="font-bold text-xs sm:text-sm text-slate-900 uppercase tracking-wider">Rekap Kas Stand Shift Ini</h3>
                    <p class="text-[11px] text-slate-400">Cocokkan uang fisik di stand dan bukti pembayaran digital</p>
                </div>
                <div class="text-xs font-black text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg w-fit">
                    Total: Rp{{ number_format($totalPersonalSales, 0, ',', '.') }} ({{ $totalPersonalTx }} struk)
                </div>
            </div>

            <!-- Breakdown 3 Metode Bayar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <!-- Cash -->
                <a 
                    href="{{ route('cashier.history', ['payment_method' => 'cash', 'search' => $search]) }}"
                    class="p-3.5 rounded-xl border transition {{ $paymentMethod === 'cash' ? 'bg-emerald-50 border-emerald-400 ring-2 ring-emerald-200' : 'bg-slate-50/70 border-slate-200 hover:border-slate-300' }}"
                >
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Tunai (Cash Fisik)</span>
                    <div class="font-black text-base text-slate-900 mt-0.5">
                        Rp{{ number_format($cashTotal, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium block mt-1">
                        {{ $cashCount }} struk • Cocokkan uang fisik stand
                    </span>
                </a>

                <!-- QRIS -->
                <a 
                    href="{{ route('cashier.history', ['payment_method' => 'qris', 'search' => $search]) }}"
                    class="p-3.5 rounded-xl border transition {{ $paymentMethod === 'qris' ? 'bg-sky-50 border-sky-400 ring-2 ring-sky-200' : 'bg-slate-50/70 border-slate-200 hover:border-slate-300' }}"
                >
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-800 block">QRIS Stand (Digital)</span>
                    <div class="font-black text-base text-slate-900 mt-0.5">
                        Rp{{ number_format($qrisTotal, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium block mt-1">
                        {{ $qrisCount }} struk • Cocokkan notifikasi QRIS
                    </span>
                </a>

                <!-- Transfer -->
                <a 
                    href="{{ route('cashier.history', ['payment_method' => 'transfer', 'search' => $search]) }}"
                    class="p-3.5 rounded-xl border transition {{ $paymentMethod === 'transfer' ? 'bg-purple-50 border-purple-400 ring-2 ring-purple-200' : 'bg-slate-50/70 border-slate-200 hover:border-slate-300' }}"
                >
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-800 block">Transfer Bank (TF)</span>
                    <div class="font-black text-base text-slate-900 mt-0.5">
                        Rp{{ number_format($transferTotal, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium block mt-1">
                        {{ $transferCount }} struk • Cocokkan mutasi bank
                    </span>
                </a>
            </div>
        </div>

        <!-- Filter Tab & Search Bar -->
        <div class="space-y-2">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-bold">
                <a 
                    href="{{ route('cashier.history', ['search' => $search]) }}"
                    class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap {{ empty($paymentMethod) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                >
                    Semua Metode
                </a>
                <a 
                    href="{{ route('cashier.history', ['payment_method' => 'cash', 'search' => $search]) }}"
                    class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap {{ $paymentMethod === 'cash' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                >
                    Tunai ({{ $cashCount }})
                </a>
                <a 
                    href="{{ route('cashier.history', ['payment_method' => 'qris', 'search' => $search]) }}"
                    class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap {{ $paymentMethod === 'qris' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                >
                    QRIS ({{ $qrisCount }})
                </a>
                <a 
                    href="{{ route('cashier.history', ['payment_method' => 'transfer', 'search' => $search]) }}"
                    class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap {{ $paymentMethod === 'transfer' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                >
                    Transfer ({{ $transferCount }})
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('cashier.history') }}" class="flex gap-2">
                @if ($paymentMethod)
                    <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                @endif
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
                @if ($search || $paymentMethod)
                    <a href="{{ route('cashier.history') }}" class="px-3.5 py-2.5 bg-slate-100 text-slate-700 text-xs sm:text-sm rounded-xl hover:bg-slate-200 font-semibold flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

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
                            <div class="text-[10px] text-slate-500 font-mono uppercase mt-0.5 font-bold">
                                {{ $tx->payment_method === 'cash' ? 'TUNAI' : strtoupper($tx->payment_method) }}
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-1.5 text-xs">
                        @foreach ($tx->items as $it)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-700">
                                    {{ $it->product_name_snapshot }} 
                                    <span class="text-slate-400">({{ $it->formatted_unit_price }})</span>
                                    <strong class="text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded-md ml-1">×{{ $it->quantity }}</strong>
                                </span>
                                <span class="text-slate-900 font-bold">{{ $it->formatted_subtotal }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            Total {{ $tx->total_items }} unit barang
                            @if ($tx->notes)
                                <span class="block text-[11px] text-slate-400 italic mt-0.5">"{{ $tx->notes }}"</span>
                            @endif
                        </div>

                        <div class="text-base sm:text-lg font-black text-indigo-700">
                            {{ $tx->formatted_total }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-12 text-center text-slate-400 text-xs">
                    Belum ada riwayat transaksi yang cocok dengan filter.
                </div>
            @endforelse

            <!-- Pagination -->
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-layouts.cashier>
