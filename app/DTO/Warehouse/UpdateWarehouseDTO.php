<?php
declare(strict_types=1);

namespace App\DTO\Warehouse;

readonly class UpdateWarehouseDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $location = null,
        public ?int $capacity = null,
        public ?bool $isActive = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            location: $data['location'] ?? null,
            capacity: $data['capacity'] ?? null,
            isActive: $data['is_active'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'is_active' => $this->isActive,
        ], fn ($value) => $value !== null);
    }
}
