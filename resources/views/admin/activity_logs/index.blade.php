<x-layouts.app>
    <x-slot:title>Log Aktivitas</x-slot:title>
    <x-slot:header>Log Aktivitas & Audit Trail</x-slot:header>

    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Deskripsi</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari aktivitas..."
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                    >
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Modul</label>
                    <select name="module" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $m)
                            <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pengguna</label>
                    <select name="user_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" onchange="this.form.submit()">
                        <option value="">Semua Pengguna</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Logs Container (Mobile Cards + Desktop Table) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <!-- Mobile Cards View (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
                @forelse ($logs as $log)
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">
                                {{ $log->module }}:{{ $log->action }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->format('d/m H:i:s') }}</span>
                        </div>

                        <div class="text-xs font-semibold text-slate-800">
                            {{ $log->user?->name ?? 'System' }} 
                            @if ($log->user)
                                <span class="text-slate-400 text-[11px] font-mono">(@ {{ $log->user->username }})</span>
                            @endif
                        </div>

                        <div class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-100">
                            {{ $log->description }}
                            @if ($log->payload)
                                <details class="mt-1 pt-1 border-t border-slate-100">
                                    <summary class="text-[10px] text-indigo-600 font-bold font-mono cursor-pointer hover:underline">Payload JSON</summary>
                                    <pre class="mt-1 p-2 bg-slate-900 text-slate-100 rounded-lg text-[10px] overflow-x-auto">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </div>

                        <div class="text-[10px] text-slate-400 font-mono">
                            IP: {{ $log->ip_address ?? '127.0.0.1' }}
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">Belum ada log tercatat.</div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Modul / Aksi</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-2.5 px-4 font-sans whitespace-nowrap">
                                    <span class="font-bold text-slate-800">{{ $log->user?->name ?? 'System' }}</span>
                                    @if ($log->user)
                                        <span class="text-slate-400 text-[10px]">(@ {{ $log->user->username }})</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $log->module }}:{{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 font-sans text-slate-800 text-xs">
                                    <div>{{ $log->description }}</div>
                                    @if ($log->payload)
                                        <details class="mt-0.5">
                                            <summary class="text-[10px] text-indigo-600 font-bold font-mono cursor-pointer hover:underline">Payload JSON</summary>
                                            <pre class="mt-1 p-2 bg-slate-900 text-slate-100 rounded-lg text-[10px] overflow-x-auto max-w-lg">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-sans">
                                    Belum ada log tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
