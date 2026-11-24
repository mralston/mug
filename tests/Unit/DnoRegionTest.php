<?php

use Mralston\Mug\Enums\DnoRegion;

it('has the 14 distribution regions with distinct GSP groups', function () {
    $groups = array_map(fn (DnoRegion $region): string => $region->gspGroupId(), DnoRegion::cases());

    expect(DnoRegion::cases())->toHaveCount(14)
        ->and(array_unique($groups))->toHaveCount(14);
});

it('finds a region from its GSP group id, with or without the underscore', function () {
    expect(DnoRegion::tryFromGspGroupId('_J'))->toBe(DnoRegion::SouthEastern)
        ->and(DnoRegion::tryFromGspGroupId('p'))->toBe(DnoRegion::NorthernScotland)
        ->and(DnoRegion::tryFromGspGroupId('_Z'))->toBeNull()
        ->and(DnoRegion::tryFromGspGroupId(null))->toBeNull()
        ->and(DnoRegion::from(12)->label())->toBe('London');
});
