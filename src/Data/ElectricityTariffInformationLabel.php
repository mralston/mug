<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * The electricity part of a Tariff Information Label. MUG gives every figure as display text.
 *
 * Mirrors MUG's `ElectricityTariffInformationLabel` definition, with MUG's field names.
 */
final readonly class ElectricityTariffInformationLabel
{
    public function __construct(
        public ?string $supplier,
        public ?string $tariffName,
        public ?string $tariffType,
        public ?string $paymentMethod,
        public ?string $unitRateDay,
        public ?string $unitRateDayPrimary,
        public ?string $unitRateDaySecondary,
        public ?string $unitRateNight,
        public ?string $standingCharge,
        public ?string $tariffEndsOn,
        public ?string $priceGuaranteedUntil,
        public ?string $exitFees,
        /** @var list<string> */
        public array $discountsAndAdditionalCharges,
        /** @var list<string> */
        public array $additionalProductsAndServices,
        public ?string $assumedAnnualConsumption,
        public ?string $assumedAnnualCost,
        public ?string $tariffComparisonRate,
        public ?string $caveat,
        public ?int $fixedMonths,
        public ?bool $fixedDynamic,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            supplier: Values::string($data, 'supplier'),
            tariffName: Values::string($data, 'tariffName'),
            tariffType: Values::string($data, 'tariffType'),
            paymentMethod: Values::string($data, 'paymentMethod'),
            unitRateDay: Values::string($data, 'unitRateDay'),
            unitRateDayPrimary: Values::string($data, 'unitRateDayPrimary'),
            unitRateDaySecondary: Values::string($data, 'unitRateDaySecondary'),
            unitRateNight: Values::string($data, 'unitRateNight'),
            standingCharge: Values::string($data, 'standingCharge'),
            tariffEndsOn: Values::string($data, 'tariffEndsOn'),
            priceGuaranteedUntil: Values::string($data, 'priceGuaranteedUntil'),
            exitFees: Values::string($data, 'exitFees'),
            discountsAndAdditionalCharges: Values::strings($data, 'discountsAndAdditionalCharges'),
            additionalProductsAndServices: Values::strings($data, 'additionalProductsAndServices'),
            assumedAnnualConsumption: Values::string($data, 'assumedAnnualConsumption'),
            assumedAnnualCost: Values::string($data, 'assumedAnnualCost'),
            tariffComparisonRate: Values::string($data, 'tariffComparisonRate'),
            caveat: Values::string($data, 'caveat'),
            fixedMonths: Values::int($data, 'fixedMonths'),
            fixedDynamic: Values::bool($data, 'fixedDynamic'),
        );
    }
}
