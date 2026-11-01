<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $dbVersion = 'Unknown';
        try {
            $pdo = DB::connection()->getPdo();
            $dbVersion = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable $e) {
            // ignore
        }

        $systemInfo = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled (True)' : 'Disabled (False)',
            'app_url' => config('app.url'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'database_name' => config('database.connections.mysql.database'),
            'database_version' => $dbVersion,
            'session_driver' => config('session.driver'),
            'timezone' => config('app.timezone'),
        ];

        return view('admin.settings.index', compact('systemInfo'));
    }

    public function clearCache(Request $request): RedirectResponse
    {
        Artisan::call('optimize:clear');

        AuditLogService::log(
            module: 'system',
            action: 'clear_cache',
            description: 'Membersihkan cache aplikasi via Pengaturan Sistem oleh '.auth()->user()->name
        );

        return back()->with('success', 'Cache aplikasi berhasil dibersihkan.');
    }
}
