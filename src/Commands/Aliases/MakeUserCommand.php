<?php

namespace Alareqi\FilamentExtend\Commands\Aliases;

use Alareqi\FilamentExtend\Commands;

class MakeUserCommand extends Commands\MakeUserCommand
{
    protected $hidden = true;

    protected $signature = 'filament:user';
}
