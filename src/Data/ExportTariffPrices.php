<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * An export tariff with its rates for each distribution region, from `Tariffs/export/tariff/{tariffId}/prices`.
 *
 * Mirrors MUG's `ExportTariffPricesDto` definition, with MUG's field names.
 */
final readonly class ExportTariffPrices
{
    public function __construct(
        public ?int $tariffId,
        public ?int $supplierId,
        public ?string $tariffName,
        public ?string $supplierName,
        /** @var list<ExportPrice> */
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
            prices: array_map(ExportPrice::fromArray(...), Values::objects($data, 'prices')),
        );
    }
}
