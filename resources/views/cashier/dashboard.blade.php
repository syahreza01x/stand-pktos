<x-layouts.cashier>
    <x-slot:title>Dashboard Petugas</x-slot:title>

    <div class="space-y-5">
        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Portal Petugas Stand</span>
                <h2 class="text-xl font-bold text-slate-900 mt-0.5">Halo, {{ $user->name }}!</h2>
                <p class="text-xs text-slate-500 mt-1">Gunakan smartphone Anda untuk input penjualan dengan cepat dan akurat.</p>
            </div>

            @if ($activeShift)
                <a href="{{ route('cashier.pos') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition">
                    <span>⚡ Buka Kasir / POS</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                <button disabled class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-200 text-slate-500 font-bold text-xs cursor-not-allowed">
                    🔒 Shift Belum Dibuka
                </button>
            @endif
        </div>

        <!-- Shift Status Box -->
        <div class="p-4 rounded-2xl {{ $activeShift ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }} shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $activeShift ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }} flex items-center justify-center font-bold text-base shrink-0">
                    {{ $activeShift ? '✓' : '!' }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm sm:text-base {{ $activeShift ? 'text-emerald-950' : 'text-amber-950' }}">
                            {{ $activeShift ? 'Shift Aktif: ' . $activeShift->shift_number . ' (' . $activeShift->eventDay->day_name . ')' : 'Tidak Ada Shift yang Sedang Open' }}
                        </h3>
                    </div>
                    <p class="text-xs {{ $activeShift ? 'text-emerald-800' : 'text-amber-800' }} mt-0.5">
                        {{ $activeShift ? 'Anda ditugaskan pada shift ini. Penjualan dibuka.' : 'Menunggu Admin membuka shift sebelum Anda dapat memproses transaksi.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Financial KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <!-- Personal Sales Today -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Penjualan Saya Hari Ini</span>
                <div class="text-lg sm:text-xl font-black text-slate-900 mt-1">
                    Rp{{ number_format($personalTodayRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    <strong>{{ $personalTodayTx }}</strong> transaksi ({{ $personalTodayUnits }} unit)
                </div>
            </div>

            <!-- Current Shift Total Revenue -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Omzet Shift Ini</span>
                <div class="text-lg sm:text-xl font-black text-indigo-600 mt-1">
                    Rp{{ number_format($shiftTotalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Gabungan seluruh kasir di shift ini
                </div>
            </div>

            <!-- Total Transactions Handled -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Transaksi Shift</span>
                <div class="text-lg sm:text-xl font-black text-emerald-600 mt-1">
                    {{ $shiftTotalTx }} Transaksi
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Dalam shift yang sedang berjalan
                </div>
            </div>
        </div>

        <!-- My Assigned Shifts Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Jadwal Tugas Shift Anda</h3>
                <span class="text-xs text-slate-500 font-medium">{{ $myShifts->count() }} Penugasan</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($myShifts as $s)
                    <div class="p-4 flex items-center justify-between gap-3 text-xs sm:text-sm">
                        <div>
                            <div class="font-bold text-slate-900">
                                Hari {{ $s->eventDay->day_name }} — {{ $s->shift_number }}
                            </div>
                            <div class="text-slate-500 text-[11px] mt-0.5">
                                {{ $s->name }} ({{ substr($s->start_time ?? '08:00', 0, 5) }} - {{ substr($s->end_time ?? '12:00', 0, 5) }})
                            </div>
                        </div>

                        <div>
                            @if ($s->status === 'open')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    OPEN
                                </span>
                            @elseif ($s->status === 'closed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                    CLOSED
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                    SCHEDULED
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Belum ada jadwal shift yang ditugaskan kepada Anda.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.cashier>
