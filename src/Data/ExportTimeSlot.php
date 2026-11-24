<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * When in the year, week and day an export rate is paid.
 *
 * Mirrors MUG's `ExportTimeSlot` definition, with MUG's field names.
 */
final readonly class ExportTimeSlot
{
    public function __construct(
        public ?string $monthFrom,
        public ?string $monthTo,
        public ?string $weekdayFrom,
        public ?string $weekdayTo,
        public ?string $timeFrom,
        public ?string $timeTo,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            monthFrom: Values::string($data, 'monthFrom'),
            monthTo: Values::string($data, 'monthTo'),
            weekdayFrom: Values::string($data, 'weekdayFrom'),
            weekdayTo: Values::string($data, 'weekdayTo'),
            timeFrom: Values::string($data, 'timeFrom'),
            timeTo: Values::string($data, 'timeTo'),
        );
    }
}
