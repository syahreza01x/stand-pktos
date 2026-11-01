<x-layouts.app>
    <x-slot:title>Produk & Harga</x-slot:title>
    <x-slot:header>Kelola 6 Produk Stand & Harga</x-slot:header>

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
        <!-- Info Banner -->
        <div class="rounded-2xl bg-blue-50/80 border border-blue-200/80 p-4 sm:p-5 text-blue-950 shadow-xs">
            <div class="flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs shrink-0 mt-0.5">ℹ</span>
                <div class="text-xs sm:text-sm leading-relaxed">
                    <strong class="font-bold text-blue-900">Catatan Integritas Harga:</strong> Penyesuaian harga di menu ini hanya berlaku untuk transaksi baru. Transaksi lampau tetap memakai snapshot harga saat transaksi dibuat sehingga pembukuan historis tidak berubah.
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex-1 max-w-sm">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari produk atau kode..."
                    class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                >
            </form>

            <button 
                type="button" 
                @click="openCreateModal()"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition duration-150 cursor-pointer flex items-center justify-center gap-1.5"
            >
                <span>+</span>
                <span>Tambah Produk</span>
            </button>
        </div>

        <!-- Products Container (Mobile Cards + Desktop Table) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <!-- Mobile Cards View (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
                @forelse ($products as $p)
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $p->category === 'Makanan' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $p->category }}
                                </span>
                                <h4 class="font-black text-sm text-slate-900 mt-1">{{ $p->name }}</h4>
                                <span class="text-xs font-mono text-slate-400">{{ $p->code }} • Urutan: #{{ $p->sort_order }}</span>
                            </div>

                            @if ($p->is_active)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Nonaktif</span>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="font-black text-base text-slate-900">{{ $p->formatted_price }}</span>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="openEditModal({{ Js::from($p) }})"
                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl text-xs transition cursor-pointer"
                                >
                                    Edit
                                </button>

                                <form action="{{ route('admin.products.toggle-active', $p) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition cursor-pointer"
                                    >
                                        {{ $p->is_active ? 'Sembunyikan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">Belum ada produk tersimpan.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Urutan</th>
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-right">Harga Satuan (Rp)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-4 text-slate-400 font-mono font-bold">#{{ $p->sort_order }}</td>
                                <td class="py-2.5 px-4 font-mono font-bold text-slate-700">{{ $p->code }}</td>
                                <td class="py-2.5 px-4 font-bold text-slate-900">{{ $p->name }}</td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $p->category === 'Makanan' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                        {{ $p->category }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-black text-slate-900">{{ $p->formatted_price }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    @if ($p->is_active)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($p) }})"
                                        class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-lg text-xs transition cursor-pointer"
                                    >
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.products.toggle-active', $p) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition cursor-pointer"
                                        >
                                            {{ $p->is_active ? 'Sembunyikan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
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
                    <h3 class="font-extrabold text-base text-slate-900" x-text="modalMode === 'create' ? 'Tambah Produk' : 'Edit Produk & Harga'"></h3>
                    <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold transition">✕</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kode</label>
                            <input type="text" name="code" x-model="code" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono uppercase font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Urutan</label>
                            <input type="number" name="sort_order" x-model="sort_order" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Produk</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori</label>
                            <select name="category" x-model="category" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Snack">Snack</option>
                                <option value="Umum">Umum</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Harga Satuan (Rp)</label>
                            <input type="number" step="500" name="price" x-model="price" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
