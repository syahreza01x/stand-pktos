<x-layouts.cashier>
    <x-slot:title>Info Shift & Petugas</x-slot:title>

    <div class="space-y-5">
        <!-- Status & Greeting Box -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-block text-[10px] uppercase tracking-wider font-extrabold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md mb-1.5">
                    Petugas Kasir
                </span>
                <h1 class="font-black text-base sm:text-lg text-slate-900">Halo, {{ $user->name }}</h1>
                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <span>Shift Saat Ini:</span>
                    @if ($activeShift)
                        <span class="inline-flex items-center font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                            Hari {{ $activeShift->eventDay->day_name }} — {{ $activeShift->shift_number }} (OPEN)
                        </span>
                    @else
                        <span class="font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                            Tidak ada shift open yang ditugaskan saat ini
                        </span>
                    @endif
                </div>
            </div>

            <div class="shrink-0">
                @if ($activeShift)
                    <a href="{{ route('cashier.pos') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-xs transition duration-150 cursor-pointer">
                        Buka Kasir POS
                    </a>
                @else
                    <span class="w-full sm:w-auto inline-block text-xs font-semibold text-slate-400 py-2 px-4 bg-slate-100 rounded-xl text-center border border-slate-200">
                        Shift Belum Dibuka
                    </span>
                @endif
            </div>
        </div>

        @if ($shiftSummary)
            <!-- REKONSILIASI KAS STAND (SHIFT INI) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-black text-sm sm:text-base text-slate-900 uppercase tracking-wider">
                                Rekonsiliasi Kas Stand — Shift Ini
                            </h2>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $shiftSummary['status'] === 'open' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700' }}">
                                {{ $shiftSummary['status'] === 'open' ? 'Shift Open' : 'Shift Closed' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Hari {{ $shiftSummary['day_name'] }} • {{ $shiftSummary['shift_number'] }} ({{ $shiftSummary['shift_name'] }})
                        </p>
                    </div>

                    @if ($myShifts->count() > 1)
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="text-slate-400 text-[11px] font-medium">Pilih Shift:</span>
                            <div class="flex gap-1 overflow-x-auto">
                                @foreach ($myShifts as $ms)
                                    <a 
                                        href="{{ route('cashier.dashboard', ['shift_id' => $ms->id]) }}"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition {{ ($viewedShift && $viewedShift->id === $ms->id) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                                    >
                                        {{ $ms->shift_number }} ({{ $ms->eventDay->day_name }})
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 3 Kotak Metode Bayar: Cash, QRIS, Transfer -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <!-- 1. Tunai (Cash) -->
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-black uppercase tracking-wider text-emerald-900">
                                1. Uang Tunai (Cash)
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-200/60 text-emerald-900">
                                Uang Fisik Stand
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900">
                            {{ $shiftSummary['cash']['formatted'] }}
                        </div>
                        <div class="text-xs text-slate-600 font-medium pt-1 border-t border-emerald-100">
                            <strong>{{ $shiftSummary['cash']['count'] }}</strong> transaksi tunai
                            <span class="block text-[11px] text-emerald-800 font-semibold mt-0.5">
                                Hitung & cocokkan dengan uang tunai di kotak/laci stand.
                            </span>
                        </div>
                    </div>

                    <!-- 2. QRIS Stand -->
                    <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-black uppercase tracking-wider text-sky-900">
                                2. QRIS Stand
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-200/60 text-sky-900">
                                Digital QRIS
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900">
                            {{ $shiftSummary['qris']['formatted'] }}
                        </div>
                        <div class="text-xs text-slate-600 font-medium pt-1 border-t border-sky-100">
                            <strong>{{ $shiftSummary['qris']['count'] }}</strong> transaksi QRIS
                            @if ($shopeePaySummary && ($shopeePaySummary['success'] ?? false))
                                <span class="block text-[11px] text-sky-800 font-semibold mt-0.5">
                                    Omset ShopeePay (Stand GenBI): <strong>{{ $shopeePaySummary['formatted_total_net_sales'] }}</strong>
                                </span>
                            @else
                                <span class="block text-[11px] text-sky-800 font-semibold mt-0.5">
                                    Cocokkan dengan notifikasi/riwayat aplikasi QRIS stand.
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Transfer Bank (TF) -->
                    <div class="p-4 rounded-xl border border-purple-200 bg-purple-50/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-black uppercase tracking-wider text-purple-900">
                                3. Transfer Bank (TF)
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-200/60 text-purple-900">
                                Transfer Rekening
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900">
                            {{ $shiftSummary['transfer']['formatted'] }}
                        </div>
                        <div class="text-xs text-slate-600 font-medium pt-1 border-t border-purple-100">
                            <strong>{{ $shiftSummary['transfer']['count'] }}</strong> transaksi transfer
                            <span class="block text-[11px] text-purple-800 font-semibold mt-0.5">
                                Cocokkan dengan mutasi rekening bank penerimaan stand.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Live Feed Mutasi Masuk ShopeePay (Stand GenBI) -->
                @if ($shopeePaySummary && !empty($shopeePaySummary['transactions']))
                    <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-black uppercase text-sky-950 tracking-wider">
                                Mutasi Masuk QRIS ShopeePay Terakhir (Stand GenBI)
                            </span>
                            <span class="text-[10px] text-sky-700 font-bold">
                                Sinkronisasi Otomatis • {{ $shopeePaySummary['fetched_at'] ?? '' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @foreach (array_slice($shopeePaySummary['transactions'], 0, 3) as $tx)
                                <div class="bg-white p-2.5 rounded-lg border border-sky-100 text-xs flex justify-between items-center shadow-2xs">
                                    <div>
                                        <span class="font-black text-slate-900 block text-xs">{{ $tx['formatted_amount'] }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $tx['time_ago'] }}</span>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ $tx['status_label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Total Hari/Shift Bar -->
                <div class="p-4 rounded-xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-semibold uppercase tracking-wider">
                            Total Seluruh Penerimaan Shift Ini:
                        </span>
                        <div class="text-2xl font-black text-emerald-400 mt-0.5">
                            {{ $shiftSummary['formatted_total_revenue'] }}
                        </div>
                    </div>

                    <div class="text-xs text-slate-300 font-medium sm:text-right">
                        <div>Akumulasi <strong>{{ $shiftSummary['total_transactions'] }} transaksi</strong> berhasil</div>
                        <span class="text-[11px] text-slate-400 block mt-0.5">
                            Data terisolasi khusus penugasan shift ini
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Schedule Card List -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800">Jadwal Tugas Shift Anda</h3>
                    <p class="text-[11px] text-slate-400">Daftar penugasan shift selama 5 hari acara</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    {{ $myShifts->count() }} Penugasan
                </span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse ($myShifts as $s)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 transition {{ $s->status === 'open' ? 'bg-emerald-50/30' : '' }}">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-sm text-slate-900">
                                    Hari {{ $s->eventDay->day_name }} — {{ $s->shift_number }}
                                </span>
                                @if ($s->status === 'open')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        SEDANG AKTIF
                                    </span>
                                @endif
                            </div>
                            <div class="text-slate-500 text-xs font-medium flex items-center gap-1.5">
                                <span>{{ $s->name }}</span>
                                <span>•</span>
                                <span>Jam: {{ substr($s->start_time ?? '08:00', 0, 5) }} — {{ substr($s->end_time ?? '12:00', 0, 5) }} WIB</span>
                            </div>
                        </div>

                        <div>
                            @if ($s->status === 'open')
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                    OPEN
                                </span>
                            @elseif ($s->status === 'closed')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    CLOSED
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    SCHEDULED
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Belum ada jadwal tugas shift.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Extra Mobile Bottom Spacer -->
        <div class="h-16 md:hidden"></div>
    </div>
</x-layouts.cashier>
