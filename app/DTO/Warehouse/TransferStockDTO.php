<?php
declare(strict_types=1);

namespace App\DTO\Warehouse;

use Illuminate\Http\Request;

readonly class TransferStockDTO
{
    /**
     * Create a new Transfer Stock DTO.
     *
     * @param int $productId
     * @param int $sourceWarehouseId
     * @param int $destinationWarehouseId
     * @param int $quantity
     * @param string|null $description
     */
    public function __construct(
        public int $productId,
        public int $sourceWarehouseId,
        public int $destinationWarehouseId,
        public int $quantity,
        public ?string $description = null
    ) {}

    /**
     * Create DTO from validated request data.
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            productId: (int) $request->validated('product_id'),
            sourceWarehouseId: (int) $request->validated('source_warehouse_id'),
            destinationWarehouseId: (int) $request->validated('destination_warehouse_id'),
            quantity: (int) $request->validated('quantity'),
            description: $request->validated('description')
        );
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'source_warehouse_id' => $this->sourceWarehouseId,
            'destination_warehouse_id' => $this->destinationWarehouseId,
            'quantity' => $this->quantity,
            'description' => $this->description,
        ];
    }
}
