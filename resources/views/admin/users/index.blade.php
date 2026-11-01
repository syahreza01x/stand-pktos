<x-layouts.app>
    <x-slot:title>Akun Petugas</x-slot:title>
    <x-slot:header>Kelola Akun Kasir & Admin Stand</x-slot:header>

    <div class="space-y-6" x-data="{
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
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 flex-1 max-w-md text-xs">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama atau username..."
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                >
                <select name="role" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="cashier" {{ $role === 'cashier' ? 'selected' : '' }}>Kasir</option>
                </select>
            </form>

            <button 
                type="button" 
                @click="openCreateModal()"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition duration-150 cursor-pointer"
            >
                Tambah Akun
            </button>
        </div>

        <!-- Users Container (Mobile Cards + Desktop Table) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <!-- Mobile Cards View (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
                @forelse ($users as $u)
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-sm text-slate-900 leading-tight">
                                    {{ $u->name }}
                                    @if ($u->id === auth()->id())
                                        <span class="text-[10px] text-indigo-600 font-semibold ml-1">(Anda)</span>
                                    @endif
                                </h4>
                                <span class="text-xs font-mono text-slate-500 block">@ {{ $u->username }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $u->email }}</span>
                            </div>

                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $u->role }}
                                </span>
                                <div class="mt-1">
                                    @if ($u->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-medium">{{ $u->transactions_count }} transaksi</span>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="openEditModal({{ Js::from($u) }})"
                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl text-xs transition cursor-pointer"
                                >
                                    Edit
                                </button>

                                @if ($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-active', $u) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition cursor-pointer"
                                            onclick="return confirm('Ubah status aktif akun?')"
                                        >
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">Tidak ada data petugas.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Username & Email</th>
                            <th class="py-3 px-4 text-center">Role</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Transaksi</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                    @if ($u->id === auth()->id())
                                        <span class="text-[10px] text-indigo-600 font-semibold">(Sedang Login)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4">
                                    <div class="font-mono text-slate-700 font-medium">@ {{ $u->username }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-center">
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
                                <td class="py-2.5 px-4 text-center font-black text-slate-800">
                                    {{ $u->transactions_count }}
                                </td>
                                <td class="py-2.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ Js::from($u) }})"
                                        class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-lg text-xs transition cursor-pointer"
                                    >
                                        Edit
                                    </button>

                                    @if ($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-active', $u) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                class="px-2.5 py-1 {{ $u->is_active ? 'text-slate-500 hover:text-slate-800' : 'text-emerald-700 hover:text-emerald-900' }} rounded-lg text-xs font-semibold transition cursor-pointer"
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
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data petugas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $users->links() }}
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
                    <h3 class="font-extrabold text-base text-slate-900" x-text="modalMode === 'create' ? 'Tambah Akun Petugas' : 'Edit Akun Petugas'"></h3>
                    <button @click="showModal = false" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">Tutup</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Username (Login)</label>
                        <input type="text" name="username" x-model="username" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email</label>
                        <input type="email" name="email" x-model="email" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role</label>
                        <select name="role" x-model="role" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                            <option value="cashier">Petugas / Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Password <span x-show="modalMode === 'edit'" class="text-slate-400 font-normal lowercase">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input type="password" name="password" x-model="password" :required="modalMode === 'create'" placeholder="••••••••" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
