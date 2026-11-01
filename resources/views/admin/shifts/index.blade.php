<x-layouts.app>
    <x-slot:title>Manajemen Shift & Penjadwalan</x-slot:title>
    <x-slot:header>Kelola Shift 1, Shift 2, & Penugasan Petugas</x-slot:header>

    <div class="space-y-6" x-data="{
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
        <!-- Information Card -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg shrink-0">
                    ⏱️
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Aturan Buka & Tutup Shift:</h3>
                    <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                        Petugas/kasir hanya dapat mengakses kasir dan memproses transaksi apabila shift mereka dalam status <strong class="text-emerald-700">OPEN</strong>. Setelah shift ditutup (<strong class="text-slate-700">CLOSED</strong>), transaksi dikunci dan laporan shift dihitung otomatis.
                    </p>
                </div>
            </div>
        </div>

        <!-- Shift Grid Grouped by 5 Days -->
        <div class="space-y-6">
            @foreach ($eventDays as $day)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-slate-900 uppercase">Hari {{ $day->day_name }}</span>
                            <span class="text-xs text-slate-500 font-mono">({{ $day->event_date->format('d/m/Y') }})</span>
                        </div>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $day->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ strtoupper($day->status) }}
                        </span>
                    </div>

                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($day->shifts as $shift)
                            <div class="p-4 rounded-xl border {{ $shift->isOpen() ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200 bg-white' }} flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-slate-900 text-sm">{{ $shift->shift_number }}</span>
                                            <span class="text-xs text-slate-500 font-medium">({{ $shift->name }})</span>
                                        </div>

                                        @if ($shift->isOpen())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                OPEN
                                            </span>
                                        @elseif ($shift->isClosed())
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                CLOSED
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                SCHEDULED
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-xs text-slate-500 mt-1">
                                        Jam Kerja: <strong>{{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '12:00', 0, 5) }}</strong>
                                    </div>

                                    <!-- Assigned Cashiers -->
                                    <div class="mt-3 pt-3 border-t border-slate-100">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1.5">Petugas Ditugaskan:</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse ($shift->cashiers as $c)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium">
                                                    👤 {{ $c->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-amber-600 italic">Belum ada petugas ditugaskan</span>
                                            @endforelse
                                        </div>
                                    </div>

                                    @if ($shift->isOpen() && $shift->opened_at)
                                        <div class="mt-2 text-[11px] text-emerald-700">
                                            Dibuka pada: {{ $shift->opened_at->format('d/m/Y H:i') }} oleh {{ $shift->openedByUser?->name ?? 'Admin' }}
                                        </div>
                                    @endif

                                    @if ($shift->isClosed() && $shift->closed_at)
                                        <div class="mt-2 text-[11px] text-slate-400">
                                            Ditutup pada: {{ $shift->closed_at->format('d/m/Y H:i') }} oleh {{ $shift->closedByUser?->name ?? 'Admin' }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($shift) }}, {{ Js::from($day) }})"
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition cursor-pointer"
                                    >
                                        Ubah Jadwal & Petugas
                                    </button>

                                    <div class="flex items-center gap-1.5">
                                        @if ($shift->isScheduled() || $shift->isClosed())
                                            <form action="{{ route('admin.shifts.open', $shift) }}" method="POST" onsubmit="return confirm('Buka shift ini sekarang? Petugas yang ditugaskan dapat langsung memproses transaksi.')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-xs transition cursor-pointer">
                                                    Buka Shift ▶
                                                </button>
                                            </form>
                                        @endif

                                        @if ($shift->isOpen())
                                            <form action="{{ route('admin.shifts.close', $shift) }}" method="POST" onsubmit="return confirm('Tutup shift ini? Petugas tidak akan dapat lagi menginput transaksi pada shift ini.')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-xs transition cursor-pointer">
                                                    Tutup Shift ⏹
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

        <!-- Edit Shift Modal -->
        <div 
            x-show="showModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base" x-text="'Pengaturan ' + shiftNumber + ' — Hari ' + dayName"></h3>
                        <p class="text-xs text-slate-500">Sesuaikan waktu dan kasir yang bertugas</p>
                    </div>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form :action="'/admin/shifts/' + shiftId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Shift</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" x-model="startTime" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" x-model="endTime" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Petugas / Kasir Bertugas</label>
                        <div class="grid grid-cols-2 gap-2 border border-slate-200 rounded-xl p-3 bg-slate-50 max-h-48 overflow-y-auto">
                            @foreach ($allCashiers as $c)
                                <label class="flex items-center gap-2 text-xs p-1.5 rounded-lg hover:bg-white cursor-pointer select-none">
                                    <input 
                                        type="checkbox" 
                                        name="cashier_ids[]" 
                                        value="{{ $c->id }}"
                                        :checked="selectedCashiers.includes({{ $c->id }})"
                                        class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                    >
                                    <span class="font-medium text-slate-800">{{ $c->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
