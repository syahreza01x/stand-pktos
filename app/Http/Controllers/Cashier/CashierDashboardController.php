<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Transaction;
use App\Services\POSService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashierDashboardController extends Controller
{
    public function __construct(
        protected POSService $posService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        // 1. Check if user has an assigned shift that is currently open
        $activeShift = Shift::with(['eventDay', 'cashiers'])
            ->where('status', 'open')
            ->whereHas('cashiers', function ($q) use ($user): void {
                $q->where('users.id', $user->id);
            })
            ->first();

        // 2. Assigned shifts for this cashier (strictly isolated to this user)
        $myShifts = $user->shifts()
            ->with('eventDay')
            ->orderBy('event_day_id')
            ->orderBy('shift_number')
            ->get();

        // 3. Determine viewed shift for cash reconciliation (strictly constrained to cashier's assigned shifts)
        $selectedShiftId = $request->query('shift_id');
        $viewedShift = null;

        if ($selectedShiftId) {
            $viewedShift = $myShifts->firstWhere('id', (int) $selectedShiftId);
        }

        if (! $viewedShift) {
            $viewedShift = $activeShift ?? $myShifts->first();
        }

        // 4. Breakdown per-shift: Cash, QRIS, Transfer, and grand total
        $shiftSummary = $viewedShift ? $this->posService->getShiftPaymentSummary($viewedShift) : null;

        // 5. Personal sales today
        $personalTodayQuery = Transaction::where('cashier_id', $user->id)
            ->where('status', '!=', 'voided')
            ->whereDate('created_at', $today);

        $personalTodayRevenue = (float) (clone $personalTodayQuery)->sum('total_amount');
        $personalTodayTx = (clone $personalTodayQuery)->count();
        $personalTodayUnits = (int) (clone $personalTodayQuery)->sum('total_items');

        return view('cashier.dashboard', compact(
            'user',
            'activeShift',
            'viewedShift',
            'myShifts',
            'shiftSummary',
            'personalTodayRevenue',
            'personalTodayTx',
            'personalTodayUnits'
        ));
    }
}
