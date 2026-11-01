<x-layouts.app>
    <x-slot:title>Hari Acara</x-slot:title>
    <x-slot:header>Kelola 5 Hari Penjualan Stand (Senin — Jumat)</x-slot:header>

    <div class="space-y-4" x-data="{
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($eventDays as $day)
                <div class="bg-white rounded-lg border border-zinc-200 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs uppercase text-zinc-900">
                                Hari {{ $day->day_name }}
                            </span>

                            @if ($day->status === 'active')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">AKTIF</span>
                            @elseif ($day->status === 'completed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600">SELESAI</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-700">MENDATANG</span>
                            @endif
                        </div>

                        <div class="text-sm font-bold text-zinc-900">
                            {{ $day->event_date->format('l, d F Y') }}
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5">
                            {{ $day->notes ?? 'Belum ada catatan.' }}
                        </div>

                        <!-- Shifts list -->
                        <div class="mt-3 pt-2 border-t border-zinc-100 space-y-1 text-xs">
                            @foreach ($day->shifts as $sh)
                                <div class="flex items-center justify-between text-[11px] py-0.5">
                                    <span class="text-zinc-700">{{ $sh->shift_number }} ({{ $sh->name }})</span>
                                    <span class="font-bold uppercase text-[10px] {{ $sh->status === 'open' ? 'text-emerald-700' : 'text-zinc-500' }}">
                                        {{ $sh->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-t border-zinc-100 flex items-center justify-between">
                        <span class="text-[11px] text-zinc-400">{{ $day->transactions_count }} transaksi</span>
                        <button 
                            type="button" 
                            @click="openEdit({{ Js::from($day) }})"
                            class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-xs transition cursor-pointer"
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
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg max-w-sm w-full p-5 border border-zinc-200" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200 mb-3">
                    <h3 class="font-bold text-sm text-zinc-900" x-text="'Edit Hari ' + dayName"></h3>
                    <button @click="showModal = false" class="text-zinc-400 hover:text-zinc-600 text-xs">✕</button>
                </div>

                <form :action="'/admin/event-days/' + dayId" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Tanggal</label>
                        <input type="date" name="event_date" x-model="eventDate" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                        <select name="status" x-model="status" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                            <option value="upcoming">Mendatang</option>
                            <option value="active">Aktif Hari Ini</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Catatan</label>
                        <textarea name="notes" x-model="notes" rows="2" class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900"></textarea>
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
