<x-layouts.app>
    <x-slot:title>Activity Logs & Audit Trail</x-slot:title>
    <x-slot:header>Log Aktivitas & Audit Trail Sistem</x-slot:header>

    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cari Deskripsi Aktivitas</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari teks log..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Modul</label>
                    <select name="module" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $m)
                            <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pengguna</label>
                    <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Semua Pengguna</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Modul / Aksi</th>
                            <th class="py-3 px-4">Deskripsi Aktivitas</th>
                            <th class="py-3 px-4">IP & Info Klien</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-3 px-4 font-sans whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $log->user?->name ?? 'System' }}</div>
                                    @if ($log->user)
                                        <div class="text-[11px] text-slate-400">@ {{ $log->user->username }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ $log->module }} : {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-sans text-slate-800 text-xs">
                                    <div>{{ $log->description }}</div>
                                    @if ($log->payload)
                                        <details class="mt-1">
                                            <summary class="text-[10px] text-indigo-600 font-mono cursor-pointer hover:underline">Lihat Payload JSON</summary>
                                            <pre class="mt-1 p-2 bg-slate-900 text-slate-100 rounded-lg text-[10px] overflow-x-auto max-w-lg">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                    <div>IP: {{ $log->ip_address ?? '127.0.0.1' }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[150px]" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-sans">
                                    Belum ada log aktivitas yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
