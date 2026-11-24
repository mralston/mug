# My Utility Genius

## Introduction

Library for working with the My Utility Genius (MUG) API: addresses and their electricity and gas
supplies from the industry data, and suppliers' published tariffs with their prices by distribution
region.

The typed calls return readonly data objects in `Mralston\Mug\Data`, whose field names are MUG's own,
so MUG's API description (Swagger) explains each one. The original address calls (`addressPostcodeReady`,
`addressRecco`, `addressReccoDetails`) still return MUG's raw arrays and behave as they always have.

Requires PHP 8.4 or later and Laravel 11, 12 or 13.

## Config

You may publish the config file as follows:

```bash
php artisan vendor:publish --tag=mug-config
```

Add the following items to your .env file:

```dotenv
MUG_ENDPOINT="${APP_ENV}"
MUG_CLIENT_ID=
MUG_SECRET=
# Optional: the cache store for the access token. Empty uses the default store.
MUG_TOKEN_CACHE_STORE=
```

My Utility Genius will supply a Client ID and Secret. `MUG_ENDPOINT` is one of `local`, `dev`,
`testing` (MUG's staging) or `production`.

MUG grants each client roles. The address calls need `AddressInfo`; the tariff calls need roles of
their own, and without them MUG answers 401, which the package throws as a `RequestException`.

### Access token

The package signs in with the client credentials and keeps the token in the cache until
`Mug::TOKEN_EXPIRY_MARGIN_SECONDS` (60) before MUG says it expires (`expires_in`, an hour), so
requests from any process share one token. If MUG refuses a cached token, the package signs in again
and retries that request once.

### User agent

Every request, the token request included, is sent with the User-Agent `mralston-mug/<version>`
(`Mug::userAgent()`). MUG sits behind Cloudflare, which refuses some clients' default user agents.

## Usage

### Original address calls

```php
use Mralston\Mug\Facades\Mug;

// Determine whether a post code is ready to switch
Mug::addressPostcodeReady($this->postCode);

// Retrieve addresses matched by a post code
Mug::addressRecco($this->postCode);

// Retrieve details about a specific address
Mug::addressReccoDetails($address);

// $address attribute is an array which must contain
// mpancore and xoserveAddressCode elements.

// You may supply an entry from the collection
// returned by Mug::addressRecco()
Mug::addressReccoDetails(
    Mug::addressRecco($this->postCode)
        ->first()
);
```

### Addresses and supplies

| Call | MUG endpoint | Returns |
|---|---|---|
| `postcodeRegion(string $postcode)` | `POST Address/Postcode/Ready` | `PostcodeRegion` |
| `addresses(string $postcode)` | `GET Address/recco` | `Collection<int, Address>`, by first line |
| `supplyDetails(Address $address)` | `POST Address/recco/details` | `?Address` |

```php
$region = Mug::postcodeRegion('ZZ99 9ZZ');
$region->gspGroupId;   // '_J'
$region->region();     // DnoRegion::SouthEastern

$address = Mug::supplyDetails(Mug::addresses('ZZ99 9ZZ')->first());

foreach ($address->electricity as $supply) {   // ElectricitySupply, one per MPAN
    $supply->mpan;                 // the full MPAN as MUG gives it (21 or 22 digits)
    $supply->currentSupplierName;
    $supply->mugSupplierId;        // MUG's supplier id, for the tariff calls
    $supply->meterType;
    $supply->profileClass;
    $supply->isEconomy7;
    $supply->isSmartMeter;
    $supply->isPrepayment;
    $supply->totalEac;             // the industry's estimated yearly use, as a decimal string
    $supply->energyDirection;      // import or export, as MUG gives it
    $supply->region();             // DnoRegion, from gspGroupId
}

$address->gas;      // list<GasSupply>, one per MPRN
$address->faults;   // list<Fault> the industry data reported
```

An MPAN, a meter serial number and an address are personal data: keep them out of URLs, logs and
exception messages.

### Tariffs

| Call | MUG endpoint | Returns |
|---|---|---|
| `touTariffs(string $postcode, ?int $supplierId, ?PaymentType $paymentType, ?bool $isDualFuel, ?int $fuelId, ?int $tariffId)` | `POST Tariffs/tou` | `Collection<int, TouTariff>` |
| `liveTariffs(?int $supplierId)` | `GET Tariffs/live` | `Collection<int, TariffSummary>` |
| `partnerTariffs(?string $supplierName, ?int $supplierId, ?string $tariffName, list<DnoRegion> $regions, list<PaymentType> $paymentTypes, list<FuelType> $fuelTypes, list<MeterType> $meterTypes, ?DatesToUse $datesToUse)` | `POST partners/tariff` | `Collection<int, PartnerTariff>` |
| `exportTariffs(int $supplierId)` | `GET Tariffs/export` | `Collection<int, TariffSummary>`; empty on 404 |
| `exportTariffPrices(int $tariffId)` | `GET Tariffs/export/tariff/{tariffId}/prices` | `?ExportTariffPrices`; null on 404 |
| `tariffInformationLabel(string $postcode, SwitchType $switchType, PayMethod $proposedPayMethod, int $tariffId, int\|string $economy7Percentage, ?DateTimeInterface $tariffValidTo, ?DecimalPlaces $decimalPlaces)` | `POST Til/Direct` | `?TariffInformationLabel` |
| `suppliers(string $postcode, SwitchType $switchType, PayMethod $currentPayMethod, int\|string $economy7Percentage)` | `POST Suppliers` | `Suppliers` |

Optional filters left null (or empty lists) are not sent. `partnerTariffs()` throws a `MugException`
when MUG answers 200 with an error `statusCode` in the body. `exportTariffs()` returns an empty
collection on 404, which MUG's production API has given for that path although its API description
lists it. Any other error status throws Laravel's `RequestException`, and a body that is not the JSON
expected throws a `MugException`.

```php
$tariffs = Mug::touTariffs('ZZ99 9ZZ', supplierId: 9001, paymentType: PaymentType::MonthlyDirectDebit);

foreach ($tariffs as $tariff) {                 // TouTariff
    $tariff->validFromDate;                     // CarbonImmutable, UTC
    $tariff->isLive;
    $tariff->isFixed;
    $tariff->fixedTermMonths;
    $tariff->isPriceIncVAT;
    $tariff->hasExitFee;

    foreach ($tariff->prices as $group) {       // TouPriceGroup: one region and payment type
        $group->region();                       // ?DnoRegion
        foreach ($group->electricity as $band) {  // PriceBand
            $band->unitRate;                    // '16.4115'
            $band->standingCharge;              // '48.79'
            $band->monthFrom; $band->monthTo;
            $band->weekdayFrom; $band->weekdayTo;
            $band->timeFrom; $band->timeTo;
        }
    }
}

$export = Mug::exportTariffPrices(900201);      // ExportTariffPrices
$export->prices[0]->rates[0]->unitRate;         // '24.57'
$export->prices[0]->rates[0]->periods;          // list<ExportTimeSlot>
```

`TouTariff::$standardTariffId` is MUG's misspelt `standardTaridffId`, and `ElectricitySupply::$ihdStatusEfd`
its `ihdStausEfd`; every other field keeps MUG's name.

### Numbers, units and dates

- **Every number MUG sends is kept as the decimal string it wrote**, never a float: the response is
  decoded with each number token quoted first (`Mralston\Mug\Support\Json`), and a number in exponent
  form is written out in full. Hand them to `BcMath\Number` or a decimal column. Integer ids and counts
  are `int`.
- **Units:** MUG's API description does not state the unit of any rate or charge (`unitRate`,
  `standingCharge`, `unitRate1Elec`, `unitRateE7Elec`, `standingChargeElec`, export `unitRate`). The
  package passes them through unchanged. UK tariffs are normally quoted in pence per kWh and pence per
  day, and the Tariff Information Label's display text uses those units, but confirm against a live
  response before relying on it. Whether a tariff's prices include VAT is `TouTariff::$isPriceIncVAT`
  (and `PartnerTariff::$vatType`).
