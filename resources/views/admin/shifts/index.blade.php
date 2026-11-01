<x-layouts.app>
    <x-slot:title>Manajemen Shift</x-slot:title>
    <x-slot:header>Kelola Shift 1, Shift 2, & Penugasan Petugas</x-slot:header>

    <div class="space-y-4" x-data="{
        showModal: false,
        shiftId: null,
        shiftNumber: '',
        dayName: '',
        name: '',
        startTime: '',
        endTime: '',
        selectedCashiers: [],

        openEditModal(shift, day) {
            this.shiftId = shift.id;
            this.shiftNumber = shift.shift_number;
            this.dayName = day.day_name;
            this.name = shift.name;
            this.startTime = shift.start_time ? shift.start_time.substring(0, 5) : '';
            this.endTime = shift.end_time ? shift.end_time.substring(0, 5) : '';
            this.selectedCashiers = shift.cashiers ? shift.cashiers.map(c => c.id) : [];
            this.showModal = true;
        }
    }">
        <div class="p-3 rounded-lg bg-zinc-100 border border-zinc-200 text-xs text-zinc-700">
            <strong>Petunjuk:</strong> Kasir hanya dapat mengakses POS dan menginput penjualan jika shift mereka berstatus <strong class="text-emerald-800">OPEN</strong>. Tekan <em>Buka Shift</em> saat stand mulai beroperasi, dan <em>Tutup Shift</em> saat sesi shift berakhir.
        </div>

        <div class="space-y-4">
            @foreach ($eventDays as $day)
                <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                    <div class="px-4 py-2.5 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs uppercase text-zinc-900">Hari {{ $day->day_name }}</span>
                            <span class="text-zinc-400 text-xs font-mono">({{ $day->event_date->format('d/m/Y') }})</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase {{ $day->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $day->status }}
                        </span>
                    </div>

                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($day->shifts as $shift)
                            <div class="p-3 rounded border {{ $shift->isOpen() ? 'border-emerald-300 bg-emerald-50/20' : 'border-zinc-200 bg-white' }} flex flex-col justify-between space-y-3">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <div class="font-bold text-xs text-zinc-900">
                                            {{ $shift->shift_number }} <span class="text-zinc-500 font-normal">({{ $shift->name }})</span>
                                        </div>

                                        @if ($shift->isOpen())
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                OPEN
                                            </span>
                                        @elseif ($shift->isClosed())
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600">
                                                CLOSED
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-700">
                                                SCHEDULED
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-zinc-500 mt-1">
                                        Jam: {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '12:00', 0, 5) }}
                                    </div>

                                    <!-- Cashiers -->
                                    <div class="mt-2 pt-2 border-t border-zinc-100">
                                        <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider block mb-1">Petugas:</span>
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($shift->cashiers as $c)
                                                <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-800 text-[11px] font-medium">
                                                    {{ $c->name }}
                                                </span>
                                            @empty
                                                <span class="text-[11px] text-zinc-400 italic">Belum ada kasir ditugaskan</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="pt-2 border-t border-zinc-100 flex items-center justify-between gap-2">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($shift) }}, {{ Js::from($day) }})"
                                        class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-xs transition cursor-pointer"
                                    >
                                        Edit Jadwal
                                    </button>

                                    <div class="flex items-center gap-1.5">
                                        @if ($shift->isScheduled() || $shift->isClosed())
                                            <form action="{{ route('admin.shifts.open', $shift) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs transition cursor-pointer">
                                                    Buka Shift
                                                </button>
                                            </form>
                                        @endif

                                        @if ($shift->isOpen())
                                            <form action="{{ route('admin.shifts.close', $shift) }}" method="POST" onsubmit="return confirm('Tutup shift ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded text-xs transition cursor-pointer">
                                                    Tutup Shift
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Edit Modal -->
        <div 
            x-show="showModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg max-w-md w-full p-5 border border-zinc-200" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200 mb-3">
                    <h3 class="font-bold text-sm text-zinc-900" x-text="'Edit ' + shiftNumber + ' — Hari ' + dayName"></h3>
                    <button @click="showModal = false" class="text-zinc-400 hover:text-zinc-600 text-xs">✕</button>
                </div>

                <form :action="'/admin/shifts/' + shiftId" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Nama Shift</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" x-model="startTime" class="w-full px-3 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" x-model="endTime" class="w-full px-3 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Penugasan Kasir</label>
                        <div class="grid grid-cols-2 gap-1.5 border border-zinc-200 rounded p-2.5 max-h-36 overflow-y-auto text-xs">
                            @foreach ($allCashiers as $c)
                                <label class="flex items-center gap-1.5 p-1 rounded hover:bg-zinc-50 cursor-pointer select-none">
                                    <input 
                                        type="checkbox" 
                                        name="cashier_ids[]" 
                                        value="{{ $c->id }}"
                                        :checked="selectedCashiers.includes({{ $c->id }})"
                                        class="rounded text-zinc-900 focus:ring-zinc-900 border-zinc-300"
                                    >
                                    <span class="font-medium text-zinc-800">{{ $c->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 border-t border-zinc-200 flex items-center justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded">Batal</button>
                        <button type="submit" class="px-4 py-1.5 bg-zinc-900 hover:bg-black text-white font-bold text-xs rounded">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
