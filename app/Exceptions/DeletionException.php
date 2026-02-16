<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class DeletionException extends Exception
{
    public function __construct(
        string $message = "Cannot delete record due to existing dependencies.",
        protected int $statusCode = 422,
        // protected string $errorCode = 'ENTITY_HAS_DEPENDENCIES'
    ) {
        parent::__construct($message);
    }

    /**
     * Renders the exception into a JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
            // 'error_code' => $this->errorCode,
        ], $this->statusCode);
    }
}
