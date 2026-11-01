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
                            Tidak ada shift open yang ditugaskan
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

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] font-bold text-indigo-800 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md uppercase tracking-wider mb-2">
                        Penjualan Saya Hari Ini
                    </span>
                    <div class="text-base sm:text-xl font-black text-slate-900 mt-1">
                        Rp{{ number_format($personalTodayRevenue, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                    {{ $personalTodayTx }} transaksi • {{ $personalTodayUnits }} item
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md uppercase tracking-wider mb-2">
                        Total Omzet Shift Ini
                    </span>
                    <div class="text-base sm:text-xl font-black text-slate-900 mt-1">
                        Rp{{ number_format($shiftTotalRevenue, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                    Gabungan seluruh kasir di shift
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs col-span-2 sm:col-span-1 flex flex-col justify-between">
                <div>
                    <span class="inline-block text-[10px] font-bold text-sky-800 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-md uppercase tracking-wider mb-2">
                        Total Transaksi Shift
                    </span>
                    <div class="text-base sm:text-xl font-black text-slate-900 mt-1">
                        {{ $shiftTotalTx }} <span class="text-xs font-semibold text-slate-500">Struk</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                    Shift yang sedang berjalan
                </div>
            </div>
        </div>

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
    </div>
</x-layouts.cashier>
