<x-layouts.app>
    <x-slot:title>Pengaturan & Info Sistem</x-slot:title>
    <x-slot:header>Pengaturan Sistem & Panduan Portabilitas Server</x-slot:header>

    <div class="space-y-6" x-data="{ showResetModal: false, inputCode: '', adminPassword: '', expectedCode: '{{ $resetCode }}' }">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach ($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif
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
                        Bersihkan Cache Aplikasi
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

        <!-- Danger Zone: Reset Transaksi Uji Coba -->
        <div class="bg-white rounded-2xl border border-rose-200 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-rose-100 pb-3">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 block">Zona Pembersihan Data</span>
                    <h3 class="font-black text-slate-900 text-base mt-0.5">Reset Data Transaksi Uji Coba</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Bersihkan database dari transaksi simulasi sebelum stand resmi dibuka untuk pembeli umum.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $totalTransactions > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ $totalTransactions }} Transaksi Tercatat
                    </span>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-rose-50/60 border border-rose-100 text-xs text-slate-700 space-y-2">
                <div class="font-bold text-rose-950">Informasi Penting Sebelum Mereset:</div>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Semua riwayat transaksi penjualan (tunai, QRIS, transfer), rincian item belanja, dan catatan void akan <strong>dihapus permanen</strong>.</li>
                    <li><strong>Data Aman & Tidak Terhapus:</strong> Akun kasir, jadwal shift tugas, katalog menu/harga, dan token integrasi ShopeePay <strong>tetap utuh</strong>.</li>
                    <li>Tindakan ini memerlukan <strong>dua lapis verifikasi</strong>: kode angka acak di layar dan password akun admin untuk mencegah salah klik.</li>
                </ul>
            </div>

            <div class="pt-1 flex items-center justify-end">
                <button 
                    type="button" 
                    @click="showResetModal = true; inputCode = ''; adminPassword = '';"
                    class="w-full sm:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition duration-150 cursor-pointer"
                >
                    Reset Seluruh Transaksi Uji Coba
                </button>
            </div>
        </div>

        <!-- Modal Konfirmasi Ganda Reset Transaksi -->
        <div 
            x-show="showResetModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            style="display: none;"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div 
                class="bg-white rounded-3xl max-w-md w-full p-6 border border-slate-200 shadow-2xl space-y-4"
                @click.outside="showResetModal = false"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 block">Konfirmasi Keamanan Ganda</span>
                        <h3 class="font-black text-base text-slate-900 mt-0.5">Konfirmasi Reset Transaksi</h3>
                    </div>
                    <button 
                        type="button" 
                        @click="showResetModal = false"
                        class="text-slate-400 hover:text-slate-600 text-xs font-bold p-1 cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                    <div class="font-bold">Peringatan:</div>
                    <div>
                        Sebanyak <strong>{{ $totalTransactions }} data transaksi</strong> akan dihapus dari database. Setelah dihapus, angka penjualan akan kembali ke 0.
                    </div>
                </div>

                <form action="{{ route('admin.settings.reset-transactions') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <!-- Verifikasi 1: Masukkan Kode Angka Acak -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">
                            1. Masukkan Kode Konfirmasi Berikut:
                        </label>
                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between">
                            <span class="text-[11px] text-slate-500 font-medium">Ketik angka ini di bawah:</span>
                            <span class="font-mono text-base font-black tracking-widest text-indigo-700 bg-white px-3 py-1 rounded-lg border border-slate-300 select-all">
                                {{ $resetCode }}
                            </span>
                        </div>
                        <input 
                            type="text" 
                            name="confirmation_code" 
                            x-model="inputCode" 
                            required
                            maxlength="6"
                            placeholder="Ketik 6 digit angka di atas" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm tracking-wider text-center focus:outline-none focus:ring-2 focus:ring-rose-500 placeholder:text-slate-400 placeholder:tracking-normal"
                        >
                    </div>

                    <!-- Verifikasi 2: Masukkan Password Admin -->
                    <div class="space-y-1.5">
                        <label for="admin_password" class="font-bold text-slate-700 block">
                            2. Masukkan Password Akun Admin:
                        </label>
                        <input 
                            type="password" 
                            id="admin_password"
                            name="admin_password" 
                            x-model="adminPassword" 
                            required
                            placeholder="Password admin saat ini" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 placeholder:text-slate-400"
                        >
                    </div>

                    <div class="pt-2 space-y-2">
                        <button 
                            type="submit" 
                            :disabled="inputCode !== expectedCode || adminPassword.length === 0"
                            class="w-full py-3 rounded-xl text-xs font-bold text-white transition shadow-xs cursor-pointer disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed bg-rose-600 hover:bg-rose-700 active:scale-95"
                        >
                            <span x-show="inputCode === expectedCode && adminPassword.length > 0">
                                Ya, Bersihkan Seluruh Transaksi Sekarang
                            </span>
                            <span x-show="inputCode !== expectedCode || adminPassword.length === 0">
                                Lengkapi Kode & Password di Atas
                            </span>
                        </button>

                        <button 
                            type="button" 
                            @click="showResetModal = false"
                            class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                        >
                            Batalkan
                        </button>
                    </div>
                </form>
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
                    <div class="font-bold text-slate-900 text-sm">
                        Lingkungan A: Windows + Laragon
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
                    <div class="font-bold text-slate-900 text-sm">
                        Lingkungan B: Linux VPS (Ubuntu / Debian)
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
                    <div class="font-bold text-slate-900 text-sm">
                        Lingkungan C: Shared Hosting (cPanel)
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        Pastikan versi PHP minimal 8.2+ dengan ekstensi <code class="bg-white px-1 py-0.5 rounded border">pdo_mysql</code>, <code class="bg-white px-1 py-0.5 rounded border">bcmath</code>, dan <code class="bg-white px-1 py-0.5 rounded border">mbstring</code>. Pisahkan isi folder <code class="bg-white px-1 py-0.5 rounded border">public/</code> ke <code class="bg-white px-1 py-0.5 rounded border">public_html/</code> dan sesuaikan path <code class="bg-white px-1 py-0.5 rounded border">index.php</code>.
                    </p>
                </div>

                <!-- Server Pinjaman -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                    <div class="font-bold text-slate-900 text-sm">
                        Lingkungan D: Laptop Pinjaman (Darurat)
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
