<?php

namespace Mralston\Mug\Enums;

/**
 * How the customer pays, as the numeric `paymentId` and `payTypes` MUG's tariff calls use.
 */
enum PaymentType: int
{
    case MonthlyDirectDebit = 1;
    case Prepayment = 2;
    case CashOrCheque = 3;
    case QuarterlyDirectDebit = 4;
}
