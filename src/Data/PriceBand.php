<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * One price band of a tariff: a unit rate and standing charge, and when in the year, week and day it applies.
 *
 * Mirrors MUG's `FuelGroupPrice` definition, with MUG's field names.
 */
final readonly class PriceBand
{
    public function __construct(
        /** Decimal string exactly as MUG sent it. */
        public ?string $standingCharge,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate,
        public ?string $displayName,
        public ?string $monthFrom,
        public ?string $monthTo,
        public ?string $weekdayFrom,
        public ?string $weekdayTo,
        public ?string $timeFrom,
        public ?string $timeTo,
        public ?string $productCode,
        /** Decimal string exactly as MUG sent it. */
        public ?string $thresholdUnits,
        /** Decimal string exactly as MUG sent it. */
        public ?string $thresholdDays,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            standingCharge: Values::decimal($data, 'standingCharge'),
            unitRate: Values::decimal($data, 'unitRate'),
            displayName: Values::string($data, 'displayName'),
            monthFrom: Values::string($data, 'monthFrom'),
            monthTo: Values::string($data, 'monthTo'),
            weekdayFrom: Values::string($data, 'weekdayFrom'),
            weekdayTo: Values::string($data, 'weekdayTo'),
            timeFrom: Values::string($data, 'timeFrom'),
            timeTo: Values::string($data, 'timeTo'),
            productCode: Values::string($data, 'productCode'),
            thresholdUnits: Values::decimal($data, 'thresholdUnits'),
            thresholdDays: Values::decimal($data, 'thresholdDays'),
        );
    }
}
