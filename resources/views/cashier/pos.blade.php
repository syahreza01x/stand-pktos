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
                    alert('Terjadi kesalahan: ' + error.message);
                } finally {
                    this.isSubmitting = false;
                }
            }
        }"
        class="space-y-4"
    >
        <!-- Active Shift Header -->
        <div class="bg-white rounded-lg p-3 border border-zinc-200 flex items-center justify-between text-xs">
            <div>
                <span class="text-zinc-500 font-medium">Shift Aktif:</span>
                <strong class="text-zinc-900 ml-1">Hari {{ $activeShift->eventDay->day_name }} — {{ $activeShift->shift_number }} ({{ $activeShift->name }})</strong>
            </div>
            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                OPEN
            </span>
        </div>

        <!-- POS Workspace -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
            
            <!-- Products Catalog (Left/Top) -->
            <div class="md:col-span-7 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-500">Pilih Barang:</span>
                    <span class="text-xs text-zinc-400">{{ $products->count() }} Produk</span>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 gap-2.5">
                    <template x-for="product in products" :key="product.id">
                        <div 
                            @click="addToCart(product)"
                            class="bg-white p-3 rounded-lg border border-zinc-200 hover:border-zinc-400 transition cursor-pointer select-none flex flex-col justify-between relative"
                            :class="cart[product.id] ? 'ring-1 ring-zinc-900' : ''"
                        >
                            <!-- Quantity count badge -->
                            <div 
                                x-show="cart[product.id] && cart[product.id].quantity > 0"
                                class="absolute top-2 right-2 bg-zinc-900 text-white font-bold text-[10px] px-1.5 py-0.5 rounded"
                                x-text="cart[product.id] ? cart[product.id].quantity : ''"
                            ></div>

                            <div>
                                <span class="text-[10px] uppercase text-zinc-400 font-semibold block" x-text="product.category"></span>
                                <h3 class="font-bold text-xs sm:text-sm text-zinc-900 mt-0.5 leading-snug" x-text="product.name"></h3>
                            </div>

                            <div class="mt-3 pt-2 border-t border-zinc-100 flex items-center justify-between">
                                <span class="font-bold text-xs text-zinc-900" x-text="formatRupiah(product.price)"></span>
                                <span class="text-xs font-semibold text-zinc-500 bg-zinc-100 px-2 py-0.5 rounded">+ Tambah</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Cart & Checkout Area (Right/Bottom) -->
            <div class="md:col-span-5 bg-white rounded-lg border border-zinc-200 p-4 sticky top-16 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200">
                    <span class="font-bold text-xs uppercase tracking-wider text-zinc-700">Keranjang Belanja</span>
                    <button 
                        type="button" 
                        @click="clearCart()" 
                        x-show="totalItemsCount > 0"
                        class="text-xs text-rose-600 hover:underline"
                    >
                        Batal
                    </button>
                </div>

                <!-- Empty State -->
                <div x-show="totalItemsCount === 0" class="py-8 text-center text-zinc-400 text-xs">
                    Pilih produk untuk menambah transaksi.
                </div>

                <!-- Cart Items List -->
                <div x-show="totalItemsCount > 0" class="divide-y divide-zinc-100 max-h-56 overflow-y-auto">
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="py-2 flex items-center justify-between gap-2 text-xs">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-zinc-900 truncate" x-text="item.name"></div>
                                <div class="text-[11px] text-zinc-400" x-text="formatRupiah(item.price) + ' × ' + item.quantity"></div>
                            </div>

                            <!-- Stepper -->
                            <div class="flex items-center gap-1">
                                <button 
                                    type="button" 
                                    @click="decrementQuantity(item.id)"
                                    class="w-6 h-6 rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 font-bold flex items-center justify-center text-xs"
                                >
                                    −
                                </button>
                                <span class="w-6 text-center font-bold text-xs text-zinc-900" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="addToCart({ id: item.id, name: item.name, price: item.price })"
                                    class="w-6 h-6 rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 font-bold flex items-center justify-center text-xs"
                                >
                                    +
                                </button>
                            </div>

                            <div class="w-16 text-right font-bold text-zinc-900" x-text="formatRupiah(item.price * item.quantity)"></div>
                        </div>
                    </template>
                </div>

                <!-- Checkout Details -->
                <div x-show="totalItemsCount > 0" class="pt-3 border-t border-zinc-200 space-y-3">
                    <div>
                        <span class="block text-[11px] font-semibold text-zinc-600 mb-1">Metode Bayar:</span>
                        <div class="grid grid-cols-3 gap-1.5 text-xs">
                            <button 
                                type="button" 
                                @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-zinc-900 text-white font-bold' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                                class="py-1.5 rounded text-center transition"
                            >
                                Tunai
                            </button>
                            <button 
                                type="button" 
                                @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'bg-zinc-900 text-white font-bold' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                                class="py-1.5 rounded text-center transition"
                            >
                                QRIS
                            </button>
                            <button 
                                type="button" 
                                @click="paymentMethod = 'transfer'"
                                :class="paymentMethod === 'transfer' ? 'bg-zinc-900 text-white font-bold' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                                class="py-1.5 rounded text-center transition"
                            >
                                Transfer
                            </button>
                        </div>
                    </div>

                    <div>
                        <input 
                            type="text" 
                            x-model="notes" 
                            placeholder="Catatan transaksi (opsional)..."
                            class="w-full px-2.5 py-1.5 text-xs rounded border border-zinc-200 focus:outline-none focus:ring-1 focus:ring-zinc-900"
                        >
                    </div>

                    <!-- Total & Pay -->
                    <div class="p-3 rounded bg-zinc-50 border border-zinc-200 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-zinc-500 block">Total Tagihan</span>
                            <span class="text-xs font-medium text-zinc-700" x-text="totalItemsCount + ' item'"></span>
                        </div>
                        <div class="text-lg font-black text-zinc-900" x-text="formatRupiah(totalAmount)"></div>
                    </div>

                    <button 
                        type="button" 
                        @click="submitTransaction()"
                        :disabled="isSubmitting || totalItemsCount === 0"
                        class="w-full py-2.5 bg-zinc-900 hover:bg-black disabled:bg-zinc-300 text-white font-bold rounded text-xs transition cursor-pointer"
                    >
                        <span x-show="!isSubmitting">Simpan Transaksi</span>
                        <span x-show="isSubmitting">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Minimalist Receipt Modal -->
        <div 
            x-show="showReceiptModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            style="display: none;"
        >
            <div class="bg-white rounded-xl max-w-sm w-full p-5 border border-zinc-200" @click.outside="showReceiptModal = false">
                <div class="border-b border-zinc-200 pb-2 mb-3">
                    <div class="font-bold text-sm text-zinc-900">Transaksi Berhasil Disimpan</div>
                    <div class="text-[11px] text-zinc-500 font-mono" x-text="completedTx?.invoice_code"></div>
                </div>

                <!-- Receipt details -->
                <div class="space-y-1.5 text-xs font-mono my-3 p-3 bg-zinc-50 rounded border border-zinc-200">
                    <div class="flex justify-between text-zinc-500 text-[11px]">
                        <span>Waktu:</span>
                        <span x-text="completedTx?.created_at"></span>
                    </div>
                    <div class="flex justify-between text-zinc-500 text-[11px]">
                        <span>Metode:</span>
                        <span class="font-bold text-zinc-800 uppercase" x-text="completedTx?.payment_method"></span>
                    </div>

                    <div class="pt-2 border-t border-zinc-200 space-y-1">
                        <template x-for="it in completedTx?.items" :key="it.name">
                            <div class="flex justify-between text-[11px]">
                                <span class="truncate max-w-[170px]" x-text="it.name + ' × ' + it.quantity"></span>
                                <span class="font-bold" x-text="it.subtotal"></span>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2 border-t border-zinc-200 flex justify-between font-bold text-xs text-zinc-900">
                        <span>TOTAL:</span>
                        <span x-text="completedTx?.formatted_total"></span>
                    </div>
                </div>

                <div class="space-y-2 mt-4">
                    <button 
                        type="button" 
                        @click="showReceiptModal = false"
                        class="w-full py-2 bg-zinc-900 hover:bg-black text-white font-bold rounded text-xs transition"
                    >
                        + Transaksi Baru
                    </button>
                    <a 
                        href="{{ route('cashier.history') }}"
                        class="block text-center w-full py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded text-xs transition"
                    >
                        Lihat Riwayat Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.cashier>
