<?php
declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\LoginRequest;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    public function test_missing_email_fails_validation(): void
    {
        $request = new LoginRequest();
        $validator = $this->app['validator']->make(
            ['password' => 'password123'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_missing_password_fails_validation(): void
    {
        $request = new LoginRequest();
        $validator = $this->app['validator']->make(
            ['email' => 'test@example.com'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_invalid_email_format_fails_validation(): void
    {
        $request = new LoginRequest();
        $validator = $this->app['validator']->make(
            [
                'email' => 'not-an-email',
                'password' => 'password123',
            ],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_valid_email_and_password_passes_validation(): void
    {
        $request = new LoginRequest();
        $validator = $this->app['validator']->make(
            [
                'email' => 'test@example.com',
                'password' => 'password123',
            ],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_empty_email_fails_validation(): void
    {
        $request = new LoginRequest();
        $validator = $this->app['validator']->make(
            [
                'email' => '',
                'password' => 'password123',
            ],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_empty_password_fails_validation(): void
    {
        $request = new LoginRequest();
        $validator = $this->app['validator']->make(
            [
                'email' => 'test@example.com',
                'password' => '',
            ],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
}
