<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShopeePayMerchantService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopeePayQRISIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected Shift $shift;

    protected EventDay $day;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier Test',
            'username' => 'cashier_test',
            'email' => 'cashier@test.local',
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
            'start_time' => '08:00:00',
            'end_time' => '12:45:00',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->cashier->shifts()->attach($this->shift->id);

        Setting::set('shopeepay_cookie', 'test_cookie_string');
        Setting::set('shopeepay_token', 'test_token_string');
    }

    public function test_admin_can_view_qris_settings_page(): void
    {
        Http::fake([
            'https://shopeepay.shopee.co.id/*' => Http::response([
                'code' => 0,
                'data' => [
                    'list' => [
                        [
                            'transactionId' => 'TX12345678',
                            'amount' => '15000',
                            'status' => 3,
                            'createTime' => time(),
                            'storeName' => 'Stand GenBI',
                            'merchantId' => 24978424,
                        ],
                    ],
                    'total' => '1',
                    'totalNetSales' => '15000',
                    'totalCompletedCount' => '1',
                ],
                'msg' => '',
            ], 200),
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.settings.qris'));
        $response->assertOk();
        $response->assertSee('ShopeePay Merchant');
        $response->assertSee('Stand GenBI');
        $response->assertSee('Rp15.000');
    }

    public function test_admin_can_update_qris_credentials(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.settings.qris.update'), [
            'shopeepay_cookie' => 'new_updated_cookie_value',
            'shopeepay_token' => 'new_updated_token_value',
            'shopeepay_endpoint' => 'https://shopeepay.shopee.co.id/merchant/v1/partner-web/get-transaction-list',
        ]);

        $response->assertRedirect(route('admin.settings.qris'));
        $response->assertSessionHas('success');

        $this->assertEquals('new_updated_cookie_value', Setting::get('shopeepay_cookie'));
        $this->assertEquals('new_updated_token_value', Setting::get('shopeepay_token'));
    }

    public function test_cashier_can_fetch_live_qris_mutations(): void
    {
        Http::fake([
            'https://shopeepay.shopee.co.id/*' => Http::response([
                'code' => 0,
                'data' => [
                    'list' => [
                        [
                            'transactionId' => 'TX998877',
                            'amount' => '10000',
                            'status' => 3,
                            'createTime' => time(),
                            'storeName' => 'Stand GenBI',
                        ],
                    ],
                    'total' => '1',
                    'totalNetSales' => '10000',
                    'totalCompletedCount' => '1',
                ],
                'msg' => '',
            ], 200),
        ]);

        $this->actingAs($this->cashier);

        $response = $this->getJson(route('cashier.qris.live-mutations', ['refresh' => 1]));
        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('store_name', 'Stand GenBI');
        $response->assertJsonPath('total_net_sales', 10000);
        $response->assertJsonPath('transactions.0.amount', 10000);
        $response->assertJsonPath('transactions.0.is_success', true);
    }

    public function test_cashier_can_check_qris_payment_match(): void
    {
        Http::fake([
            'https://shopeepay.shopee.co.id/*' => Http::response([
                'code' => 0,
                'data' => [
                    'list' => [
                        [
                            'transactionId' => 'TX_MATCH_123',
                            'amount' => '15.000', // Real ShopeePay format uses dot for thousands
                            'status' => 3,
                            'createTime' => time() - 30, // 30 seconds ago
                            'storeName' => 'Stand GenBI',
                        ],
                    ],
                    'total' => '1',
                    'totalNetSales' => '15.000',
                    'totalCompletedCount' => '1',
                ],
                'msg' => '',
            ], 200),
        ]);

        $this->actingAs($this->cashier);

        // Matching amount 15.000
        $responseMatch = $this->postJson(route('cashier.qris.check-match'), [
            'amount' => 15000,
        ]);

        $responseMatch->assertOk();
        $responseMatch->assertJsonPath('matched', true);
        $responseMatch->assertJsonPath('transaction.amount', 15000);

        // Non-matching amount 50.000
        $responseNoMatch = $this->postJson(route('cashier.qris.check-match'), [
            'amount' => 50000,
        ]);

        $responseNoMatch->assertOk();
        $responseNoMatch->assertJsonPath('matched', false);
    }

    public function test_parse_amount_handles_shopeepay_dot_thousands_separators(): void
    {
        $service = app(ShopeePayMerchantService::class);

        $this->assertEquals(5000.0, $service->parseAmount('5.000'));
        $this->assertEquals(1.0, $service->parseAmount('1'));
        $this->assertEquals(5002.0, $service->parseAmount('5.002'));
        $this->assertEquals(15000.0, $service->parseAmount('15.000'));
        $this->assertEquals(15000.0, $service->parseAmount('15000'));
        $this->assertEquals(15000.0, $service->parseAmount(15000));
        $this->assertEquals(1500000.0, $service->parseAmount('1.500.000'));
        $this->assertEquals(0.0, $service->parseAmount(''));
    }

    public function test_guest_cannot_access_qris_api(): void
    {
        $response = $this->getJson(route('cashier.qris.live-mutations'));
        $response->assertUnauthorized();
    }
}
