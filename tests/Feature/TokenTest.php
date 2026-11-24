<?php

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mralston\Mug\Mug;
use Mralston\Mug\Testing\MugFake;

function tokenRequests(): int
{
    return Http::recorded(fn (Request $request): bool => str_ends_with($request->url(), '/connect/token'))->count();
}

it('signs in once and shares the token between instances through the cache', function () {
    MugFake::register();

    new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->postcodeRegion('ZZ99 9ZZ');
    new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->postcodeRegion('ZZ99 9ZZ');

    expect(tokenRequests())->toBe(1);
});

it('keeps the token until shortly before it expires', function () {
    MugFake::register();
    $this->travelTo(now());

    new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->postcodeRegion('ZZ99 9ZZ');

    $this->travel(3600 - Mug::TOKEN_EXPIRY_MARGIN_SECONDS - 1)->seconds();
    new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->postcodeRegion('ZZ99 9ZZ');
    expect(tokenRequests())->toBe(1);

    $this->travel(2)->seconds();
    new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->postcodeRegion('ZZ99 9ZZ');
    expect(tokenRequests())->toBe(2);
});

it('signs in again and retries once when a cached token is refused', function () {
    Cache::put(
        'mralston-mug:token:'.hash('sha256', 'https://authorisation-staging.myutilitygenius.co.uk|fake-client'),
        'revoked-token',
        600,
    );

    MugFake::register([
        'addressPostcodeReady' => fn (Request $request) => $request->header('Authorization') === ['Bearer revoked-token']
            ? Http::response('', 401)
            : Http::response(MugFake::fixture('addressPostcodeReady')),
    ]);

    $region = new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->postcodeRegion('ZZ99 9ZZ');

    expect($region->postcodeIsSwitchable)->toBeTrue()
        ->and(tokenRequests())->toBe(1)
        ->and(Cache::get('mralston-mug:token:'.hash('sha256', 'https://authorisation-staging.myutilitygenius.co.uk|fake-client')))
        ->toBe('fake-mug-access-token');
});

it('does not retry when a freshly fetched token is refused', function () {
    MugFake::register(['tariffsTou' => Http::response('', 401)]);

    expect(fn () => new Mug('fake-client', 'fake-secret', 'testing', Cache::store())->touTariffs('ZZ99 9ZZ'))
        ->toThrow(RequestException::class);

    expect(tokenRequests())->toBe(1)
        ->and(Http::recorded(fn (Request $request): bool => str_ends_with($request->url(), '/Tariffs/tou'))->count())->toBe(1);
});

it('works without a cache, keeping the token for the instance', function () {
    MugFake::register();

    $mug = new Mug('fake-client', 'fake-secret', 'testing');
    $mug->postcodeRegion('ZZ99 9ZZ');
    $mug->liveTariffs();

    expect(tokenRequests())->toBe(1);
});
