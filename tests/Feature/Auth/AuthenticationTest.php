<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول');
        $response->assertSee('اسم المستخدم');
        $response->assertSee('كلمة المرور');
    }

    public function test_active_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->create([
            'username' => 'cashier1',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'cashier1',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create([
            'username' => 'deactivated_user',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'deactivated_user',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'valid_user',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'username' => 'valid_user',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guests_are_redirected_to_login_when_accessing_root(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_deactivated_logged_in_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        // Now deactivate user
        $user->is_active = false;
        $user->save();

        $response = $this->actingAs($user)->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('username');
    }
}
