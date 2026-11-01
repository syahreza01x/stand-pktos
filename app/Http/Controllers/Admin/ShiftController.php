<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Shift;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $eventDays = EventDay::with(['shifts.cashiers', 'shifts.openedByUser', 'shifts.closedByUser'])
            ->orderBy('event_date')
            ->get();

        $allCashiers = User::where('role', 'cashier')->where('is_active', true)->orderBy('name')->get();

        return view('admin.shifts.index', compact('eventDays', 'allCashiers'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'cashier_ids' => ['nullable', 'array'],
            'cashier_ids.*' => ['exists:users,id'],
        ]);

        $shift->update([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
        ]);

        if (isset($validated['cashier_ids'])) {
            $shift->cashiers()->sync($validated['cashier_ids']);
        } else {
            $shift->cashiers()->detach();
        }

        AuditLogService::log(
            module: 'shift',
            action: 'update',
            description: "Memperbarui jadwal {$shift->shift_number} - {$shift->name} ({$shift->eventDay->day_name})",
            payload: ['shift_id' => $shift->id, 'cashier_ids' => $validated['cashier_ids'] ?? []]
        );

        return redirect()->route('admin.shifts.index')->with('success', "Jadwal {$shift->name} berhasil diperbarui.");
    }

    public function openShift(Shift $shift): RedirectResponse
    {
        if ($shift->isOpen()) {
            return back()->with('info', 'Shift ini sudah dalam status Open.');
        }

        $shift->update([
            'status' => 'open',
            'opened_at' => Carbon::now(),
            'opened_by' => auth()->id(),
        ]);

        AuditLogService::log(
            module: 'shift',
            action: 'open',
            description: "Shift {$shift->shift_number} ({$shift->eventDay->day_name}) dibuka oleh ".auth()->user()->name,
            payload: ['shift_id' => $shift->id]
        );

        return back()->with('success', "Shift {$shift->shift_number} ({$shift->name}) berhasil dibuka! Petugas yang ditugaskan kini dapat memproses transaksi.");
    }

    public function closeShift(Shift $shift): RedirectResponse
    {
        if ($shift->isClosed()) {
            return back()->with('info', 'Shift ini sudah dalam status Closed.');
        }

        $shift->update([
            'status' => 'closed',
            'closed_at' => Carbon::now(),
            'closed_by' => auth()->id(),
        ]);

        AuditLogService::log(
            module: 'shift',
            action: 'close',
            description: "Shift {$shift->shift_number} ({$shift->eventDay->day_name}) ditutup oleh ".auth()->user()->name,
            payload: ['shift_id' => $shift->id]
        );

        return back()->with('success', "Shift {$shift->shift_number} ({$shift->name}) resmi ditutup.");
    }
}
