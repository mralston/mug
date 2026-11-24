<?php

namespace Mralston\Mug\Testing;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Fakes MUG at the HTTP layer with responses in the shapes MUG's API description gives, so the
 * package's own requests and mapping run unchanged in a host application's tests. Every value in
 * the fixtures is made up.
 *
 * Mug::fake() registers every endpoint; pass overrides keyed by endpoint name to change one, such
 * as `['tariffsTou' => Http::response('', 401)]`.
 */
final class MugFake
{
    /**
     * Each endpoint's name, its URL pattern for Http::fake() and its fixture. More specific patterns
     * come first, since the first pattern a URL matches answers it.
     *
     * @var array<string, array{pattern: string, fixture: string}>
     */
    public const array ENDPOINTS = [
        'token' => ['pattern' => '*/connect/token', 'fixture' => 'token.json'],
        'addressPostcodeReady' => ['pattern' => '*/request/Address/Postcode/Ready', 'fixture' => 'address-postcode-ready.json'],
        'addressReccoDetails' => ['pattern' => '*/request/Address/recco/details', 'fixture' => 'address-recco-details.json'],
        'addressRecco' => ['pattern' => '*/request/Address/recco?*', 'fixture' => 'address-recco.json'],
        'tariffsTou' => ['pattern' => '*/request/Tariffs/tou', 'fixture' => 'tariffs-tou.json'],
        'tariffsLive' => ['pattern' => '*/request/Tariffs/live*', 'fixture' => 'tariffs-live.json'],
        'tariffsExportPrices' => ['pattern' => '*/request/Tariffs/export/tariff/*/prices', 'fixture' => 'tariffs-export-prices.json'],
        'tariffsExport' => ['pattern' => '*/request/Tariffs/export?*', 'fixture' => 'tariffs-export.json'],
        'partnersTariff' => ['pattern' => '*/request/partners/tariff', 'fixture' => 'partners-tariff.json'],
        'tilDirect' => ['pattern' => '*/request/Til/Direct', 'fixture' => 'til-direct.json'],
        'suppliers' => ['pattern' => '*/request/Suppliers', 'fixture' => 'suppliers.json'],
    ];

    private const string FIXTURE_DIRECTORY = __DIR__.'/../../tests/fixtures';

    /**
     * @param  array<string, PromiseInterface|callable>  $overrides  Responses by endpoint name.
     */
    public static function register(array $overrides = []): void
    {
        Http::fake(self::responses($overrides));
    }

    /**
     * The URL patterns and responses for Http::fake().
     *
     * @param  array<string, PromiseInterface|callable>  $overrides  Responses by endpoint name.
     * @return array<string, PromiseInterface|callable>
     */
    public static function responses(array $overrides = []): array
    {
        $unknown = array_diff(array_keys($overrides), array_keys(self::ENDPOINTS));

        if ($unknown !== []) {
            throw new InvalidArgumentException('No MUG endpoint is named '.implode(', ', $unknown).'.');
        }

        $responses = [];

        foreach (self::ENDPOINTS as $name => $endpoint) {
            $responses[$endpoint['pattern']] = $overrides[$name]
                ?? Http::response(self::fixture($name), 200, ['Content-Type' => 'application/json']);
        }

        return $responses;
    }

    /**
     * An endpoint's fixture as raw JSON, with its numbers exactly as written.
     */
    public static function fixture(string $endpoint): string
    {
        if (! isset(self::ENDPOINTS[$endpoint])) {
            throw new InvalidArgumentException("No MUG endpoint is named {$endpoint}.");
        }

        return (string) file_get_contents(self::FIXTURE_DIRECTORY.'/'.self::ENDPOINTS[$endpoint]['fixture']);
    }
}
