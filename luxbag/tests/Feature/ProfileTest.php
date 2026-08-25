<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for user profile management and address management.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();

        $this->user = User::factory()->create([
            'name'   => 'Nguyen Van A',
            'status' => 'active',
        ]);
        $this->user->assignRole('customer');
    }

    // ─── Profile Page ─────────────────────────────────────────────

    public function test_profile_page_is_accessible_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/tai-khoan');
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get('/tai-khoan');
        $response->assertRedirect('/dang-nhap');
    }

    // ─── Update Profile ───────────────────────────────────────────

    public function test_user_can_update_profile(): void
    {
        $response = $this->actingAs($this->user)->put('/tai-khoan/cap-nhat', [
            'name'  => 'Nguyen Van Updated',
            'email' => $this->user->email,
            'phone' => '0912345678',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'   => $this->user->id,
            'name' => 'Nguyen Van Updated',
        ]);
    }

    // ─── Change Password ──────────────────────────────────────────

    public function test_user_can_change_password(): void
    {
        $response = $this->actingAs($this->user)->put('/tai-khoan/doi-mat-khau', [
            'current_password'      => 'password', // UserFactory default
            'password'              => 'NewPass@123',
            'password_confirmation' => 'NewPass@123',
        ]);

        $response->assertRedirect();
        $this->assertNotNull(auth()->user());
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)->put('/tai-khoan/doi-mat-khau', [
            'current_password'      => 'wrongpassword',
            'password'              => 'NewPass@123',
            'password_confirmation' => 'NewPass@123',
        ]);

        // ProfileController returns back()->with('error', ...) NOT withErrors()
        $response->assertSessionHas('error');
    }

    // ─── Addresses ────────────────────────────────────────────────

    public function test_address_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get('/tai-khoan/dia-chi');
        $response->assertStatus(200);
    }

    public function test_user_can_add_new_address(): void
    {
        $response = $this->actingAs($this->user)->post('/tai-khoan/dia-chi', [
            'full_name'      => 'Nguyen Van A',
            'phone'          => '0900000001',
            'province'       => 'Hà Nội',
            'district'       => 'Đống Đa',
            'ward'           => 'Phương Liên',
            'address_detail' => '123 Đường Test',
            'is_default'     => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('user_addresses', [
            'user_id'   => $this->user->id,
            'full_name' => 'Nguyen Van A',
        ]);
    }

    public function test_user_can_delete_their_own_address(): void
    {
        $address = UserAddress::create([
            'user_id'        => $this->user->id,
            'full_name'      => 'Test Recipient',
            'phone'          => '0900000002',
            'province'       => 'TP.HCM',
            'district'       => 'Quận 1',
            'ward'           => 'Phường 1',
            'address_detail' => '456 Street',
            'is_default'     => false,
        ]);

        $response = $this->actingAs($this->user)->delete("/tai-khoan/dia-chi/{$address->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('user_addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_delete_another_users_address(): void
    {
        $otherUser = User::factory()->create(['status' => 'active']);
        $otherUser->assignRole('customer');

        $address = UserAddress::create([
            'user_id'        => $otherUser->id,
            'full_name'      => 'Other User',
            'phone'          => '0900000003',
            'province'       => 'Đà Nẵng',
            'district'       => 'Hải Châu',
            'ward'           => 'Phường 1',
            'address_detail' => '789 Street',
            'is_default'     => false,
        ]);

        $response = $this->actingAs($this->user)->delete("/tai-khoan/dia-chi/{$address->id}");

        // deleteAddress uses: UserAddress::where('user_id', Auth::id())->findOrFail($id)
        // So it will 404 when trying to delete another user's address.
        $response->assertStatus(404);
    }
}
