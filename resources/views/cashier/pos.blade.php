<x-layouts.cashier>
    <x-slot:title>Input Penjualan (POS)</x-slot:title>

    <div 
        x-data="{
            products: {{ Js::from($products) }},
            shiftSummary: {{ Js::from($shiftSummary) }},
            activeShiftId: {{ $activeShift->id }},
            cart: {},
            paymentMethod: 'cash',
            notes: '',
            isSubmitting: false,
            showReceiptModal: false,
            showShiftSummaryModal: false,
            showMobileCheckout: false,
            completedTx: null,
            idempotencyKey: '',

            // Modal states for grouped menu cards
            showQtellaModal: false,
            selectedQtellaFlavor: 'Balado',
            qtellaPrice: 5000,
            qtellaQty: 1,

            showKoinModal: false,
            selectedKoinFlavor: 'Balado',
            koinPrice: 2000,
            koinQty: 1,

            flavors: ['Balado', 'BBQ', 'Jagung Bakar', 'Extra Pedas'],

            // Live QRIS ShopeePay state
            liveQrisList: [],
            isCheckingQris: false,
            qrisTotalNetSales: 'Rp0',
            lastQrisCheckTime: '',
            qrisMatched: false,
            qrisMatchMessage: '',

            init() {
                this.generateIdempotencyKey();
                this.fetchLiveQris(false);
            },

            async fetchLiveQris(force = true) {
                this.isCheckingQris = true;
                try {
                    const res = await fetch('{{ route('cashier.qris.live-mutations') }}?refresh=' + (force ? '1' : '0'));
                    const data = await res.json();
                    if (data && data.success) {
                        this.liveQrisList = data.transactions || [];
                        this.qrisTotalNetSales = data.formatted_total_net_sales || 'Rp0';
                        this.lastQrisCheckTime = data.fetched_at || '';
                        this.checkQrisMatch();
                    }
                } catch (e) {
                    console.error('QRIS poll error', e);
                } finally {
                    this.isCheckingQris = false;
                }
            },

            checkQrisMatch() {
                if (this.totalAmount <= 0) {
                    this.qrisMatched = false;
                    this.qrisMatchMessage = '';
                    return;
                }
                const found = this.liveQrisList.find(tx => tx.is_success && Math.abs(tx.amount - this.totalAmount) < 0.01);
                if (found) {
                    this.qrisMatched = true;
                    this.qrisMatchMessage = 'Pembayaran ' + found.formatted_amount + ' terverifikasi masuk (' + found.formatted_time + ')';
                } else {
                    this.qrisMatched = false;
                    this.qrisMatchMessage = 'Belum ada pembayaran ' + this.formatRupiah(this.totalAmount) + ' baru. Minta pembeli scan & klik Cek.';
                }
            },

            setPaymentMethod(method) {
                this.paymentMethod = method;
                if (method === 'qris') {
                    this.fetchLiveQris(true);
                }
            },

            generateIdempotencyKey() {
                this.idempotencyKey = 'pos-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);
            },

            // Helpers for finding products from the DB list
            getQtellaProduct(flavor) {
                return this.products.find(p => p.name.toLowerCase().includes('qtella') && p.name.toLowerCase().includes(flavor.toLowerCase()));
            },

            getKoinProduct(flavor) {
                return this.products.find(p => p.name.toLowerCase().includes('koin') && p.name.toLowerCase().includes(flavor.toLowerCase()));
            },

            getMojitoProduct() {
                return this.products.find(p => p.code === 'PRD-MOJITO' || p.name.toLowerCase().includes('mojito'));
            },

            // Modal openers
            openQtellaModal() {
                this.selectedQtellaFlavor = 'Balado';
                this.qtellaPrice = 5000;
                this.qtellaQty = 1;
                this.showQtellaModal = true;
            },

            openKoinModal() {
                this.selectedKoinFlavor = 'Balado';
                this.koinPrice = 2000;
                this.koinQty = 1;
                this.showKoinModal = true;
            },

            // Shortcut price setters
            setQtellaPrice(price) {
                this.qtellaPrice = price;
            },

            setKoinPrice(price) {
                this.koinPrice = price;
            },

            // Add Qtella with selected flavor & custom price
            addQtellaToCart() {
                const product = this.getQtellaProduct(this.selectedQtellaFlavor);
                const price = Number(this.qtellaPrice) > 0 ? Number(this.qtellaPrice) : 5000;
                if (product) {
                    this.addToCartWithPriceAndQty(product, price, this.qtellaQty);
                }
                this.showQtellaModal = false;
            },

            // Add Kerupuk Koin with selected flavor & custom price
            addKoinToCart() {
                const product = this.getKoinProduct(this.selectedKoinFlavor);
                const price = Number(this.koinPrice) > 0 ? Number(this.koinPrice) : 2000;
                if (product) {
                    this.addToCartWithPriceAndQty(product, price, this.koinQty);
                }
                this.showKoinModal = false;
            },

            // Cart operations keyed by product_id + price
            addToCartWithPriceAndQty(product, price, qty = 1) {
                const key = product.id + '_' + price;
                if (!this.cart[key]) {
                    this.cart[key] = {
                        key: key,
                        id: product.id,
                        name: product.name,
                        price: parseFloat(price),
                        quantity: qty
                    };
                } else {
                    this.cart[key].quantity += qty;
                }
            },

            addToCart(product) {
                this.addToCartWithPriceAndQty(product, parseFloat(product.price), 1);
            },

            decrementQuantity(key) {
                if (this.cart[key]) {
                    this.cart[key].quantity -= 1;
                    if (this.cart[key].quantity <= 0) {
                        delete this.cart[key];
                    }
                }
            },

            incrementQuantity(key) {
                if (this.cart[key]) {
                    this.cart[key].quantity += 1;
                }
            },

            clearCart() {
                this.cart = {};
                this.notes = '';
                this.showMobileCheckout = false;
                this.generateIdempotencyKey();
            },

            get cartItems() {
                return Object.values(this.cart);
            },

            get totalItemsCount() {
                return this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
            },

            get totalAmount() {
                return this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            // Group helpers for card badges
            getGroupCartItems(type) {
                if (type === 'qtella') {
                    return this.cartItems.filter(item => item.name.toLowerCase().includes('qtella'));
                }
                if (type === 'koin') {
                    return this.cartItems.filter(item => item.name.toLowerCase().includes('koin'));
                }
                if (type === 'mojito') {
                    return this.cartItems.filter(item => item.name.toLowerCase().includes('mojito'));
                }
                return [];
            },

            getGroupCartCount(type) {
                return this.getGroupCartItems(type).reduce((sum, item) => sum + item.quantity, 0);
            },

            formatRupiah(number) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(number);
            },

            async submitTransaction() {
                if (this.totalItemsCount === 0 || this.isSubmitting) return;

                this.isSubmitting = true;

                const payload = {
                    shift_id: this.activeShiftId,
                    payment_method: this.paymentMethod,
                    idempotency_key: this.idempotencyKey,
                    notes: this.notes,
                    items: this.cartItems.map(item => ({
                        product_id: item.id,
                        custom_price: item.price,
                        quantity: item.quantity
                    }))
                };

                try {
                    const response = await fetch('{{ route('cashier.pos.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.completedTx = result.data;
                        if (result.data && result.data.shift_summary) {
                            this.shiftSummary = result.data.shift_summary;
                        }
                        this.showMobileCheckout = false;
                        this.showReceiptModal = true;
                        this.clearCart();
                    } else {
                        alert(result.message || 'Gagal memproses transaksi.');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan: ' + error.message);
                } finally {
                    this.isSubmitting = false;
                }
            }
        }"
        class="space-y-4"
    >
        <!-- Shift Aktif Info Bar (Compact & To The Point on Mobile) -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200/90 shadow-2xs space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <div class="min-w-0">
                        <span class="font-black text-xs sm:text-sm text-slate-900 truncate block">
                            Hari {{ $activeShift->eventDay->day_name }} — {{ $activeShift->shift_number }}
                        </span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                        OPEN
                    </span>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <button 
                        type="button" 
                        @click="showShiftSummaryModal = true"
                        class="px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition cursor-pointer"
                    >
                        Rekap Kas Shift
                    </button>
                </div>
            </div>

            <!-- Mini Live Reconciliation Bar (Hidden on Mobile to save space, visible on tablet/desktop) -->
            <div class="hidden sm:grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-100">
                <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-2.5">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wide block">Tunai (Stand)</span>
                    <span class="text-xs sm:text-sm font-black text-emerald-900" x-text="formatRupiah(shiftSummary?.cash?.total || 0)"></span>
                    <span class="block text-[10px] text-emerald-700" x-text="(shiftSummary?.cash?.count || 0) + ' trx'"></span>
                </div>

                <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-2.5">
                    <span class="text-[10px] font-bold text-blue-800 uppercase tracking-wide block">QRIS Stand</span>
                    <span class="text-xs sm:text-sm font-black text-blue-900" x-text="formatRupiah(shiftSummary?.qris?.total || 0)"></span>
                    <span class="block text-[10px] text-blue-700" x-text="(shiftSummary?.qris?.count || 0) + ' trx'"></span>
                </div>

                <div class="bg-purple-50/70 border border-purple-100 rounded-xl p-2.5">
                    <span class="text-[10px] font-bold text-purple-800 uppercase tracking-wide block">Transfer (TF)</span>
                    <span class="text-xs sm:text-sm font-black text-purple-900" x-text="formatRupiah(shiftSummary?.transfer?.total || 0)"></span>
                    <span class="block text-[10px] text-purple-700" x-text="(shiftSummary?.transfer?.count || 0) + ' trx'"></span>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wide block">Total Shift</span>
                    <span class="text-xs sm:text-sm font-black text-slate-900" x-text="formatRupiah(shiftSummary?.total_revenue || 0)"></span>
                    <span class="block text-[10px] text-slate-500" x-text="(shiftSummary?.total_transactions || 0) + ' transaksi'"></span>
                </div>
            </div>
        </div>

        <!-- Section Header (Clean & Minimal) -->
        <div class="flex items-center justify-between px-1">
            <h2 class="font-bold text-xs sm:text-sm text-slate-700 uppercase tracking-wider">Pilih Barang:</h2>
            <span class="text-[11px] font-bold text-slate-500">
                3 Menu Pilihan
            </span>
        </div>

        <!-- Primary Layout: Menu Cards Grid & Desktop Cart -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Menu Cards (Left side on Desktop, Full on Mobile) -->
            <div :class="totalItemsCount > 0 ? 'pb-16' : ''" class="lg:col-span-7 xl:col-span-8 space-y-3.5">

                <!-- CARD 1: QTELLA -->
                <div 
                    class="bg-white p-4 sm:p-5 rounded-2xl border transition duration-150 shadow-xs space-y-3.5"
                    :class="getGroupCartCount('qtella') > 0 ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/10' : 'border-slate-200 hover:border-slate-300'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 mb-1.5">
                                Makanan
                            </span>
                            <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-tight">Qtella</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Varian rasa: Balado, BBQ, Jagung Bakar, Extra Pedas</p>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-sm sm:text-base text-slate-900 block">Harga Kustom</span>
                        </div>
                    </div>

                    <!-- Items in Cart Breakdown for Qtella -->
                    <template x-if="getGroupCartCount('qtella') > 0">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Sudah di keranjang:</span>
                                <span class="font-black text-indigo-700" x-text="getGroupCartCount('qtella') + ' porsi'"></span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="item in getGroupCartItems('qtella')" :key="item.key">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-slate-800 shadow-2xs">
                                        <span x-text="item.name.replace('Qtella - ', '')"></span>
                                        <span class="text-slate-500" x-text="'(' + formatRupiah(item.price) + ')'"></span>
                                        <span class="font-black text-indigo-700" x-text="'×' + item.quantity"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Card Action Button -->
                    <div>
                        <button 
                            type="button" 
                            @click="openQtellaModal()"
                            class="w-full py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm transition duration-150 cursor-pointer text-center flex items-center justify-center gap-2"
                            :class="getGroupCartCount('qtella') > 0 ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs' : 'bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 hover:border-indigo-600'"
                        >
                            <span x-text="getGroupCartCount('qtella') > 0 ? '+ Tambah Rasa / Harga Lain' : 'Pilih Rasa & Harga'"></span>
                        </button>
                    </div>
                </div>

                <!-- CARD 2: KERUPUK KOIN -->
                <div 
                    class="bg-white p-4 sm:p-5 rounded-2xl border transition duration-150 shadow-xs space-y-3.5"
                    :class="getGroupCartCount('koin') > 0 ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/10' : 'border-slate-200 hover:border-slate-300'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 mb-1.5">
                                Makanan
                            </span>
                            <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-tight">Kerupuk Koin</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Varian rasa: Balado, BBQ, Jagung Bakar, Extra Pedas</p>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-sm sm:text-base text-slate-900 block">Harga Kustom</span>
                        </div>
                    </div>

                    <!-- Items in Cart Breakdown for Kerupuk Koin -->
                    <template x-if="getGroupCartCount('koin') > 0">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Sudah di keranjang:</span>
                                <span class="font-black text-indigo-700" x-text="getGroupCartCount('koin') + ' porsi'"></span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="item in getGroupCartItems('koin')" :key="item.key">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-slate-800 shadow-2xs">
                                        <span x-text="item.name.replace('Kerupuk Koin - ', '').replace('Krupuk Koin - ', '')"></span>
                                        <span class="text-slate-500" x-text="'(' + formatRupiah(item.price) + ')'"></span>
                                        <span class="font-black text-indigo-700" x-text="'×' + item.quantity"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Card Action Button -->
                    <div>
                        <button 
                            type="button" 
                            @click="openKoinModal()"
                            class="w-full py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm transition duration-150 cursor-pointer text-center flex items-center justify-center gap-2"
                            :class="getGroupCartCount('koin') > 0 ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs' : 'bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 hover:border-indigo-600'"
                        >
                            <span x-text="getGroupCartCount('koin') > 0 ? '+ Tambah Rasa / Harga Lain' : 'Pilih Rasa & Harga'"></span>
                        </button>
                    </div>
                </div>

                <!-- CARD 3: MOJITO -->
                <div 
                    class="bg-white p-4 sm:p-5 rounded-2xl border transition duration-150 shadow-xs space-y-3.5"
                    :class="getGroupCartCount('mojito') > 0 ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/10' : 'border-slate-200 hover:border-slate-300'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 mb-1.5">
                                Minuman
                            </span>
                            <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-tight">Mojito</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Minuman segar jeruk nipis dan daun mint dingin</p>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-base sm:text-lg text-slate-900 block">Rp5.000</span>
                            <span class="text-[11px] text-slate-400 font-medium">per cup</span>
                        </div>
                    </div>

                    <!-- Direct Add or Stepper on Mojito Card -->
                    <div>
                        <template x-if="getGroupCartCount('mojito') === 0">
                            <button 
                                type="button" 
                                @click="const m = getMojitoProduct(); if (m) addToCart(m);"
                                class="w-full py-2.5 px-4 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 hover:border-sky-600 font-bold text-xs sm:text-sm transition duration-150 cursor-pointer text-center"
                            >
                                Tambah Mojito
                            </button>
                        </template>

                        <template x-if="getGroupCartCount('mojito') > 0">
                            <div class="flex items-center justify-between gap-2 bg-sky-600 text-white rounded-xl p-1.5">
                                <button 
                                    type="button" 
                                    @click="const it = getGroupCartItems('mojito')[0]; if (it) decrementQuantity(it.key);"
                                    class="w-8 h-8 rounded-lg bg-sky-700 hover:bg-sky-800 active:scale-95 text-white font-bold text-sm flex items-center justify-center transition cursor-pointer"
                                >
                                    -
                                </button>
                                <div class="text-center font-bold text-xs sm:text-sm text-white">
                                    <span x-text="getGroupCartCount('mojito') + ' Mojito di keranjang'"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="const m = getMojitoProduct(); if (m) addToCart(m);"
                                    class="w-8 h-8 rounded-lg bg-sky-700 hover:bg-sky-800 active:scale-95 text-white font-bold text-sm flex items-center justify-center transition cursor-pointer"
                                >
                                    +
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Desktop Sticky Cart (Visible on lg: screens) -->
            <div class="hidden lg:block lg:col-span-5 xl:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sticky top-20 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Keranjang Transaksi</h3>
                        <span class="text-xs text-slate-500" x-text="totalItemsCount + ' item dipilih'"></span>
                    </div>
                    <button 
                        type="button" 
                        @click="clearCart()" 
                        x-show="totalItemsCount > 0"
                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold cursor-pointer"
                    >
                        Kosongkan
                    </button>
                </div>

                <!-- Empty State -->
                <div x-show="totalItemsCount === 0" class="py-12 text-center text-slate-400 text-xs">
                    Pilih menu di sebelah kiri untuk menambah pesanan.
                </div>

                <!-- Cart Items List -->
                <div x-show="totalItemsCount > 0" class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-1">
                    <template x-for="item in cartItems" :key="item.key">
                        <div class="py-2.5 flex items-center justify-between gap-2 text-xs">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-900 truncate" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-500" x-text="formatRupiah(item.price) + ' × ' + item.quantity"></div>
                            </div>

                            <div class="flex items-center gap-1">
                                <button 
                                    type="button" 
                                    @click="decrementQuantity(item.key)"
                                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center cursor-pointer"
                                >
                                    -
                                </button>
                                <span class="w-6 text-center font-bold text-xs text-slate-900" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="incrementQuantity(item.key)"
                                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center cursor-pointer"
                                >
                                    +
                                </button>
                            </div>

                            <div class="w-20 text-right font-bold text-slate-900" x-text="formatRupiah(item.price * item.quantity)"></div>
                        </div>
                    </template>
                </div>

                <!-- Desktop Checkout Form -->
                <div x-show="totalItemsCount > 0" class="pt-3 border-t border-slate-200 space-y-3">
                    <div>
                        <span class="block text-xs font-semibold text-slate-700 mb-1.5">Metode Bayar:</span>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button 
                                type="button" 
                                @click="setPaymentMethod('cash')"
                                :class="paymentMethod === 'cash' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                            >
                                Tunai
                            </button>
                            <button 
                                type="button" 
                                @click="setPaymentMethod('qris')"
                                :class="paymentMethod === 'qris' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                            >
                                QRIS
                            </button>
                            <button 
                                type="button" 
                                @click="setPaymentMethod('transfer')"
                                :class="paymentMethod === 'transfer' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                            >
                                Transfer
                            </button>
                        </div>
                    </div>

                    <!-- Live QRIS ShopeePay Verification Box (Desktop) -->
                    <div x-show="paymentMethod === 'qris'" class="p-3.5 rounded-2xl bg-sky-50/70 border border-sky-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-black uppercase text-sky-900 tracking-wider block">QRIS Stand GenBI (ShopeePay)</span>
                                <span class="text-[11px] text-slate-500">Cek mutasi uang masuk otomatis</span>
                            </div>
                            <button 
                                type="button" 
                                @click="fetchLiveQris(true)"
                                :disabled="isCheckingQris"
                                class="px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-sky-100 disabled:opacity-50 text-sky-700 border border-sky-300 rounded-lg shadow-xs transition cursor-pointer"
                            >
                                <span x-show="!isCheckingQris">Cek Masuk</span>
                                <span x-show="isCheckingQris">Memeriksa...</span>
                            </button>
                        </div>

                        <!-- Match Alert Status -->
                        <div x-show="totalAmount > 0">
                            <template x-if="qrisMatched">
                                <div class="p-2.5 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold text-xs flex items-center justify-between">
                                    <div>
                                        <span class="block text-[10px] uppercase tracking-wider text-emerald-800 font-black">Uang Masuk Terverifikasi</span>
                                        <span x-text="qrisMatchMessage"></span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-900 font-black text-[10px]">COCOK</span>
                                </div>
                            </template>
                            <template x-if="!qrisMatched">
                                <div class="p-2.5 rounded-xl bg-white/80 border border-sky-200 text-slate-600 text-[11px]">
                                    <div class="flex items-center justify-between font-semibold">
                                        <span>Menunggu scan pembeli...</span>
                                        <span class="font-mono text-slate-500" x-text="formatRupiah(totalAmount)"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5" x-text="qrisMatchMessage || 'Minta pembeli scan QRIS, lalu klik tombol Cek Masuk.'"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Recent 3 incoming transactions -->
                        <div class="pt-2 border-t border-sky-200/60 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mutasi QRIS Terkini:</span>
                            <template x-if="liveQrisList.length === 0">
                                <div class="text-[11px] text-slate-400 italic">Belum ada transaksi QRIS masuk.</div>
                            </template>
                            <template x-for="item in liveQrisList.slice(0, 3)" :key="item.transaction_id">
                                <div class="flex items-center justify-between text-[11px] bg-white/80 px-2 py-1.5 rounded-lg border border-sky-100">
                                    <div>
                                        <strong class="text-slate-800" x-text="item.formatted_amount"></strong>
                                        <span class="text-[10px] text-slate-400 ml-1" x-text="item.time_ago"></span>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="item.status_label"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <input 
                            type="text" 
                            x-model="notes" 
                            placeholder="Catatan pesanan (opsional)..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Total Tagihan:</span>
                        <span class="text-xl font-black text-indigo-700" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <button 
                        type="button" 
                        @click="submitTransaction()"
                        :disabled="isSubmitting || totalItemsCount === 0"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-bold rounded-xl text-sm transition cursor-pointer shadow-xs"
                    >
                        <span x-show="!isSubmitting">Simpan Transaksi (Bayar)</span>
                        <span x-show="isSubmitting">Memproses Transaksi...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- MODAL SUB-MENU 1: QTELLA (PILIH RASA & INPUT / SHORTCUT) -->
        <!-- ======================================================== -->
        <div 
            x-show="showQtellaModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div 
                class="bg-white rounded-3xl max-w-sm w-full p-5 sm:p-6 border border-slate-200 shadow-2xl space-y-4"
                @click.outside="showQtellaModal = false"
            >
                <!-- Header -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800">
                            Makanan
                        </span>
                        <h3 class="font-black text-lg text-slate-900 mt-1">Qtella</h3>
                        <p class="text-xs text-slate-500">Pilih rasa dan atur harga pesanan</p>
                    </div>
                    <button 
                        type="button" 
                        @click="showQtellaModal = false"
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition cursor-pointer"
                    >
                        Batal
                    </button>
                </div>

                <!-- Sub Menu 1: Pilihan Rasa -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                        1. Pilih Rasa
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="flavor in flavors" :key="flavor">
                            <button 
                                type="button" 
                                @click="selectedQtellaFlavor = flavor"
                                :class="selectedQtellaFlavor === flavor 
                                    ? 'bg-indigo-600 text-white font-bold border-indigo-600 ring-2 ring-indigo-200 shadow-xs' 
                                    : 'bg-slate-50 hover:bg-slate-100 text-slate-800 border-slate-200 font-semibold'"
                                class="p-3 rounded-xl border text-xs sm:text-sm text-center transition cursor-pointer"
                            >
                                <span x-text="flavor"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Sub Menu 2: Harga / Porsi (Shortcut & Custom Input) -->
                <div class="space-y-2 pt-1 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            2. Harga Satuan (Rp)
                        </label>
                        <span class="text-[11px] font-bold text-indigo-600" x-text="formatRupiah(qtellaPrice || 0) + ' / bungkus'"></span>
                    </div>

                    <!-- Shortcut Buttons: 5k & 10k -->
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            @click="setQtellaPrice(5000)"
                            :class="qtellaPrice === 5000 
                                ? 'bg-indigo-600 text-white font-bold border-indigo-600 ring-2 ring-indigo-200 shadow-xs' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200 font-semibold'"
                            class="py-2.5 px-3 rounded-xl border text-center transition cursor-pointer"
                        >
                            <span class="block text-xs font-black">Shortcut 5k</span>
                            <span class="block text-[11px] opacity-80 font-medium">Rp5.000</span>
                        </button>

                        <button 
                            type="button" 
                            @click="setQtellaPrice(10000)"
                            :class="qtellaPrice === 10000 
                                ? 'bg-indigo-600 text-white font-bold border-indigo-600 ring-2 ring-indigo-200 shadow-xs' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200 font-semibold'"
                            class="py-2.5 px-3 rounded-xl border text-center transition cursor-pointer"
                        >
                            <span class="block text-xs font-black">Shortcut 10k</span>
                            <span class="block text-[11px] opacity-80 font-medium">Rp10.000</span>
                        </button>
                    </div>

                    <!-- Manual Input Field for Custom Price -->
                    <div class="pt-1">
                        <span class="block text-[11px] text-slate-500 font-medium mb-1">Atau input nominal kustom (Rp):</span>
                        <input 
                            type="number" 
                            step="500" 
                            min="500" 
                            x-model.number="qtellaPrice" 
                            placeholder="5000"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- Sub Menu 3: Jumlah Porsi -->
                <div class="space-y-2 pt-1 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                        3. Jumlah Porsi
                    </label>
                    <div class="flex items-center justify-center gap-3">
                        <button 
                            type="button" 
                            @click="if (qtellaQty > 1) qtellaQty--"
                            class="w-12 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-900 font-black text-lg flex items-center justify-center transition cursor-pointer"
                        >
                            -
                        </button>
                        <div class="w-16 text-center">
                            <span class="font-black text-xl text-slate-900" x-text="qtellaQty"></span>
                            <span class="block text-[10px] text-slate-400 font-medium">bungkus</span>
                        </div>
                        <button 
                            type="button" 
                            @click="qtellaQty++"
                            class="w-12 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-900 font-black text-lg flex items-center justify-center transition cursor-pointer"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Subtotal Preview & Submit -->
                <div class="pt-2 border-t border-slate-100 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">
                            <span x-text="'Qtella ' + selectedQtellaFlavor + ' (' + formatRupiah(qtellaPrice || 0) + ')'"></span>
                        </span>
                        <span class="font-black text-indigo-700 text-sm" x-text="formatRupiah((qtellaPrice || 0) * qtellaQty)"></span>
                    </div>

                    <button 
                        type="button" 
                        @click="addQtellaToCart()"
                        :disabled="!qtellaPrice || qtellaPrice <= 0"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 text-white font-bold rounded-xl text-xs sm:text-sm transition cursor-pointer shadow-sm text-center"
                    >
                        + Tambahkan ke Keranjang
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL SUB-MENU 2: KERUPUK KOIN (RASA & HARGA KUSTOM 2K-10K)     -->
        <!-- ============================================================== -->
        <div 
            x-show="showKoinModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div 
                class="bg-white rounded-3xl max-w-sm w-full p-5 sm:p-6 border border-slate-200 shadow-2xl space-y-4"
                @click.outside="showKoinModal = false"
            >
                <!-- Header -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800">
                            Makanan
                        </span>
                        <h3 class="font-black text-lg text-slate-900 mt-1">Kerupuk Koin</h3>
                        <p class="text-xs text-slate-500">Pilih varian rasa & atur harga kustom</p>
                    </div>
                    <button 
                        type="button" 
                        @click="showKoinModal = false"
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition cursor-pointer"
                    >
                        Batal
                    </button>
                </div>

                <!-- Sub Menu 1: Pilihan Rasa -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                        1. Pilih Rasa
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="flavor in flavors" :key="flavor">
                            <button 
                                type="button" 
                                @click="selectedKoinFlavor = flavor"
                                :class="selectedKoinFlavor === flavor 
                                    ? 'bg-indigo-600 text-white font-bold border-indigo-600 ring-2 ring-indigo-200 shadow-xs' 
                                    : 'bg-slate-50 hover:bg-slate-100 text-slate-800 border-slate-200 font-semibold'"
                                class="p-3 rounded-xl border text-xs sm:text-sm text-center transition cursor-pointer"
                            >
                                <span x-text="flavor"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Sub Menu 2: Harga / Porsi (Shortcut & Custom Input) -->
                <div class="space-y-2 pt-1 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            2. Harga Satuan (Rp)
                        </label>
                        <span class="text-[11px] font-bold text-indigo-600" x-text="formatRupiah(koinPrice || 0) + ' / porsi'"></span>
                    </div>

                    <!-- Shortcut Buttons: 2k, 3k, 5k, 10k -->
                    <div class="grid grid-cols-4 gap-1.5">
                        <button 
                            type="button" 
                            @click="setKoinPrice(2000)"
                            :class="koinPrice === 2000 
                                ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-xs' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200 font-semibold'"
                            class="py-2 rounded-xl border text-center transition cursor-pointer"
                        >
                            <span class="block text-xs font-black">2k</span>
                        </button>

                        <button 
                            type="button" 
                            @click="setKoinPrice(3000)"
                            :class="koinPrice === 3000 
                                ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-xs' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200 font-semibold'"
                            class="py-2 rounded-xl border text-center transition cursor-pointer"
                        >
                            <span class="block text-xs font-black">3k</span>
                        </button>

                        <button 
                            type="button" 
                            @click="setKoinPrice(5000)"
                            :class="koinPrice === 5000 
                                ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-xs' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200 font-semibold'"
                            class="py-2 rounded-xl border text-center transition cursor-pointer"
                        >
                            <span class="block text-xs font-black">5k</span>
                        </button>

                        <button 
                            type="button" 
                            @click="setKoinPrice(10000)"
                            :class="koinPrice === 10000 
                                ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-xs' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200 font-semibold'"
                            class="py-2 rounded-xl border text-center transition cursor-pointer"
                        >
                            <span class="block text-xs font-black">10k</span>
                        </button>
                    </div>

                    <!-- Manual Input Field for Custom Price -->
                    <div class="pt-1">
                        <span class="block text-[11px] text-slate-500 font-medium mb-1">Atau input nominal kustom (Rp):</span>
                        <input 
                            type="number" 
                            step="500" 
                            min="500" 
                            x-model.number="koinPrice" 
                            placeholder="2000"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- Sub Menu 3: Jumlah Porsi -->
                <div class="space-y-2 pt-1 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                        3. Jumlah Porsi
                    </label>
                    <div class="flex items-center justify-center gap-3">
                        <button 
                            type="button" 
                            @click="if (koinQty > 1) koinQty--"
                            class="w-12 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-900 font-black text-lg flex items-center justify-center transition cursor-pointer"
                        >
                            -
                        </button>
                        <div class="w-16 text-center">
                            <span class="font-black text-xl text-slate-900" x-text="koinQty"></span>
                            <span class="block text-[10px] text-slate-400 font-medium">porsi</span>
                        </div>
                        <button 
                            type="button" 
                            @click="koinQty++"
                            class="w-12 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-900 font-black text-lg flex items-center justify-center transition cursor-pointer"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Subtotal Preview & Submit -->
                <div class="pt-2 border-t border-slate-100 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">
                            <span x-text="'Koin ' + selectedKoinFlavor + ' (' + formatRupiah(koinPrice || 0) + ')'"></span>
                        </span>
                        <span class="font-black text-indigo-700 text-sm" x-text="formatRupiah((koinPrice || 0) * koinQty)"></span>
                    </div>

                    <button 
                        type="button" 
                        @click="addKoinToCart()"
                        :disabled="!koinPrice || koinPrice <= 0"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 text-white font-bold rounded-xl text-xs sm:text-sm transition cursor-pointer shadow-sm text-center"
                    >
                        + Tambahkan ke Keranjang
                    </button>
                </div>
            </div>
        </div>

        <!-- MOBILE FLOATING CHECKOUT BAR -->
        <div 
            x-show="totalItemsCount > 0"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="lg:hidden fixed bottom-16 inset-x-3 z-30"
            style="display: none;"
        >
            <div class="bg-slate-900 text-white rounded-2xl p-3.5 shadow-2xl flex items-center justify-between border border-slate-800">
                <div class="pl-1">
                    <span class="text-[11px] text-slate-400 block font-medium" x-text="totalItemsCount + ' item di keranjang'"></span>
                    <div class="text-base font-black text-emerald-400" x-text="formatRupiah(totalAmount)"></div>
                </div>

                <button 
                    type="button" 
                    @click="showMobileCheckout = true"
                    class="py-2.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs transition cursor-pointer shadow-sm"
                >
                    Bayar Sekarang
                </button>
            </div>
        </div>

        <!-- MOBILE CHECKOUT BOTTOM DRAWER (Slide Up) -->
        <div 
            x-show="showMobileCheckout"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="lg:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex flex-col justify-end"
            style="display: none;"
        >
            <div 
                class="bg-white rounded-t-3xl max-h-[85vh] overflow-y-auto p-5 space-y-4 shadow-2xl"
                @click.outside="showMobileCheckout = false"
            >
                <!-- Drawer Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Selesaikan Pembayaran</h3>
                        <span class="text-xs text-slate-500" x-text="totalItemsCount + ' item belanja'"></span>
                    </div>
                    <button @click="showMobileCheckout = false" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                        Tutup
                    </button>
                </div>

                <!-- Items Review -->
                <div class="divide-y divide-slate-100 max-h-48 overflow-y-auto pr-1">
                    <template x-for="item in cartItems" :key="item.key">
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div class="flex-1 min-w-0 pr-2">
                                <div class="font-bold text-slate-900 truncate" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-500" x-text="formatRupiah(item.price) + ' × ' + item.quantity"></div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button 
                                    type="button" 
                                    @click="decrementQuantity(item.key)"
                                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center cursor-pointer"
                                >
                                    -
                                </button>
                                <span class="w-5 text-center font-bold text-xs text-slate-900" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="incrementQuantity(item.key)"
                                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center cursor-pointer"
                                >
                                    +
                                </button>
                                <span class="w-16 text-right font-bold text-slate-900" x-text="formatRupiah(item.price * item.quantity)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Payment Method -->
                <div>
                    <span class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Metode Pembayaran:</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            @click="setPaymentMethod('cash')"
                            :class="paymentMethod === 'cash' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-2.5 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                        >
                            Tunai
                        </button>
                        <button 
                            type="button" 
                            @click="setPaymentMethod('qris')"
                            :class="paymentMethod === 'qris' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-2.5 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                        >
                            QRIS
                        </button>
                        <button 
                            type="button" 
                            @click="setPaymentMethod('transfer')"
                            :class="paymentMethod === 'transfer' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-2.5 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                        >
                            Transfer
                        </button>
                    </div>
                </div>

                <!-- Live QRIS ShopeePay Verification Box (Mobile) -->
                <div x-show="paymentMethod === 'qris'" class="p-3.5 rounded-2xl bg-sky-50/70 border border-sky-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase text-sky-900 tracking-wider block">QRIS Stand GenBI (ShopeePay)</span>
                            <span class="text-[11px] text-slate-500">Cek mutasi uang masuk otomatis</span>
                        </div>
                        <button 
                            type="button" 
                            @click="fetchLiveQris(true)"
                            :disabled="isCheckingQris"
                            class="px-2.5 py-1 text-[11px] font-bold bg-white hover:bg-sky-100 disabled:opacity-50 text-sky-700 border border-sky-300 rounded-lg shadow-xs transition cursor-pointer"
                        >
                            <span x-show="!isCheckingQris">Cek Masuk</span>
                            <span x-show="isCheckingQris">Memeriksa...</span>
                        </button>
                    </div>

                    <!-- Match Alert Status -->
                    <div x-show="totalAmount > 0">
                        <template x-if="qrisMatched">
                            <div class="p-2.5 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold text-xs flex items-center justify-between">
                                <div>
                                    <span class="block text-[10px] uppercase tracking-wider text-emerald-800 font-black">Uang Masuk Terverifikasi</span>
                                    <span x-text="qrisMatchMessage"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-900 font-black text-[10px]">COCOK</span>
                            </div>
                        </template>
                        <template x-if="!qrisMatched">
                            <div class="p-2.5 rounded-xl bg-white/80 border border-sky-200 text-slate-600 text-[11px]">
                                <div class="flex items-center justify-between font-semibold">
                                    <span>Menunggu scan pembeli...</span>
                                    <span class="font-mono text-slate-500" x-text="formatRupiah(totalAmount)"></span>
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-0.5" x-text="qrisMatchMessage || 'Minta pembeli scan QRIS, lalu klik tombol Cek Masuk.'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Recent 3 incoming transactions -->
                    <div class="pt-2 border-t border-sky-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mutasi QRIS Terkini:</span>
                        <template x-if="liveQrisList.length === 0">
                            <div class="text-[11px] text-slate-400 italic">Belum ada transaksi QRIS masuk.</div>
                        </template>
                        <template x-for="item in liveQrisList.slice(0, 3)" :key="item.transaction_id">
                            <div class="flex items-center justify-between text-[11px] bg-white/80 px-2 py-1.5 rounded-lg border border-sky-100">
                                <div>
                                    <strong class="text-slate-800" x-text="item.formatted_amount"></strong>
                                    <span class="text-[10px] text-slate-400 ml-1" x-text="item.time_ago"></span>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="item.status_label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <input 
                        type="text" 
                        x-model="notes" 
                        placeholder="Catatan pesanan (opsional)..."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Grand Total Card -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-slate-600 font-semibold">Total Tagihan:</span>
                    <span class="text-xl font-black text-indigo-700" x-text="formatRupiah(totalAmount)"></span>
                </div>

                <!-- Action Button -->
                <button 
                    type="button" 
                    @click="submitTransaction()"
                    :disabled="isSubmitting || totalItemsCount === 0"
                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-bold rounded-xl text-sm transition cursor-pointer shadow-md"
                >
                    <span x-show="!isSubmitting">Konfirmasi & Simpan Transaksi</span>
                    <span x-show="isSubmitting">Menyimpan Transaksi...</span>
                </button>
            </div>
        </div>

        <!-- Shift Reconciliation Modal -->
        <div 
            x-show="showShiftSummaryModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl max-w-md w-full p-6 border border-slate-200 shadow-2xl" @click.outside="showShiftSummaryModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-black text-base text-slate-900">Rekap Kas Shift</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $activeShift->name }} ({{ $activeShift->shift_number }})</p>
                    </div>
                    <button 
                        type="button" 
                        @click="showShiftSummaryModal = false"
                        class="text-slate-400 hover:text-slate-600 text-sm font-bold p-1 cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

                <div class="py-4 space-y-3">
                    <!-- Cash / Tunai -->
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[11px] font-bold uppercase text-emerald-800 tracking-wider">1. Uang Tunai / Cash (Laci Stand)</span>
                                <div class="text-xl font-black text-emerald-950 mt-1" x-text="formatRupiah(shiftSummary?.cash?.total || 0)"></div>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-200 text-emerald-900" x-text="(shiftSummary?.cash?.count || 0) + ' Transaksi'"></span>
                        </div>
                        <p class="text-xs text-emerald-800 mt-2 font-medium">
                            Cocokkan dengan uang cash fisik yang ada di laci / dompet stand saat ini.
                        </p>
                    </div>

                    <!-- QRIS -->
                    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[11px] font-bold uppercase text-blue-800 tracking-wider">2. QRIS Stand (Digital)</span>
                                <div class="text-xl font-black text-blue-950 mt-1" x-text="formatRupiah(shiftSummary?.qris?.total || 0)"></div>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-200 text-blue-900" x-text="(shiftSummary?.qris?.count || 0) + ' Transaksi'"></span>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-blue-200/60 flex items-center justify-between text-xs text-blue-900">
                            <span>Total Omset QRIS ShopeePay:</span>
                            <strong class="font-bold text-sm" x-text="qrisTotalNetSales"></strong>
                        </div>
                        <p class="text-xs text-blue-800 mt-1 font-medium">
                            Tersinkronisasi langsung dengan Shopee Partner (Stand GenBI).
                        </p>
                    </div>

                    <!-- Transfer -->
                    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[11px] font-bold uppercase text-purple-800 tracking-wider">3. Transfer Bank (TF)</span>
                                <div class="text-xl font-black text-purple-950 mt-1" x-text="formatRupiah(shiftSummary?.transfer?.total || 0)"></div>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-purple-200 text-purple-900" x-text="(shiftSummary?.transfer?.count || 0) + ' Transaksi'"></span>
                        </div>
                        <p class="text-xs text-purple-800 mt-2 font-medium">
                            Cocokkan dengan mutasi rekening / bukti transfer pembeli.
                        </p>
                    </div>

                    <!-- Grand Total Shift -->
                    <div class="p-4 rounded-2xl bg-slate-900 text-white flex justify-between items-center">
                        <div>
                            <span class="text-xs text-slate-400 font-semibold uppercase block">Total Omset Shift:</span>
                            <span class="text-xl font-black text-white" x-text="formatRupiah(shiftSummary?.total_revenue || 0)"></span>
                        </div>
                        <div class="text-right text-xs text-slate-300">
                            <div><strong class="text-white" x-text="shiftSummary?.total_transactions || 0"></strong> transaksi</div>
                            <div><strong class="text-white" x-text="shiftSummary?.total_items || 0"></strong> unit barang</div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button 
                        type="button" 
                        @click="showShiftSummaryModal = false"
                        class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-sm transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Receipt Modal -->
        <div 
            x-show="showReceiptModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl" @click.outside="showReceiptModal = false">
                <div class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold w-fit mx-auto mb-3">
                    Transaksi Sukses
                </div>

                <div class="text-center mb-4">
                    <h3 class="font-bold text-lg text-slate-900">Transaksi Berhasil!</h3>
                    <div class="text-xs text-indigo-700 font-mono font-bold mt-0.5" x-text="completedTx?.invoice_code"></div>
                </div>

                <!-- Receipt details -->
                <div class="space-y-2 text-xs font-mono my-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Waktu:</span>
                        <span x-text="completedTx?.created_at"></span>
                    </div>
                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Metode:</span>
                        <span class="font-bold text-slate-800 uppercase" x-text="completedTx?.payment_method"></span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 space-y-1">
                        <template x-for="it in completedTx?.items" :key="it.name + '_' + it.price">
                            <div class="flex justify-between text-[11px]">
                                <span class="truncate max-w-[170px]" x-text="it.name + ' × ' + it.quantity"></span>
                                <span class="font-bold" x-text="it.subtotal"></span>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2 border-t border-slate-300 flex justify-between font-bold text-sm text-slate-900">
                        <span>TOTAL:</span>
                        <span class="text-emerald-700" x-text="completedTx?.formatted_total"></span>
                    </div>
                </div>

                <div class="space-y-2 mt-5">
                    <button 
                        type="button" 
                        @click="showReceiptModal = false"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition cursor-pointer shadow-sm"
                    >
                        + Transaksi Baru
                    </button>
                    <a 
                        href="{{ route('cashier.history') }}"
                        class="block text-center w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition"
                    >
                        Lihat Riwayat Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.cashier>
