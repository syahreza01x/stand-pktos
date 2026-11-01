<x-layouts.app>
    <x-slot:title>Manajemen Hari Acara</x-slot:title>
    <x-slot:header>Kelola 5 Hari Penjualan Stand (Senin — Jumat)</x-slot:header>

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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($eventDays as $day)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg {{ $day->day_name === 'Senin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                Hari {{ $day->day_name }}
                            </span>

                            @if ($day->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">AKTIF HARI INI</span>
                            @elseif ($day->status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">SELESAI</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">MENDATANG</span>
                            @endif
                        </div>

                        <div class="text-lg font-black text-slate-900">
                            {{ $day->event_date->format('l, d F Y') }}
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            {{ $day->notes ?? 'Belum ada catatan hari.' }}
                        </div>

                        <!-- Shift Breakdown for this Day -->
                        <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Shift Terjadwal:</span>
                            @foreach ($day->shifts as $sh)
                                <div class="p-2.5 rounded-xl bg-slate-50 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $sh->shift_number }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $sh->name }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $sh->status === 'open' ? 'bg-emerald-100 text-emerald-800' : ($sh->status === 'closed' ? 'bg-slate-200 text-slate-600' : 'bg-amber-100 text-amber-800') }}">
                                        {{ strtoupper($sh->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">{{ $day->transactions_count }} transaksi</span>
                        <button 
                            type="button" 
                            @click="openEdit({{ Js::from($day) }})"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition cursor-pointer"
                        >
                            Ubah Status / Tanggal
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Edit Modal -->
        <div 
            x-show="showModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-bold text-slate-900 text-base" x-text="'Edit Hari ' + dayName"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form :action="'/admin/event-days/' + dayId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pelaksanaan</label>
                        <input type="date" name="event_date" x-model="eventDate" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Hari Acara</label>
                        <select name="status" x-model="status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="upcoming">Mendatang (Upcoming)</option>
                            <option value="active">Sedang Berjalan (Active)</option>
                            <option value="completed">Selesai (Completed)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan</label>
                        <textarea name="notes" x-model="notes" rows="2" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
