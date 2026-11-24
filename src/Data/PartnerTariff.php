<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * A tariff from `partners/tariff`, with its prices.
 *
 * Mirrors MUG's `DualTariff` definition, with MUG's field names.
 */
final readonly class PartnerTariff
{
    public function __construct(
        public ?string $fuelType,
        public ?string $supplierName,
        public ?string $tariffName,
        public ?string $exitFeeElec,
        public ?string $exitFeeGas,
        public ?string $exitFeeCombined,
        public ?string $exitFeeEndDate,
        public ?string $fixedTermMonths,
        public ?string $fixedTermEndDate,
        /** @var list<string> */
        public array $tariffTypes,
        /** @var list<PartnerPrice> */
        public array $prices,
        public ?int $supplierId,
        public ?string $supplierRef,
        public ?string $vatType,
        public ?string $featureText1,
        public ?string $featureText2,
        public ?string $featureText3,
        public ?string $featureText4,
        public ?string $featureText5,
        public ?string $featureText6,
        public ?string $featureText7,
        public ?string $featureText8,
        public ?string $featureText9,
        public ?string $featureText10,
        public ?string $featureText11,
        public ?int $minKWHAllowedGas,
        public ?int $maxKWHAllowedGas,
        public ?int $minKWHAllowedElec,
        public ?int $maxKWHAllowedElec,
        public ?string $supplierPhoneCancellation,
        public ?bool $warmHomeDiscountFlag,
        public ?string $additionalProductsTextElec,
        public ?string $additionalProductsTextGas,
        public ?string $priceGuaranteedText,
        public ?string $incentivesText,
        public ?string $keyFactsText,
        public ?string $straplineText,
        public ?bool $postcodeRestrictedFlag,
        public ?bool $smartMeterFlag,
        public ?bool $exclusiveFlag,
        public ?string $smartMeterType,
        public ?string $tariffTermsPdf,
        public ?string $supplierLogo,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fuelType: Values::string($data, 'fuelType'),
            supplierName: Values::string($data, 'supplierName'),
            tariffName: Values::string($data, 'tariffName'),
            exitFeeElec: Values::string($data, 'exitFeeElec'),
            exitFeeGas: Values::string($data, 'exitFeeGas'),
            exitFeeCombined: Values::string($data, 'exitFeeCombined'),
            exitFeeEndDate: Values::string($data, 'exitFeeEndDate'),
            fixedTermMonths: Values::string($data, 'fixedTermMonths'),
            fixedTermEndDate: Values::string($data, 'fixedTermEndDate'),
            tariffTypes: Values::strings($data, 'tariffTypes'),
            prices: array_map(PartnerPrice::fromArray(...), Values::objects($data, 'prices')),
            supplierId: Values::int($data, 'supplierId'),
            supplierRef: Values::string($data, 'supplierRef'),
            vatType: Values::string($data, 'vatType'),
            featureText1: Values::string($data, 'featureText1'),
            featureText2: Values::string($data, 'featureText2'),
            featureText3: Values::string($data, 'featureText3'),
            featureText4: Values::string($data, 'featureText4'),
            featureText5: Values::string($data, 'featureText5'),
            featureText6: Values::string($data, 'featureText6'),
            featureText7: Values::string($data, 'featureText7'),
            featureText8: Values::string($data, 'featureText8'),
            featureText9: Values::string($data, 'featureText9'),
            featureText10: Values::string($data, 'featureText10'),
            featureText11: Values::string($data, 'featureText11'),
            minKWHAllowedGas: Values::int($data, 'minKWHAllowedGas'),
            maxKWHAllowedGas: Values::int($data, 'maxKWHAllowedGas'),
            minKWHAllowedElec: Values::int($data, 'minKWHAllowedElec'),
            maxKWHAllowedElec: Values::int($data, 'maxKWHAllowedElec'),
            supplierPhoneCancellation: Values::string($data, 'supplierPhoneCancellation'),
            warmHomeDiscountFlag: Values::bool($data, 'warmHomeDiscountFlag'),
            additionalProductsTextElec: Values::string($data, 'additionalProductsTextElec'),
            additionalProductsTextGas: Values::string($data, 'additionalProductsTextGas'),
            priceGuaranteedText: Values::string($data, 'priceGuaranteedText'),
            incentivesText: Values::string($data, 'incentivesText'),
            keyFactsText: Values::string($data, 'keyFactsText'),
            straplineText: Values::string($data, 'straplineText'),
            postcodeRestrictedFlag: Values::bool($data, 'postcodeRestrictedFlag'),
            smartMeterFlag: Values::bool($data, 'smartMeterFlag'),
            exclusiveFlag: Values::bool($data, 'exclusiveFlag'),
            smartMeterType: Values::string($data, 'smartMeterType'),
            tariffTermsPdf: Values::string($data, 'tariffTermsPdf'),
            supplierLogo: Values::string($data, 'supplierLogo'),
        );
    }
}
