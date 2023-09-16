<?php

namespace Alareqi\FilamentExtend\Commands;

use Illuminate\Console\Command;

class FilamentExtendCommand extends Command
{
    public $signature = 'filament-extend';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
