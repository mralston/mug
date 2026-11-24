<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * A gas supply (one MPRN) at an address, from the industry data.
 *
 * Mirrors MUG's `ReccoGasDetails` definition, with MUG's field names.
 */
final readonly class GasSupply
{
    public function __construct(
        public ?string $mprn,
        public ?string $mpaq,
        public ?string $ndmq,
        public ?string $dmq,
        public ?string $ldzId,
        public ?string $meterType,
        public ?string $meterTypeDescription,
        public ?string $currentSupplierId,
        public ?string $currentSupplierName,
        public ?string $meterSerialNumber,
        public ?string $smartEquipmentTechnicalCode,
        public ?bool $isPrepayment,
        public ?bool $isSmartMeter,
        public ?int $mugSupplierId,
        public ?string $previousSupplierName,
        public ?string $previousSupplierCode,
        public ?string $incomingSupplierCode,
        public ?string $supplierEfd,
        public ?string $pendingSupplierRegEffDate,
        public ?string $marketSectorCode,
        public ?string $amrIndicator,
        public ?string $lastMeterReadDate,
        public ?string $lastMeterReadType,
        public ?string $lastMeterReadValue,
        public ?string $uprn,
        public ?string $meterStatus,
        public ?string $meterStatusDescription,
        public ?bool $isDomestic,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mprn: Values::string($data, 'mprn'),
            mpaq: Values::string($data, 'mpaq'),
            ndmq: Values::string($data, 'ndmq'),
            dmq: Values::string($data, 'dmq'),
            ldzId: Values::string($data, 'ldzId'),
            meterType: Values::string($data, 'meterType'),
            meterTypeDescription: Values::string($data, 'meterTypeDescription'),
            currentSupplierId: Values::string($data, 'currentSupplierId'),
            currentSupplierName: Values::string($data, 'currentSupplierName'),
            meterSerialNumber: Values::string($data, 'meterSerialNumber'),
            smartEquipmentTechnicalCode: Values::string($data, 'smartEquipmentTechnicalCode'),
            isPrepayment: Values::bool($data, 'isPrepayment'),
            isSmartMeter: Values::bool($data, 'isSmartMeter'),
            mugSupplierId: Values::int($data, 'mugSupplierId'),
            previousSupplierName: Values::string($data, 'previousSupplierName'),
            previousSupplierCode: Values::string($data, 'previousSupplierCode'),
            incomingSupplierCode: Values::string($data, 'incomingSupplierCode'),
            supplierEfd: Values::string($data, 'supplierEfd'),
            pendingSupplierRegEffDate: Values::string($data, 'pendingSupplierRegEffDate'),
            marketSectorCode: Values::string($data, 'marketSectorCode'),
            amrIndicator: Values::string($data, 'amrIndicator'),
            lastMeterReadDate: Values::string($data, 'lastMeterReadDate'),
            lastMeterReadType: Values::string($data, 'lastMeterReadType'),
            lastMeterReadValue: Values::string($data, 'lastMeterReadValue'),
            uprn: Values::string($data, 'uprn'),
            meterStatus: Values::string($data, 'meterStatus'),
            meterStatusDescription: Values::string($data, 'meterStatusDescription'),
            isDomestic: Values::bool($data, 'isDomestic'),
        );
    }
}
