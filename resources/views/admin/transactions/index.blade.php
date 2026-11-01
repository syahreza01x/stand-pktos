<x-layouts.app>
    <x-slot:title>Riwayat Transaksi</x-slot:title>
    <x-slot:header>Riwayat Seluruh Transaksi Stand</x-slot:header>

    <div class="space-y-4" x-data="{
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
        <!-- Minimalist Filters Toolbar -->
        <div class="bg-white rounded-lg border border-zinc-200 p-3">
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                <!-- Search -->
                <div class="col-span-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari kode invoice (TRX-...)"
                        class="w-full px-2.5 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900"
                    >
                </div>

                <!-- Day Filter -->
                <div>
                    <select name="event_day_id" class="w-full px-2 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        @foreach ($eventDays as $d)
                            <option value="{{ $d->id }}" {{ $dayId == $d->id ? 'selected' : '' }}>{{ $d->day_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Filter -->
                <div>
                    <select name="shift_id" class="w-full px-2 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                        <option value="">Semua Shift</option>
                        @foreach ($shifts as $s)
                            <option value="{{ $s->id }}" {{ $shiftId == $s->id ? 'selected' : '' }}>{{ $s->eventDay->day_name }} - {{ $s->shift_number }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cashier Filter -->
                <div>
                    <select name="cashier_id" class="w-full px-2 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                        <option value="">Semua Kasir</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->id }}" {{ $cashierId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full px-2 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Berhasil</option>
                        <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Void</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Invoice</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Kasir</th>
                            <th class="py-2.5 px-3">Shift</th>
                            <th class="py-2.5 px-3">Item Belanja</th>
                            <th class="py-2.5 px-3 text-right">Total (Rp)</th>
                            <th class="py-2.5 px-3 text-center">Metode</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-zinc-50 {{ $tx->isVoided() ? 'bg-zinc-50/60' : '' }}">
                                <td class="py-2 px-3 font-mono font-bold text-zinc-900 whitespace-nowrap">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->invoice_code }}
                                    </a>
                                </td>
                                <td class="py-2 px-3 text-zinc-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2 px-3 text-zinc-800 whitespace-nowrap">{{ $tx->cashier->name }}</td>
                                <td class="py-2 px-3 text-zinc-600 whitespace-nowrap">{{ $tx->shift->eventDay->day_name }} — {{ $tx->shift->shift_number }}</td>
                                <td class="py-2 px-3 text-zinc-700 max-w-xs truncate">
                                    @foreach ($tx->items as $it)
                                        <span class="inline-block mr-1.5">{{ $it->product_name_snapshot }} (×{{ $it->quantity }})</span>
                                    @endforeach
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-zinc-900 {{ $tx->isVoided() ? 'line-through text-zinc-400' : '' }}">
                                    {{ $tx->formatted_total }}
                                </td>
                                <td class="py-2 px-3 text-center uppercase text-[10px] text-zinc-500 font-mono">{{ $tx->payment_method }}</td>
                                <td class="py-2 px-3 text-center">
                                    @if ($tx->status === 'completed')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BERHASIL</span>
                                    @elseif ($tx->status === 'voided')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">VOID</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">KOREKSI</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-right space-x-1 whitespace-nowrap">
                                    <a 
                                        href="{{ route('admin.transactions.show', $tx) }}"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-[11px] inline-block transition"
                                    >
                                        Detail
                                    </a>

                                    @if (! $tx->isVoided())
                                        <button 
                                            type="button" 
                                            @click="openVoid({{ Js::from($tx) }})"
                                            class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded text-[11px] font-semibold transition cursor-pointer"
                                        >
                                            Void
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 text-center text-zinc-400 text-xs">
                                    Tidak ada transaksi yang cocok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-zinc-200">
                {{ $transactions->links() }}
            </div>
        </div>

        <!-- Void Modal -->
        <div 
            x-show="showVoidModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg max-w-sm w-full p-5 border border-zinc-200" @click.outside="showVoidModal = false">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200 mb-3">
                    <div>
                        <h3 class="font-bold text-sm text-zinc-900">Batalkan Transaksi (Void)</h3>
                        <span class="text-xs text-zinc-500 font-mono" x-text="voidInvoice"></span>
                    </div>
                    <button @click="showVoidModal = false" class="text-zinc-400 hover:text-zinc-600 text-xs">✕</button>
                </div>

                <form :action="voidAction" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">
                            Alasan Pembatalan Transaksi <span class="text-rose-600">*</span>
                        </label>
                        <textarea 
                            name="reason" 
                            x-model="voidReason" 
                            required 
                            rows="3" 
                            placeholder="Alasan pembatalan..."
                            class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-rose-500"
                        ></textarea>
                    </div>

                    <div class="pt-2 border-t border-zinc-200 flex items-center justify-end gap-2">
                        <button type="button" @click="showVoidModal = false" class="px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded">Batal</button>
                        <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded cursor-pointer">Konfirmasi Void</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
