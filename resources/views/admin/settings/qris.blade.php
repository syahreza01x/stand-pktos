<x-layouts.app>
    <x-slot:title>Integrasi QRIS ShopeePay Merchant</x-slot:title>
    <x-slot:header>Pengaturan & Sinkronisasi QRIS Shopee Partner (Stand GenBI)</x-slot:header>

    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                Terdapat kesalahan dalam pengisian formulir. Silakan periksa kembali data Anda.
            </div>
        @endif

        <!-- Status Koneksi Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Status Koneksi API</span>
                    <h3 class="font-black text-slate-900 text-base mt-0.5">
                        ShopeePay Merchant (Shopee Partner)
                    </h3>
                </div>

                <div class="flex items-center gap-2">
                    @if ($testStatus && ($testStatus['success'] ?? false))
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            TERHUBUNG
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            PERLU CEK TOKEN
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px] block">Nama Toko Stand:</span>
                    <span class="font-black text-slate-900 text-sm mt-0.5 block">
                        {{ $testStatus['store_name'] ?? 'Stand GenBI' }}
                    </span>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px] block">Merchant ID:</span>
                    <span class="font-black text-slate-900 text-sm mt-0.5 block font-mono">
                        {{ $testStatus['merchant_id'] ?? '-' }}
                    </span>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px] block">Total Omset QRIS Riil:</span>
                    <span class="font-black text-emerald-700 text-sm mt-0.5 block">
                        Rp{{ number_format((float) ($testStatus['total_net_sales'] ?? 0), 0, ',', '.') }}
                    </span>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px] block">Total Transaksi Berhasil:</span>
                    <span class="font-black text-indigo-700 text-sm mt-0.5 block">
                        {{ $testStatus['total_count'] ?? 0 }} Transaksi
                    </span>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-100">
                Pesan Sistem: <strong>{{ $testStatus['message'] ?? 'Belum dilakukan pengujian.' }}</strong>
            </p>
        </div>

        <!-- Form Update Kredensial -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-black text-slate-900 text-base">Konfigurasi Kredensial Shopee Partner</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Jika sesi di portal Shopee Partner kedaluwarsa, salin Cookie dan Token baru dari Developer Tools browser Anda lalu simpan di sini.
                </p>
            </div>

            <form action="{{ route('admin.settings.qris.update') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label for="shopeepay_cookie" class="font-bold text-slate-700 block mb-1">
                        Session Cookie (SPC_ST, SPC_U, __shopee_partner_website_x_token_live)
                    </label>
                    <textarea 
                        name="shopeepay_cookie" 
                        id="shopeepay_cookie" 
                        rows="4"
                        required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-[11px] bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder:text-slate-400"
                        placeholder="Tempel string Cookie lengkap dari portal partner.shopee.co.id di sini..."
                    >{{ old('shopeepay_cookie', $cookie) }}</textarea>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">
                        Didapat dari Request Headers tab Network saat membuka menu transaksi di partner.shopee.co.id.
                    </span>
                </div>

                <div>
                    <label for="shopeepay_token" class="font-bold text-slate-700 block mb-1">
                        Metadata Token (data.metadata.token)
                    </label>
                    <textarea 
                        name="shopeepay_token" 
                        id="shopeepay_token" 
                        rows="2"
                        required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-[11px] bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder:text-slate-400"
                        placeholder="B:EH6XSE...=="
                    >{{ old('shopeepay_token', $token) }}</textarea>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">
                        Didapat dari tab Payload request get-transaction-list di partner.shopee.co.id.
                    </span>
                </div>

                <div>
                    <label for="shopeepay_endpoint" class="font-bold text-slate-700 block mb-1">
                        API Endpoint URL (Opsional)
                    </label>
                    <input 
                        type="url" 
                        name="shopeepay_endpoint" 
                        id="shopeepay_endpoint"
                        value="{{ old('shopeepay_endpoint', $endpoint) }}"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition duration-150 cursor-pointer"
                    >
                        Simpan Perubahan Kredensial
                    </button>
                </div>
            </form>
        </div>

        <!-- 10 Transaksi Terakhir Masuk di ShopeePay -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">Pratinjau Mutasi QRIS Masuk Terkini</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Daftar 10 transaksi terakhir yang tercatat di server ShopeePay (Terakhir diambil: {{ $liveTransactions['fetched_at'] ?? '-' }})
                    </p>
                </div>

                <a 
                    href="{{ route('admin.settings.qris') }}" 
                    class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition"
                >
                    Refresh Mutasi
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                            <th class="py-2.5 px-3">Waktu Transaksi</th>
                            <th class="py-2.5 px-3">Nominal Masuk</th>
                            <th class="py-2.5 px-3">ID Transaksi Shopee</th>
                            <th class="py-2.5 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($liveTransactions['transactions'] as $tx)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">{{ $tx['formatted_time'] }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $tx['time_ago'] }}</div>
                                </td>
                                <td class="py-3 px-3 font-black text-emerald-700 text-sm">
                                    {{ $tx['formatted_amount'] }}
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-500 text-[11px]">
                                    {{ $tx['transaction_id'] }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tx['is_success'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $tx['status_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada transaksi QRIS yang tercatat atau data gagal dimuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

