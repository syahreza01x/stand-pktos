<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionConcurrencyAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier1;

    protected User $cashier2;

    protected EventDay $day;

    protected Shift $shift;

    protected Product $boba;

    protected Product $roti;

    protected Product $esTeh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Concurrency',
            'username' => 'admin_conc',
            'email' => 'admin@conc.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier1 = User::create([
            'name' => 'Budi Kasir',
            'username' => 'budi_conc',
            'email' => 'budi@conc.local',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->cashier2 = User::create([
            'name' => 'Siti Kasir',
            'username' => 'siti_conc',
            'email' => 'siti@conc.local',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->day = EventDay::create([
            'day_name' => 'Senin',
            'event_date' => Carbon::today()->toDateString(),
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'event_day_id' => $this->day->id,
            'shift_number' => 'Shift 1',
            'name' => 'Shift Pagi',
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => $this->admin->id,
        ]);

        $this->shift->cashiers()->attach([$this->cashier1->id, $this->cashier2->id]);

        $this->boba = Product::create([
            'code' => 'PRD-BOBA',
            'name' => 'Boba Brown Sugar',
            'category' => 'Minuman',
            'price' => 10000,
            'is_active' => true,
        ]);

        $this->roti = Product::create([
            'code' => 'PRD-ROTI',
            'name' => 'Roti Panggang',
            'category' => 'Makanan',
            'price' => 5000,
            'is_active' => true,
        ]);

        $this->esTeh = Product::create([
            'code' => 'PRD-ESTEH',
            'name' => 'Es Teh Manis',
            'category' => 'Minuman',
            'price' => 4000,
            'is_active' => true,
        ]);
    }

    public function test_cashier_can_submit_transaction_and_prices_calculated_server_side(): void
    {
        $this->actingAs($this->cashier1);

        $payload = [
            'shift_id' => $this->shift->id,
            'payment_method' => 'cash',
            'idempotency_key' => Str::uuid()->toString(),
            'notes' => 'Pelanggan pertama',
            'items' => [
                ['product_id' => $this->boba->id, 'quantity' => 3], // 3 * 10.000 = 30.000
                ['product_id' => $this->roti->id, 'quantity' => 2], // 2 * 5.000 = 10.000
                ['product_id' => $this->esTeh->id, 'quantity' => 4], // 4 * 4.000 = 16.000
            ],
        ];

        $response = $this->postJson(route('cashier.pos.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_amount' => '56000.00',
                'total_items' => 9,
            ],
        ]);

        $this->assertDatabaseHas('transactions', [
            'shift_id' => $this->shift->id,
            'cashier_id' => $this->cashier1->id,
            'total_items' => 9,
            'total_amount' => 56000.00,
            'status' => 'completed',
        ]);

        $this->assertDatabaseCount('transaction_items', 3);
    }

    public function test_transaction_items_preserve_immutable_price_snapshot(): void
    {
        $this->actingAs($this->cashier1);

        $payload = [
            'shift_id' => $this->shift->id,
            'payment_method' => 'qris',
            'idempotency_key' => Str::uuid()->toString(),
            'items' => [
                ['product_id' => $this->boba->id, 'quantity' => 2], // 2 * 10.000 = 20.000
            ],
        ];

        $this->postJson(route('cashier.pos.store'), $payload);

        $transaction = Transaction::first();
        $this->assertEquals(20000, (float) $transaction->total_amount);

        // Admin updates master product price to 15.000
        $this->boba->update(['price' => 15000]);

        // Historical transaction snapshot must remain intact at 10.000
        $item = $transaction->items()->first();
        $this->assertEquals(10000, (float) $item->unit_price_snapshot);
        $this->assertEquals(20000, (float) $item->subtotal);
        $this->assertEquals(20000, (float) $transaction->fresh()->total_amount);
    }

    public function test_idempotency_prevents_duplicate_submission(): void
    {
        $this->actingAs($this->cashier1);
        $key = 'key-test-123456';

        $payload = [
            'shift_id' => $this->shift->id,
            'payment_method' => 'cash',
            'idempotency_key' => $key,
            'items' => [
                ['product_id' => $this->boba->id, 'quantity' => 1],
            ],
        ];

        // First submit
        $res1 = $this->postJson(route('cashier.pos.store'), $payload);
        $res1->assertStatus(200);

        // Second duplicate submit with same idempotency_key
        $res2 = $this->postJson(route('cashier.pos.store'), $payload);
        $res2->assertStatus(200);

        // Transaction count must be exactly 1
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_admin_can_void_transaction_with_mandatory_reason_and_audit(): void
    {
        $this->actingAs($this->cashier1);

        $payload = [
            'shift_id' => $this->shift->id,
            'payment_method' => 'cash',
            'idempotency_key' => Str::uuid()->toString(),
            'items' => [
                ['product_id' => $this->roti->id, 'quantity' => 2],
            ],
        ];

        $this->postJson(route('cashier.pos.store'), $payload);
        $tx = Transaction::first();

        // Admin voids the transaction
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.transactions.void', $tx), [
            'reason' => 'Pelanggan membatalkan pesanan roti',
        ]);

        $response->assertSessionHas('success');
        $tx->refresh();

        $this->assertEquals('voided', $tx->status);
        $this->assertDatabaseHas('transaction_corrections', [
            'transaction_id' => $tx->id,
            'corrected_by' => $this->admin->id,
            'action' => 'void',
            'reason' => 'Pelanggan membatalkan pesanan roti',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'transaction',
            'action' => 'void',
        ]);
    }

    public function test_concurrent_cashiers_can_process_simultaneous_transactions(): void
    {
        // Cashier 1 processes a transaction
        $this->actingAs($this->cashier1);
        $this->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift->id,
            'payment_method' => 'cash',
            'idempotency_key' => 'c1-tx',
            'items' => [['product_id' => $this->boba->id, 'quantity' => 2]],
        ]);

        // Cashier 2 processes a transaction on same shift
        $this->actingAs($this->cashier2);
        $this->postJson(route('cashier.pos.store'), [
            'shift_id' => $this->shift->id,
            'payment_method' => 'qris',
            'idempotency_key' => 'c2-tx',
            'items' => [['product_id' => $this->roti->id, 'quantity' => 4]],
        ]);

        $this->assertDatabaseCount('transactions', 2);

        $tx1 = Transaction::where('cashier_id', $this->cashier1->id)->first();
        $tx2 = Transaction::where('cashier_id', $this->cashier2->id)->first();

        $this->assertEquals(20000, (float) $tx1->total_amount);
        $this->assertEquals(20000, (float) $tx2->total_amount);
    }
}
