<x-layouts.app>
    <x-slot:title>Manajemen Barang & Harga</x-slot:title>
    <x-slot:header>Kelola 6 Produk Stand & Penyesuaian Harga</x-slot:header>

    <div class="space-y-6" x-data="{
        showModal: false,
        modalMode: 'create',
        editId: null,
        code: '',
        name: '',
        category: 'Minuman',
        price: '',
        sort_order: 0,
        formAction: '{{ route('admin.products.store') }}',

        openCreateModal() {
            this.modalMode = 'create';
            this.editId = null;
            this.code = 'PRD-' + Math.random().toString(36).substring(2, 6).toUpperCase();
            this.name = '';
            this.category = 'Makanan';
            this.price = '';
            this.sort_order = 1;
            this.formAction = '{{ route('admin.products.store') }}';
            this.showModal = true;
        },

        openEditModal(prod) {
            this.modalMode = 'edit';
            this.editId = prod.id;
            this.code = prod.code;
            this.name = prod.name;
            this.category = prod.category;
            this.price = parseFloat(prod.price);
            this.sort_order = prod.sort_order;
            this.formAction = '/admin/products/' + prod.id;
            this.showModal = true;
        }
    }">
        <!-- Information Box (Data Integrity Guarantee) -->
        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950 flex items-start gap-3 text-xs">
            <span class="text-xl">🛡️</span>
            <div>
                <strong class="font-bold">Keamanan Integritas Data Harga:</strong>
                <p class="text-indigo-800 mt-0.5 leading-relaxed">
                    Setiap perubahan harga barang di menu ini hanya berlaku untuk transaksi baru. Transaksi lampau tetap mempertahankan snapshot harga asli saat transaksi dibuat, sehingga laporan keuangan historis tidak akan pernah berubah atau terkorupsi.
                </p>
            </div>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex items-center gap-2 flex-1 max-w-sm">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama atau kode produk..."
                    class="w-full px-4 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </form>

            <button 
                type="button" 
                @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer"
            >
                <span>+ Tambah Produk Baru</span>
            </button>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Urutan</th>
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-right">Harga Satuan (Rp)</th>
                            <th class="py-3 px-4 text-center">Frekuensi Transaksi</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $p)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-400">
                                    {{ $p->sort_order }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-600">
                                    {{ $p->code }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700">
                                        {{ $p->category }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900 text-sm">
                                    {{ $p->formatted_price }}
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600">
                                    {{ $p->transaction_items_count }} kali
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($p->is_active)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Dijual (Aktif)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Disembunyikan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($p) }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition"
                                    >
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.products.toggle-active', $p) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="px-2.5 py-1.5 {{ $p->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-semibold rounded-lg text-xs transition"
                                        >
                                            {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada produk yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create / Edit Product Modal -->
        <div 
            x-show="showModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-bold text-slate-900 text-base" x-text="modalMode === 'create' ? 'Tambah Produk Stand' : 'Edit Produk & Harga'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Produk</label>
                            <input type="text" name="code" x-model="code" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan Display</label>
                            <input type="number" name="sort_order" x-model="sort_order" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" x-model="name" required placeholder="misal: Boba Brown Sugar" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category" x-model="category" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Snack">Snack</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                            <input type="number" step="500" name="price" x-model="price" required placeholder="10000" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs" x-text="modalMode === 'create' ? 'Simpan Produk' : 'Perbarui Produk'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
