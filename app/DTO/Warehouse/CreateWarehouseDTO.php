<?php
declare(strict_types=1);

namespace App\DTO\Warehouse;

readonly class CreateWarehouseDTO
{
    public function __construct(
        public string $name,
        public string $location,
        public ?int $capacity = null,
        public bool $isActive = true
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            location: $data['location'],
            capacity: $data['capacity'] ?? null,
            isActive: $data['is_active'] ?? true
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
