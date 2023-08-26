<?php

namespace Mralston\Mug\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static addressPostcodeReady(string $postcode)
 *
 * @see \Mralston\Mug\Mug
 */
class Mug extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mug';
    }
}
