<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup roles required for many tests (Spatie Permission).
     * Called only in tests that opt in via setupRoles().
     */
    protected function setupRoles(): void
    {
        $roles = ['super-admin', 'admin', 'manager', 'staff', 'customer'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
