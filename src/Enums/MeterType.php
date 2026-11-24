<?php

namespace Mralston\Mug\Enums;

/**
 * An electricity meter type, as `partners/tariff` names it.
 */
enum MeterType: string
{
    case Economy7 = 'E7';
    case Standard = 'STD';
}
