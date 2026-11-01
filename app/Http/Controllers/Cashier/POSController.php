<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shift;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class POSController extends Controller
{
    public function __construct(
        protected POSService $posService
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        // Admin can access POS anytime (e.g. assisting cashiers or testing)
        $shiftQuery = Shift::with(['eventDay', 'cashiers'])->where('status', 'open');

        if (! $user->isAdmin()) {
            $shiftQuery->whereHas('cashiers', function ($q) use ($user): void {
                $q->where('users.id', $user->id);
            });
        }

        $activeShift = $shiftQuery->first();

        if (! $activeShift) {
            return redirect()->route('cashier.dashboard')->with('error', 'Tidak ada shift yang berstatus OPEN yang ditugaskan kepada Anda saat ini. Hubungi Admin untuk membuka shift.');
        }

        $products = Product::active()->get();

        return view('cashier.pos', compact('activeShift', 'products', 'user'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Keranjang belanja tidak boleh kosong.',
            'items.min' => 'Pilih minimal satu produk untuk transaksi.',
        ]);

        try {
            $transaction = $this->posService->processTransaction(auth()->user(), $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil diproses!',
                    'data' => [
                        'invoice_code' => $transaction->invoice_code,
                        'total_amount' => $transaction->total_amount,
                        'formatted_total' => $transaction->formatted_total,
                        'total_items' => $transaction->total_items,
                        'payment_method' => strtoupper($transaction->payment_method),
                        'created_at' => $transaction->created_at->format('d/m/Y H:i:s'),
                        'items' => $transaction->items->map(fn ($it) => [
                            'name' => $it->product_name_snapshot,
                            'price' => $it->formatted_unit_price,
                            'quantity' => $it->quantity,
                            'subtotal' => $it->formatted_subtotal,
                        ]),
                    ],
                ]);
            }

            return redirect()->route('cashier.pos')->with('success', "Transaksi {$transaction->invoice_code} berhasil diproses!");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
