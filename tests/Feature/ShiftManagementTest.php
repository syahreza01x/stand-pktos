<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier1;

    protected User $cashier2;

    protected EventDay $day;

    protected Shift $shift1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Shift',
            'username' => 'admin_shift',
            'email' => 'admin@shift.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier1 = User::create([
            'name' => 'Kasir 1',
            'username' => 'kasir1',
            'email' => 'kasir1@shift.local',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->cashier2 = User::create([
            'name' => 'Kasir 2',
            'username' => 'kasir2',
            'email' => 'kasir2@shift.local',
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
            'start_time' => '08:00:00',
            'end_time' => '12:30:00',
            'status' => 'scheduled',
        ]);
    }

    public function test_admin_can_open_shift(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.shifts.open', $this->shift1));
        $response->assertSessionHas('success');

        $this->shift1->refresh();
        $this->assertEquals('open', $this->shift1->status);
        $this->assertNotNull($this->shift1->opened_at);
        $this->assertEquals($this->admin->id, $this->shift1->opened_by);
    }

    public function test_admin_can_close_shift(): void
    {
        $this->shift1->update(['status' => 'open', 'opened_at' => now(), 'opened_by' => $this->admin->id]);
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.shifts.close', $this->shift1));
        $response->assertSessionHas('success');

        $this->shift1->refresh();
        $this->assertEquals('closed', $this->shift1->status);
        $this->assertNotNull($this->shift1->closed_at);
        $this->assertEquals($this->admin->id, $this->shift1->closed_by);
    }

    public function test_cashier_can_access_pos_when_assigned_and_shift_is_open(): void
    {
        $this->shift1->update(['status' => 'open']);
        $this->shift1->cashiers()->attach($this->cashier1->id);

        $this->actingAs($this->cashier1);
        $response = $this->get(route('cashier.pos'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Produk Stand');
    }

    public function test_cashier_cannot_access_pos_when_shift_is_not_open(): void
    {
        $this->shift1->update(['status' => 'scheduled']);
        $this->shift1->cashiers()->attach($this->cashier1->id);

        $this->actingAs($this->cashier1);
        $response = $this->get(route('cashier.pos'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_cashier_cannot_access_pos_when_not_assigned_to_open_shift(): void
    {
        $this->shift1->update(['status' => 'open']);
        $this->shift1->cashiers()->attach($this->cashier1->id);

        // Kasir 2 is NOT attached to the open shift
        $this->actingAs($this->cashier2);
        $response = $this->get(route('cashier.pos'));

        $response->assertRedirect(route('cashier.dashboard'));
    }
}
