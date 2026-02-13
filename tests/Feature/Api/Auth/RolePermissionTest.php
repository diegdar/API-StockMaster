<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_an_admin_has_all_permissions()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->assertTrue($admin->hasPermissionTo('manage products'));
        $this->assertTrue($admin->hasPermissionTo('manage warehouses'));
        $this->assertTrue($admin->hasPermissionTo('record movements'));
        $this->assertTrue($admin->hasPermissionTo('view inventory'));
    }

    public function test_a_worker_has_limited_permissions()
    {
        $worker = User::factory()->create();
        $worker->assignRole('Worker');

        $this->assertFalse($worker->hasPermissionTo('manage products'));
        $this->assertFalse($worker->hasPermissionTo('manage warehouses'));
        $this->assertTrue($worker->hasPermissionTo('record movements'));
        $this->assertTrue($worker->hasPermissionTo('view inventory'));
    }

    public function test_a_viewer_only_has_read_permissions()
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $this->assertFalse($viewer->hasPermissionTo('manage products'));
        $this->assertFalse($viewer->hasPermissionTo('record movements'));
        $this->assertTrue($viewer->hasPermissionTo('view inventory'));
    }
}
