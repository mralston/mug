<?php

namespace Mralston\Mug\Enums;

/**
 * Which dates `partners/tariff` uses to decide which prices are current.
 */
enum DatesToUse: string
{
    case OnSale = 'onsale-dates';
    case Regulatory = 'regulatory-dates';
    case RegulatoryWithThreeMonthBuffer = 'regulatory-dates-with-3-month-buffer';
    case None = 'no-dates';
}
