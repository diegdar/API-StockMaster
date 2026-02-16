<?php
declare(strict_types=1);

namespace App\DTO\Category;

readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], fn ($value) => $value !== null);
    }
}
