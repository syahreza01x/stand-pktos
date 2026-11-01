<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('STAND PKTOS');
    }

    public function test_admin_can_login_with_username_and_is_redirected_to_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'admin_test',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_cashier_can_login_and_is_redirected_to_pos(): void
    {
        $cashier = User::create([
            'name' => 'Budi Kasir',
            'username' => 'budi_kasir',
            'email' => 'budi@test.local',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'budi_kasir',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($cashier);
        $response->assertRedirect(route('cashier.pos'));
    }

    public function test_deactivated_user_cannot_login(): void
    {
        User::create([
            'name' => 'Nonaktif Kasir',
            'username' => 'nonaktif',
            'email' => 'nonaktif@test.local',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'nonaktif',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'User Logout',
            'username' => 'userlogout',
            'email' => 'logout@test.local',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
