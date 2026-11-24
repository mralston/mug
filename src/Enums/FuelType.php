<?php

namespace Mralston\Mug\Enums;

/**
 * The fuels a tariff covers, as `partners/tariff` names them.
 */
enum FuelType: string
{
    case ElectricityAndGas = 'elecgas';
    case Electricity = 'elec';
    case Gas = 'gas';
}
