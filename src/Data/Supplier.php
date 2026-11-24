<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * An energy supplier from `Suppliers`.
 *
 * Mirrors MUG's `SupplierDto` definition, with MUG's field names.
 */
final readonly class Supplier
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $websiteUrl,
        public ?string $billSampleUrl,
        public ?bool $hasWarmHomeDiscount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Values::int($data, 'id'),
            name: Values::string($data, 'name'),
            websiteUrl: Values::string($data, 'websiteUrl'),
            billSampleUrl: Values::string($data, 'billSampleUrl'),
            hasWarmHomeDiscount: Values::bool($data, 'hasWarmHomeDiscount'),
        );
    }
}
