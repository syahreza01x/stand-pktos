<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\View\View;

class CashierDashboardController extends Controller
{
    public function index(): View
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

        // 2. Upcoming / assigned shifts for this cashier
        $myShifts = $user->shifts()
            ->with('eventDay')
            ->orderBy('event_day_id')
            ->orderBy('shift_number')
            ->get();

        // 3. Personal sales today
        $personalTodayQuery = Transaction::where('cashier_id', $user->id)
            ->where('status', '!=', 'voided')
            ->whereDate('created_at', $today);

        $personalTodayRevenue = (float) (clone $personalTodayQuery)->sum('total_amount');
        $personalTodayTx = (clone $personalTodayQuery)->count();
        $personalTodayUnits = (int) (clone $personalTodayQuery)->sum('total_items');

        // 4. Shift total revenue (if shift is open)
        $shiftTotalRevenue = 0.00;
        $shiftTotalTx = 0;
        if ($activeShift) {
            $shiftQuery = Transaction::where('shift_id', $activeShift->id)
                ->where('status', '!=', 'voided');

            $shiftTotalRevenue = (float) (clone $shiftQuery)->sum('total_amount');
            $shiftTotalTx = (clone $shiftQuery)->count();
        }

        return view('cashier.dashboard', compact(
            'user',
            'activeShift',
            'myShifts',
            'personalTodayRevenue',
            'personalTodayTx',
            'personalTodayUnits',
            'shiftTotalRevenue',
            'shiftTotalTx'
        ));
    }
}
