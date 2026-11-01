<x-layouts.cashier>
    <x-slot:title>Input Penjualan (POS)</x-slot:title>

    <div 
        x-data="{
            products: {{ Js::from($products) }},
            activeShiftId: {{ $activeShift->id }},
            cart: {},
            paymentMethod: 'cash',
            notes: '',
            isSubmitting: false,
            showReceiptModal: false,
            showMobileCheckout: false,
            completedTx: null,
            idempotencyKey: '',

            init() {
                this.generateIdempotencyKey();
            },

            generateIdempotencyKey() {
                this.idempotencyKey = 'pos-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);
            },

            addToCart(product) {
                if (!this.cart[product.id]) {
                    this.cart[product.id] = {
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        quantity: 1
                    };
                } else {
                    this.cart[product.id].quantity += 1;
                }
            },

            decrementQuantity(productId) {
                if (this.cart[productId]) {
                    this.cart[productId].quantity -= 1;
                    if (this.cart[productId].quantity <= 0) {
                        delete this.cart[productId];
                    }
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
        <!-- Shift Aktif Info Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Shift Berjalan:</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">
                    Hari {{ $activeShift->eventDay->day_name }} — {{ $activeShift->shift_number }}
                </div>
                <div class="text-xs text-slate-500">{{ $activeShift->name }}</div>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                Shift Open
            </span>
        </div>

        <!-- Section Header -->
        <div class="flex items-center justify-between px-1">
            <h2 class="font-bold text-sm sm:text-base text-slate-900">Pilih Barang:</h2>
            <span class="text-xs text-slate-500 font-medium">{{ $products->count() }} Produk Stand</span>
        </div>

        <!-- Products Grid & Desktop Cart Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Products Grid (Left on Desktop, Full on Mobile) -->
            <div class="lg:col-span-7 xl:col-span-8">
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <template x-for="product in products" :key="product.id">
                        <div 
                            class="bg-white p-3.5 sm:p-4 rounded-2xl border transition duration-150 flex flex-col justify-between select-none relative shadow-xs"
                            :class="cart[product.id] && cart[product.id].quantity > 0 ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/10' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <!-- Card Header: Category & Name -->
                            <div>
                                <span 
                                    class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-md mb-2"
                                    :class="product.category === 'Makanan' ? 'bg-amber-100 text-amber-800' : (product.category === 'Minuman' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700')"
                                    x-text="product.category"
                                ></span>

                                <h3 class="font-bold text-xs sm:text-sm text-slate-900 leading-snug line-clamp-2" x-text="product.name"></h3>
                            </div>

                            <!-- Card Footer: Price & Add/Stepper -->
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <div class="font-black text-sm sm:text-base text-slate-900 mb-2.5" x-text="formatRupiah(product.price)"></div>

                                <!-- Dynamic Action: + Tambah or Interactive Stepper -->
                                <template x-if="!cart[product.id] || cart[product.id].quantity === 0">
                                    <button 
                                        type="button" 
                                        @click="addToCart(product)"
                                        class="w-full py-2 px-3 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 hover:border-indigo-600 font-bold text-xs transition duration-150 cursor-pointer text-center"
                                    >
                                        + Tambah
                                    </button>
                                </template>

                                <template x-if="cart[product.id] && cart[product.id].quantity > 0">
                                    <div class="flex items-center justify-between gap-1 bg-indigo-600 text-white rounded-xl p-1">
                                        <button 
                                            type="button" 
                                            @click="decrementQuantity(product.id)"
                                            class="w-7 h-7 rounded-lg bg-indigo-700 hover:bg-indigo-800 active:scale-95 text-white font-bold text-sm flex items-center justify-center transition cursor-pointer"
                                        >
                                            −
                                        </button>
                                        <span class="font-extrabold text-xs px-2 text-white" x-text="cart[product.id].quantity"></span>
                                        <button 
                                            type="button" 
                                            @click="addToCart(product)"
                                            class="w-7 h-7 rounded-lg bg-indigo-700 hover:bg-indigo-800 active:scale-95 text-white font-bold text-sm flex items-center justify-center transition cursor-pointer"
                                        >
                                            +
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Desktop Sticky Cart (Visible only on lg: screens) -->
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
                    Pilih produk di sebelah kiri untuk menambah ke transaksi.
                </div>

                <!-- Cart Items List -->
                <div x-show="totalItemsCount > 0" class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-1">
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="py-2.5 flex items-center justify-between gap-2 text-xs">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-900 truncate" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-500" x-text="formatRupiah(item.price) + ' × ' + item.quantity"></div>
                            </div>

                            <div class="flex items-center gap-1">
                                <button 
                                    type="button" 
                                    @click="decrementQuantity(item.id)"
                                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center cursor-pointer"
                                >
                                    −
                                </button>
                                <span class="w-6 text-center font-bold text-xs text-slate-900" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="addToCart({ id: item.id, name: item.name, price: item.price })"
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
                                @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                            >
                                Tunai
                            </button>
                            <button 
                                type="button" 
                                @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                            >
                                QRIS
                            </button>
                            <button 
                                type="button" 
                                @click="paymentMethod = 'transfer'"
                                :class="paymentMethod === 'transfer' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                            >
                                Transfer
                            </button>
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

        <!-- MOBILE FLOATING CHECKOUT BAR (Solusi UX Terbaik untuk Layar HP) -->
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
                    class="py-2.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shadow-sm"
                >
                    <span>Bayar Sekarang</span>
                    <span>→</span>
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
                    <button @click="showMobileCheckout = false" class="p-1.5 text-slate-400 hover:text-slate-700 text-sm font-bold">
                        ✕
                    </button>
                </div>

                <!-- Items Review -->
                <div class="divide-y divide-slate-100 max-h-48 overflow-y-auto pr-1">
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="py-2 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-500" x-text="formatRupiah(item.price) + ' × ' + item.quantity"></div>
                            </div>
                            <div class="font-bold text-slate-900" x-text="formatRupiah(item.price * item.quantity)"></div>
                        </div>
                    </template>
                </div>

                <!-- Payment Method -->
                <div>
                    <span class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Metode Pembayaran:</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-2.5 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                        >
                            Tunai
                        </button>
                        <button 
                            type="button" 
                            @click="paymentMethod = 'qris'"
                            :class="paymentMethod === 'qris' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-2.5 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                        >
                            QRIS
                        </button>
                        <button 
                            type="button" 
                            @click="paymentMethod = 'transfer'"
                            :class="paymentMethod === 'transfer' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-2.5 rounded-xl border text-xs font-medium text-center transition cursor-pointer"
                        >
                            Transfer
                        </button>
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

        <!-- Receipt Modal -->
        <div 
            x-show="showReceiptModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl" @click.outside="showReceiptModal = false">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-xl font-bold mb-3">
                    ✓
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
                        <template x-for="it in completedTx?.items" :key="it.name">
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
