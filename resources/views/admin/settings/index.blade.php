<x-layouts.app>
    <x-slot:title>Pengaturan & Info Sistem</x-slot:title>
    <x-slot:header>Pengaturan Sistem & Panduan Portabilitas Server</x-slot:header>

    <div class="space-y-6">
        <!-- System Parameters Grid -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Informasi Lingkungan Server Aktif</h3>
                    <p class="text-xs text-slate-500">Status runtime PHP, Framework Laravel, dan Koneksi MySQL</p>
                </div>

                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-xl border border-indigo-200 transition cursor-pointer">
                        🧹 Bersihkan Cache Aplikasi
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mt-4 text-xs">
                @foreach ($systemInfo as $key => $val)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 font-mono uppercase text-[10px] tracking-wider block">{{ str_replace('_', ' ', $key) }}</span>
                        <div class="font-bold text-slate-800 text-sm mt-0.5 font-mono truncate" title="{{ $val }}">
                            {{ $val }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Server-Agnostic Guarantee & Deployment Instructions -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Arsitektur Bebas Server (Server-Agnostic Architecture)</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Aplikasi ini dirancang tanpa ketergantungan path lokal, tanpa hardcode IP atau domain, dan dapat dipindahkan ke laptop lain, server pinjaman, maupun VPS cloud secara instan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-700">
                <!-- Windows Laragon -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-slate-900 text-sm">
                        <span>🪟</span> Lingkungan A: Windows + Laragon
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        Jalankan Laragon (Apache & MySQL). Tempatkan folder proyek di <code class="bg-white px-1 py-0.5 rounded border">C:\laragon\www\Stand-PKTOS</code> atau jalankan langsung dengan <code class="bg-white px-1 py-0.5 rounded border">php artisan serve --host=0.0.0.0 --port=8000</code>.
                    </p>
                    <div class="p-2 bg-indigo-50 text-indigo-900 rounded-lg text-[11px]">
                        <strong>Akses HP Kasir:</strong> Hubungkan HP dan laptop ke Wi-Fi / Hotspot yang sama, lalu buka IP laptop (misal: <code class="font-mono">http://192.168.1.10:8000</code>) atau gunakan Cloudflare Tunnel (<code class="font-mono">cloudflared tunnel --url http://localhost:8000</code>).
                    </div>
                </div>

                <!-- Linux VPS -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-slate-900 text-sm">
                        <span>🐧</span> Lingkungan B: Linux VPS (Ubuntu / Debian)
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        Arahkan Document Root Nginx/Apache ke folder <code class="bg-white px-1 py-0.5 rounded border">/public</code>. Pastikan permission folder <code class="bg-white px-1 py-0.5 rounded border">storage/</code> dan <code class="bg-white px-1 py-0.5 rounded border">bootstrap/cache/</code> adalah 775 atau dapat ditulis oleh www-data.
                    </p>
                    <div class="p-2 bg-slate-100 text-slate-700 rounded-lg text-[11px] font-mono">
                        git pull origin main<br>
                        composer install --no-dev --optimize-autoloader<br>
                        php artisan migrate --force<br>
                        php artisan config:cache && php artisan route:cache
                    </div>
                </div>

                <!-- Shared Hosting -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-slate-900 text-sm">
                        <span>🌐</span> Lingkungan C: Shared Hosting (cPanel)
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        Pastikan versi PHP minimal 8.2+ dengan ekstensi <code class="bg-white px-1 py-0.5 rounded border">pdo_mysql</code>, <code class="bg-white px-1 py-0.5 rounded border">bcmath</code>, dan <code class="bg-white px-1 py-0.5 rounded border">mbstring</code>. Pisahkan isi folder <code class="bg-white px-1 py-0.5 rounded border">public/</code> ke <code class="bg-white px-1 py-0.5 rounded border">public_html/</code> dan sesuaikan path <code class="bg-white px-1 py-0.5 rounded border">index.php</code>.
                    </p>
                </div>

                <!-- Server Pinjaman -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-slate-900 text-sm">
                        <span>💻</span> Lingkungan D: Laptop Pinjaman (Darurat)
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        Jika laptop utama bermasalah di hari acara:
                    </p>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600">
                        <li>Ekspor database MySQL dari laptop lama (<code class="bg-white px-1 py-0.5 rounded border">mysqldump -u root stand_pktos > backup.sql</code>).</li>
                        <li>Salin folder proyek ke laptop baru.</li>
                        <li>Import database (<code class="bg-white px-1 py-0.5 rounded border">mysql -u root stand_pktos &lt; backup.sql</code>).</li>
                        <li>Jalankan <code class="bg-white px-1 py-0.5 rounded border">php artisan serve --host=0.0.0.0</code>. Transaksi langsung pulih tanpa kehilangan data!</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

