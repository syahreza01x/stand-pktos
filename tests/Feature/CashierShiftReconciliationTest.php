<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierShiftReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierSeninPagi;

    protected User $cashierSeninSiang;

    protected EventDay $daySenin;

    protected Shift $shiftPagi;

    protected Shift $shiftSiang;

    protected Product $productQtella;

    protected Product $productMojito;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashierSeninPagi = User::create([
            'name' => 'Farhad',
            'username' => 'seninpagi',
            'email' => 'seninpagi@stand-pktos.local',
            'password' => bcrypt('seninpagi2026'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->cashierSeninSiang = User::create([
            'name' => 'Natasya & Naila',
            'username' => 'seninsiang',
            'email' => 'seninsiang@stand-pktos.local',
            'password' => bcrypt('seninsiang2026'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->daySenin = EventDay::create([
            'day_name' => 'Senin',
            'event_date' => Carbon::today()->toDateString(),
            'status' => 'active',
        ]);

        $this->shiftPagi = Shift::create([
            'event_day_id' => $this->daySenin->id,
            'shift_number' => 'Shift 1',
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '12:30:00',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->shiftSiang = Shift::create([
            'event_day_id' => $this->daySenin->id,
            'shift_number' => 'Shift 2',
            'name' => 'Shift Siang',
            'start_time' => '12:30:00',
            'end_time' => '17:00:00',
            'status' => 'scheduled',
        ]);

        $this->cashierSeninPagi->shifts()->attach($this->shiftPagi->id);
        $this->cashierSeninSiang->shifts()->attach($this->shiftSiang->id);

        $this->productQtella = Product::create([
            'category' => 'food',
            'name' => 'Qtella Balado',
            'code' => 'QTL-BALADO',
            'price' => 5000,
            'is_active' => true,
        ]);

        $this->productMojito = Product::create([
            'category' => 'drink',
            'name' => 'Mojito',
            'code' => 'PRD-MOJITO',
            'price' => 5000,
            'is_active' => true,
        ]);

        // Seed 3 transactions for shiftPagi: 1 cash (10.000), 1 qris (5.000), 1 transfer (15.000)
        $tx1 = Transaction::create([
            'invoice_code' => 'TRX-TEST-001',
            'event_day_id' => $this->daySenin->id,
            'shift_id' => $this->shiftPagi->id,
            'cashier_id' => $this->cashierSeninPagi->id,
            'payment_method' => 'cash',
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'total_items' => 2,
            'status' => 'completed',
            'idempotency_key' => 'idem-1',
        ]);
        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'product_id' => $this->productQtella->id,
            'product_name_snapshot' => $this->productQtella->name,
            'unit_price_snapshot' => 5000,
            'quantity' => 2,
            'subtotal' => 10000,
        ]);

        $tx2 = Transaction::create([
            'invoice_code' => 'TRX-TEST-002',
            'event_day_id' => $this->daySenin->id,
            'shift_id' => $this->shiftPagi->id,
            'cashier_id' => $this->cashierSeninPagi->id,
            'payment_method' => 'qris',
            'subtotal_amount' => 5000,
            'discount_amount' => 0,
            'total_amount' => 5000,
            'total_items' => 1,
            'status' => 'completed',
            'idempotency_key' => 'idem-2',
        ]);
        TransactionItem::create([
            'transaction_id' => $tx2->id,
            'product_id' => $this->productMojito->id,
            'product_name_snapshot' => $this->productMojito->name,
            'unit_price_snapshot' => 5000,
            'quantity' => 1,
            'subtotal' => 5000,
        ]);

        $tx3 = Transaction::create([
            'invoice_code' => 'TRX-TEST-003',
            'event_day_id' => $this->daySenin->id,
            'shift_id' => $this->shiftPagi->id,
            'cashier_id' => $this->cashierSeninPagi->id,
            'payment_method' => 'transfer',
            'subtotal_amount' => 15000,
            'discount_amount' => 0,
            'total_amount' => 15000,
            'total_items' => 3,
            'status' => 'completed',
            'idempotency_key' => 'idem-3',
        ]);
        TransactionItem::create([
            'transaction_id' => $tx3->id,
            'product_id' => $this->productQtella->id,
            'product_name_snapshot' => $this->productQtella->name,
            'unit_price_snapshot' => 5000,
            'quantity' => 3,
            'subtotal' => 15000,
        ]);
    }

    public function test_cashier_dashboard_shows_reconciliation_breakdown_by_cash_qris_and_tf(): void
    {
        $this->actingAs($this->cashierSeninPagi);

        $response = $this->get(route('cashier.dashboard'));
        $response->assertOk();

        // Check that view contains reconciliation header
        $response->assertSee('Rekonsiliasi Kas Stand');
        $response->assertSee('1. Uang Tunai (Cash)');
        $response->assertSee('Uang Fisik Stand');
        $response->assertSee('2. QRIS Stand');
        $response->assertSee('3. Transfer Bank (TF)');

        // Cash total: 10.000, QRIS total: 5.000, Transfer total: 15.000, Grand total: 30.000
        $response->assertSee('Rp10.000');
        $response->assertSee('Rp5.000');
        $response->assertSee('Rp15.000');
        $response->assertSee('Rp30.000');
    }

    public function test_cashier_cannot_view_unassigned_shift_in_dashboard(): void
    {
        $this->actingAs($this->cashierSeninPagi);

        // Attempt to pass shift_id of shiftSiang (which is not assigned to cashierSeninPagi)
        $response = $this->get(route('cashier.dashboard', ['shift_id' => $this->shiftSiang->id]));
        $response->assertOk();

        // The dashboard should fall back to cashier's active/assigned shift, never showing unauthorized shift data
        $viewData = $response->viewData('viewedShift');
        $this->assertEquals($this->shiftPagi->id, $viewData->id);
    }

    public function test_pos_view_includes_shift_summary(): void
    {
        $this->actingAs($this->cashierSeninPagi);

        $response = $this->get(route('cashier.pos'));
        $response->assertOk();

        $response->assertViewHas('shiftSummary');
        $summary = $response->viewData('shiftSummary');

        $this->assertEquals(10000, $summary['cash']['total']);
        $this->assertEquals(1, $summary['cash']['count']);
        $this->assertEquals(5000, $summary['qris']['total']);
        $this->assertEquals(1, $summary['qris']['count']);
        $this->assertEquals(15000, $summary['transfer']['total']);
        $this->assertEquals(1, $summary['transfer']['count']);
        $this->assertEquals(30000, $summary['total_revenue']);
        $this->assertEquals(3, $summary['total_transactions']);
        $this->assertEquals(6, $summary['total_items']);

        $response->assertSee('Rekap Kas Shift');
    }

    public function test_pos_store_returns_updated_shift_summary_in_json(): void
    {
        $this->actingAs($this->cashierSeninPagi);

        $response = $this->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shiftPagi->id,
            'payment_method' => 'cash',
            'idempotency_key' => 'idem-pos-test-cash',
            'notes' => 'Pembeli beli 1 mojito tunai',
            'items' => [
                [
                    'product_id' => $this->productMojito->id,
                    'custom_price' => 5000,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.shift_summary.cash.total', 15000); // 10.000 + 5.000
        $response->assertJsonPath('data.shift_summary.cash.count', 2);
        $response->assertJsonPath('data.shift_summary.total_revenue', 35000);
    }

    public function test_cashier_history_filters_by_payment_method_and_shows_breakdown(): void
    {
        $this->actingAs($this->cashierSeninPagi);

        // All payments
        $responseAll = $this->get(route('cashier.history'));
        $responseAll->assertOk();
        $responseAll->assertSee('TRX-TEST-001');
        $responseAll->assertSee('TRX-TEST-002');
        $responseAll->assertSee('TRX-TEST-003');

        // Cash only filter
        $responseCash = $this->get(route('cashier.history', ['payment_method' => 'cash']));
        $responseCash->assertOk();
        $responseCash->assertSee('TRX-TEST-001');
        $responseCash->assertDontSee('TRX-TEST-002');
        $responseCash->assertDontSee('TRX-TEST-003');

        // QRIS only filter
        $responseQris = $this->get(route('cashier.history', ['payment_method' => 'qris']));
        $responseQris->assertOk();
        $responseQris->assertDontSee('TRX-TEST-001');
        $responseQris->assertSee('TRX-TEST-002');
        $responseQris->assertDontSee('TRX-TEST-003');

        // Transfer only filter
        $responseTransfer = $this->get(route('cashier.history', ['payment_method' => 'transfer']));
        $responseTransfer->assertOk();
        $responseTransfer->assertDontSee('TRX-TEST-001');
        $responseTransfer->assertDontSee('TRX-TEST-002');
        $responseTransfer->assertSee('TRX-TEST-003');
    }
}
