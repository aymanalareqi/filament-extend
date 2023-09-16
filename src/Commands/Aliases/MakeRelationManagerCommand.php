<?php

namespace Alareqi\FilamentExtend\Commands\Aliases;

use Alareqi\FilamentExtend\Commands;

class MakeRelationManagerCommand extends Commands\MakeRelationManagerCommand
{
    protected $hidden = true;

    protected $signature = 'filament:relation-manager {resource?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--panel=} {--F|force}';
}
