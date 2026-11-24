<?php

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mralston\Mug\Data\Address;
use Mralston\Mug\Data\ElectricitySupply;
use Mralston\Mug\Data\PriceBand;
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
use Mralston\Mug\Facades\Mug;

beforeEach(fn () => Mug::fake());

function sentTo(string $path): ?Request
{
    return Http::recorded(fn (Request $request): bool => str_contains($request->url(), $path))->first()[0] ?? null;
}

it('gives the region a postcode is in', function () {
    $region = Mug::postcodeRegion('ZZ99 9ZZ');

    expect($region->postcodeIsSwitchable)->toBeTrue()
        ->and($region->gspGroupId)->toBe('_J')
        ->and($region->gspRegionId)->toBe('19')
        ->and($region->region())->toBe(DnoRegion::SouthEastern);
});

it('lists addresses as typed objects in order of their first line', function () {
    $addresses = Mug::addresses('ZZ99 9ZZ');

    expect($addresses)->toHaveCount(2)
        ->and($addresses[0])->toBeInstanceOf(Address::class)
        ->and($addresses[0]->addressLine1)->toBe('1 Example Street')
        ->and($addresses[0]->mpanCores)->toBe(['1999999999001'])
        ->and($addresses[0]->electricity)->toBe([]);

    expect(sentTo('/request/Address/recco')->url())->toContain('postcode=ZZ99');
});

it('fills in the supply details of an address', function () {
    $address = Mug::supplyDetails(Mug::addresses('ZZ99 9ZZ')->first());
    $supply = $address->electricity[0];

    expect($supply)->toBeInstanceOf(ElectricitySupply::class)
        ->and($supply->mpan)->toBe('028018011999999999001')
        ->and($supply->mpanCore)->toBe('1999999999001')
        ->and($supply->profileClass)->toBe('02')
        ->and($supply->currentSupplierName)->toBe('Example Energy')
        ->and($supply->mugSupplierId)->toBe(9001)
        ->and($supply->isEconomy7)->toBeTrue()
        ->and($supply->isSmartMeter)->toBeTrue()
        ->and($supply->isPrepayment)->toBeFalse()
        ->and($supply->totalEac)->toBe('3456.7')
        ->and($supply->energyDirection)->toBe('I')
        ->and($supply->ihdStatusEfd)->toBe('2024-04-01')
        ->and($supply->region())->toBe(DnoRegion::SouthEastern)
        ->and($address->gas[0]->mprn)->toBe('9999999001')
        ->and($address->faults)->toBe([]);

    $request = sentTo('/request/Address/recco/details');
    expect($request['mpancores'])->toBe(['1999999999001'])
        ->and($request['xoserveAddressCodes'])->toBe(['9999999001'])
        ->and($request['isElectricityEacNeeded'])->toBeTrue();
});

it('gives null supply details when MUG finds none', function () {
    Http::swap(new Factory);
    Mug::fake(['addressReccoDetails' => Http::response('[]')]);

    $address = Address::fromArray(['mpancore' => ['1999999999001'], 'xoserveAddressCode' => []]);

    expect(Mug::supplyDetails($address))->toBeNull();
});

