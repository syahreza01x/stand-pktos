<x-layouts.app>
    <x-slot:title>Log Aktivitas</x-slot:title>
    <x-slot:header>Log Aktivitas & Audit Trail</x-slot:header>

    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white rounded-lg border border-zinc-200 p-3">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari deskripsi..."
                        class="w-full px-2.5 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900"
                    >
                </div>

                <div>
                    <select name="module" class="w-full px-2 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $m)
                            <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="user_id" class="w-full px-2 py-1.5 rounded border border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-900" onchange="this.form.submit()">
                        <option value="">Semua Pengguna</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Pengguna</th>
                            <th class="py-2.5 px-3">Modul / Aksi</th>
                            <th class="py-2.5 px-3">Deskripsi</th>
                            <th class="py-2.5 px-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-mono text-[11px]">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-zinc-50">
                                <td class="py-2 px-3 text-zinc-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-2 px-3 font-sans whitespace-nowrap">
                                    <span class="font-bold text-zinc-800">{{ $log->user?->name ?? 'System' }}</span>
                                    @if ($log->user)
                                        <span class="text-zinc-400 text-[10px]">(@ {{ $log->user->username }})</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-zinc-100 text-zinc-700">
                                        {{ $log->module }}:{{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 font-sans text-zinc-800 text-xs">
                                    <div>{{ $log->description }}</div>
                                    @if ($log->payload)
                                        <details class="mt-0.5">
                                            <summary class="text-[10px] text-zinc-500 font-mono cursor-pointer hover:underline">Payload JSON</summary>
                                            <pre class="mt-1 p-2 bg-zinc-900 text-zinc-100 rounded text-[10px] overflow-x-auto max-w-lg">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-zinc-400 whitespace-nowrap">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-zinc-400 text-xs font-sans">
                                    Belum ada log tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-zinc-200">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
