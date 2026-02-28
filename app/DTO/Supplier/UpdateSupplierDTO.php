<?php
declare(strict_types=1);

namespace App\DTO\Supplier;

readonly class UpdateSupplierDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $contactEmail = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?bool $isActive = null
    ) {}

    /**
     * Create DTO from array data (partial updates allowed).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            contactEmail: $data['contact_email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            isActive: $data['is_active'] ?? null
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'contact_email' => $this->contactEmail,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->isActive,
        ];
    }
}
