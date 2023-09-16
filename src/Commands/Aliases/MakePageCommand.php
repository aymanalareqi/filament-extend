<?php

namespace Alareqi\FilamentExtend\Commands\Aliases;

use Alareqi\FilamentExtend\Commands;

class MakePageCommand extends Commands\MakePageCommand
{
    protected $hidden = true;

    protected $signature = 'filament:page {name?} {--R|resource=} {--T|type=} {--panel=} {--F|force}';
}
