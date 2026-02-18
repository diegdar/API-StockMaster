<?php
declare(strict_types=1);

namespace App\Rules;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Rule;

class ActiveWarehouse implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $warehouse = Warehouse::find($value);

        return $warehouse !== null && $warehouse->is_active === true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The selected warehouse is not active.';
    }
}
