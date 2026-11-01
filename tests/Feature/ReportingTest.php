<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use App\Services\POSService;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected EventDay $day;

    protected Shift $shift1;

    protected Shift $shift2;

    protected Product $boba;

    protected Product $roti;

    protected POSService $posService;

    protected ReportingService $reportingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posService = app(POSService::class);
        $this->reportingService = app(ReportingService::class);

        $this->admin = User::create([
            'name' => 'Admin Report',
            'username' => 'admin_rep',
            'email' => 'admin@rep.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Report',
            'username' => 'kasir_rep',
            'email' => 'kasir@rep.local',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->day = EventDay::create([
            'day_name' => 'Senin',
            'event_date' => Carbon::today()->toDateString(),
            'status' => 'active',
        ]);

        $this->shift1 = Shift::create([
            'event_day_id' => $this->day->id,
            'shift_number' => 'Shift 1',
            'name' => 'Shift Pagi',
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => $this->admin->id,
        ]);

        $this->shift2 = Shift::create([
            'event_day_id' => $this->day->id,
            'shift_number' => 'Shift 2',
            'name' => 'Shift Sore',
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => $this->admin->id,
        ]);

        $this->shift1->cashiers()->attach($this->cashier->id);
        $this->shift2->cashiers()->attach($this->cashier->id);

        $this->boba = Product::create([
            'code' => 'PRD-BOBA',
            'name' => 'Boba Brown Sugar',
            'category' => 'Minuman',
            'price' => 10000,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->roti = Product::create([
            'code' => 'PRD-ROTI',
            'name' => 'Roti Panggang',
            'category' => 'Makanan',
            'price' => 5000,
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    public function test_shift_and_daily_reports_aggregate_accurately(): void
    {
        // Shift 1: 3 Boba (30.000) + 2 Roti (10.000) = 40.000 (5 items)
        $this->posService->processTransaction($this->cashier, [
            'shift_id' => $this->shift1->id,
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $this->boba->id, 'quantity' => 3],
                ['product_id' => $this->roti->id, 'quantity' => 2],
            ],
        ]);

        // Shift 2: 1 Boba (10.000) + 4 Roti (20.000) = 30.000 (5 items)
        $this->posService->processTransaction($this->cashier, [
            'shift_id' => $this->shift2->id,
            'payment_method' => 'qris',
            'items' => [
                ['product_id' => $this->boba->id, 'quantity' => 1],
                ['product_id' => $this->roti->id, 'quantity' => 4],
            ],
        ]);

        // Test Shift 1 report
        $s1Rep = $this->reportingService->getShiftReport($this->shift1);
        $this->assertEquals(40000, $s1Rep['total_revenue']);
        $this->assertEquals(5, $s1Rep['total_quantity']);
        $this->assertEquals(1, $s1Rep['transaction_count']);

        // Test Shift 2 report
        $s2Rep = $this->reportingService->getShiftReport($this->shift2);
        $this->assertEquals(30000, $s2Rep['total_revenue']);
        $this->assertEquals(5, $s2Rep['total_quantity']);
        $this->assertEquals(1, $s2Rep['transaction_count']);

        // Test Daily Combined Report (Shift 1 + Shift 2)
        $dailyRep = $this->reportingService->getDailyReport($this->day);
        $this->assertEquals(70000, $dailyRep['daily_total']['total_revenue']);
        $this->assertEquals(10, $dailyRep['daily_total']['total_quantity']);
        $this->assertEquals(2, $dailyRep['daily_total']['transaction_count']);

        // Test 5-Day Overall Recap
        $recap = $this->reportingService->getFiveDayOverallRecap();
        $this->assertEquals(70000, $recap['total_revenue_5_days']);
        $this->assertEquals(10, $recap['total_units_5_days']);
        $this->assertEquals(2, $recap['total_transactions_5_days']);

        // Best seller should be Roti (6 units) followed by Boba (4 units)
        $topProduct = $recap['products_ranking']->first();
        $this->assertEquals('Roti Panggang', $topProduct->product_name_snapshot);
        $this->assertEquals(6, $topProduct->total_units);
    }

    public function test_excel_export_daily_report_returns_download_response(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.export-daily', $this->day));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_excel_export_five_days_returns_download_response(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.export-five-days'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }
}
