<?php

namespace Mralston\Mug\Enums;

/**
 * The customer's fuels now and after the switch, as `Til/Direct` and `Suppliers` name them. Dual
 * consolidated is both fuels from one supplier; dual distinct is each from a different one.
 */
enum SwitchType: string
{
    case DualConsolidatedToDualConsolidated = 'DualConsolidatedToDualConsolidated';
    case DualConsolidatedToDualDistinct = 'DualConsolidatedToDualDistinct';
    case DualConsolidatedToElectricity = 'DualConsolidatedToElectricity';
    case DualConsolidatedToGas = 'DualConsolidatedToGas';
    case DualDistinctToDualConsolidated = 'DualDistinctToDualConsolidated';
    case DualDistinctToDualDistinct = 'DualDistinctToDualDistinct';
    case DualDistinctToElectricity = 'DualDistinctToElectricity';
    case DualDistinctToGas = 'DualDistinctToGas';
    case ElectricityToDualConsolidated = 'ElectricityToDualConsolidated';
    case ElectricityToDualDistinct = 'ElectricityToDualDistinct';
    case ElectricityToElectricity = 'ElectricityToElectricity';
    case ElectricityToGas = 'ElectricityToGas';
    case GasToDualConsolidated = 'GasToDualConsolidated';
    case GasToDualDistinct = 'GasToDualDistinct';
    case GasToElectricity = 'GasToElectricity';
    case GasToGas = 'GasToGas';
}
