<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionCorrection;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        // Total transactions recorded
        $totalTransactions = Transaction::count();
        $totalItems = TransactionItem::count();

        // Generate dynamic 6-digit verification code stored in session
        $resetCode = (string) random_int(100000, 999999);
        session(['reset_confirmation_code' => $resetCode]);

        return view('admin.settings.index', compact('systemInfo', 'totalTransactions', 'totalItems', 'resetCode'));
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

    /**
     * Safely reset/delete all test transaction data while preserving users, products, and shifts.
     */
    public function resetTransactions(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation_code' => ['required', 'string'],
            'admin_password' => ['required', 'string'],
        ], [
            'confirmation_code.required' => 'Kode angka konfirmasi wajib dimasukkan.',
            'admin_password.required' => 'Password akun admin wajib diisi untuk verifikasi keamanan.',
        ]);

        $expectedCode = (string) session('reset_confirmation_code');

        if (empty($expectedCode) || trim($request->input('confirmation_code')) !== $expectedCode) {
            return back()->with('error', 'Kode angka konfirmasi tidak cocok. Penghapusan transaksi dibatalkan demi keamanan.');
        }

        /** @var User $user */
        $user = auth()->user();

        if (! Hash::check($request->input('admin_password'), $user->password)) {
            return back()->with('error', 'Password akun admin salah. Penghapusan data transaksi dibatalkan.');
        }

        $count = Transaction::count();

        DB::transaction(function () use ($count, $user): void {
            TransactionCorrection::query()->delete();
            TransactionItem::query()->delete();
            Transaction::query()->delete();

            AuditLogService::log(
                module: 'system',
                action: 'reset_transactions',
                description: "Mereset total {$count} data transaksi uji coba stand oleh {$user->name}",
                userId: $user->id
            );
        });

        session()->forget('reset_confirmation_code');

        return back()->with('success', "Seluruh data transaksi uji coba ({$count} transaksi) berhasil dihapus bersih! Sistem siap digunakan untuk acara resmi.");
    }
}
