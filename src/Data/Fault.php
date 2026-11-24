<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * A fault the industry data reported for an address, from MUG's `FaultResponse` with its `Detail`
 * folded in.
 */
final readonly class Fault
{
    public function __construct(
        public ?string $faultString,
        public ?string $errorCode,
        public ?string $item,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $detail = is_array($data['detail'] ?? null) ? $data['detail'] : [];

        return new self(
            faultString: Values::string($data, 'faultstring'),
            errorCode: Values::string($detail, 'errorcode'),
            item: Values::string($detail, 'item'),
        );
    }
}
