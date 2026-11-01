<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventDayController extends Controller
{
    public function index(): View
    {
        $eventDays = EventDay::with(['shifts.cashiers'])
            ->withCount('transactions')
            ->orderBy('event_date')
            ->get();

        return view('admin.event_days.index', compact('eventDays'));
    }

    public function update(Request $request, EventDay $eventDay): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:upcoming,active,completed'],
            'notes' => ['nullable', 'string', 'max:500'],
            'event_date' => ['required', 'date'],
        ]);

        $eventDay->update($validated);

        AuditLogService::log(
            module: 'event_day',
            action: 'update',
            description: "Memperbarui Hari {$eventDay->day_name} (Status: {$eventDay->status})",
            payload: ['day_id' => $eventDay->id, 'status' => $eventDay->status]
        );

        return redirect()->route('admin.event-days.index')->with('success', "Hari {$eventDay->day_name} berhasil diperbarui.");
    }
}
