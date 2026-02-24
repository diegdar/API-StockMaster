<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

/**
 * Trait for setting up API test users with roles.
 * Provides methods to create Admin, Worker, and Viewer users for testing.
 */
trait ApiTestUsersTrait
{
    protected User $admin;
    protected User $worker;
    protected User $viewer;

    /**
     * Set up users for API tests.
     *
     * Seeds the RoleAndPermissionSeeder and creates 3 users with roles Admin, Worker and Viewer.
     */
    protected function setupApiUsers(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->assignRole($this->admin, 'Admin');

        $this->worker = User::factory()->create();
        $this->assignRole($this->worker, 'Worker');

        $this->viewer = User::factory()->create();
        $this->assignRole($this->viewer, 'Viewer');
    }

    /**
     * Assign a role to a user.
     *
     * @param User $user
     * @param string $roleName
     *
     * @throws \InvalidArgumentException
     */
    protected function assignRole(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();
        if ($role) {
            $user->assignRole($role);
        }
    }

    /**
     * Get user by role name.
     *
     * @param string $role
     * @return User
     * @throws \InvalidArgumentException
     */
    protected function getUserByRole(string $role): User
    {
        return match ($role) {
            'Admin' => $this->admin,
            'Worker' => $this->worker,
            'Viewer' => $this->viewer,
            default => throw new \InvalidArgumentException("Unknown role: {$role}"),
        };
    }
}
