<?php

namespace Mralston\Mug;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JsonException;
use Mralston\Mug\Data\Address;
use Mralston\Mug\Data\ExportTariffPrices;
use Mralston\Mug\Data\PartnerTariff;
use Mralston\Mug\Data\PostcodeRegion;
use Mralston\Mug\Data\Suppliers;
use Mralston\Mug\Data\TariffInformationLabel;
use Mralston\Mug\Data\TariffSummary;
use Mralston\Mug\Data\TouTariff;
use Mralston\Mug\Enums\DatesToUse;
use Mralston\Mug\Enums\DecimalPlaces;
use Mralston\Mug\Enums\DnoRegion;
use Mralston\Mug\Enums\FuelType;
use Mralston\Mug\Enums\MeterType;
use Mralston\Mug\Enums\PaymentType;
use Mralston\Mug\Enums\PayMethod;
use Mralston\Mug\Enums\SwitchType;
use Mralston\Mug\Exceptions\MugException;
use Mralston\Mug\Support\Json;
use Mralston\Mug\Support\Values;

class Mug
{
    /**
     * Sent in the User-Agent on every request. MUG sits behind Cloudflare, which refuses some
     * clients' default agents, so the package always names itself.
     */
    public const string VERSION = '1.1.0';

    /**
     * A cached token is dropped this many seconds before MUG says it expires, so a request never
     * leaves with a token that lapses on the way.
     */
    public const int TOKEN_EXPIRY_MARGIN_SECONDS = 60;

    private const string TOKEN_CACHE_PREFIX = 'mralston-mug:token:';

    private const int HTTP_UNAUTHORISED = 401;

    private const int HTTP_NOT_FOUND = 404;

    private const int HTTP_FIRST_ERROR_STATUS = 400;

    /**
     * Endpoints to be used based on environment.
     *
     * @var array<string, string>
     */
    private array $authEndpoints = [
        'local' => 'https://authorisation-staging.myutilitygenius.co.uk',
        'dev' => 'https://authorisation-staging.myutilitygenius.co.uk',
        'testing' => 'https://authorisation-staging.myutilitygenius.co.uk',
        'production' => 'https://authorisation.myutilitygenius.co.uk',
    ];

    /**
     * Endpoints to be used based on environment.
     *
     * @var array<string, string>
     */
    private array $endpoints = [
        'local' => 'https://api-home-staging.myutilitygenius.co.uk',
        'dev' => 'https://api-home-staging.myutilitygenius.co.uk',
        'testing' => 'https://api-home-staging.myutilitygenius.co.uk',
        'production' => 'https://api-home.myutilitygenius.co.uk',
    ];

    private string $endpoint;

    private string $authEndpoint;

    private ?string $token = null;

    private bool $tokenFromCache = false;

    public function __construct(
        public string $clientId,
        public string $secret,
        string $endpoint,
        private ?Cache $cache = null,
    ) {
        $this->authEndpoint = $this->authEndpoints[$endpoint];
        $this->endpoint = $this->endpoints[$endpoint];
    }

    public static function userAgent(): string
    {
        return 'mralston-mug/'.self::VERSION;
    }

    public function addressPostcodeReady(?string $postCode = null): bool
    {
        if (empty($postCode)) {
            return false;
        }

        $json = $this->call('post', '/request/Address/Postcode/Ready', [
            'postcode' => $postCode,
        ])
            ->throw()
            ->json();

        return $json['postcodeReadyDto']['postcodeIsSwitchable'];
    }

    /**
     * The addresses at a postcode as MUG's raw arrays, in order of their first line. addresses()
     * gives them as typed objects.
     *
     * @return Collection<array-key, mixed>
     */
    public function addressRecco(?string $postCode = null): Collection
    {
        if (empty($postCode)) {
            return collect();
        }

        return $this->call('get', '/request/Address/recco', [
            'postcode' => $postCode,
        ])
            ->throw()
            ->collect()
            ->sortBy('addressLine1');
    }

