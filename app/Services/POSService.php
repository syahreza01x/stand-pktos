<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionCorrection;
use App\Models\TransactionItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSService
{
    /**
     * Process and store a sales transaction atomically.
     *
     * @param  array{
     *     shift_id?: int,
     *     payment_method: string,
     *     idempotency_key?: string,
     *     notes?: string,
     *     items: array<int, array{product_id: int, quantity: int}>
     * }  $data
     *
     * @throws Exception
     */
    public function processTransaction(User $cashier, array $data): Transaction
    {
        // 1. Double submission check via idempotency key
        if (! empty($data['idempotency_key'])) {
            $existing = Transaction::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // 2. Identify and validate shift
        $shiftQuery = Shift::query()->where('status', 'open');

        if (! empty($data['shift_id'])) {
            $shiftQuery->where('id', $data['shift_id']);
        }

        // If cashier is not admin, ensure they are assigned to this shift
        if (! $cashier->isAdmin()) {
            $shiftQuery->whereHas('cashiers', function ($q) use ($cashier): void {
                $q->where('users.id', $cashier->id);
            });
        }

        /** @var Shift|null $shift */
        $shift = $shiftQuery->first();

        if (! $shift) {
            throw new Exception('Shift yang ditugaskan saat ini tidak dalam status Open atau Anda tidak memiliki akses.');
        }

        if (empty($data['items']) || ! is_array($data['items'])) {
            throw new Exception('Daftar barang belanjaan tidak boleh kosong.');
        }

        // 3. Collect product IDs and fetch from database
        $productIds = array_column($data['items'], 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        if ($products->isEmpty()) {
            throw new Exception('Barang yang dipilih tidak valid.');
        }

        return DB::transaction(function () use ($cashier, $shift, $data, $products) {
            $totalItems = 0;
            $subtotalAmount = 0.00;
            $preparedItems = [];

            foreach ($data['items'] as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $product = $products->get($productId);
                if (! $product) {
                    throw new Exception("Produk ID {$productId} tidak ditemukan dalam database.");
                }

                if (! empty($item['custom_price']) && is_numeric($item['custom_price']) && (float) $item['custom_price'] > 0) {
                    $unitPrice = (float) $item['custom_price'];
                } else {
                    $unitPrice = (float) $product->price;
                }

                $itemSubtotal = $unitPrice * $quantity;

                $totalItems += $quantity;
                $subtotalAmount += $itemSubtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'unit_price_snapshot' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($totalItems <= 0) {
                throw new Exception('Jumlah kuantitas barang belanjaan minimal 1 unit.');
            }

            // Generate clean invoice code: TRX-YYMMDD-S{1/2}-XXXX
            $datePrefix = date('ymd');
            $shiftNum = str_contains($shift->shift_number, '2') ? '2' : '1';
            $randomCode = strtoupper(Str::random(4));
            $invoiceCode = "TRX-{$datePrefix}-S{$shiftNum}-{$randomCode}";

            $transaction = Transaction::create([
                'invoice_code' => $invoiceCode,
                'event_day_id' => $shift->event_day_id,
                'shift_id' => $shift->id,
                'cashier_id' => $cashier->id,
                'total_items' => $totalItems,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => 0.00,
                'total_amount' => $subtotalAmount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status' => 'completed',
                'idempotency_key' => $data['idempotency_key'] ?? Str::uuid()->toString(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Save items with snapshots
            foreach ($preparedItems as $prepItem) {
                $prepItem['transaction_id'] = $transaction->id;
                TransactionItem::create($prepItem);
            }

            // Log activity
            AuditLogService::log(
                module: 'transaction',
                action: 'create',
                description: "Transaksi {$invoiceCode} sebesar Rp".number_format($subtotalAmount, 0, ',', '.')." diproses oleh {$cashier->name}.",
                payload: [
                    'invoice_code' => $invoiceCode,
                    'total_items' => $totalItems,
                    'total_amount' => $subtotalAmount,
                    'shift_id' => $shift->id,
                ],
                userId: $cashier->id
            );

            return $transaction->load(['items.product', 'cashier', 'shift', 'eventDay']);
        });
    }

    /**
     * Void (cancel) a transaction with audit trail.
     */
    public function voidTransaction(Transaction $transaction, User $admin, string $reason): Transaction
    {
        if ($transaction->isVoided()) {
            throw new Exception('Transaksi ini sudah dibatalkan sebelumnya.');
        }

        return DB::transaction(function () use ($transaction, $admin, $reason) {
            $oldValues = $transaction->toArray();

            $transaction->update([
                'status' => 'voided',
            ]);

            TransactionCorrection::create([
                'transaction_id' => $transaction->id,
                'corrected_by' => $admin->id,
                'action' => 'void',
                'reason' => $reason,
                'old_values' => $oldValues,
                'new_values' => $transaction->fresh()->toArray(),
            ]);

            AuditLogService::log(
                module: 'transaction',
                action: 'void',
                description: "Transaksi {$transaction->invoice_code} dibatalkan oleh Admin {$admin->name}. Alasan: {$reason}",
                payload: ['reason' => $reason, 'invoice' => $transaction->invoice_code],
                userId: $admin->id
            );

            return $transaction;
        });
    }
}
