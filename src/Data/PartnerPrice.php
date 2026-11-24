<?php

namespace Mralston\Mug\Data;

use Carbon\CarbonImmutable;
use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Enums\PaymentType;
use Mralston\Mug\Support\Values;

/**
 * One set of prices for a tariff from `partners/tariff`, for the regions, payment types and meter type it lists.
 *
 * Mirrors MUG's `DualPrice` definition, with MUG's field names.
 */
final readonly class PartnerPrice
{
    public function __construct(
        /** Decimal string exactly as MUG sent it. */
        public ?string $standingChargeGas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $standingChargeElec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate1Gas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate1Elec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate1LimitElec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate1LimitGas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate2Gas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate2Elec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRateE7Elec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate2LimitGas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $unitRate3Gas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $priceLimitKWHMinElec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $priceLimitKWHMaxElec,
        /** Decimal string exactly as MUG sent it. */
        public ?string $priceLimitKWHMinGas,
        /** Decimal string exactly as MUG sent it. */
        public ?string $priceLimitKWHMaxGas,
        public ?string $productCodeElec,
        public ?string $productCodeGas,
        public ?string $productCodeElec2,
        public ?string $productCodeGas2,
        /** @var list<string> */
        public array $distIds,
        /** @var list<int> */
        public array $payTypes,
        /** @var list<Discount> */
        public array $discounts,
        public ?string $meterTypeElec,
        public ?CarbonImmutable $regulatoryStartDate,
        public ?CarbonImmutable $regulatoryEndDate,
        public ?CarbonImmutable $onsaleStartDate,
        public ?CarbonImmutable $onsaleEndDate,
        /** Undescribed in MUG's API description, so passed through as decoded. */
        public mixed $onsaleAnalysisStartDate,
        /** Undescribed in MUG's API description, so passed through as decoded. */
        public mixed $onsaleAnalysisEndDate,
        /** @var list<string> */
        public array $timePeriodsElec,
        /** @var list<string> */
        public array $timePeriodsGas,
        public ?string $directDebitRulesText,
        /** Decimal string exactly as MUG sent it. */
        public ?string $directDebitWinterUpliftPercent,
        public ?string $gasTcr,
        public ?string $elecTcr,
        public ?string $unitRate1LimitPeriodElec,
        public ?string $unitRate1LimitPeriodGas,
        public ?string $productCodeElecgas,
        public ?string $productCodeElecgas2,
        public ?bool $onlineTariffFlag,
        public ?bool $paperlessBillingFlag,
        public ?bool $paperBillingFlag,
        public ?bool $greenFlag,
        public ?bool $exclusiveFlag,
        public ?string $fuelType,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            standingChargeGas: Values::decimal($data, 'standingChargeGas'),
            standingChargeElec: Values::decimal($data, 'standingChargeElec'),
            unitRate1Gas: Values::decimal($data, 'unitRate1Gas'),
            unitRate1Elec: Values::decimal($data, 'unitRate1Elec'),
            unitRate1LimitElec: Values::decimal($data, 'unitRate1LimitElec'),
            unitRate1LimitGas: Values::decimal($data, 'unitRate1LimitGas'),
            unitRate2Gas: Values::decimal($data, 'unitRate2Gas'),
            unitRate2Elec: Values::decimal($data, 'unitRate2Elec'),
            unitRateE7Elec: Values::decimal($data, 'unitRateE7Elec'),
            unitRate2LimitGas: Values::decimal($data, 'unitRate2LimitGas'),
            unitRate3Gas: Values::decimal($data, 'unitRate3Gas'),
            priceLimitKWHMinElec: Values::decimal($data, 'priceLimitKWHMinElec'),
            priceLimitKWHMaxElec: Values::decimal($data, 'priceLimitKWHMaxElec'),
            priceLimitKWHMinGas: Values::decimal($data, 'priceLimitKWHMinGas'),
            priceLimitKWHMaxGas: Values::decimal($data, 'priceLimitKWHMaxGas'),
            productCodeElec: Values::string($data, 'productCodeElec'),
            productCodeGas: Values::string($data, 'productCodeGas'),
            productCodeElec2: Values::string($data, 'productCodeElec2'),
            productCodeGas2: Values::string($data, 'productCodeGas2'),
            distIds: Values::strings($data, 'distIds'),
            payTypes: Values::ints($data, 'payTypes'),
            discounts: array_map(Discount::fromArray(...), Values::objects($data, 'discounts')),
            meterTypeElec: Values::string($data, 'meterTypeElec'),
            regulatoryStartDate: Values::dateTime($data, 'regulatoryStartDate'),
            regulatoryEndDate: Values::dateTime($data, 'regulatoryEndDate'),
            onsaleStartDate: Values::dateTime($data, 'onsaleStartDate'),
            onsaleEndDate: Values::dateTime($data, 'onsaleEndDate'),
            onsaleAnalysisStartDate: $data['onsaleAnalysisStartDate'] ?? null,
            onsaleAnalysisEndDate: $data['onsaleAnalysisEndDate'] ?? null,
            timePeriodsElec: Values::strings($data, 'timePeriodsElec'),
            timePeriodsGas: Values::strings($data, 'timePeriodsGas'),
            directDebitRulesText: Values::string($data, 'directDebitRulesText'),
            directDebitWinterUpliftPercent: Values::decimal($data, 'directDebitWinterUpliftPercent'),
            gasTcr: Values::string($data, 'gasTcr'),
            elecTcr: Values::string($data, 'elecTcr'),
            unitRate1LimitPeriodElec: Values::string($data, 'unitRate1LimitPeriodElec'),
            unitRate1LimitPeriodGas: Values::string($data, 'unitRate1LimitPeriodGas'),
            productCodeElecgas: Values::string($data, 'productCodeElecgas'),
            productCodeElecgas2: Values::string($data, 'productCodeElecgas2'),
            onlineTariffFlag: Values::bool($data, 'onlineTariffFlag'),
            paperlessBillingFlag: Values::bool($data, 'paperlessBillingFlag'),
            paperBillingFlag: Values::bool($data, 'paperBillingFlag'),
            greenFlag: Values::bool($data, 'greenFlag'),
            exclusiveFlag: Values::bool($data, 'exclusiveFlag'),
            fuelType: Values::string($data, 'fuelType'),
        );
    }

    /**
     * The regions these prices apply in, from `distIds`.
     *
     * @return list<DnoRegion>
     */
    public function regions(): array
    {
        return array_values(array_filter(array_map(
            fn (string $distId): ?DnoRegion => is_numeric($distId) ? DnoRegion::tryFrom((int) $distId) : null,
            $this->distIds,
        )));
    }

    /**
     * The payment types these prices apply to, from `payTypes`.
     *
     * @return list<PaymentType>
     */
    public function paymentTypes(): array
    {
        return array_values(array_filter(array_map(PaymentType::tryFrom(...), $this->payTypes)));
    }
}
