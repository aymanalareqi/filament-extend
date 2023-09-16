<?php

namespace Alareqi\FilamentExtend\Commands\Aliases;

use Alareqi\FilamentExtend\Commands;

class MakeResourceCommand extends Commands\MakeResourceCommand
{
    protected $hidden = true;

    protected $signature = 'filament:resource {name?} {--soft-deletes} {--view} {--G|generate} {--S|simple} {--panel=} {--F|force}';
}
