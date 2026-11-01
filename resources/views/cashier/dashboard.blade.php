<x-layouts.cashier>
    <x-slot:title>Info Shift & Petugas</x-slot:title>

    <div class="space-y-4">
        <!-- Status Box -->
        <div class="bg-white rounded-lg p-4 border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="font-bold text-sm text-zinc-900">Halo, {{ $user->name }}</div>
                <div class="text-xs text-zinc-500 mt-0.5">
                    Status Shift Saat Ini: 
                    @if ($activeShift)
                        <strong class="text-emerald-700">Hari {{ $activeShift->eventDay->day_name }} — {{ $activeShift->shift_number }} (OPEN)</strong>
                    @else
                        <strong class="text-zinc-600">Tidak ada shift open yang ditugaskan</strong>
                    @endif
                </div>
            </div>

            @if ($activeShift)
                <a href="{{ route('cashier.pos') }}" class="inline-flex items-center justify-center px-4 py-2 rounded bg-zinc-900 hover:bg-black text-white font-bold text-xs transition">
                    Buka Kasir POS →
                </a>
            @else
                <span class="text-xs text-zinc-400 py-1 px-2 bg-zinc-100 rounded inline-block text-center">
                    Shift Belum Dibuka
                </span>
            @endif
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="bg-white p-3 rounded-lg border border-zinc-200">
                <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider block">Penjualan Saya Hari Ini</span>
                <div class="text-base font-bold text-zinc-900 mt-1">
                    Rp{{ number_format($personalTodayRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">
                    {{ $personalTodayTx }} transaksi ({{ $personalTodayUnits }} item)
                </div>
            </div>

            <div class="bg-white p-3 rounded-lg border border-zinc-200">
                <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider block">Total Omzet Shift Ini</span>
                <div class="text-base font-bold text-zinc-900 mt-1">
                    Rp{{ number_format($shiftTotalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">
                    Gabungan kasir di shift ini
                </div>
            </div>

            <div class="bg-white p-3 rounded-lg border border-zinc-200 col-span-2 sm:col-span-1">
                <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider block">Total Transaksi Shift</span>
                <div class="text-base font-bold text-zinc-900 mt-1">
                    {{ $shiftTotalTx }} Transaksi
                </div>
                <div class="text-[11px] text-zinc-500 mt-0.5">
                    Shift yang sedang berjalan
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
                <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">Jadwal Tugas Shift Anda</span>
                <span class="text-xs text-zinc-400">{{ $myShifts->count() }} Penugasan</span>
            </div>

            <div class="divide-y divide-zinc-100 text-xs">
                @forelse ($myShifts as $s)
                    <div class="p-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-zinc-900">
                                Hari {{ $s->eventDay->day_name }} — {{ $s->shift_number }}
                            </div>
                            <div class="text-zinc-500 text-[11px] mt-0.5">
                                {{ $s->name }} ({{ substr($s->start_time ?? '08:00', 0, 5) }} - {{ substr($s->end_time ?? '12:00', 0, 5) }})
                            </div>
                        </div>

                        <div>
                            @if ($s->status === 'open')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    OPEN
                                </span>
                            @elseif ($s->status === 'closed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600">
                                    CLOSED
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-700">
                                    SCHEDULED
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-zinc-400 text-xs">
                        Belum ada jadwal tugas shift.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.cashier>
