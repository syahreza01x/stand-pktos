<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealTimeEventSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $kasirPagi;

    protected User $kasirSiang;

    protected EventDay $daySenin;

    protected Shift $shift1;

    protected Shift $shift2;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed realistic database structure
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->kasirPagi = User::where('username', 'seninpagi')->firstOrFail();
        $this->kasirSiang = User::where('username', 'seninsiang')->firstOrFail();

        $this->daySenin = EventDay::where('day_name', 'like', 'Senin%')->firstOrFail();
        $this->shift1 = Shift::where('event_day_id', $this->daySenin->id)->where('shift_number', 'Shift 1')->firstOrFail();
        $this->shift2 = Shift::where('event_day_id', $this->daySenin->id)->where('shift_number', 'Shift 2')->firstOrFail();
    }

    public function test_full_real_world_event_lifecycle_simulation(): void
    {
        // -------------------------------------------------------------
        // SESI 1: HARI SENIN — SHIFT 1 (PAGI)
        // -------------------------------------------------------------
        $this->shift1->update(['status' => 'open', 'opened_at' => now(), 'closed_at' => null, 'closed_by' => null]);
        $this->shift2->update(['status' => 'scheduled', 'opened_at' => null, 'closed_at' => null, 'closed_by' => null]);

        $qtlBalado = Product::where('code', 'PRD-QTL-BAL')->firstOrFail();
        $qtlPedas = Product::where('code', 'PRD-QTL-PDS')->firstOrFail();
        $koinBbq = Product::where('code', 'PRD-KOIN-BBQ')->firstOrFail();
        $mojito = Product::where('code', 'PRD-MOJITO')->firstOrFail();

        // 1. Kasir seninpagi: 2 Qtella Balado (Rp5.000 shortcut) = Rp10.000, Tunai
        $resp1 = $this->actingAs($this->kasirPagi)->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift1->id,
            'payment_method' => 'cash',
            'notes' => 'Pembeli mahasiswa FEB',
            'items' => [
                ['product_id' => $qtlBalado->id, 'custom_price' => 5000, 'quantity' => 2],
            ],
        ]);
        $resp1->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(10000, $resp1->json('data.total_amount'));

        // 2. Kasir seninpagi: 1 Koin BBQ (kustom 3k) + 1 Mojito (Rp5.000) = Rp8.000, QRIS
        $resp2 = $this->actingAs($this->kasirPagi)->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift1->id,
            'payment_method' => 'qris',
            'notes' => 'Scan QRIS Stand',
            'items' => [
                ['product_id' => $koinBbq->id, 'custom_price' => 3000, 'quantity' => 1],
                ['product_id' => $mojito->id, 'quantity' => 1],
            ],
        ]);
        $resp2->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(8000, $resp2->json('data.total_amount'));

        // 3. Kasir seninpagi: 1 Qtella Extra Pedas (porsi besar shortcut 10k) = Rp10.000, Transfer
        $resp3 = $this->actingAs($this->kasirPagi)->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift1->id,
            'payment_method' => 'transfer',
            'items' => [
                ['product_id' => $qtlPedas->id, 'custom_price' => 10000, 'quantity' => 1],
            ],
        ]);
        $resp3->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(10000, $resp3->json('data.total_amount'));

        // Cek Riwayat Pribadi seninpagi
        $historyResp = $this->actingAs($this->kasirPagi)->get(route('cashier.history'));
        $historyResp->assertOk();
        $historyResp->assertSee($resp1->json('data.invoice_code'));
        $historyResp->assertSee($resp2->json('data.invoice_code'));
        $historyResp->assertSee($resp3->json('data.invoice_code'));

        // -------------------------------------------------------------
        // SESI 2: ROTASI SHIFT OLEH ADMIN
        // -------------------------------------------------------------
        // Admin menutup Shift 1
        $closeResp = $this->actingAs($this->admin)->post(route('admin.shifts.close', $this->shift1));
        $closeResp->assertRedirect();
        $this->assertTrue($this->shift1->fresh()->isClosed());

        // Setelah shift ditutup, kasir seninpagi DILARANG memproses transaksi lagi
        $forbiddenResp = $this->actingAs($this->kasirPagi)->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift1->id,
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $qtlBalado->id, 'custom_price' => 5000, 'quantity' => 1],
            ],
        ]);
        $forbiddenResp->assertStatus(422);

        // Admin membuka Shift 2 (Siang)
        $openResp = $this->actingAs($this->admin)->post(route('admin.shifts.open', $this->shift2));
        $openResp->assertRedirect();
        $this->assertTrue($this->shift2->fresh()->isOpen());

        // -------------------------------------------------------------
        // SESI 3: HARI SENIN — SHIFT 2 (SIANG/SORE)
        // -------------------------------------------------------------
        $qtlJagung = Product::where('code', 'PRD-QTL-JAG')->firstOrFail();
        $koinBalado = Product::where('code', 'PRD-KOIN-BAL')->firstOrFail();

        // 4. Kasir seninsiang: 1 Qtella Jagung Bakar 5k + 2 Mojito 5k = Rp15.000, Tunai
        $resp4 = $this->actingAs($this->kasirSiang)->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift2->id,
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $qtlJagung->id, 'custom_price' => 5000, 'quantity' => 1],
                ['product_id' => $mojito->id, 'quantity' => 2],
            ],
        ]);
        $resp4->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(15000, $resp4->json('data.total_amount'));

        // 5. Kasir seninsiang: 2 Kerupuk Koin Balado (shortcut 5k) = Rp10.000, QRIS
        $resp5 = $this->actingAs($this->kasirSiang)->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift2->id,
            'payment_method' => 'qris',
            'items' => [
                ['product_id' => $koinBalado->id, 'custom_price' => 5000, 'quantity' => 2],
            ],
        ]);
        $resp5->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(10000, $resp5->json('data.total_amount'));

        // Admin menutup Shift 2
        $this->actingAs($this->admin)->post(route('admin.shifts.close', $this->shift2));
        $this->assertTrue($this->shift2->fresh()->isClosed());

        // -------------------------------------------------------------
        // SESI 4: LAPORAN REKAPITULASI HARIAN OLEH ADMIN
        // -------------------------------------------------------------
        $reportResp = $this->actingAs($this->admin)->get(route('admin.reports.daily', ['event_day_id' => $this->daySenin->id]));
        $reportResp->assertOk();

        // Verifikasi kalkulasi omzet di laporan:
        // Shift 1: Rp10.000 + Rp8.000 + Rp10.000 = Rp28.000 (4 unit)
        // Shift 2: Rp15.000 + Rp10.000 = Rp25.000 (5 unit)
        // Total Hari: Rp53.000 (9 unit)
        $reportResp->assertSee('28.000');
        $reportResp->assertSee('25.000');
        $reportResp->assertSee('53.000');

        // -------------------------------------------------------------
        // SESI 5: EXPORT EXCEL SPREADSHEET
        // -------------------------------------------------------------
        // Export Harian
        $exportDailyResp = $this->actingAs($this->admin)->get(route('admin.reports.export-daily', $this->daySenin));
        $exportDailyResp->assertOk();
        $this->assertStringContainsString('attachment;', $exportDailyResp->headers->get('content-disposition'));
        $this->assertStringContainsString('.xls', $exportDailyResp->headers->get('content-disposition'));

        // Export 5 Hari
        $export5DaysResp = $this->actingAs($this->admin)->get(route('admin.reports.export-five-days'));
        $export5DaysResp->assertOk();
        $this->assertStringContainsString('.xls', $export5DaysResp->headers->get('content-disposition'));

        // -------------------------------------------------------------
        // SESI 6: KOREKSI TRANSAKSI / VOID OLEH ADMIN DENGAN AUDIT TRAIL
        // -------------------------------------------------------------
        $tx5 = Transaction::where('invoice_code', $resp5->json('data.invoice_code'))->firstOrFail();
        $voidResp = $this->actingAs($this->admin)->post(route('admin.transactions.void', $tx5), [
            'reason' => 'Pembeli membatalkan pesanan karena salah varian rasa',
        ]);
        $voidResp->assertRedirect();

        // Pastikan status transaksi terupdate menjadi 'voided'
        $this->assertEquals('voided', $tx5->fresh()->status);

        // Pastikan koreksi tercatat di tabel audit trail
        $this->assertDatabaseHas('transaction_corrections', [
            'transaction_id' => $tx5->id,
            'corrected_by' => $this->admin->id,
            'action' => 'void',
            'reason' => 'Pembeli membatalkan pesanan karena salah varian rasa',
        ]);

        // Verifikasi laporan terupdate otomatis (Rp53.000 - Rp10.000 = Rp43.000)
        $updatedReportResp = $this->actingAs($this->admin)->get(route('admin.reports.daily', ['event_day_id' => $this->daySenin->id]));
        $updatedReportResp->assertOk();
        $updatedReportResp->assertSee('43.000');
    }
}
