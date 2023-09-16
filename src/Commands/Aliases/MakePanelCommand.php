<?php

namespace Alareqi\FilamentExtend\Commands\Aliases;

use Alareqi\FilamentExtend\Commands;

class MakePanelCommand extends Commands\MakePanelCommand
{
    protected $hidden = true;

    protected $signature = 'filament:panel {id?} {--F|force}';
}
