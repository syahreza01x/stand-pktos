<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;
use App\Services\POSService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionHistoryController extends Controller
{
    public function __construct(
        protected POSService $posService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $dayId = $request->query('event_day_id');
        $shiftId = $request->query('shift_id');
        $cashierId = $request->query('cashier_id');
        $productId = $request->query('product_id');
        $status = $request->query('status');

        $transactions = Transaction::with(['cashier', 'shift', 'eventDay', 'items.product', 'corrections.correctedByUser'])
            ->when($search, function ($q, $search): void {
                $q->where('invoice_code', 'like', "%{$search}%");
            })
            ->when($dayId, function ($q, $dayId): void {
                $q->where('event_day_id', $dayId);
            })
            ->when($shiftId, function ($q, $shiftId): void {
                $q->where('shift_id', $shiftId);
            })
            ->when($cashierId, function ($q, $cashierId): void {
                $q->where('cashier_id', $cashierId);
            })
            ->when($status, function ($q, $status): void {
                $q->where('status', $status);
            })
            ->when($productId, function ($q, $productId): void {
                $q->whereHas('items', function ($itemQ) use ($productId): void {
                    $itemQ->where('product_id', $productId);
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $eventDays = EventDay::orderBy('event_date')->get();
        $shifts = Shift::orderBy('event_day_id')->orderBy('shift_number')->get();
        $cashiers = User::where('role', 'cashier')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('admin.transactions.index', compact(
            'transactions',
            'eventDays',
            'shifts',
            'cashiers',
            'products',
            'search',
            'dayId',
            'shiftId',
            'cashierId',
            'productId',
            'status'
        ));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['cashier', 'shift', 'eventDay', 'items.product', 'corrections.correctedByUser']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function void(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan transaksi wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        try {
            $this->posService->voidTransaction($transaction, auth()->user(), $validated['reason']);

            return back()->with('success', "Transaksi {$transaction->invoice_code} berhasil dibatalkan dengan catatan audit trail.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
