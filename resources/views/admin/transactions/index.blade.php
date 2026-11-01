<x-layouts.app>
    <x-slot:title>Riwayat Seluruh Transaksi</x-slot:title>
    <x-slot:header>Riwayat Transaksi Penjualan Stand</x-slot:header>

    <div class="space-y-6" x-data="{
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
        <!-- Comprehensive Filter Toolbar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Cari Kode Invoice</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="TRX-..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Day Filter -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Hari Acara</label>
                    <select name="event_day_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        @foreach ($eventDays as $d)
                            <option value="{{ $d->id }}" {{ $dayId == $d->id ? 'selected' : '' }}>{{ $d->day_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Filter -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Shift</label>
                    <select name="shift_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua Shift</option>
                        @foreach ($shifts as $s)
                            <option value="{{ $s->id }}" {{ $shiftId == $s->id ? 'selected' : '' }}>{{ $s->eventDay->day_name }} — {{ $s->shift_number }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cashier Filter -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Petugas / Kasir</label>
                    <select name="cashier_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua Kasir</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->id }}" {{ $cashierId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Status Transaksi</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Berhasil</option>
                        <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Shift & Hari</th>
                            <th class="py-3 px-4">Rincian Barang</th>
                            <th class="py-3 px-4 text-right">Total (Rp)</th>
                            <th class="py-3 px-4 text-center">Metode</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-slate-50/60 transition {{ $tx->isVoided() ? 'bg-rose-50/20' : '' }}">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-700 whitespace-nowrap">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->invoice_code }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $tx->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800 whitespace-nowrap">
                                    {{ $tx->cashier->name }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                    {{ $tx->shift->eventDay->day_name }} — {{ $tx->shift->shift_number }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-700 max-w-xs">
                                    <div class="space-y-0.5">
                                        @foreach ($tx->items as $it)
                                            <div class="truncate">
                                                {{ $it->product_name_snapshot }} <span class="text-slate-400 font-bold">×{{ $it->quantity }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900 {{ $tx->isVoided() ? 'line-through text-slate-400' : '' }}">
                                    {{ $tx->formatted_total }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                        {{ $tx->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($tx->status === 'completed')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            BERHASIL
                                        </span>
                                    @elseif ($tx->status === 'voided')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            VOIDED
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            KOREKSI
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                    <a 
                                        href="{{ route('admin.transactions.show', $tx) }}"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition inline-block"
                                    >
                                        Detail
                                    </a>

                                    @if (! $tx->isVoided())
                                        <button 
                                            type="button" 
                                            @click="openVoid({{ Js::from($tx) }})"
                                            class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-xs transition cursor-pointer"
                                        >
                                            Batalkan
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada transaksi yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $transactions->links() }}
            </div>
        </div>

        <!-- Void Confirmation Modal with Audit Trail Reason -->
        <div 
            x-show="showVoidModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200" @click.outside="showVoidModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Batalkan Transaksi (Void)</h3>
                        <p class="text-xs text-rose-600 font-mono" x-text="voidInvoice"></p>
                    </div>
                    <button @click="showVoidModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form :action="voidAction" method="POST" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs">
                        ⚠️ Pembatalan transaksi akan mengecualikan nominal transaksi ini dari seluruh laporan omzet stand. Transaksi tidak akan dihapus permanen melainkan disimpan di riwayat audit.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Pembatalan Transaksi <span class="text-rose-600">*</span>
                        </label>
                        <textarea 
                            name="reason" 
                            x-model="voidReason" 
                            required 
                            rows="3" 
                            placeholder="misal: Pelanggan salah pesan / barang habis / salah input kasir"
                            class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        ></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showVoidModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">Konfirmasi Void</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
