<x-layouts.app>
    <x-slot:title>Akun Petugas</x-slot:title>
    <x-slot:header>Kelola Akun Kasir & Admin Stand</x-slot:header>

    <div class="space-y-4" x-data="{
        showModal: false,
        modalMode: 'create',
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
        <!-- Toolbar -->
        <div class="flex items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 flex-1 max-w-sm text-xs">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama, username..."
                    class="w-full px-3 py-1.5 rounded border border-zinc-300 bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900"
                >
                <select name="role" class="px-2 py-1.5 rounded border border-zinc-300 bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="cashier" {{ $role === 'cashier' ? 'selected' : '' }}>Kasir</option>
                </select>
            </form>

            <button 
                type="button" 
                @click="openCreateModal()"
                class="px-3 py-1.5 bg-zinc-900 hover:bg-black text-white font-semibold text-xs rounded transition cursor-pointer"
            >
                + Tambah Akun
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Nama Lengkap</th>
                            <th class="py-2.5 px-3">Username & Email</th>
                            <th class="py-2.5 px-3 text-center">Role</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Transaksi</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($users as $u)
                            <tr class="hover:bg-zinc-50">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-zinc-900">{{ $u->name }}</div>
                                    @if ($u->id === auth()->id())
                                        <span class="text-[10px] text-zinc-400 font-medium">(Sedang Login)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-mono text-zinc-700">@ {{ $u->username }}</div>
                                    <div class="text-[11px] text-zinc-400">{{ $u->email }}</div>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $u->role === 'admin' ? 'bg-zinc-200 text-zinc-800' : 'bg-zinc-100 text-zinc-700' }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if ($u->is_active)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-zinc-800">
                                    {{ $u->transactions_count }}
                                </td>
                                <td class="py-2.5 px-3 text-right space-x-1 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($u) }})"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-[11px] transition"
                                    >
                                        Edit
                                    </button>

                                    @if ($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-active', $u) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 {{ $u->is_active ? 'text-zinc-500 hover:text-zinc-800' : 'text-emerald-700 hover:text-emerald-900' }} rounded text-[11px] transition"
                                                onclick="return confirm('Ubah status aktif akun?')"
                                            >
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-zinc-400 text-xs">
                                    Tidak ada data petugas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-zinc-200">
                {{ $users->links() }}
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
                    <h3 class="font-bold text-sm text-zinc-900" x-text="modalMode === 'create' ? 'Tambah Akun Petugas' : 'Edit Akun Petugas'"></h3>
                    <button @click="showModal = false" class="text-zinc-400 hover:text-zinc-600 text-xs">✕</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Username (Login)</label>
                        <input type="text" name="username" x-model="username" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Role</label>
                        <select name="role" x-model="role" required class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                            <option value="cashier">Petugas / Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">
                            Password <span x-show="modalMode === 'edit'" class="text-zinc-400 font-normal">(Kosongkan bila tidak diubah)</span>
                        </label>
                        <input type="password" name="password" x-model="password" :required="modalMode === 'create'" placeholder="••••••••" class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900">
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
