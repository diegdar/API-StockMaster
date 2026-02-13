<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    /**
     * Setup test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createPassportClient();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * Create a personal access client for testing.
     */
    private function createPassportClient(): void
    {
        $clientId = 'test-personal-access-client-id';

        $exists = DB::table('oauth_clients')->where('id', $clientId)->exists();

        if (!$exists) {
            DB::table('oauth_clients')->insert([
                'id' => $clientId,
                'name' => 'Test Personal Access Client',
                'secret' => 'test-secret-key',
                'provider' => 'users',
                'redirect_uris' => json_encode(['http://localhost']),
                'grant_types' => json_encode(['personal_access']),
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Data provider for registration validation error tests.
     */
    public static function registrationValidationProvider(): array
    {
        return [
            'missing all required fields' => [
                'payload' => [],
                'expectedErrors' => ['name', 'email', 'password'],
            ],
            'invalid email format' => [
                'payload' => [
                    'name' => 'Test User',
                    'email' => 'invalid-email',
                    'password' => 'password123',
                ],
                'expectedErrors' => ['email'],
            ],
            'password too short' => [
                'payload' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => '123',
                ],
                'expectedErrors' => ['password'],
            ],
        ];
    }

    /**
     * Test registration fails with various validation errors.
     *
     * @dataProvider registrationValidationProvider
     */
    public function test_registration_fails_with_validation_errors(array $payload, array $expectedErrors): void
    {
        $response = $this->postJson(route('auth.register'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    /**
     * Test registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $payload = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('auth.register'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test successful user registration returns access token.
     */
    public function test_successful_registration_returns_access_token(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('auth.register'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'access_token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }
}
