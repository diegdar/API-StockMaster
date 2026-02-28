<?php
declare(strict_types=1);

namespace App\DTO\Supplier;

readonly class CreateSupplierDTO
{
    public function __construct(
        public string $name,
        public ?string $contactEmail = null,
        public ?string $phone = null,
        public ?string $address = null
    ) {}

    /**
     * Create DTO from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            contactEmail: $data['contact_email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null
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
        ];
    }
}
