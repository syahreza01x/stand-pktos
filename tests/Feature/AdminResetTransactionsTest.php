<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionCorrection;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResetTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected Shift $shift;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Master',
            'username' => 'admin_test',
            'email' => 'admin@test.local',
            'password' => bcrypt('adminsecret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Shift',
            'username' => 'kasir_test',
            'email' => 'kasir@test.local',
            'password' => bcrypt('kasirsecret123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $day = EventDay::create([
            'day_name' => 'Senin',
            'event_date' => Carbon::today()->toDateString(),
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'event_day_id' => $day->id,
            'shift_number' => 'Shift 1',
            'name' => 'Shift Pagi (08:00 - 12:45)',
            'start_time' => '08:00:00',
            'end_time' => '12:45:00',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->product = Product::create([
            'category' => 'food',
            'name' => 'Kerupuk Koin Balado',
            'code' => 'KOIN-BALADO',
            'price' => 2000,
            'is_active' => true,
        ]);

        // Create 2 sample transactions
        $tx1 = Transaction::create([
            'invoice_code' => 'TRX-TEST-RESET-1',
            'event_day_id' => $day->id,
            'shift_id' => $this->shift->id,
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'cash',
            'subtotal_amount' => 4000,
            'discount_amount' => 0,
            'total_amount' => 4000,
            'total_items' => 2,
            'status' => 'completed',
            'idempotency_key' => 'idem-reset-1',
        ]);

        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'product_id' => $this->product->id,
            'product_name_snapshot' => $this->product->name,
            'unit_price_snapshot' => 2000,
            'quantity' => 2,
            'subtotal' => 4000,
        ]);

        $tx2 = Transaction::create([
            'invoice_code' => 'TRX-TEST-RESET-2',
            'event_day_id' => $day->id,
            'shift_id' => $this->shift->id,
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'qris',
            'subtotal_amount' => 6000,
            'discount_amount' => 0,
            'total_amount' => 6000,
            'total_items' => 3,
            'status' => 'voided',
            'idempotency_key' => 'idem-reset-2',
        ]);

        TransactionCorrection::create([
            'transaction_id' => $tx2->id,
            'corrected_by' => $this->admin->id,
            'action' => 'void',
            'reason' => 'Simulasi pembatalan pesanan',
        ]);
    }

    public function test_cashier_cannot_access_reset_transactions_route(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->post(route('admin.settings.reset-transactions'), [
            'confirmation_code' => '123456',
            'admin_password' => 'adminsecret123',
        ]);

        $response->assertForbidden();
    }

    public function test_reset_fails_if_confirmation_code_does_not_match(): void
    {
        $this->actingAs($this->admin);

        $response = $this->withSession(['reset_confirmation_code' => '888999'])
            ->post(route('admin.settings.reset-transactions'), [
                'confirmation_code' => '111222', // Wrong code
                'admin_password' => 'adminsecret123',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(2, Transaction::count());
    }

    public function test_reset_fails_if_admin_password_is_incorrect(): void
    {
        $this->actingAs($this->admin);

        $response = $this->withSession(['reset_confirmation_code' => '888999'])
            ->post(route('admin.settings.reset-transactions'), [
                'confirmation_code' => '888999',
                'admin_password' => 'wrongpassword',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(2, Transaction::count());
    }

    public function test_reset_succeeds_with_correct_code_and_password(): void
    {
        $this->actingAs($this->admin);

        $response = $this->withSession(['reset_confirmation_code' => '888999'])
            ->post(route('admin.settings.reset-transactions'), [
                'confirmation_code' => '888999',
                'admin_password' => 'adminsecret123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // All transactions, items, and corrections are wiped
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, TransactionItem::count());
        $this->assertEquals(0, TransactionCorrection::count());

        // Master data remains intact
        $this->assertEquals(1, Product::count());
        $this->assertEquals(2, User::count());
        $this->assertEquals(1, Shift::count());
    }
}
