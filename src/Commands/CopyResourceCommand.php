<?php

namespace Alareqi\FilamentExtend\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyResourceCommand extends Command
{
    public $signature = 'copy:resource {name} {newName} ';

    public $description = 'Copy existing Filament resource';

    public function handle(): int
    {
        $name = $this->argument('name');
        $newName = $this->argument('newName');
        $name = str($name)->studly->beforeLast('Resource')->trim();
        $newName = str($newName)->studly->beforeLast('Resource')->trim();
        $this->copyResource($name, $newName);
        return self::SUCCESS;
    }

    protected function copyResource(string $name, string $newName)
    {
        $langName = str($name)->singular()->snake();
        $newLangName = str($newName)->singular()->snake();
        $resourceDirectory = app_path('Filament/Resources');
        $resourceFileName =  $name . 'Resource.php';
        $newResourceFileName = $newName . 'Resource.php';
        if (file_exists($resourceDirectory . '/' . $newResourceFileName)) {
            $this->warn('File ' . $newResourceFileName . ' already exists');
            $result = $this->confirm('Do you want to overwrite it?');
            if (!$result) {
                return self::FAILURE;
            }
        }
        $resourceContent = file_get_contents($resourceDirectory . '/' . $resourceFileName);
        $newResourceContent = str_replace($name, $newName, $resourceContent);
        $newResourceContent = str_replace($langName, $newLangName, $newResourceContent);
        file_put_contents($resourceDirectory . '/' . $newResourceFileName, $newResourceContent);

        $resourcePages = File::files($resourceDirectory . '/' . $name . 'Resource/Pages');
        foreach ($resourcePages as  $pageFile) {
            $newPageFileName = $newName . 'Resource/Pages/' . str_replace($name, $newName,  basename($pageFile));
            // dd($pageFile->getRealPath());
            $newPageContent = file_get_contents($pageFile->getRealPath());
            $newPageContent = str_replace($name, $newName, $newPageContent);
            $newPageContent = str_replace($langName, $newLangName, $newPageContent);
            File::ensureDirectoryExists($resourceDirectory . '/' . $newName . 'Resource/Pages/');
            file_put_contents($resourceDirectory . '/' . $newPageFileName, $newPageContent);
        }
        $this->info('Resource ' . $name . 'Resource copied to ' . $newName . 'Resource successfully');
    }
}
