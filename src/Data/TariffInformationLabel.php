<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * The Tariff Information Label for a tariff, from `Til/Direct`.
 *
 * Mirrors MUG's `TilDto` definition, with MUG's field names.
 */
final readonly class TariffInformationLabel
{
    public function __construct(
        public ?ElectricityTariffInformationLabel $electricity,
        public ?GasTariffInformationLabel $gas,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            electricity: Values::object($data, 'electricityTariffInformationLabel', ElectricityTariffInformationLabel::fromArray(...)),
            gas: Values::object($data, 'gasTariffInformationLabel', GasTariffInformationLabel::fromArray(...)),
        );
    }
}
