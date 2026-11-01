<x-layouts.app>
    <x-slot:title>Hari Acara</x-slot:title>
    <x-slot:header>Kelola 5 Hari Penjualan Stand</x-slot:header>

    <div class="space-y-6" x-data="{
        showModal: false,
        dayId: null,
        dayName: '',
        eventDate: '',
        status: 'upcoming',
        notes: '',

        openEdit(day) {
            this.dayId = day.id;
            this.dayName = day.day_name;
            this.eventDate = day.event_date.substring(0, 10);
            this.status = day.status;
            this.notes = day.notes || '';
            this.showModal = true;
        }
    }">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($eventDays as $day)
                <div class="rounded-2xl p-5 border transition duration-150 flex flex-col justify-between shadow-xs {{ $day->status === 'active' ? 'bg-gradient-to-br from-emerald-50/50 to-white border-emerald-300 ring-2 ring-emerald-500/15' : 'bg-white border-slate-200/90 hover:border-slate-300' }}">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-sm uppercase tracking-wide text-slate-900">
                                Hari {{ $day->day_name }}
                            </span>

                            @if ($day->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    AKTIF HARI INI
                                </span>
                            @elseif ($day->status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">SELESAI</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">MENDATANG</span>
                            @endif
                        </div>

                        <div>
                            <div class="text-base font-extrabold text-slate-900">
                                {{ $day->event_date->format('l, d F Y') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1 font-medium">
                                {{ $day->notes ?? 'Belum ada catatan acara.' }}
                            </div>
                        </div>

                        <!-- Shifts list -->
                        <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sesi Shift:</span>
                            @foreach ($day->shifts as $sh)
                                <div class="flex items-center justify-between text-xs py-1 px-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                    <span class="text-slate-800 font-medium">{{ $sh->shift_number }} ({{ $sh->name }})</span>
                                    <span class="font-black uppercase text-[10px] {{ $sh->status === 'open' ? 'text-emerald-700 font-extrabold' : 'text-slate-500' }}">
                                        {{ $sh->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-medium">{{ $day->transactions_count }} transaksi</span>
                        <button 
                            type="button" 
                            @click="openEdit({{ Js::from($day) }})"
                            class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 text-indigo-700 border border-indigo-200 font-bold rounded-xl text-xs transition cursor-pointer"
                        >
                            Ubah
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Modal -->
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
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl space-y-4" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="font-extrabold text-base text-slate-900" x-text="'Edit Hari ' + dayName"></h3>
                    <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold transition">✕</button>
                </div>

                <form :action="'/admin/event-days/' + dayId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Acara</label>
                        <input type="date" name="event_date" x-model="eventDate" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Hari</label>
                        <select name="status" x-model="status" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                            <option value="upcoming">Mendatang</option>
                            <option value="active">Aktif Hari Ini</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan</label>
                        <textarea name="notes" x-model="notes" rows="2" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" placeholder="Catatan kegiatan stan..."></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
