<x-layouts.app>
    <x-slot:title>Manajemen Akun Petugas</x-slot:title>
    <x-slot:header>Kelola Akun Petugas Stand & Admin</x-slot:header>

    <div class="space-y-6" x-data="{
        showModal: false,
        modalMode: 'create', // 'create' or 'edit'
        editId: null,
        name: '',
        username: '',
        email: '',
        role: 'cashier',
        password: '',
        formAction: '{{ route('admin.users.store') }}',

        openCreateModal() {
            this.modalMode = 'create';
            this.editId = null;
            this.name = '';
            this.username = '';
            this.email = '';
            this.role = 'cashier';
            this.password = '';
            this.formAction = '{{ route('admin.users.store') }}';
            this.showModal = true;
        },

        openEditModal(user) {
            this.modalMode = 'edit';
            this.editId = user.id;
            this.name = user.name;
            this.username = user.username;
            this.email = user.email;
            this.role = user.role;
            this.password = '';
            this.formAction = '/admin/users/' + user.id;
            this.showModal = true;
        }
    }">
        <!-- Action Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 flex-1 max-w-md">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama, username, atau email..."
                    class="w-full px-4 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                <select name="role" class="px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="cashier" {{ $role === 'cashier' ? 'selected' : '' }}>Petugas</option>
                </select>
            </form>

            <button 
                type="button" 
                @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer"
            >
                <span>+ Tambah Akun Petugas</span>
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Nama Petugas</th>
                            <th class="py-3 px-4">Username & Email</th>
                            <th class="py-3 px-4 text-center">Role</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Total Transaksi</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                    @if ($u->id === auth()->id())
                                        <span class="text-[10px] text-indigo-600 font-semibold">(Anda Sedang Login)</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-mono text-slate-700 font-medium">@ {{ $u->username }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($u->role === 'admin')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                                            Admin
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800">
                                            Petugas / Kasir
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($u->is_active)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    {{ $u->transactions_count }}
                                </td>
                                <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($u) }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition"
                                    >
                                        Edit
                                    </button>

                                    @if ($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-active', $u) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                class="px-2.5 py-1.5 {{ $u->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-semibold rounded-lg text-xs transition"
                                                onclick="return confirm('Ubah status aktif akun ini?')"
                                            >
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data petugas yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Create / Edit User Modal -->
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
                    <h3 class="font-bold text-slate-900 text-base" x-text="modalMode === 'create' ? 'Tambah Akun Petugas Baru' : 'Edit Akun Petugas'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username (untuk Login)</label>
                        <input type="text" name="username" x-model="username" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Role / Peran</label>
                        <select name="role" x-model="role" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="cashier">Petugas / Kasir (Akses Terbatas)</option>
                            <option value="admin">Administrator Stand (Akses Penuh)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Password <span x-show="modalMode === 'edit'" class="text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span>
                        </label>
                        <input type="password" name="password" x-model="password" :required="modalMode === 'create'" placeholder="••••••••" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs" x-text="modalMode === 'create' ? 'Simpan Akun' : 'Perbarui Akun'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
