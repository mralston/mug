<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * One export rate and the time slots it is paid in.
 *
 * Mirrors MUG's `ExportFuelGroupPrice` definition, with MUG's field names.
 */
final readonly class ExportRate
{
    public function __construct(
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate,
        public ?string $displayName,
        /** @var list<ExportTimeSlot> */
        public array $periods,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            unitRate: Values::decimal($data, 'unitRate'),
            displayName: Values::string($data, 'displayName'),
            periods: array_map(ExportTimeSlot::fromArray(...), Values::objects($data, 'periods')),
        );
    }
}
