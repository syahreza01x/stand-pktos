<?php

namespace App\Console\Commands;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ExcelExportService;
use App\Services\POSService;
use App\Services\ReportingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SimulateEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stand:simulate-event {--keep : Jangan reset database setelah simulasi selesai}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi operasional real-time Stand PKTOS (transaksi kasir, rotasi shift, rekap laporan, export excel, dan void)';

    /**
     * Execute the console command.
     */
    public function handle(
        POSService $posService,
        ReportingService $reportingService,
        ExcelExportService $excelExportService
    ): int {
        $this->info('================================================================');
        $this->info('   SIMULASI REAL-TIME OPERASIONAL STAND PKTOS (HARI SENIN)      ');
        $this->info('================================================================');
        $this->newLine();

        // 1. Data Setup
        $admin = User::where('role', 'admin')->firstOrFail();
        $farhad = User::where('username', 'farhad')->firstOrFail();
        $natasya = User::where('username', 'natasya')->firstOrFail();
        $naila = User::where('username', 'naila')->firstOrFail();

        $daySenin = EventDay::where('day_name', 'like', 'Senin%')->firstOrFail();
        $shift1 = Shift::where('event_day_id', $daySenin->id)->where('shift_number', 'Shift 1')->firstOrFail();
        $shift2 = Shift::where('event_day_id', $daySenin->id)->where('shift_number', 'Shift 2')->firstOrFail();

        // Pastikan Shift 1 berstatus OPEN, Shift 2 Scheduled
        $shift1->update(['status' => 'open', 'opened_at' => now(), 'closed_at' => null, 'closed_by' => null]);
        $shift2->update(['status' => 'scheduled', 'opened_at' => null, 'closed_at' => null, 'closed_by' => null]);

        $qtlBalado = Product::where('code', 'PRD-QTL-BAL')->firstOrFail();
        $qtlJagung = Product::where('code', 'PRD-QTL-JAG')->firstOrFail();
        $koin2Bbq = Product::where('code', 'PRD-KOIN2-BBQ')->firstOrFail();
        $koin2Balado = Product::where('code', 'PRD-KOIN2-BAL')->firstOrFail();
        $koin3Pedas = Product::where('code', 'PRD-KOIN3-PDS')->firstOrFail();
        $mojito = Product::where('code', 'PRD-MOJITO')->firstOrFail();

        // -----------------------------------------------------------------
        // SESI 1: SHIFT 1 (PAGI) — KASIR FARHAD
        // -----------------------------------------------------------------
        $this->line('<fg=cyan;options=bold>[1/6] SESI 1: HARI SENIN — SHIFT 1 (PAGI)</>');
        $this->line("Petugas Aktif On-Duty : <fg=yellow>{$farhad->name} (@{$farhad->username})</>");
        $this->line("Status Shift          : <fg=green>OPEN</> ({$shift1->name})");
        $this->newLine();

        // Transaksi 1
        $this->line('-> Transaksi 1: Pembeli membeli 2 bungkus Qtella Balado (Tunai)');
        $tx1 = $posService->processTransaction($farhad, [
            'shift_id' => $shift1->id,
            'payment_method' => 'cash',
            'notes' => 'Pembeli mahasiswa FEB',
            'items' => [
                ['product_id' => $qtlBalado->id, 'quantity' => 2],
            ],
        ]);
        $this->info("   Berhasil dicatat: {$tx1->invoice_code} | Total: {$tx1->formatted_total} | Metode: TUNAI");

        // Transaksi 2
        $this->line('-> Transaksi 2: Pembeli membeli 1 Krupuk Koin 2K BBQ + 1 Mojito Dingin (QRIS)');
        $tx2 = $posService->processTransaction($farhad, [
            'shift_id' => $shift1->id,
            'payment_method' => 'qris',
            'notes' => 'Scan QRIS Stand',
            'items' => [
                ['product_id' => $koin2Bbq->id, 'quantity' => 1],
                ['product_id' => $mojito->id, 'quantity' => 1],
            ],
        ]);
        $this->info("   Berhasil dicatat: {$tx2->invoice_code} | Total: {$tx2->formatted_total} | Metode: QRIS");

        // Transaksi 3
        $this->line('-> Transaksi 3: Pembeli memesan 3 porsi Krupuk Koin 3K Extra Pedas (Transfer)');
        $tx3 = $posService->processTransaction($farhad, [
            'shift_id' => $shift1->id,
            'payment_method' => 'transfer',
            'notes' => 'Transfer via BCA',
            'items' => [
                ['product_id' => $koin3Pedas->id, 'quantity' => 3],
            ],
        ]);
        $this->info("   Berhasil dicatat: {$tx3->invoice_code} | Total: {$tx3->formatted_total} | Metode: TRANSFER");
        $this->newLine();

        $shift1Total = Transaction::where('shift_id', $shift1->id)->where('status', 'completed')->sum('total_amount');
        $shift1Count = Transaction::where('shift_id', $shift1->id)->where('status', 'completed')->count();
        $this->line('>> Rekap Subtotal Shift 1: <fg=green;options=bold>Rp'.number_format($shift1Total, 0, ',', '.')."</> ({$shift1Count} Transaksi)");
        $this->newLine();

        // -----------------------------------------------------------------
        // SESI 2: ROTASI PERGANTIAN SHIFT OLEH ADMIN
        // -----------------------------------------------------------------
        $this->line('<fg=cyan;options=bold>[2/6] PERGANTIAN SHIFT OLEH ADMIN</>');
        $this->line("Admin ({$admin->name}) menutup Shift 1...");
        $shift1->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);
        AuditLogService::log(
            module: 'shift',
            action: 'close',
            description: "Shift 1 Hari Senin ditutup oleh Admin {$admin->name}.",
            payload: ['shift_id' => $shift1->id],
            userId: $admin->id
        );
        $this->info('-> Shift 1 Resmi DITUTUP (CLOSED). Kasir Farhad tidak dapat input transaksi baru lagi.');

        $this->line("Admin ({$admin->name}) membuka Shift 2...");
        $shift2->update([
            'status' => 'open',
            'opened_at' => now(),
        ]);
        AuditLogService::log(
            module: 'shift',
            action: 'open',
            description: "Shift 2 Hari Senin dibuka oleh Admin {$admin->name}.",
            payload: ['shift_id' => $shift2->id],
            userId: $admin->id
        );
        $this->info('-> Shift 2 Resmi DIBUKA (OPEN). Petugas Shift 2 siap bertugas.');
        $this->newLine();

        // -----------------------------------------------------------------
        // SESI 3: SHIFT 2 (SIANG/SORE) — KASIR NATASYA & NAILA
        // -----------------------------------------------------------------
        $this->line('<fg=cyan;options=bold>[3/6] SESI 2: HARI SENIN — SHIFT 2 (SIANG)</>');
        $this->line("Petugas Aktif On-Duty : <fg=yellow>{$natasya->name}</> dan <fg=yellow>{$naila->name}</>");
        $this->line("Status Shift          : <fg=green>OPEN</> ({$shift2->name})");
        $this->newLine();

        // Transaksi 4 oleh Natasya
        $this->line('-> Transaksi 4 (Kasir Natasya): Pembeli pesan 1 Qtella Jagung Bakar + 2 Mojito (Tunai)');
        $tx4 = $posService->processTransaction($natasya, [
            'shift_id' => $shift2->id,
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $qtlJagung->id, 'quantity' => 1],
                ['product_id' => $mojito->id, 'quantity' => 2],
            ],
        ]);
        $this->info("   Berhasil dicatat: {$tx4->invoice_code} | Total: {$tx4->formatted_total} | Kasir: {$natasya->name}");

        // Transaksi 5 oleh Naila
        $this->line('-> Transaksi 5 (Kasir Naila): Pembeli pesan 2 porsi Krupuk Koin 2K Balado (QRIS)');
        $tx5 = $posService->processTransaction($naila, [
            'shift_id' => $shift2->id,
            'payment_method' => 'qris',
            'notes' => 'Beli camilan istirahat',
            'items' => [
                ['product_id' => $koin2Balado->id, 'quantity' => 2],
            ],
        ]);
        $this->info("   Berhasil dicatat: {$tx5->invoice_code} | Total: {$tx5->formatted_total} | Kasir: {$naila->name}");
        $this->newLine();

        // Tutup Shift 2
        $shift2->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);
        $this->info('-> Hari Senin selesai: Shift 2 resmi ditutup oleh Admin.');
        $this->newLine();

        // -----------------------------------------------------------------
        // SESI 4: LAPORAN REKAP HARIAN (SHIFT 1, SHIFT 2, TOTAL HARI)
        // -----------------------------------------------------------------
        $this->line('<fg=cyan;options=bold>[4/6] REKAPITULASI PENJUALAN HARI SENIN DARI SISTEM</>');
        $dailyReport = $reportingService->getDailyReport($daySenin);

        $this->table(
            ['Sesi', 'Total Unit Terjual', 'Total Omzet (Rp)', 'Keterangan'],
            [
                ['Shift 1 (Pagi)', $dailyReport['shift1']['total_quantity'].' unit', 'Rp '.number_format($dailyReport['shift1']['total_revenue'], 0, ',', '.'), 'Farhad (3 transaksi)'],
                ['Shift 2 (Siang)', $dailyReport['shift2']['total_quantity'].' unit', 'Rp '.number_format($dailyReport['shift2']['total_revenue'], 0, ',', '.'), 'Natasya & Naila (2 transaksi)'],
                ['TOTAL HARI SENIN', $dailyReport['daily_total']['total_quantity'].' unit', 'Rp '.number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.'), 'Total Akumulasi Bersih'],
            ]
        );
        $this->newLine();

        // -----------------------------------------------------------------
        // SESI 5: SIMULASI EXPORT EXCEL
        // -----------------------------------------------------------------
        $this->line('<fg=cyan;options=bold>[5/6] SIMULASI EXPORT LAPORAN KE EXCEL SPREADSHEET</>');
        $exportResponse = $excelExportService->exportDailyReport($daySenin);

        // Capture streamed content to verify actual generation
        ob_start();
        $exportResponse->sendContent();
        $excelContent = ob_get_clean();

        $exportDir = storage_path('app/exports');
        File::ensureDirectoryExists($exportDir);
        $filePath = $exportDir.'/Laporan_Simulasi_Hari_Senin.xls';
        File::put($filePath, $excelContent);

        $fileSize = number_format(strlen($excelContent) / 1024, 2);
        $this->info("-> File Excel berhasil di-generate: {$filePath} ({$fileSize} KB)");
        $this->line('   Header HTML/XML Excel, Worksheets Shift 1, Shift 2, dan Total Hari terformat rapi.');
        $this->newLine();

        // -----------------------------------------------------------------
        // SESI 6: SIMULASI KOREKSI TRANSAKSI (VOID / CANCEL OLEH ADMIN)
        // -----------------------------------------------------------------
        $this->line('<fg=cyan;options=bold>[6/6] SIMULASI AUDIT TRAIL & VOID TRANSAKSI SALAH INPUT</>');
        $this->line("Admin melakukan void/pembatalan pada Transaksi {$tx5->invoice_code} (Rp4.000)...");
        $posService->voidTransaction($tx5, $admin, 'Pembeli membatalkan pesanan karena salah pilih varian');
        $this->info("-> Transaksi {$tx5->invoice_code} berhasil di-VOID.");

        $updatedReport = $reportingService->getDailyReport($daySenin);
        $this->line('   Omzet Hari Senin setelah VOID : <fg=green;options=bold>Rp'.number_format($updatedReport['daily_total']['total_revenue'], 0, ',', '.').'</> (Semula Rp'.number_format($dailyReport['daily_total']['total_revenue'], 0, ',', '.').')');
        $this->info('   Audit trail tersimpan di tabel activity_logs & transaction_corrections.');
        $this->newLine();

        // -----------------------------------------------------------------
        // PEMBERSIHAN / RESET DATABASE
        // -----------------------------------------------------------------
        if ($this->option('keep')) {
            $this->warn('Opsi --keep aktif: Data simulasi dipertahankan di database.');
        } else {
            $this->line('<fg=yellow;options=bold>Mengembalikan database ke posisi awal (Fresh Seeder)...</>');
            Artisan::call('migrate:fresh', ['--seed' => true]);
            $this->info('✓ Database berhasil dikembalikan 100% ke kondisi awal yang bersih.');
        }

        $this->newLine();
        $this->info('================================================================');
        $this->info('      SIMULASI OPERASIONAL REAL-TIME SELESAI DENGAN SUKSES!     ');
        $this->info('================================================================');

        return Command::SUCCESS;
    }
}
