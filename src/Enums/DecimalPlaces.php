<?php

namespace Mralston\Mug\Enums;

/**
 * How many decimal places `Til/Direct` writes its figures to.
 */
enum DecimalPlaces: string
{
    case Zero = 'Zero';
    case One = 'One';
    case Two = 'Two';
    case Three = 'Three';
    case Four = 'Four';
    case Five = 'Five';
    case Six = 'Six';
    case Seven = 'Seven';
}