    /**
     * An address's supply details as MUG's raw array. supplyDetails() gives them as a typed object.
     *
     * @param  array<string, mixed>|null  $address  An entry from addressRecco(), or any array with
     *                                              its `mpancore` and `xoserveAddressCode`.
     * @return array<string, mixed>
     */
    public function addressReccoDetails(?array $address = null): array
    {
        if (empty($address)) {
            return [];
        }

        $json = $this->call('post', '/request/Address/recco/details', [
            'mpanCores' => $address['mpancore'],
            'xoserveAddressCodes' => $address['xoserveAddressCode'],
            'isElectricityEacNeeded' => true,
        ])
            ->throw()
            ->json()[0];

        // The MPAN is returned as given. A 22 digit one is not padded: it is the MHHS layout,
        // whose top line has a four digit Standard Settlement Configuration where the legacy
        // layout has a three digit meter timeswitch code.
        return $json;
    }

    /**
     * Whether a postcode can be switched, and the distribution region it is in.
     */
    public function postcodeRegion(string $postcode): PostcodeRegion
    {
        $json = $this->decodeObject($this->call('post', '/request/Address/Postcode/Ready', [
            'postcode' => $postcode,
        ])->throw());

        return PostcodeRegion::fromArray(is_array($json['postcodeReadyDto'] ?? null) ? $json['postcodeReadyDto'] : []);
    }

    /**
     * The addresses at a postcode from the industry data, in order of their first line.
     *
     * @return Collection<int, Address>
     */
    public function addresses(string $postcode): Collection
    {
        return $this->decodeList($this->call('get', '/request/Address/recco', [
            'postcode' => $postcode,
        ])->throw())
            ->map(Address::fromArray(...))
            ->sortBy(fn (Address $address): string => (string) $address->addressLine1)
            ->values();
    }

    /**
     * An address with its supply details filled in: MPAN, region, current supplier, meter type and
     * the industry's estimated yearly use. Null when MUG finds nothing for it.
     */
    public function supplyDetails(Address $address): ?Address
    {
        return $this->decodeList($this->call('post', '/request/Address/recco/details', [
            'mpancores' => $address->mpanCores,
            'xoserveAddressCodes' => $address->xoserveAddressCodes,
            'isElectricityEacNeeded' => true,
        ])->throw())
            ->map(Address::fromArray(...))
            ->first();
    }

    /**
     * Time of use tariffs with their price bands for each region. Every filter is optional; MUG's
     * API description does not say which it needs.
     *
     * @param  int|null  $fuelId  MUG's own fuel id, whose values its API description does not list.
     * @return Collection<int, TouTariff>
     */
    public function touTariffs(
        string $postcode,
        ?int $supplierId = null,
        ?PaymentType $paymentType = null,
        ?bool $isDualFuel = null,
        ?int $fuelId = null,
        ?int $tariffId = null,
    ): Collection {
        return $this->decodeList($this->call('post', '/request/Tariffs/tou', $this->withoutNulls([
            'postcode' => $postcode,
            'supplierId' => $supplierId,
            'paymentId' => $paymentType?->value,
            'isDualFuel' => $isDualFuel,
            'fuelId' => $fuelId,
            'tariffId' => $tariffId,
        ]))->throw())
            ->map(TouTariff::fromArray(...));
    }

    /**
     * The live tariffs, for one supplier or all of them, without prices.
     *
     * @return Collection<int, TariffSummary>
     */
    public function liveTariffs(?int $supplierId = null): Collection
    {
        return $this->tariffSummaries($this->call('get', '/request/Tariffs/live', $this->withoutNulls([
            'SupplierId' => $supplierId,
        ]))->throw());
    }

