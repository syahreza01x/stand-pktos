<?php

namespace App\Services;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Get product-by-product breakdown for a specific shift.
     *
     * @return array{
     *     shift: Shift,
     *     items: array<int, array{product_id: int, product_name: string, unit_price: float, quantity: int, subtotal: float}>,
     *     total_quantity: int,
     *     total_revenue: float,
     *     transaction_count: int
     * }
     */
    public function getShiftReport(Shift $shift): array
    {
        $products = Product::orderBy('sort_order')->orderBy('name')->get();

        // Get sales aggregated by product_name_snapshot or product_id for valid transactions
        $sales = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.shift_id', $shift->id)
            ->where('transactions.status', '!=', 'voided')
            ->select(
                'transaction_items.product_id',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_subtotal')
            )
            ->groupBy('transaction_items.product_id')
            ->get()
            ->keyBy('product_id');

        $items = [];
        $totalQuantity = 0;
        $totalRevenue = 0.00;

        foreach ($products as $product) {
            $sale = $sales->get($product->id);
            $qty = $sale ? (int) $sale->total_qty : 0;
            $subtotal = $sale ? (float) $sale->total_subtotal : 0.00;
            $price = ($qty > 0) ? round($subtotal / $qty, 2) : (float) $product->price;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $product->category,
                'unit_price' => $price,
                'quantity' => $qty,
                'subtotal' => $subtotal,
            ];

            $totalQuantity += $qty;
            $totalRevenue += $subtotal;
        }

        $txCount = Transaction::where('shift_id', $shift->id)->where('status', '!=', 'voided')->count();

        $paymentRows = Transaction::query()
            ->where('shift_id', $shift->id)
            ->where('status', '!=', 'voided')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(id) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $shiftPayments = [
            'cash' => [
                'total' => (float) ($paymentRows->get('cash')?->total ?? 0),
                'count' => (int) ($paymentRows->get('cash')?->count ?? 0),
            ],
            'qris' => [
                'total' => (float) ($paymentRows->get('qris')?->total ?? 0),
                'count' => (int) ($paymentRows->get('qris')?->count ?? 0),
            ],
            'transfer' => [
                'total' => (float) ($paymentRows->get('transfer')?->total ?? 0),
                'count' => (int) ($paymentRows->get('transfer')?->count ?? 0),
            ],
        ];

        return [
            'shift' => $shift,
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'total_revenue' => $totalRevenue,
            'transaction_count' => $txCount,
            'payments' => $shiftPayments,
        ];
    }

    /**
     * Get Daily Report covering Shift 1, Shift 2, and Day Total.
     *
     * @return array{
     *     day: EventDay,
     *     shift1: array,
     *     shift2: array,
     *     daily_total: array{items: array, total_quantity: int, total_revenue: float, transaction_count: int}
     * }
     */
    public function getDailyReport(EventDay $day): array
    {
        $shifts = $day->shifts()->orderBy('shift_number')->get();

        $shift1Model = $shifts->firstWhere('shift_number', 'Shift 1') ?? $shifts->first();
        $shift2Model = $shifts->firstWhere('shift_number', 'Shift 2') ?? ($shifts->count() > 1 ? $shifts->skip(1)->first() : null);

        $shift1Report = $shift1Model ? $this->getShiftReport($shift1Model) : null;
        $shift2Report = $shift2Model ? $this->getShiftReport($shift2Model) : null;

        // Calculate combined daily totals per product
        $products = Product::orderBy('sort_order')->orderBy('name')->get();
        $dailyItems = [];
        $dailyTotalQty = 0;
        $dailyTotalRevenue = 0.00;

        foreach ($products as $idx => $prod) {
            $s1Qty = $shift1Report['items'][$idx]['quantity'] ?? 0;
            $s1Subtotal = $shift1Report['items'][$idx]['subtotal'] ?? 0;

            $s2Qty = $shift2Report['items'][$idx]['quantity'] ?? 0;
            $s2Subtotal = $shift2Report['items'][$idx]['subtotal'] ?? 0;

            $combQty = $s1Qty + $s2Qty;
            $combSubtotal = $s1Subtotal + $s2Subtotal;

            $dailyItems[] = [
                'product_id' => $prod->id,
                'product_name' => $prod->name,
                'category' => $prod->category,
                'unit_price' => (float) $prod->price,
                'quantity_s1' => $s1Qty,
                'quantity_s2' => $s2Qty,
                'total_quantity' => $combQty,
                'total_subtotal' => $combSubtotal,
            ];

            $dailyTotalQty += $combQty;
            $dailyTotalRevenue += $combSubtotal;
        }

        $dailyTxCount = Transaction::where('event_day_id', $day->id)->where('status', '!=', 'voided')->count();

        $dailyPaymentRows = Transaction::query()
            ->where('event_day_id', $day->id)
            ->where('status', '!=', 'voided')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(id) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $dailyPayments = [
            'cash' => [
                'total' => (float) ($dailyPaymentRows->get('cash')?->total ?? 0),
                'count' => (int) ($dailyPaymentRows->get('cash')?->count ?? 0),
            ],
            'qris' => [
                'total' => (float) ($dailyPaymentRows->get('qris')?->total ?? 0),
                'count' => (int) ($dailyPaymentRows->get('qris')?->count ?? 0),
            ],
            'transfer' => [
                'total' => (float) ($dailyPaymentRows->get('transfer')?->total ?? 0),
                'count' => (int) ($dailyPaymentRows->get('transfer')?->count ?? 0),
            ],
        ];

        return [
            'day' => $day,
            'shift1' => $shift1Report,
            'shift2' => $shift2Report,
            'daily_total' => [
                'items' => $dailyItems,
                'total_quantity' => $dailyTotalQty,
                'total_revenue' => $dailyTotalRevenue,
                'transaction_count' => $dailyTxCount,
                'payments' => $dailyPayments,
            ],
        ];
    }

    /**
     * Get 5-Day Overall Recap across the entire event.
     *
     * @return array{
     *     total_revenue_5_days: float,
     *     total_units_5_days: int,
     *     total_transactions_5_days: int,
     *     daily_summary: array<int, array{day: EventDay, revenue: float, units: int, tx_count: int}>,
     *     products_ranking: array<int, array{product_id: int, product_name: string, total_units: int, total_revenue: float}>,
     *     shifts_comparison: array{shift1_revenue: float, shift1_units: int, shift2_revenue: float, shift2_units: int},
     *     cashiers_recap: array<int, array{cashier: User, total_revenue: float, total_units: int, tx_count: int}>
     * }
     */
    public function getFiveDayOverallRecap(): array
    {
        $days = EventDay::orderBy('event_date')->get();

        $dailySummary = [];
        $grandRevenue = 0.00;
        $grandUnits = 0;
        $grandTxCount = 0;

        foreach ($days as $day) {
            $report = $this->getDailyReport($day);
            $revenue = $report['daily_total']['total_revenue'];
            $units = $report['daily_total']['total_quantity'];
            $txCount = $report['daily_total']['transaction_count'];

            $dailySummary[] = [
                'day' => $day,
                'revenue' => $revenue,
                'units' => $units,
                'tx_count' => $txCount,
                'payments' => $report['daily_total']['payments'],
            ];

            $grandRevenue += $revenue;
            $grandUnits += $units;
            $grandTxCount += $txCount;
        }

        // 5-day payment method breakdown
        $fiveDayPaymentRows = Transaction::query()
            ->where('status', '!=', 'voided')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(id) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $fiveDayPayments = [
            'cash' => [
                'total' => (float) ($fiveDayPaymentRows->get('cash')?->total ?? 0),
                'count' => (int) ($fiveDayPaymentRows->get('cash')?->count ?? 0),
            ],
            'qris' => [
                'total' => (float) ($fiveDayPaymentRows->get('qris')?->total ?? 0),
                'count' => (int) ($fiveDayPaymentRows->get('qris')?->count ?? 0),
            ],
            'transfer' => [
                'total' => (float) ($fiveDayPaymentRows->get('transfer')?->total ?? 0),
                'count' => (int) ($fiveDayPaymentRows->get('transfer')?->count ?? 0),
            ],
        ];

        // Product ranking (best-selling)
        $productStats = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', '!=', 'voided')
            ->select(
                'transaction_items.product_id',
                'transaction_items.product_name_snapshot',
                DB::raw('SUM(transaction_items.quantity) as total_units'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('transaction_items.product_id', 'transaction_items.product_name_snapshot')
            ->orderByDesc('total_units')
            ->get();

        // Shift 1 vs Shift 2 comparison
        $shiftStats = Transaction::query()
            ->join('shifts', 'transactions.shift_id', '=', 'shifts.id')
            ->where('transactions.status', '!=', 'voided')
            ->select(
                'shifts.shift_number',
                DB::raw('SUM(transactions.total_amount) as revenue'),
                DB::raw('SUM(transactions.total_items) as units'),
                DB::raw('COUNT(transactions.id) as tx_count')
            )
            ->groupBy('shifts.shift_number')
            ->get()
            ->keyBy('shift_number');

        $s1Stats = $shiftStats->get('Shift 1');
        $s2Stats = $shiftStats->get('Shift 2');

        // Cashier performance recap
        $cashierStats = Transaction::query()
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->where('transactions.status', '!=', 'voided')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'users.username',
                DB::raw('SUM(transactions.total_amount) as total_revenue'),
                DB::raw('SUM(transactions.total_items) as total_units'),
                DB::raw('COUNT(transactions.id) as tx_count')
            )
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderByDesc('total_revenue')
            ->get();

        return [
            'total_revenue_5_days' => $grandRevenue,
            'total_units_5_days' => $grandUnits,
            'total_transactions_5_days' => $grandTxCount,
            'daily_summary' => $dailySummary,
            'products_ranking' => $productStats,
            'shifts_comparison' => [
                'shift1_revenue' => (float) ($s1Stats->revenue ?? 0),
                'shift1_units' => (int) ($s1Stats->units ?? 0),
                'shift1_tx_count' => (int) ($s1Stats->tx_count ?? 0),
                'shift2_revenue' => (float) ($s2Stats->revenue ?? 0),
                'shift2_units' => (int) ($s2Stats->units ?? 0),
                'shift2_tx_count' => (int) ($s2Stats->tx_count ?? 0),
            ],
            'cashiers_recap' => $cashierStats,
            'payments_5_days' => $fiveDayPayments,
        ];
    }
}
