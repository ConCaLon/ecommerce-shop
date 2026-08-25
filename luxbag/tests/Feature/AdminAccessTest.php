<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for admin panel access control.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    // ─── Guest Access ─────────────────────────────────────────────

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/dang-nhap');
    }

    // ─── Customer Access ──────────────────────────────────────────

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['status' => 'active']);
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->get('/admin');
        // Should be forbidden (403) or redirect
        $this->assertContains($response->status(), [403, 302]);
    }

    // ─── Admin Access ─────────────────────────────────────────────

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $superAdmin = User::factory()->create(['status' => 'active']);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin');
        $response->assertStatus(200);
    }

    // ─── Admin Sections ───────────────────────────────────────────

    public function test_admin_can_access_products_list(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/san-pham');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_orders_list(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/don-hang');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_customers_list(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/khach-hang');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_categories_list(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/danh-muc');
        $response->assertStatus(200);
    }
}