    /**
     * Tariffs with their prices, filtered as `partners/tariff` allows. An empty filter is left out.
     *
     * @param  list<DnoRegion>  $regions
     * @param  list<PaymentType>  $paymentTypes
     * @param  list<FuelType>  $fuelTypes
     * @param  list<MeterType>  $meterTypes
     * @return Collection<int, PartnerTariff>
     *
     * @throws MugException when MUG answers with an error status in the body.
     */
    public function partnerTariffs(
        ?string $supplierName = null,
        ?int $supplierId = null,
        ?string $tariffName = null,
        array $regions = [],
        array $paymentTypes = [],
        array $fuelTypes = [],
        array $meterTypes = [],
        ?DatesToUse $datesToUse = null,
    ): Collection {
        $json = $this->decodeObject($this->call('post', '/request/partners/tariff', $this->withoutNulls([
            'supplierName' => $supplierName,
            'supplierId' => $supplierId,
            'tariffName' => $tariffName,
            'distIds' => $this->listOrNull(array_map(fn (DnoRegion $region): string => (string) $region->value, $regions)),
            'payTypes' => $this->listOrNull(array_map(fn (PaymentType $type): int => $type->value, $paymentTypes)),
            'fuelTypes' => $this->listOrNull(array_map(fn (FuelType $type): string => $type->value, $fuelTypes)),
            'meterTypesElec' => $this->listOrNull(array_map(fn (MeterType $type): string => $type->value, $meterTypes)),
            'datesToUse' => $datesToUse?->value,
        ]))->throw());

        $status = Values::int($json, 'statusCode');

        if ($status !== null && $status >= self::HTTP_FIRST_ERROR_STATUS) {
            throw new MugException("MUG refused partners/tariff with status {$status}: ".Values::string($json, 'message'));
        }

        $data = is_array($json['data'] ?? null) ? $json['data'] : [];

        return collect(Values::objects($data, 'tariffs'))->map(PartnerTariff::fromArray(...));
    }

    /**
     * A supplier's export tariffs, without prices. Empty when MUG answers 404, which it has done for
     * this path although its API description lists it.
     *
     * @return Collection<int, TariffSummary>
     */
    public function exportTariffs(int $supplierId): Collection
    {
        $response = $this->call('get', '/request/Tariffs/export', ['SupplierId' => $supplierId]);

        if ($response->status() === self::HTTP_NOT_FOUND) {
            return collect();
        }

        return $this->tariffSummaries($response->throw());
    }

    /**
     * An export tariff's rates for each region, with the time slots each rate is paid in. Null when
     * MUG has no such tariff (404).
     */
    public function exportTariffPrices(int $tariffId): ?ExportTariffPrices
    {
        $response = $this->call('get', "/request/Tariffs/export/tariff/{$tariffId}/prices");

        if ($response->status() === self::HTTP_NOT_FOUND) {
            return null;
        }

        $json = $this->decodeObject($response->throw());

        return Values::object($json, 'tariff', ExportTariffPrices::fromArray(...));
    }

    /**
     * The Tariff Information Label for a tariff at a postcode.
     *
     * @param  int|string  $economy7Percentage  The share of use at night, 0 to 100.
     */
    public function tariffInformationLabel(
        string $postcode,
        SwitchType $switchType,
        PayMethod $proposedPayMethod,
        int $tariffId,
        int|string $economy7Percentage,
        ?DateTimeInterface $tariffValidTo = null,
        ?DecimalPlaces $decimalPlaces = null,
    ): ?TariffInformationLabel {
        $json = $this->decodeObject($this->call('post', '/request/Til/Direct', $this->withoutNulls([
            'postcode' => $postcode,
            'switchType' => $switchType->value,
            'proposedPayMethod' => $proposedPayMethod->value,
            'tariffId' => $tariffId,
            'economy7Percentage' => $this->jsonNumber($economy7Percentage),
            'tariffValidToDate' => $tariffValidTo === null ? null : $this->mugDateTime($tariffValidTo),
            'decimalPlaces' => $decimalPlaces?->value,
        ]))->throw());

        return Values::object($json, 'tilDto', TariffInformationLabel::fromArray(...));
    }

    /**
     * The suppliers MUG offers for a switch at a postcode, by fuel.
     *
     * @param  int|string  $economy7Percentage  The share of use at night, 0 to 100.
     */
    public function suppliers(
        string $postcode,
        SwitchType $switchType,
        PayMethod $currentPayMethod,
        int|string $economy7Percentage,
    ): Suppliers {
        $json = $this->decodeObject($this->call('post', '/request/Suppliers', [
            'postcode' => $postcode,
            'switchType' => $switchType->value,
            'currentPayMethod' => $currentPayMethod->value,
            'economy7Percentage' => $this->jsonNumber($economy7Percentage),
        ])->throw());

        return Suppliers::fromArray(is_array($json['suppliersDto'] ?? null) ? $json['suppliersDto'] : []);
    }