- **Regions:** `DnoRegion` is the 14 distribution regions by distributor id (10 to 23, MUG's
  `distIds`), with each one's GSP group (`_A` to `_P`). `region()` on a price group or tariff summary
  reads MUG's `dno` or `dnoAreaId` as a distributor id and gives null for any other value.
- **Date-times** become `CarbonImmutable` in UTC. MUG sends them without an offset, so they are read as
  UK time. Industry dates in the supply details (`supplierEfd`, `eacEfdate` and the like) are plain
  strings, as MUG's API description types them.
- **Time-of-use band fields** (`monthFrom`, `weekdayFrom`, `timeFrom` and the rest) are strings whose
  format MUG's API description does not give, so they are passed through as sent.

### Enums

| Enum | Values | Used by |
|---|---|---|
| `PaymentType` (int) | `MonthlyDirectDebit` 1, `Prepayment` 2, `CashOrCheque` 3, `QuarterlyDirectDebit` 4 | `touTariffs`, `partnerTariffs` |
| `PayMethod` (string) | `MonthlyDirectDebit`, `QuarterlyDirectDebit`, `PrePaymentMeter`, `CashOrCheque` | `tariffInformationLabel`, `suppliers` |
| `SwitchType` (string) | `ElectricityToElectricity` and the other 15 from and to combinations | `tariffInformationLabel`, `suppliers` |
| `FuelType` (string) | `elecgas`, `elec`, `gas` | `partnerTariffs` |
| `MeterType` (string) | `E7`, `STD` | `partnerTariffs` |
| `DatesToUse` (string) | `onsale-dates`, `regulatory-dates`, `regulatory-dates-with-3-month-buffer`, `no-dates` | `partnerTariffs` |
| `DecimalPlaces` (string) | `Zero` to `Seven` | `tariffInformationLabel` |
| `DnoRegion` (int) | the 14 regions, 10 to 23 | regions everywhere |

