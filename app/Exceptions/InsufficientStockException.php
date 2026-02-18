<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientStockException extends Exception
{
    /**
     * Create a new insufficient stock exception.
     *
     * @param int $available
     * @param int $requested
     */
    public function __construct(
        public readonly int $available,
        public readonly int $requested
    ) {
        parent::__construct(
            "Insufficient stock. Available: {$available}, Requested: {$requested}"
        );
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Insufficient stock in source warehouse',
            'errors' => [
                'quantity' => ["Available stock: {$this->available}, Requested: {$this->requested}"]
            ]
        ], 422);
    }
}
