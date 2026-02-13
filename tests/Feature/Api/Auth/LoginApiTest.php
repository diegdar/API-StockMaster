<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginApiTest extends TestCase
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
     * Data provider for login validation error tests.
     */
    public static function loginValidationProvider(): array
    {
        return [
            'missing all required fields' => [
                'payload' => [],
                'expectedErrors' => ['email', 'password'],
            ],
            'missing email only' => [
                'payload' => ['password' => 'password123'],
                'expectedErrors' => ['email'],
            ],
            'missing password only' => [
                'payload' => ['email' => 'test@example.com'],
                'expectedErrors' => ['password'],
            ],
            'invalid email format' => [
                'payload' => [
                    'email' => 'not-an-email',
                    'password' => 'password123',
                ],
                'expectedErrors' => ['email'],
            ],
        ];
    }

    /**
     * Test login fails with various validation errors.
     *
     * @dataProvider loginValidationProvider
     */
    public function test_login_fails_with_validation_errors(array $payload, array $expectedErrors): void
    {
        $response = $this->postJson(route('auth.login'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $payload = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('auth.login'), $payload);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ])
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);
    }

    /**
     * Test login fails with wrong password.
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ];

        $response = $this->postJson(route('auth.login'), $payload);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test successful login returns access token and user data.
     */
    public function test_successful_login_returns_access_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('auth.login'), $payload);

        $response->assertStatus(200)
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
            ])
            ->assertJson([
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.access_token'));
    }

    /**
     * Test login does not expose password in response.
     */
    public function test_login_does_not_expose_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('auth.login'), $payload);

        $response->assertStatus(200)
            ->assertJsonMissing(['password', 'password_hash']);
    }
}
