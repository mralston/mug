<?php

namespace Mralston\Mug\Facades;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Facade;
use Mralston\Mug\Testing\MugFake;

/**
 * @method static bool addressPostcodeReady(?string $postCode = null)
 * @method static \Illuminate\Support\Collection<int, array<string, mixed>> addressRecco(?string $postCode = null)
 * @method static array<string, mixed> addressReccoDetails(?array<string, mixed> $address = null)
 * @method static \Mralston\Mug\Data\PostcodeRegion postcodeRegion(string $postcode)
 * @method static \Illuminate\Support\Collection<int, \Mralston\Mug\Data\Address> addresses(string $postcode)
 * @method static \Mralston\Mug\Data\Address|null supplyDetails(\Mralston\Mug\Data\Address $address)
 * @method static \Illuminate\Support\Collection<int, \Mralston\Mug\Data\TouTariff> touTariffs(string $postcode, ?int $supplierId = null, ?\Mralston\Mug\Enums\PaymentType $paymentType = null, ?bool $isDualFuel = null, ?int $fuelId = null, ?int $tariffId = null)
 * @method static \Illuminate\Support\Collection<int, \Mralston\Mug\Data\TariffSummary> liveTariffs(?int $supplierId = null)
 * @method static \Illuminate\Support\Collection<int, \Mralston\Mug\Data\PartnerTariff> partnerTariffs(?string $supplierName = null, ?int $supplierId = null, ?string $tariffName = null, list<\Mralston\Mug\Enums\DnoRegion> $regions = [], list<\Mralston\Mug\Enums\PaymentType> $paymentTypes = [], list<\Mralston\Mug\Enums\FuelType> $fuelTypes = [], list<\Mralston\Mug\Enums\MeterType> $meterTypes = [], ?\Mralston\Mug\Enums\DatesToUse $datesToUse = null)
 * @method static \Illuminate\Support\Collection<int, \Mralston\Mug\Data\TariffSummary> exportTariffs(int $supplierId)
 * @method static \Mralston\Mug\Data\ExportTariffPrices|null exportTariffPrices(int $tariffId)
 * @method static \Mralston\Mug\Data\TariffInformationLabel|null tariffInformationLabel(string $postcode, \Mralston\Mug\Enums\SwitchType $switchType, \Mralston\Mug\Enums\PayMethod $proposedPayMethod, int $tariffId, int|string $economy7Percentage, ?\DateTimeInterface $tariffValidTo = null, ?\Mralston\Mug\Enums\DecimalPlaces $decimalPlaces = null)
 * @method static \Mralston\Mug\Data\Suppliers suppliers(string $postcode, \Mralston\Mug\Enums\SwitchType $switchType, \Mralston\Mug\Enums\PayMethod $currentPayMethod, int|string $economy7Percentage)
 *
 * @see \Mralston\Mug\Mug
 */
class Mug extends Facade
{
    /**
     * Answers every MUG call from the package's made-up fixtures, through Http::fake(). Pass
     * responses keyed by endpoint name (MugFake::ENDPOINTS) to change any of them.
     *
     * @param  array<string, PromiseInterface|callable>  $overrides
     */
    public static function fake(array $overrides = []): void
    {
        MugFake::register($overrides);
    }

    protected static function getFacadeAccessor(): string
    {
        return 'mug';
    }
}
