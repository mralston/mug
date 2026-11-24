<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Support\Values;

/**
 * A tariff as `Tariffs/live`, `Tariffs/export` and `Tariffs/all` list it, without prices.
 *
 * Mirrors MUG's `TariffDto` definition, with MUG's field names.
 */
final readonly class TariffSummary
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?bool $isDefaultOrStandard,
        public ?int $dnoAreaId,
        public ?string $dnoAreaName,
        public ?bool $isDefaultOrStandardPaperBilling,
        public ?bool $isDefaultOrStandardPaperlessBilling,
        public ?bool $isFixedFromEnergyStartDate,
        public ?bool $isFixed,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Values::int($data, 'id'),
            name: Values::string($data, 'name'),
            isDefaultOrStandard: Values::bool($data, 'isDefaultOrStandard'),
            dnoAreaId: Values::int($data, 'dnoAreaId'),
            dnoAreaName: Values::string($data, 'dnoAreaName'),
            isDefaultOrStandardPaperBilling: Values::bool($data, 'isDefaultOrStandardPaperBilling'),
            isDefaultOrStandardPaperlessBilling: Values::bool($data, 'isDefaultOrStandardPaperlessBilling'),
            isFixedFromEnergyStartDate: Values::bool($data, 'isFixedFromEnergyStartDate'),
            isFixed: Values::bool($data, 'isFixed'),
        );
    }

    /**
     * The region, when MUG's `dnoAreaId` is a distributor id (10 to 23); null when it is not.
     */
    public function region(): ?DnoRegion
    {
        return $this->dnoAreaId === null ? null : DnoRegion::tryFrom($this->dnoAreaId);
    }
}
