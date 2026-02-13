<?php
declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        // Mínimo 8 caracteres
        if (strlen($value) < 8) {
            return false;
        }

        // Al menos una mayúscula
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }

        // Al menos un carácter especial
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute must be at least 8 characters, contain at least one uppercase letter and one special character.';
    }
}
