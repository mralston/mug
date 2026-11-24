<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Enums\PaymentType;
use Mralston\Mug\Support\Values;

/**
 * One distribution region's prices for a tariff from `Tariffs/tou`.
 *
 * Mirrors MUG's `FuelGroupPriceList` definition, with MUG's field names.
 */
final readonly class TouPriceGroup
{
    public function __construct(
        public ?int $dno,
        public ?int $paymentId,
        public ?bool $isDualFuel,
        /** @var list<PriceBand> */
        public array $electricity,
        /** @var list<PriceBand> */
        public array $gas,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dno: Values::int($data, 'dno'),
            paymentId: Values::int($data, 'paymentId'),
            isDualFuel: Values::bool($data, 'isDualFuel'),
            electricity: array_map(PriceBand::fromArray(...), Values::objects($data, 'electricity')),
            gas: array_map(PriceBand::fromArray(...), Values::objects($data, 'gas')),
        );
    }

    /**
     * The region, when MUG's `dno` is a distributor id (10 to 23); null when it is not.
     */
    public function region(): ?DnoRegion
    {
        return $this->dno === null ? null : DnoRegion::tryFrom($this->dno);
    }

    /**
     * The payment type, from `paymentId`.
     */
    public function paymentType(): ?PaymentType
    {
        return $this->paymentId === null ? null : PaymentType::tryFrom($this->paymentId);
    }
}
