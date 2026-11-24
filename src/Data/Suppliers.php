<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * The suppliers `Suppliers` offers for a switch, by fuel.
 *
 * Mirrors MUG's `SuppliersDto` definition, with MUG's field names.
 */
final readonly class Suppliers
{
    public function __construct(
        /** @var list<Supplier> */
        public array $dualConsolidatedSuppliers,
        /** @var list<Supplier> */
        public array $electricitySuppliers,
        /** @var list<Supplier> */
        public array $gasSuppliers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dualConsolidatedSuppliers: array_map(Supplier::fromArray(...), Values::objects($data, 'dualConsolidatedSuppliers')),
            electricitySuppliers: array_map(Supplier::fromArray(...), Values::objects($data, 'electricitySuppliers')),
            gasSuppliers: array_map(Supplier::fromArray(...), Values::objects($data, 'gasSuppliers')),
        );
    }
}
