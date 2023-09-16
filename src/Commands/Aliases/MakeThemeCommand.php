<?php

namespace Alareqi\FilamentExtend\Commands\Aliases;

use Alareqi\FilamentExtend\Commands;

class MakeThemeCommand extends Commands\MakeThemeCommand
{
    protected $hidden = true;

    protected $signature = 'filament:theme {panel?} {--F|force}';
}
