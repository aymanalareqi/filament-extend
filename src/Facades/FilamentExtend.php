<?php

namespace Alareqi\FilamentExtend\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Alareqi\FilamentExtend\FilamentExtend
 */
class FilamentExtend extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Alareqi\FilamentExtend\FilamentExtend::class;
    }
}