`touTariffs()`'s `$fuelId` is a plain int: MUG's API description does not list its values.

## Testing with the fake

`Mug::fake()` answers every call from made-up fixtures in `tests/fixtures` (fictional postcode
`ZZ99 9ZZ`, addresses, MPANs and suppliers) through `Http::fake()`, so the package's real requests and
mapping run in your tests.

```php
use Illuminate\Support\Facades\Http;
use Mralston\Mug\Facades\Mug;

Mug::fake();

// Change any endpoint's answer, by name (see MugFake::ENDPOINTS)
Mug::fake([
    'tariffsTou' => Http::response('', 401),
    'addressReccoDetails' => Http::response('[]'),
]);

// The raw fixture, to build a variation on it
$json = \Mralston\Mug\Testing\MugFake::fixture('tariffsTou');
```

The endpoint names are `token`, `addressPostcodeReady`, `addressReccoDetails`, `addressRecco`,
`tariffsTou`, `tariffsLive`, `tariffsExportPrices`, `tariffsExport`, `partnersTariff`, `tilDirect` and
`suppliers`. `Http::fake()` answers from the first stub that matches, so call `Mug::fake()` once per
test with all its overrides, before any catch-all `Http::fake()` of your own.

The fake reads its fixtures from `tests/fixtures`, so that directory ships with the package.

The package's own tests run with:

```bash
composer install
vendor/bin/pest
vendor/bin/phpstan analyse
vendor/bin/pint --test
```

They never call MUG.

## Security Vulnerabilities

Please [e-mail security vulnerabilities directly to me](mailto:matt@mralston.co.uk).

## Licence

This package is open-sourced software licenced under the [MIT license](LICENSE.md).
