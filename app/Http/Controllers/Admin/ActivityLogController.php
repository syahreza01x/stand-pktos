<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $module = $request->query('module');
        $userId = $request->query('user_id');
        $search = $request->query('search');

        $logs = ActivityLog::with('user')
            ->when($module, function ($q, $module): void {
                $q->where('module', $module);
            })
            ->when($userId, function ($q, $userId): void {
                $q->where('user_id', $userId);
            })
            ->when($search, function ($q, $search): void {
                $q->where('description', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $modules = ActivityLog::distinct()->pluck('module');
        $users = User::orderBy('name')->get();

        return view('admin.activity_logs.index', compact('logs', 'modules', 'users', 'module', 'userId', 'search'));
    }
}
