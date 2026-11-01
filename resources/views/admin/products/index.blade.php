<x-layouts.app>
    <x-slot:title>Produk & Harga</x-slot:title>
    <x-slot:header>Kelola 6 Produk Stand & Harga</x-slot:header>

    <div class="space-y-4" x-data="{
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
        <div class="p-3 rounded-lg bg-zinc-100 border border-zinc-200 text-xs text-zinc-700">
            <strong>Catatan Integritas Harga:</strong> Penyesuaian harga di menu ini hanya berlaku untuk transaksi baru. Transaksi lampau tetap memakai snapshot harga saat transaksi dibuat sehingga pembukuan historis tidak berubah.
        </div>

        <!-- Toolbar -->
        <div class="flex items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex-1 max-w-xs">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari produk atau kode..."
                    class="w-full px-3 py-1.5 text-xs rounded border border-zinc-300 bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900"
                >
            </form>

            <button 
                type="button" 
                @click="openCreateModal()"
                class="px-3 py-1.5 bg-zinc-900 hover:bg-black text-white font-semibold text-xs rounded transition cursor-pointer"
            >
                + Tambah Produk
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">No</th>
                            <th class="py-2.5 px-3">Kode</th>
                            <th class="py-2.5 px-3">Nama Produk</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3 text-right">Harga Satuan (Rp)</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($products as $p)
                            <tr class="hover:bg-zinc-50">
                                <td class="py-2.5 px-3 text-zinc-400 font-mono">{{ $p->sort_order }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-zinc-700">{{ $p->code }}</td>
                                <td class="py-2.5 px-3 font-bold text-zinc-900">{{ $p->name }}</td>
                                <td class="py-2.5 px-3 text-zinc-600">{{ $p->category }}</td>
                                <td class="py-2.5 px-3 text-right font-bold text-zinc-900">{{ $p->formatted_price }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    @if ($p->is_active)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-200 text-zinc-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right space-x-1 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($p) }})"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-[11px] transition"
                                    >
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.products.toggle-active', $p) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="px-2 py-1 {{ $p->is_active ? 'text-zinc-500 hover:text-zinc-800' : 'text-emerald-700 hover:text-emerald-900' }} rounded text-[11px] transition"
                                        >
                                            {{ $p->is_active ? 'Sembunyikan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-zinc-400 text-xs">
                                    Belum ada produk tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div 
            x-show="showModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg max-w-sm w-full p-5 border border-zinc-200" @click.outside="showModal = false">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200 mb-3">
                    <h3 class="font-bold text-sm text-zinc-900" x-text="modalMode === 'create' ? 'Tambah Produk' : 'Edit Produk & Harga'"></h3>
                    <button @click="showModal = false" class="text-zinc-400 hover:text-zinc-600 text-xs">✕</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Kode</label>
                            <input type="text" name="code" x-model="code" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900 uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Urutan</label>
                            <input type="number" name="sort_order" x-model="sort_order" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Kategori</label>
                            <select name="category" x-model="category" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Snack">Snack</option>
                                <option value="Umum">Umum</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Harga Satuan (Rp)</label>
                            <input type="number" step="500" name="price" x-model="price" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
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
