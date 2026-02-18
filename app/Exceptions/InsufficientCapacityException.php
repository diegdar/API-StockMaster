<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientCapacityException extends Exception
{
    /**
     * Create a new insufficient capacity exception.
     *
     * @param int $availableCapacity
     * @param int $requestedCapacity
     */
    public function __construct(
        public readonly int $availableCapacity,
        public readonly int $requestedCapacity
    ) {
        parent::__construct(
            "Insufficient capacity in destination warehouse. Available: {$availableCapacity}, Requested: {$requestedCapacity}"
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
            'message' => 'Insufficient capacity in destination warehouse',
            'errors' => [
                'destination_warehouse' => ["Available capacity: {$this->availableCapacity}, Requested: {$this->requestedCapacity}"]
            ]
        ], 422);
    }
}
