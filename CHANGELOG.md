# Changelog

## Unreleased (`nova` branch)

### Added

- Typed calls returning readonly data objects: `postcodeRegion`, `addresses`, `supplyDetails`,
  `touTariffs`, `liveTariffs`, `partnerTariffs`, `exportTariffs`, `exportTariffPrices`,
  `tariffInformationLabel` and `suppliers`.
- Enums for MUG's closed sets: `DnoRegion`, `PaymentType`, `PayMethod`, `SwitchType`, `FuelType`,
  `MeterType`, `DatesToUse`, `DecimalPlaces`.
- Numbers in typed responses are kept as the decimal strings MUG sent, never floats.
- `Mug::fake()` and `MugFake`, answering every call from made-up fixtures through `Http::fake()`.
- The access token is cached until 60 seconds before it expires (`MUG_TOKEN_CACHE_STORE` picks the
  store), and a refused cached token is replaced and the request retried once.
- Every request sends the User-Agent `mralston-mug/<version>`.
- `mug-config` publish tag, as the README always said (`pdf-config` still works).
- Pest tests, PHPStan (Larastan, level 8) and Pint.

### Changed

- Requires PHP 8.4 and Laravel 11, 12 or 13.
- `addressPostcodeReady`, `addressRecco` and `addressReccoDetails` keep their signatures and results;
  they now share the token cache and user agent.

## v1.0.5

- `addressReccoDetails` returns the MPAN as MUG gives it, 21 or 22 digits.
