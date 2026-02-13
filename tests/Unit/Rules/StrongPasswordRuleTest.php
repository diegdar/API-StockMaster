<?php
declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\StrongPassword;
use Tests\TestCase;

class StrongPasswordRuleTest extends TestCase
{
    public function test_empty_password_fails(): void
    {
        $rule = new StrongPassword();
        $this->assertFalse($rule->passes('password', ''));
    }

    public function test_password_too_short_fails(): void
    {
        $rule = new StrongPassword();
        $this->assertFalse($rule->passes('password', 'Pass1!'));
    }

    public function test_password_without_uppercase_fails(): void
    {
        $rule = new StrongPassword();
        $this->assertFalse($rule->passes('password', 'password123!'));
    }

    public function test_password_without_special_char_fails(): void
    {
        $rule = new StrongPassword();
        $this->assertFalse($rule->passes('password', 'Password123'));
    }

    public function test_valid_password_passes(): void
    {
        $rule = new StrongPassword();
        $this->assertTrue($rule->passes('password', 'Password123!'));
    }

    public function test_valid_password_with_different_special_char_passes(): void
    {
        $rule = new StrongPassword();
        $this->assertTrue($rule->passes('password', 'SecureP@ss'));
    }
}
