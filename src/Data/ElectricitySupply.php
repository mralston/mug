<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Support\Values;

/**
 * An electricity supply (one MPAN) at an address, from the industry data.
 *
 * Mirrors MUG's `EXElecDetails` definition, with MUG's field names.
 */
final readonly class ElectricitySupply
{
    public function __construct(
        public ?string $mpan,
        public ?string $meterType,
        public ?string $meterTypeDescription,
        public ?string $meterSerialNumber,
        public ?string $consumerType,
        public ?string $profileClass,
        public ?string $supplierMPID,
        public ?string $currentSupplierName,
        public ?string $meteredIndicator,
        public ?string $meteredIndicatorDescription,
        public ?string $domesticConsumerIndicator,
        public ?string $supplierEfd,
        public ?string $rmpState,
        public ?string $lineLossFactor,
        public ?string $meterTimeswitchClass,
        public ?string $ssc,
        public ?string $installationSupplierName,
        public ?int $mugSupplierId,
        public ?bool $isPrepayment,
        public ?bool $isSmartMeter,
        public ?bool $isDomestic,
        public ?bool $isEconomy7,
        public ?string $tradingStatus,
        public ?string $meterStatus,
        public ?string $meterStatusDescription,
        public ?string $greendealInEffect,
        public ?string $energisationStatusEfd,
        public ?string $tradingStatusEfd,
        /** Decimal string exactly as MUG sent it. */
        public ?string $totalEac,
        public ?string $eacEfdate,
        public ?string $mpanCore,
        public ?string $energyDirection,
        public ?string $energyDirectionEfFromDate,
        public ?string $energyDirectionEfToDate,
        public ?string $ihdStatus,
        public ?string $ihdStatusEfd,
        public ?string $gspGroupId,
        public ?string $gspRegionId,
        public ?string $mhhsIndicator,
        public ?string $mhhsIndicatorEfd,
        public ?string $duosTariffId,
        public ?string $duosTariffIdEfd,
        public ?string $annualConsumption,
        public ?string $annualConsumptionEfd,
        public ?string $annualConsumptionQualityIndicator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mpan: Values::string($data, 'mpan'),
            meterType: Values::string($data, 'meterType'),
            meterTypeDescription: Values::string($data, 'meterTypeDescription'),
            meterSerialNumber: Values::string($data, 'meterSerialNumber'),
            consumerType: Values::string($data, 'consumerType'),
            profileClass: Values::string($data, 'profileClass'),
            supplierMPID: Values::string($data, 'supplierMPID'),
            currentSupplierName: Values::string($data, 'currentSupplierName'),
            meteredIndicator: Values::string($data, 'meteredIndicator'),
            meteredIndicatorDescription: Values::string($data, 'meteredIndicatorDescription'),
            domesticConsumerIndicator: Values::string($data, 'domesticConsumerIndicator'),
            supplierEfd: Values::string($data, 'supplierEfd'),
            rmpState: Values::string($data, 'rmpState'),
            lineLossFactor: Values::string($data, 'lineLossFactor'),
            meterTimeswitchClass: Values::string($data, 'meterTimeswitchClass'),
            ssc: Values::string($data, 'ssc'),
            installationSupplierName: Values::string($data, 'installationSupplierName'),
            mugSupplierId: Values::int($data, 'mugSupplierId'),
            isPrepayment: Values::bool($data, 'isPrepayment'),
            isSmartMeter: Values::bool($data, 'isSmartMeter'),
            isDomestic: Values::bool($data, 'isDomestic'),
            isEconomy7: Values::bool($data, 'isEconomy7'),
            tradingStatus: Values::string($data, 'tradingStatus'),
            meterStatus: Values::string($data, 'meterStatus'),
            meterStatusDescription: Values::string($data, 'meterStatusDescription'),
            greendealInEffect: Values::string($data, 'greendealInEffect'),
            energisationStatusEfd: Values::string($data, 'energisationStatusEfd'),
            tradingStatusEfd: Values::string($data, 'tradingStatusEfd'),
            totalEac: Values::decimal($data, 'totalEac'),
            eacEfdate: Values::string($data, 'eacEfdate'),
            mpanCore: Values::string($data, 'mpancore'),
            energyDirection: Values::string($data, 'energyDirection'),
            energyDirectionEfFromDate: Values::string($data, 'energyDirectionEfFromDate'),
            energyDirectionEfToDate: Values::string($data, 'energyDirectionEfToDate'),
            ihdStatus: Values::string($data, 'ihdStatus'),
            ihdStatusEfd: Values::string($data, 'ihdStausEfd'),
            gspGroupId: Values::string($data, 'gspGroupId'),
            gspRegionId: Values::string($data, 'gspRegionId'),
            mhhsIndicator: Values::string($data, 'mhhsIndicator'),
            mhhsIndicatorEfd: Values::string($data, 'mhhsIndicatorEFD'),
            duosTariffId: Values::string($data, 'duosTariffId'),
            duosTariffIdEfd: Values::string($data, 'duosTariffIdEFD'),
            annualConsumption: Values::string($data, 'annualConsumption'),
            annualConsumptionEfd: Values::string($data, 'annualConsumptionEFD'),
            annualConsumptionQualityIndicator: Values::string($data, 'annualConsumptionQualityIndicator'),
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
