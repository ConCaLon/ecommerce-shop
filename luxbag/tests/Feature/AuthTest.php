<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature tests for user authentication (login, register, logout).
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    // ─── Login Page ───────────────────────────────────────────────

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/dang-nhap');
        $response->assertStatus(200);
    }

    // ─── Register Page ────────────────────────────────────────────

    public function test_register_page_is_accessible(): void
    {
        $response = $this->get('/dang-ky');
        $response->assertStatus(200);
    }

    // ─── Register ─────────────────────────────────────────────────

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/dang-ky', [
            'name'                  => 'Nguyen Van Test',
            'email'                 => 'test@example.com',
            'phone'                 => '0900000000',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name'  => 'Nguyen Van Test',
        ]);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/dang-ky', [
            'name'                  => 'Another User',
            'email'                 => 'existing@example.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_fails_when_password_too_short(): void
    {
        $response = $this->post('/dang-ky', [
            'name'                  => 'Test User',
            'email'                 => 'new@example.com',
            'password'              => 'short1',
            'password_confirmation' => 'short1',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->post('/dang-ky', [
            'name'                  => 'Test User',
            'email'                 => 'new@example.com',
            'password'              => 'Password1',
            'password_confirmation' => 'DifferentPass1',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ─── Login ────────────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password1'),
            'status'   => 'active',
        ]);
        $user->assignRole('customer');

        $response = $this->post('/dang-nhap', [
            'email'    => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('CorrectPass1'),
            'status'   => 'active',
        ]);

        $response = $this->post('/dang-nhap', [
            'email'    => $user->email,
            'password' => 'WrongPassword1',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_banned_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password1'),
            'status'   => 'banned',
        ]);

        $response = $this->post('/dang-nhap', [
            'email'    => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password1'),
            'status'   => 'unverified',
        ]);

        $response = $this->post('/dang-nhap', [
            'email'    => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    // ─── Logout ───────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->post('/dang-xuat');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    // ─── Guest Redirect ───────────────────────────────────────────

    public function test_guest_is_redirected_when_accessing_protected_route(): void
    {
        $response = $this->get('/dat-hang/thanh-toan');
        $response->assertRedirect('/dang-nhap');
    }

    // ─── Admin Redirect After Login ───────────────────────────────

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'password' => bcrypt('Admin@123456'),
            'status'   => 'active',
        ]);
        $admin->assignRole('admin');

        $response = $this->post('/dang-nhap', [
            'email'    => $admin->email,
            'password' => 'Admin@123456',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }
}
