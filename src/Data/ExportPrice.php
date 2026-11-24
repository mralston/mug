<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Support\Values;

/**
 * One distribution region's export rates.
 *
 * Mirrors MUG's `ExportPrice` definition, with MUG's field names.
 */
final readonly class ExportPrice
{
    public function __construct(
        public ?int $dno,
        public ?int $paymentId,
        /** @var list<ExportRate> */
        public array $rates,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dno: Values::int($data, 'dno'),
            paymentId: Values::int($data, 'paymentId'),
            rates: array_map(ExportRate::fromArray(...), Values::objects($data, 'rates')),
        );
    }

    /**
     * The region, when MUG's `dno` is a distributor id (10 to 23); null when it is not.
     */
    public function region(): ?DnoRegion
    {
        return $this->dno === null ? null : DnoRegion::tryFrom($this->dno);
    }
}
