<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $search = $request->query('search');
        $status = $request->query('status');
        $paymentMethod = $request->query('payment_method');

        // Strictly isolated to this cashier's transactions
        $baseQuery = Transaction::query()
            ->where('cashier_id', $user->id)
            ->where('status', '!=', 'voided');

        $cashTotal = (float) (clone $baseQuery)->where('payment_method', 'cash')->sum('total_amount');
        $cashCount = (clone $baseQuery)->where('payment_method', 'cash')->count();

        $qrisTotal = (float) (clone $baseQuery)->where('payment_method', 'qris')->sum('total_amount');
        $qrisCount = (clone $baseQuery)->where('payment_method', 'qris')->count();

        $transferTotal = (float) (clone $baseQuery)->where('payment_method', 'transfer')->sum('total_amount');
        $transferCount = (clone $baseQuery)->where('payment_method', 'transfer')->count();

        $totalPersonalSales = (float) (clone $baseQuery)->sum('total_amount');
        $totalPersonalUnits = (int) (clone $baseQuery)->sum('total_items');
        $totalPersonalTx = (clone $baseQuery)->count();

        $transactions = Transaction::with(['items.product', 'shift.eventDay'])
            ->where('cashier_id', $user->id)
            ->when($search, function ($q, $search): void {
                $q->where('invoice_code', 'like', "%{$search}%");
            })
            ->when($status, function ($q, $status): void {
                $q->where('status', $status);
            })
            ->when($paymentMethod && in_array($paymentMethod, ['cash', 'qris', 'transfer']), function ($q) use ($paymentMethod): void {
                $q->where('payment_method', $paymentMethod);
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('cashier.history', compact(
            'transactions',
            'search',
            'status',
            'paymentMethod',
            'totalPersonalSales',
            'totalPersonalUnits',
            'totalPersonalTx',
            'cashTotal',
            'cashCount',
            'qrisTotal',
            'qrisCount',
            'transferTotal',
            'transferCount'
        ));
    }
}
