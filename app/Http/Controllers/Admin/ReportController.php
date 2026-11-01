<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Services\ExcelExportService;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService,
        protected ExcelExportService $excelExportService
    ) {}

    public function daily(Request $request): View
    {
        $dayId = $request->query('event_day_id');

        $activeDay = $dayId
            ? EventDay::findOrFail($dayId)
            : (EventDay::where('status', 'active')->first() ?? EventDay::first());

        $dailyReport = $activeDay ? $this->reportingService->getDailyReport($activeDay) : null;
        $eventDays = EventDay::orderBy('event_date')->get();

        return view('admin.reports.daily', compact('dailyReport', 'activeDay', 'eventDays'));
    }

    public function fiveDays(): View
    {
        $recap = $this->reportingService->getFiveDayOverallRecap();

        return view('admin.reports.five_days', compact('recap'));
    }

    public function exportDaily(EventDay $eventDay): StreamedResponse
    {
        return $this->excelExportService->exportDailyReport($eventDay);
    }

    public function exportFiveDays(): StreamedResponse
    {
        return $this->excelExportService->exportFiveDayRecap();
    }
}
