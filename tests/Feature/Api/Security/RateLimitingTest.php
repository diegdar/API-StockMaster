<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Security;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    public function test_it_limits_api_requests()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Hit a dummy api route multiple times
        // We'll use a route that doesn't exist but is under the 'api' prefix
        // to verify the rate limiting middleware is working.

        for ($i = 0; $i < 60; $i++) {
            $this->getJson(route('products.index'));
        }

        $response = $this->getJson(route('products.index'));

        $response->assertStatus(429);
    }
}
