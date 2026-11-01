<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Shift;
use App\Models\Transaction;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    public function index(): View
    {
        $today = Carbon::today()->toDateString();
        $activeDay = EventDay::where('event_date', $today)->first()
            ?? EventDay::where('status', 'active')->first()
            ?? EventDay::first();

        // 1. Overall KPIs
        $totalRevenue = (float) Transaction::where('status', '!=', 'voided')->sum('total_amount');
        $totalTransactions = Transaction::where('status', '!=', 'voided')->count();
        $totalItemsSold = (int) Transaction::where('status', '!=', 'voided')->sum('total_items');

        // 2. Today's KPIs
        $todayQuery = Transaction::where('status', '!=', 'voided');
        if ($activeDay) {
            $todayQuery->where('event_day_id', $activeDay->id);
        } else {
            $todayQuery->whereDate('created_at', $today);
        }
        $todayRevenue = (float) (clone $todayQuery)->sum('total_amount');
        $todayTransactions = (clone $todayQuery)->count();
        $todayUnits = (int) (clone $todayQuery)->sum('total_items');

        // 3. Active Shift & Assigned Cashiers
        $activeShift = Shift::with(['cashiers', 'eventDay'])
            ->where('status', 'open')
            ->first();

        // 4. Latest Transactions
        $recentTransactions = Transaction::with(['cashier', 'shift', 'eventDay', 'items'])
            ->latest('id')
            ->take(8)
            ->get();

        // 5. 5-Day Recap Summary for visual charts & cards
        $recap = $this->reportingService->getFiveDayOverallRecap();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalTransactions',
            'totalItemsSold',
            'todayRevenue',
            'todayTransactions',
            'todayUnits',
            'activeShift',
            'activeDay',
            'recentTransactions',
            'recap'
        ));
    }
}
