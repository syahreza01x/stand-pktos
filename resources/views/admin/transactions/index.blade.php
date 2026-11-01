<x-layouts.app>
    <x-slot:title>Riwayat Transaksi</x-slot:title>
    <x-slot:header>Riwayat Seluruh Transaksi Stand</x-slot:header>

    <div class="space-y-5" x-data="{
        showVoidModal: false,
        voidAction: '',
        voidInvoice: '',
        voidReason: '',

        openVoid(tx) {
            this.voidInvoice = tx.invoice_code;
            this.voidAction = '/admin/transactions/' + tx.id + '/void';
            this.voidReason = '';
            this.showVoidModal = true;
        }
    }">
        <!-- Roomy Filters Toolbar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <!-- Search -->
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Struk</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Kode TRX-..."
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                    >
                </div>

                <!-- Day Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hari Acara</label>
                    <select name="event_day_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        @foreach ($eventDays as $d)
                            <option value="{{ $d->id }}" {{ $dayId == $d->id ? 'selected' : '' }}>{{ $d->day_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Shift</label>
                    <select name="shift_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                        <option value="">Semua Shift</option>
                        @foreach ($shifts as $s)
                            <option value="{{ $s->id }}" {{ $shiftId == $s->id ? 'selected' : '' }}>{{ $s->eventDay->day_name }} - {{ $s->shift_number }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cashier Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kasir</label>
                    <select name="cashier_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                        <option value="">Semua Kasir</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->id }}" {{ $cashierId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Berhasil</option>
                        <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Void</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Transactions Container (Mobile Cards + Desktop Table) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <!-- Mobile Cards View (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
                @forelse ($transactions as $tx)
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('admin.transactions.show', $tx) }}" class="font-mono font-bold text-xs text-indigo-700 hover:underline">
                                    {{ $tx->invoice_code }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    {{ $tx->created_at->format('d/m/Y H:i') }} • {{ $tx->shift->eventDay->day_name }} ({{ $tx->shift->shift_number }})
                                </div>
                            </div>

                            @if ($tx->status === 'completed')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">BERHASIL</span>
                            @elseif ($tx->status === 'voided')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">VOID</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">KOREKSI</span>
                            @endif
                        </div>

                        <div class="text-xs text-slate-600">
                            Kasir: <strong class="text-slate-800">{{ $tx->cashier->name }}</strong>
                        </div>

                        <div class="text-xs text-slate-500 bg-white p-2 rounded-lg border border-slate-200/60">
                            @foreach ($tx->items as $it)
                                <div class="flex justify-between py-0.5 text-[11px]">
                                    <span>{{ $it->product_name_snapshot }} (×{{ $it->quantity }})</span>
                                    <span class="font-semibold text-slate-700">{{ $it->formatted_subtotal }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <div>
                                <div class="text-xs text-slate-400">{{ $tx->payment_method }} • {{ $tx->total_items }} unit</div>
                                <div class="text-base font-black {{ $tx->isVoided() ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                    {{ $tx->formatted_total }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a 
                                    href="{{ route('admin.transactions.show', $tx) }}"
                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl text-xs transition"
                                >
                                    Detail
                                </a>

                                @if (! $tx->isVoided())
                                    <button 
                                        type="button" 
                                        @click="openVoid({{ Js::from($tx) }})"
                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition cursor-pointer"
                                    >
                                        Void
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">Tidak ada transaksi yang cocok dengan filter.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Shift</th>
                            <th class="py-3 px-4">Item Belanja</th>
                            <th class="py-3 px-4 text-right">Total (Rp)</th>
                            <th class="py-3 px-4 text-center">Metode</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-slate-50/80 transition {{ $tx->isVoided() ? 'bg-slate-50/60' : '' }}">
                                <td class="py-2.5 px-4 font-mono font-bold text-indigo-700 whitespace-nowrap">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->invoice_code }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2.5 px-4 text-slate-800 font-medium whitespace-nowrap">{{ $tx->cashier->name }}</td>
                                <td class="py-2.5 px-4 text-slate-600 whitespace-nowrap">{{ $tx->shift->eventDay->day_name }} — {{ $tx->shift->shift_number }}</td>
                                <td class="py-2.5 px-4 text-slate-700 max-w-xs truncate">
                                    @foreach ($tx->items as $it)
                                        <span class="inline-block mr-1.5">{{ $it->product_name_snapshot }} (×{{ $it->quantity }})</span>
                                    @endforeach
                                </td>
                                <td class="py-2.5 px-4 text-right font-black text-slate-900 {{ $tx->isVoided() ? 'line-through text-slate-400' : '' }}">
                                    {{ $tx->formatted_total }}
                                </td>
                                <td class="py-2.5 px-4 text-center uppercase text-[10px] text-slate-500 font-mono">{{ $tx->payment_method }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    @if ($tx->status === 'completed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">BERHASIL</span>
                                    @elseif ($tx->status === 'voided')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">VOID</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">KOREKSI</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a 
                                        href="{{ route('admin.transactions.show', $tx) }}"
                                        class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-lg text-xs inline-block transition"
                                    >
                                        Detail
                                    </a>

                                    @if (! $tx->isVoided())
                                        <button 
                                            type="button" 
                                            @click="openVoid({{ Js::from($tx) }})"
                                            class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg text-xs transition cursor-pointer"
                                        >
                                            Void
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada transaksi yang cocok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $transactions->links() }}
            </div>
        </div>

        <!-- Void Modal -->
        <div 
            x-show="showVoidModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl space-y-4" @click.outside="showVoidModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Batalkan Transaksi (Void)</h3>
                        <span class="text-xs text-indigo-700 font-mono font-bold" x-text="voidInvoice"></span>
                    </div>
                    <button @click="showVoidModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold transition">✕</button>
                </div>

                <form :action="voidAction" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alasan Pembatalan Transaksi <span class="text-rose-600">*</span>
                        </label>
                        <textarea 
                            name="reason" 
                            x-model="voidReason" 
                            required 
                            rows="3" 
                            placeholder="Tuliskan alasan pembatalan struk ini..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium"
                        ></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showVoidModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">Konfirmasi Void</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
