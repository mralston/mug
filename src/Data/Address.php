<?php

namespace Mralston\Mug\Data;

use Mralston\Mug\Support\Values;

/**
 * An address from the industry data (`Address/recco`), with its supply details once `Address/recco/details` has filled them in.
 *
 * Mirrors MUG's `ReccoAddressResponseModel` definition, with MUG's field names.
 */
final readonly class Address
{
    public function __construct(
        public ?string $companyName,
        public ?string $uprn,
        public ?string $addressLine1,
        public ?string $addressLine2,
        public ?string $city,
        public ?string $county,
        public ?string $postcode,
        public ?string $text,
        public ?string $addressType,
        public ?string $longitude,
        public ?string $latitude,
        /** @var list<string> */
        public array $mpanCores,
        /** @var list<string> */
        public array $xoserveAddressCodes,
        /** @var list<ElectricitySupply> */
        public array $electricity,
        /** @var list<GasSupply> */
        public array $gas,
        /** @var list<Fault> */
        public array $faults,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyName: Values::string($data, 'companyName'),
            uprn: Values::string($data, 'uprn'),
            addressLine1: Values::string($data, 'addressLine1'),
            addressLine2: Values::string($data, 'addressLine2'),
            city: Values::string($data, 'city'),
            county: Values::string($data, 'county'),
            postcode: Values::string($data, 'postcode'),
            text: Values::string($data, 'text'),
            addressType: Values::string($data, 'addressType'),
            longitude: Values::string($data, 'longitude'),
            latitude: Values::string($data, 'latitude'),
            mpanCores: Values::strings($data, 'mpancore'),
            xoserveAddressCodes: Values::strings($data, 'xoserveAddressCode'),
            electricity: array_map(ElectricitySupply::fromArray(...), Values::objects($data, 'elecdetails')),
            gas: array_map(GasSupply::fromArray(...), Values::objects($data, 'gasdetails')),
            faults: array_map(Fault::fromArray(...), Values::objects($data, 'fault')),
        );
    }
}
