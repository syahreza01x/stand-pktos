<x-layouts.cashier>
    <x-slot:title>Point of Sale (Input Penjualan)</x-slot:title>

    <div 
        x-data="{
            products: {{ Js::from($products) }},
            activeShiftId: {{ $activeShift->id }},
            cart: {},
            paymentMethod: 'cash',
            notes: '',
            isSubmitting: false,
            showReceiptModal: false,
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

            setQuantity(productId, qty) {
                const num = parseInt(qty);
                if (num > 0) {
                    if (!this.cart[productId]) {
                        const prod = this.products.find(p => p.id === productId);
                        if (prod) {
                            this.cart[productId] = {
                                id: prod.id,
                                name: prod.name,
                                price: parseFloat(prod.price),
                                quantity: num
                            };
                        }
                    } else {
                        this.cart[productId].quantity = num;
                    }
                } else {
                    delete this.cart[productId];
                }
            },

            removeFromCart(productId) {
                delete this.cart[productId];
            },

            clearCart() {
                this.cart = {};
                this.notes = '';
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
                        this.showReceiptModal = true;
                        this.clearCart();
                    } else {
                        alert(result.message || 'Gagal memproses transaksi.');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan jaringan atau server: ' + error.message);
                } finally {
                    this.isSubmitting = false;
                }
            }
        }"
        class="space-y-4"
    >
        <!-- Active Shift Header Banner -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center font-bold text-lg">
                    ⚡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Shift Aktif</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                            OPEN
                        </span>
                    </div>
                    <div class="font-bold text-slate-900 text-sm sm:text-base">
                        Hari {{ $activeShift->eventDay->day_name }} — {{ $activeShift->shift_number }} ({{ $activeShift->name }})
                    </div>
                </div>
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Petugas Bertugas: <span class="font-bold text-slate-800">{{ auth()->user()->name }}</span>
            </div>
        </div>

        <!-- Main Workspace: Products Grid + Sticky / Responsive Cart -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Products Catalog (Left/Top) -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-slate-900 text-base sm:text-lg">Katalog Produk Stand</h2>
                    <span class="text-xs font-medium text-slate-500">{{ $products->count() }} Produk Tersedia</span>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <template x-for="product in products" :key="product.id">
                        <div 
                            @click="addToCart(product)"
                            class="group relative bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 hover:shadow-md transition duration-150 cursor-pointer flex flex-col justify-between active:scale-[0.98] select-none"
                        >
                            <!-- Quantity Badge when in cart -->
                            <div 
                                x-show="cart[product.id] && cart[product.id].quantity > 0"
                                class="absolute top-2.5 right-2.5 bg-indigo-600 text-white font-bold text-xs px-2 py-0.5 rounded-full shadow-xs"
                                x-text="cart[product.id] ? cart[product.id].quantity : ''"
                            ></div>

                            <div>
                                <span class="inline-block text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-600 mb-2" x-text="product.category"></span>
                                <h3 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-indigo-600 transition" x-text="product.name"></h3>
                            </div>

                            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div class="font-extrabold text-slate-900 text-sm sm:text-base" x-text="formatRupiah(product.price)"></div>
                                <button 
                                    type="button" 
                                    class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-slate-600 text-sm font-bold transition"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Cart & Checkout Area (Right/Bottom) -->
            <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 sticky top-20">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h2 class="font-bold text-slate-900 text-base">Keranjang Transaksi</h2>
                        <span class="text-xs text-slate-500 font-medium" x-text="totalItemsCount + ' unit barang'"></span>
                    </div>
                    <button 
                        type="button" 
                        @click="clearCart()" 
                        x-show="totalItemsCount > 0"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition"
                    >
                        Kosongkan
                    </button>
                </div>

                <!-- Empty State -->
                <div x-show="totalItemsCount === 0" class="py-12 text-center text-slate-400 space-y-2">
                    <div class="text-3xl">🛒</div>
                    <p class="text-sm font-medium">Keranjang masih kosong</p>
                    <p class="text-xs">Klik produk di sebelah kiri untuk menambah ke transaksi.</p>
                </div>

                <!-- Cart Items List -->
                <div x-show="totalItemsCount > 0" class="divide-y divide-slate-100 max-h-72 overflow-y-auto my-3 pr-1">
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-800 text-xs sm:text-sm truncate" x-text="item.name"></h4>
                                <div class="text-[11px] text-slate-500">
                                    <span x-text="formatRupiah(item.price)"></span> × <span class="font-bold" x-text="item.quantity"></span>
                                </div>
                            </div>

                            <!-- Stepper Controls -->
                            <div class="flex items-center gap-1 shrink-0">
                                <button 
                                    type="button" 
                                    @click="decrementQuantity(item.id)"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center transition"
                                >
                                    −
                                </button>
                                <span class="w-7 text-center font-bold text-xs text-slate-800" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="addToCart({ id: item.id, name: item.name, price: item.price })"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center transition"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Subtotal -->
                            <div class="w-20 text-right font-bold text-xs sm:text-sm text-slate-900 shrink-0" x-text="formatRupiah(item.price * item.quantity)"></div>
                        </div>
                    </template>
                </div>

                <!-- Checkout Parameters (Payment & Notes) -->
                <div x-show="totalItemsCount > 0" class="pt-3 border-t border-slate-200 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button" 
                                @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 text-xs rounded-xl border text-center transition font-medium"
                            >
                                💵 Tunai
                            </button>
                            <button 
                                type="button" 
                                @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 text-xs rounded-xl border text-center transition font-medium"
                            >
                                📱 QRIS
                            </button>
                            <button 
                                type="button" 
                                @click="paymentMethod = 'transfer'"
                                :class="paymentMethod === 'transfer' ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2 text-xs rounded-xl border text-center transition font-medium"
                            >
                                🏦 Transfer
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input 
                            type="text" 
                            x-model="notes" 
                            placeholder="misal: es sedikit, take-away"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <!-- Grand Total Summary Card -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Tagihan</div>
                            <div class="text-xs text-slate-600" x-text="totalItemsCount + ' barang' "></div>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-indigo-700" x-text="formatRupiah(totalAmount)"></div>
                    </div>

                    <!-- Action Submit Button -->
                    <button 
                        type="button" 
                        @click="submitTransaction()"
                        :disabled="isSubmitting || totalItemsCount === 0"
                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-sm text-sm sm:text-base flex items-center justify-center gap-2 transition cursor-pointer"
                    >
                        <span x-show="!isSubmitting">Simpan Transaksi (Bayar)</span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Receipt / Struk Modal -->
        <div 
            x-show="showReceiptModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 text-center" @click.outside="showReceiptModal = false">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl mb-4 font-bold">
                    ✓
                </div>

                <h3 class="text-xl font-bold text-slate-900">Transaksi Sukses!</h3>
                <p class="text-xs text-slate-500 mt-0.5">Data berhasil disimpan ke sistem.</p>

                <!-- Receipt Box -->
                <div class="my-4 p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300 text-left text-xs space-y-2 font-mono">
                    <div class="flex justify-between font-bold border-b border-slate-200 pb-2">
                        <span>Invoice:</span>
                        <span class="text-indigo-600" x-text="completedTx?.invoice_code"></span>
                    </div>

                    <div class="flex justify-between text-slate-500">
                        <span>Waktu:</span>
                        <span x-text="completedTx?.created_at"></span>
                    </div>

                    <div class="flex justify-between text-slate-500">
                        <span>Metode:</span>
                        <span class="font-bold text-slate-800" x-text="completedTx?.payment_method"></span>
                    </div>

                    <!-- Items breakdown in modal -->
                    <div class="pt-2 border-t border-slate-200 space-y-1">
                        <template x-for="it in completedTx?.items" :key="it.name">
                            <div class="flex justify-between text-[11px]">
                                <span class="truncate max-w-[170px]" x-text="it.name + ' × ' + it.quantity"></span>
                                <span class="font-bold" x-text="it.subtotal"></span>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2 border-t-2 border-slate-300 flex justify-between font-bold text-sm">
                        <span>TOTAL BAYAR:</span>
                        <span class="text-emerald-700" x-text="completedTx?.formatted_total"></span>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="space-y-2">
                    <button 
                        type="button" 
                        @click="showReceiptModal = false"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition"
                    >
                        + Transaksi Baru
                    </button>
                    <a 
                        href="{{ route('cashier.history') }}"
                        class="block w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition"
                    >
                        Lihat Riwayat Penjualan
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.cashier>