it('maps time of use tariffs with their price bands, keeping rates as decimal strings', function () {
    $tariffs = Mug::touTariffs('ZZ99 9ZZ', supplierId: 9001, paymentType: PaymentType::MonthlyDirectDebit, isDualFuel: false, fuelId: 1);

    /** @var TouTariff $tariff */
    $tariff = $tariffs->first();
    $group = $tariff->prices[0];
    $bands = $group->electricity;

    expect($tariff->tariffId)->toBe(900101)
        ->and($tariff->tariffName)->toBe('Example Three Rate')
        ->and($tariff->isLive)->toBeTrue()
        ->and($tariff->isFixed)->toBeFalse()
        ->and($tariff->fixedTermMonths)->toBe('0')
        ->and($tariff->isPriceIncVAT)->toBeTrue()
        ->and($tariff->priceType)->toBe(1)
        ->and($tariff->hasExitFee)->toBeFalse()
        ->and($tariff->standardTariffId)->toBe(900100)
        ->and($tariff->liveRemovalDate)->toBeNull()
        ->and($tariff->validFromDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($tariff->validFromDate->toIso8601String())->toBe('2026-09-30T23:00:00+00:00')
        ->and($group->region())->toBe(DnoRegion::SouthEastern)
        ->and($group->paymentType())->toBe(PaymentType::MonthlyDirectDebit)
        ->and($group->gas)->toBe([])
        ->and($bands)->toHaveCount(3)
        ->and($bands[0])->toBeInstanceOf(PriceBand::class)
        ->and($bands[0]->unitRate)->toBe('16.4115')
        ->and($bands[0]->standingCharge)->toBe('48.79')
        ->and($bands[0]->timeFrom)->toBe('02:00')
        ->and($bands[0]->productCode)->toBe('EXAMPLE-3R-OFF')
        ->and($bands[0]->thresholdUnits)->toBe('0')
        ->and($bands[1]->monthFrom)->toBe('10')
        ->and($bands[1]->weekdayTo)->toBe('5')
        ->and($bands[2]->unitRate)->toBe('27.3');

    $request = sentTo('/request/Tariffs/tou');
    expect($request->data())->toBe([
        'postcode' => 'ZZ99 9ZZ',
        'supplierId' => 9001,
        'paymentId' => 1,
        'isDualFuel' => false,
        'fuelId' => 1,
    ]);
});

it('lists live tariffs, filtered by supplier when asked', function () {
    $tariffs = Mug::liveTariffs(9001);

    expect($tariffs)->toHaveCount(2)
        ->and($tariffs[0]->id)->toBe(900101)
        ->and($tariffs[0]->name)->toBe('Example Three Rate')
        ->and($tariffs[0]->dnoAreaId)->toBe(19)
        ->and($tariffs[0]->region())->toBe(DnoRegion::SouthEastern)
        ->and($tariffs[1]->isDefaultOrStandard)->toBeTrue();

    expect(sentTo('/request/Tariffs/live')->url())->toEndWith('/request/Tariffs/live?SupplierId=9001');
});

it('maps partner tariffs with their prices and discounts', function () {
    $tariffs = Mug::partnerTariffs(
        supplierId: 9001,
        regions: [DnoRegion::SouthEastern, DnoRegion::Southern],
        paymentTypes: [PaymentType::MonthlyDirectDebit],
        fuelTypes: [FuelType::Electricity],
        meterTypes: [MeterType::Economy7],
        datesToUse: DatesToUse::OnSale,
    );

    $tariff = $tariffs->first();
    $price = $tariff->prices[0];

    expect($tariff->tariffName)->toBe('Example Night Saver')
        ->and($tariff->fixedTermMonths)->toBe('12')
        ->and($tariff->exitFeeElec)->toBe('0')
        ->and($tariff->tariffTypes)->toBe(['fixed', 'e7'])
        ->and($tariff->supplierId)->toBe(9001)
        ->and($tariff->maxKWHAllowedElec)->toBe(100000)
        ->and($tariff->minKWHAllowedGas)->toBeNull()
        ->and($price->unitRate1Elec)->toBe('29.104')
        ->and($price->unitRateE7Elec)->toBe('13.65')
        ->and($price->unitRate2Elec)->toBeNull()
        ->and($price->standingChargeElec)->toBe('51.21')
        ->and($price->meterTypeElec)->toBe('E7')
        ->and($price->distIds)->toBe(['19', '20'])
        ->and($price->regions())->toBe([DnoRegion::SouthEastern, DnoRegion::Southern])
        ->and($price->payTypes)->toBe([1, 4])
        ->and($price->paymentTypes())->toBe([PaymentType::MonthlyDirectDebit, PaymentType::QuarterlyDirectDebit])
        ->and($price->onsaleStartDate->toIso8601String())->toBe('2026-09-30T23:00:00+00:00')
        ->and($price->onsaleEndDate)->toBeNull()
        ->and($price->timePeriodsElec)->toBe(['00:30-07:30'])
        ->and($price->discounts[0]->value)->toBe('50')
        ->and($price->discounts[0]->percentage)->toBeFalse();

    expect(sentTo('/request/partners/tariff')->data())->toBe([
        'supplierId' => 9001,
        'distIds' => ['19', '20'],
        'payTypes' => [1],
        'fuelTypes' => ['elec'],
        'meterTypesElec' => ['E7'],
        'datesToUse' => 'onsale-dates',
    ]);
});

it('throws when partners/tariff reports an error in its body', function () {
    Http::swap(new Factory);
    Mug::fake(['partnersTariff' => Http::response(['statusCode' => 400, 'message' => 'Invalid distIds', 'data' => null])]);

    Mug::partnerTariffs(tariffName: 'Example');
})->throws(MugException::class, 'MUG refused partners/tariff with status 400: Invalid distIds');

it('lists a supplier\'s export tariffs', function () {
    $tariffs = Mug::exportTariffs(9001);

    expect($tariffs)->toHaveCount(1)
        ->and($tariffs[0]->name)->toBe('Example Export Flex');

    expect(sentTo('/request/Tariffs/export')->url())->toEndWith('/request/Tariffs/export?SupplierId=9001');
});

it('gives no export tariffs when MUG answers 404 for the list', function () {
    Http::swap(new Factory);
    Mug::fake(['tariffsExport' => Http::response('', 404)]);

    expect(Mug::exportTariffs(9001))->toBeEmpty();
});

it('maps an export tariff\'s rates and time slots', function () {
    $tariff = Mug::exportTariffPrices(900201);
    $rates = $tariff->prices[0]->rates;

    expect($tariff->tariffName)->toBe('Example Export Flex')
        ->and($tariff->supplierId)->toBe(9001)
        ->and($tariff->prices[0]->region())->toBe(DnoRegion::SouthEastern)
        ->and($rates[0]->unitRate)->toBe('24.57')
        ->and($rates[1]->unitRate)->toBe('15.0')
        ->and($rates[1]->periods)->toHaveCount(2)
        ->and($rates[1]->periods[1]->weekdayFrom)->toBe('6')
        ->and($rates[0]->periods[0]->timeTo)->toBe('19:00');

    expect(sentTo('/prices')->url())->toEndWith('/request/Tariffs/export/tariff/900201/prices');
});

it('gives null export prices when MUG has no such tariff', function () {
    Http::swap(new Factory);
    Mug::fake(['tariffsExportPrices' => Http::response('', 404)]);

    expect(Mug::exportTariffPrices(1))->toBeNull();
});

it('gets a tariff information label', function () {
    $label = Mug::tariffInformationLabel(
        'ZZ99 9ZZ',
        SwitchType::ElectricityToElectricity,
        PayMethod::MonthlyDirectDebit,
        900301,
        '42.5',
        CarbonImmutable::parse('2027-06-30T23:00:00Z'),
        DecimalPlaces::Three,
    );

    expect($label->electricity->supplier)->toBe('Example Energy')
        ->and($label->electricity->unitRateNight)->toBe('13.650p/kWh')
        ->and($label->electricity->discountsAndAdditionalCharges)->toBe(['None'])
        ->and($label->electricity->fixedMonths)->toBe(12)
        ->and($label->gas)->toBeNull();

    expect(sentTo('/request/Til/Direct')->data())->toBe([
        'postcode' => 'ZZ99 9ZZ',
        'switchType' => 'ElectricityToElectricity',
        'proposedPayMethod' => 'MonthlyDirectDebit',
        'tariffId' => 900301,
        'economy7Percentage' => 42.5,
        'tariffValidToDate' => '2027-07-01T00:00:00',
        'decimalPlaces' => 'Three',
    ]);
});

it('lists the suppliers for a switch', function () {
    $suppliers = Mug::suppliers('ZZ99 9ZZ', SwitchType::ElectricityToElectricity, PayMethod::PrePaymentMeter, 0);

    expect($suppliers->electricitySuppliers)->toHaveCount(2)
        ->and($suppliers->electricitySuppliers[1]->name)->toBe('Sample Power')
        ->and($suppliers->electricitySuppliers[0]->hasWarmHomeDiscount)->toBeTrue()
        ->and($suppliers->gasSuppliers)->toBe([]);

    expect(sentTo('/request/Suppliers')->data())->toBe([
        'postcode' => 'ZZ99 9ZZ',
        'switchType' => 'ElectricityToElectricity',
        'currentPayMethod' => 'PrePaymentMeter',
        'economy7Percentage' => 0,
    ]);
});

it('refuses a percentage that is not a number', function () {
    Mug::suppliers('ZZ99 9ZZ', SwitchType::ElectricityToElectricity, PayMethod::PrePaymentMeter, 'lots');
})->throws(InvalidArgumentException::class);

it('throws a MUG exception for a body that is not JSON', function () {
    Http::swap(new Factory);
    Mug::fake(['tariffsLive' => Http::response('<html>Blocked</html>')]);

    Mug::liveTariffs();
})->throws(MugException::class);

it('refuses an override for an endpoint it does not know', function () {
    Mug::fake(['tariffsEverything' => Http::response('[]')]);
})->throws(InvalidArgumentException::class);
