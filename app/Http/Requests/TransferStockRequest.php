<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ActiveWarehouse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'source_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                'different:destination_warehouse_id',
            ],
            'destination_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                'different:source_warehouse_id',
                new ActiveWarehouse(),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'The product ID is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'source_warehouse_id.required' => 'The source warehouse ID is required.',
            'source_warehouse_id.exists' => 'The selected source warehouse does not exist.',
            'source_warehouse_id.different' => 'The source and destination warehouses must be different.',
            'destination_warehouse_id.required' => 'The destination warehouse ID is required.',
            'destination_warehouse_id.exists' => 'The selected destination warehouse does not exist.',
            'destination_warehouse_id.different' => 'The source and destination warehouses must be different.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 1.',
            'description.max' => 'The description may not be greater than 500 characters.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
