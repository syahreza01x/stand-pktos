<x-layouts.app>
    <x-slot:title>Manajemen Shift</x-slot:title>
    <x-slot:header>Kelola Shift & Petugas</x-slot:header>

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
        <!-- Info Banner (No Emojis/Icons) -->
        <div class="rounded-2xl bg-blue-50/80 border border-blue-200/80 p-4 sm:p-5 text-blue-950 shadow-xs">
            <div class="text-xs sm:text-sm leading-relaxed">
                <strong class="font-bold text-blue-900">Petunjuk Operasional Shift:</strong> Kasir hanya dapat mengakses POS dan menginput penjualan jika shift mereka berstatus <span class="inline-block px-2 py-0.5 rounded-md font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">OPEN</span>. Tekan tombol hijau <em>Buka Shift</em> saat stand mulai beroperasi, dan tombol merah <em>Tutup Shift</em> saat sesi shift berakhir.
            </div>
        </div>

        <!-- Days & Shifts List -->
        <div class="space-y-6">
            @foreach ($eventDays as $day)
                <div class="space-y-3">
                    <!-- Day Section Header -->
                    <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Hari {{ $day->day_name }}</h2>
                            <span class="text-xs font-mono font-medium text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md">
                                {{ $day->event_date->format('d/m/Y') }}
                            </span>
                        </div>
                        @if ($day->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-xs">
                                HARI AKTIF
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 uppercase text-[11px]">
                                {{ $day->status }}
                            </span>
                        @endif
                    </div>

                    <!-- Shift Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($day->shifts as $shift)
                            <div class="rounded-2xl p-4 sm:p-5 transition duration-200 flex flex-col justify-between space-y-4 {{ $shift->isOpen() ? 'bg-gradient-to-br from-emerald-50/60 to-white border-2 border-emerald-400 shadow-md ring-4 ring-emerald-500/10' : ($shift->isClosed() ? 'bg-slate-50/80 border border-slate-200/90 shadow-xs' : 'bg-white border border-slate-200 shadow-xs hover:border-slate-300') }}">
                                <div class="space-y-2.5">
                                    <!-- Header: Shift Name & Status Badge -->
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <h3 class="font-extrabold text-sm sm:text-base text-slate-900">
                                                {{ $shift->shift_number }}
                                            </h3>
                                            <div class="text-xs text-slate-500 font-medium">
                                                {{ $shift->name }}
                                            </div>
                                        </div>

                                        @if ($shift->isOpen())
                                            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                                OPEN
                                            </span>
                                        @elseif ($shift->isClosed())
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                                CLOSED
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                SCHEDULED
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Operational Hours -->
                                    <div class="text-xs font-medium text-slate-600 flex items-center gap-1.5 bg-slate-50/80 px-2.5 py-1.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400">Jam Operasional:</span>
                                        <strong class="text-slate-800">{{ substr($shift->start_time ?? '08:00', 0, 5) }} — {{ substr($shift->end_time ?? '12:00', 0, 5) }} WIB</strong>
                                    </div>

                                    <!-- Cashiers Assigned Area -->
                                    <div class="pt-2">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
                                            Petugas Kasir On-Duty:
                                        </span>
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse ($shift->cashiers as $c)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200/80 text-slate-800 text-xs font-semibold transition">
                                                    {{ $c->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-400 italic py-1">Belum ada kasir ditugaskan</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons: Clearly Differentiated and Colored (No Icons) -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <!-- Edit Jadwal: Distinct Soft Indigo Pill Button -->
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($shift) }}, {{ Js::from($day) }})"
                                        class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 text-indigo-700 border border-indigo-200 hover:border-indigo-300 font-bold text-xs sm:text-sm rounded-xl transition duration-150 shadow-xs cursor-pointer"
                                    >
                                        Edit Jadwal
                                    </button>

                                    <!-- Shift Switch Action -->
                                    <div class="flex items-center gap-2">
                                        @if ($shift->isScheduled() || $shift->isClosed())
                                            <form action="{{ route('admin.shifts.open', $shift) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition duration-150 shadow-xs cursor-pointer">
                                                    Buka Shift
                                                </button>
                                            </form>
                                        @endif

                                        @if ($shift->isOpen())
                                            <form action="{{ route('admin.shifts.close', $shift) }}" method="POST" onsubmit="return confirm('Tutup shift ini? Kasir tidak akan dapat bertransaksi setelah shift ditutup.')">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl transition duration-150 shadow-xs cursor-pointer">
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

        <!-- Roomy, Accessible Edit Modal -->
        <div 
            x-show="showModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;"
        >
            <div 
                class="bg-white rounded-3xl max-w-md w-full p-6 border border-slate-200 shadow-2xl space-y-4" 
                @click.outside="showModal = false"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900" x-text="'Edit ' + shiftNumber + ' — Hari ' + dayName"></h3>
                        <p class="text-xs text-slate-500 mt-0.5">Ubah jam operasional dan kasir yang bertugas</p>
                    </div>
                    <button @click="showModal = false" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">Tutup</button>
                </div>

                <form :action="'/admin/shifts/' + shiftId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Shift</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Mulai</label>
                            <input type="time" name="start_time" x-model="startTime" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Selesai</label>
                            <input type="time" name="end_time" x-model="endTime" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Petugas Kasir</label>
                        <div class="space-y-2 border border-slate-200 rounded-2xl p-3 max-h-48 overflow-y-auto">
                            @foreach ($allCashiers as $c)
                                <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-indigo-50/50 cursor-pointer select-none transition">
                                    <input 
                                        type="checkbox" 
                                        name="cashier_ids[]" 
                                        value="{{ $c->id }}"
                                        :checked="selectedCashiers.includes({{ $c->id }})"
                                        class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                    >
                                    <div>
                                        <span class="font-bold text-xs text-slate-900 block leading-tight">{{ $c->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono block">@ {{ $c->username }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs sm:text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
