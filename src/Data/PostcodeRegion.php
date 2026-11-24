<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Support\Values;

/**
 * Whether a postcode can be switched and the region it is in, from `Address/Postcode/Ready`.
 *
 * Mirrors MUG's `PostcodeReadyDto` definition, with MUG's field names.
 */
final readonly class PostcodeRegion
{
    public function __construct(
        public ?bool $postcodeIsSwitchable,
        public ?string $suggestedFormat,
        public ?string $groupId,
        public ?string $gspGroupId,
        public ?string $gspRegionId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            postcodeIsSwitchable: Values::bool($data, 'postcodeIsSwitchable'),
            suggestedFormat: Values::string($data, 'suggestedFormat'),
            groupId: Values::string($data, 'groupId'),
            gspGroupId: Values::string($data, 'gspGroupId'),
            gspRegionId: Values::string($data, 'gspRegionId'),
        );
    }

    /**
     * The region, from the GSP group id.
     */
    public function region(): ?DnoRegion
    {
        return DnoRegion::tryFromGspGroupId($this->gspGroupId);
    }
}
