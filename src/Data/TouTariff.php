<?php

namespace Mralston\Mug\Data;

use Carbon\CarbonImmutable;
use Mralston\Mug\Support\Values;

/**
 * A tariff from `Tariffs/tou`, with its prices for each distribution region.
 *
 * Mirrors MUG's `ToUTariffDetailsResponse` definition, with MUG's field names.
 */
final readonly class TouTariff
{
    public function __construct(
        public ?int $tariffId,
        public ?int $supplierId,
        public ?string $tariffName,
        public ?string $supplierName,
        public ?CarbonImmutable $validFromDate,
        public ?CarbonImmutable $validToDate,
        public ?CarbonImmutable $liveRemovalDate,
        public ?bool $isLive,
        public ?bool $isSwitchable,
        public ?bool $isGreen,
        public ?bool $isFixed,
        public ?bool $isFixedFromStartDate,
        public ?bool $isSmartMeter,
        public ?string $fixedTermMonths,
        public ?string $tosUrl,
        public ?bool $hasExitFee,
        public ?bool $hasReward,
        public ?bool $hasWinterPremium,
        public ?bool $isStandard,
        public ?int $standardTariffId,
        public ?int $priceType,
        public ?bool $isPriceIncVAT,
        /** @var list<TouPriceGroup> */
        public array $prices,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tariffId: Values::int($data, 'tariffId'),
            supplierId: Values::int($data, 'supplierId'),
            tariffName: Values::string($data, 'tariffName'),
            supplierName: Values::string($data, 'supplierName'),
            validFromDate: Values::dateTime($data, 'validFromDate'),
            validToDate: Values::dateTime($data, 'validToDate'),
            liveRemovalDate: Values::dateTime($data, 'liveRemovalDate'),
            isLive: Values::bool($data, 'isLive'),
            isSwitchable: Values::bool($data, 'isSwitchable'),
            isGreen: Values::bool($data, 'isGreen'),
            isFixed: Values::bool($data, 'isFixed'),
            isFixedFromStartDate: Values::bool($data, 'isFixedFromStartDate'),
            isSmartMeter: Values::bool($data, 'isSmartMeter'),
            fixedTermMonths: Values::string($data, 'fixedTermMonths'),
            tosUrl: Values::string($data, 'tosUrl'),
            hasExitFee: Values::bool($data, 'hasExitFee'),
            hasReward: Values::bool($data, 'hasReward'),
            hasWinterPremium: Values::bool($data, 'hasWinterPremium'),
            isStandard: Values::bool($data, 'isStandard'),
            standardTariffId: Values::int($data, 'standardTaridffId'),
            priceType: Values::int($data, 'priceType'),
            isPriceIncVAT: Values::bool($data, 'isPriceIncVAT'),
            prices: array_map(TouPriceGroup::fromArray(...), Values::objects($data, 'prices')),
        );
    }
}
