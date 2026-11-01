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

        $transactions = Transaction::with(['items.product', 'shift.eventDay'])
            ->where('cashier_id', $user->id)
            ->when($search, function ($q, $search): void {
                $q->where('invoice_code', 'like', "%{$search}%");
            })
            ->when($status, function ($q, $status): void {
                $q->where('status', $status);
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $totalPersonalSales = (float) Transaction::where('cashier_id', $user->id)
            ->where('status', '!=', 'voided')
            ->sum('total_amount');

        $totalPersonalUnits = (int) Transaction::where('cashier_id', $user->id)
            ->where('status', '!=', 'voided')
            ->sum('total_items');

        $totalPersonalTx = Transaction::where('cashier_id', $user->id)
            ->where('status', '!=', 'voided')
            ->count();

        return view('cashier.history', compact(
            'transactions',
            'search',
            'status',
            'totalPersonalSales',
            'totalPersonalUnits',
            'totalPersonalTx'
        ));
    }
}
