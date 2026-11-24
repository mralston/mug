<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * A discount on a tariff from `partners/tariff`.
 *
 * Mirrors MUG's `Discount` definition, with MUG's field names.
 */
final readonly class Discount
{
    public function __construct(
        /** Decimal string exactly as MUG sent it. */
        public ?string $value,
        public ?string $label,
        public ?string $fuelType,
        public ?bool $percentage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: Values::decimal($data, 'value'),
            label: Values::string($data, 'label'),
            fuelType: Values::string($data, 'fuelType'),
            percentage: Values::bool($data, 'percentage'),
        );
    }
}