    /**
     * Sends a request with a token, fetching a fresh one and trying once more if a token from the
     * cache is refused (MUG may have revoked it before its stated expiry).
     *
     * @param  array<string, mixed>  $data  The query for a GET, the JSON body for a POST.
     */
    private function call(string $method, string $path, array $data = []): Response
    {
        $response = $this->send($method, $path, $data);

        if ($response->status() === self::HTTP_UNAUTHORISED && $this->tokenFromCache) {
            $this->authenticate(force: true);
            $response = $this->send($method, $path, $data);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function send(string $method, string $path, array $data): Response
    {
        $this->authenticate();

        $request = $this->request()->withToken((string) $this->token);

        return $method === 'get'
            ? $request->get($this->endpoint.$path, $data)
            : $request->post($this->endpoint.$path, $data);
    }

    private function request(): PendingRequest
    {
        return Http::withUserAgent(self::userAgent())->acceptJson();
    }

    private function authenticate(bool $force = false): void
    {
        if ($this->token !== null && ! $force) {
            return;
        }

        $cacheKey = self::TOKEN_CACHE_PREFIX.hash('sha256', $this->authEndpoint.'|'.$this->clientId);

        if (! $force) {
            $cached = $this->cache?->get($cacheKey);

            if (is_string($cached) && $cached !== '') {
                $this->token = $cached;
                $this->tokenFromCache = true;

                return;
            }
        }

        $json = $this->request()
            ->asForm()
            ->post($this->authEndpoint.'/connect/token', [
                'grant_type' => 'client_credentials',
                'scope' => 'DomesticApi',
                'client_id' => $this->clientId,
                'client_secret' => $this->secret,
            ])
            ->throw()
            ->json();

        $this->token = $json['access_token'];
        $this->tokenFromCache = false;

        $lifetime = (int) ($json['expires_in'] ?? 0) - self::TOKEN_EXPIRY_MARGIN_SECONDS;

        if ($lifetime > 0) {
            $this->cache?->put($cacheKey, $this->token, $lifetime);
        }
    }

    /**
     * @return Collection<int, TariffSummary>
     */
    private function tariffSummaries(Response $response): Collection
    {
        $json = $this->decodeObject($response);
        $tariffs = is_array($json['tariffsDto'] ?? null) ? $json['tariffsDto'] : [];

        return collect(Values::objects($tariffs, 'tariffs'))->map(TariffSummary::fromArray(...));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeObject(Response $response): array
    {
        $json = $this->decode($response);

        if (! is_array($json) || (array_is_list($json) && $json !== [])) {
            throw new MugException('MUG answered '.$response->effectiveUri()?->getPath().' with something other than a JSON object.');
        }

        return $json;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function decodeList(Response $response): Collection
    {
        $json = $this->decode($response);

        if (! is_array($json) || ! array_is_list($json)) {
            throw new MugException('MUG answered '.$response->effectiveUri()?->getPath().' with something other than a JSON list.');
        }

        return collect(Values::objects(['items' => $json], 'items'));
    }

    private function decode(Response $response): mixed
    {
        try {
            return Json::decode($response->body());
        } catch (JsonException $exception) {
            throw new MugException('MUG answered '.$response->effectiveUri()?->getPath().' with a body that is not JSON.', previous: $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutNulls(array $data): array
    {
        return array_filter($data, fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  list<int|string>  $values
     * @return list<int|string>|null
     */
    private function listOrNull(array $values): ?array
    {
        return $values === [] ? null : $values;
    }

    /**
     * A JSON number for MUG from an integer or a decimal string, so callers never pass a float.
     */
    private function jsonNumber(int|string $value): int|float
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Expected a number, given a string that is not one.');
        }

        return str_contains($value, '.') || stripos($value, 'e') !== false ? (float) $value : (int) $value;
    }

    /**
     * A date-time as MUG reads one: UK local time without an offset.
     */
    private function mugDateTime(DateTimeInterface $moment): string
    {
        return CarbonImmutable::instance($moment)->setTimezone(Values::MUG_TIME_ZONE)->format('Y-m-d\TH:i:s');
    }
}
