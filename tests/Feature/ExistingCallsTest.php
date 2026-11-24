<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Mralston\Mug\Facades\Mug;

beforeEach(fn () => Mug::fake());

it('still answers whether a postcode is ready to switch', function () {
    expect(Mug::addressPostcodeReady('ZZ99 9ZZ'))->toBeTrue()
        ->and(Mug::addressPostcodeReady(''))->toBeFalse();
});

it('still lists addresses as arrays sorted by their first line', function () {
    $addresses = Mug::addressRecco('ZZ99 9ZZ');

    expect($addresses)->toBeInstanceOf(Collection::class)
        ->and($addresses->first()['addressLine1'])->toBe('1 Example Street')
        ->and($addresses->first()['mpancore'])->toBe(['1999999999001']);
});

it('still returns supply details as an array, with the MPAN as given', function () {
    $details = Mug::addressReccoDetails(Mug::addressRecco('ZZ99 9ZZ')->first());

    expect($details)->toBeArray()
        ->and($details['elecdetails'][0]['mpan'])->toBe('028018011999999999001');

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/request/Address/recco/details')
        && $request['mpanCores'] === ['1999999999001']
        && $request['xoserveAddressCodes'] === ['9999999001']
        && $request['isElectricityEacNeeded'] === true);
});

it('names itself in the user agent on every request, the token request included', function () {
    Mug::addressPostcodeReady('ZZ99 9ZZ');

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/connect/token')
        && $request->header('User-Agent') === ['mralston-mug/'.Mralston\Mug\Mug::VERSION]);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/Postcode/Ready')
        && $request->header('User-Agent') === ['mralston-mug/'.Mralston\Mug\Mug::VERSION]
        && $request->header('Authorization') === ['Bearer fake-mug-access-token']);
});
