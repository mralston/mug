<?php

namespace Mralston\Mug\Enums;

/**
 * How the customer pays, as the named values `Til/Direct` and `Suppliers` use.
 */
enum PayMethod: string
{
    case MonthlyDirectDebit = 'MonthlyDirectDebit';
    case QuarterlyDirectDebit = 'QuarterlyDirectDebit';
    case PrePaymentMeter = 'PrePaymentMeter';
    case CashOrCheque = 'CashOrCheque';
}
