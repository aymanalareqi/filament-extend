<?php

namespace Alareqi\FilamentExtend\Commands;

use Alareqi\FilamentUsersRoles\Support\Utils;
use Filament\Support\Commands\Concerns\CanIndentStrings;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModelCommand extends Command
{
    use CanIndentStrings;
    use CanManipulateFiles;

    /**
     * The console command signature.
     *
     * @var string
     */
    public $signature = 'make:filament:model {model} {--soft-deletes} {--translatable}';
    // {--seeder : Exclude the given entities during generation }
    // the idea is to generate a seeder that can be used on production deployment

    /**
     * The console command description.
     *
     * @var string
     */
    public $description = 'Generate auth model for Filament Users Roles';

    public function handle(): int
    {
        $makeModel = true;
        $makeMigration = true;
        $migrationName = null;
        $model = $this->argument('model');

        $model = str($model)->camel()->ucfirst();
        $tableName = $model->snake()->pluralStudly();
        // check if model exists
        if (Utils::isModelExists($model)) {
            $this->warn("{$model} model already exists");
            if (! $this->confirm('Do you want to overwrite it?')) {
                $this->error("{$model} model generation skipped.");
                $makeModel = false;
            }
        }
        if ($migrationName = $this->migrationExist($tableName)) {
            $this->warn("{$tableName} migration already exists");
            if (! $this->confirm('Do you want to overwrite it?')) {
                $this->error("{$tableName} migration generation skipped.");
                $makeMigration = false;
            }
        }
        // copy stub to app/Models
        if ($makeModel) {
            $this->copyStubToAppModels($model);
        }
        // copy stub to database/migrations
        if ($makeMigration) {
            $this->copyStubToMigrations($tableName, $migrationName);
        }
        // success
        $this->info("{$model} model generated successfully.");

        return self::SUCCESS;
    }

    protected function copyStubToAppModels(string $model): void
    {
        $namespaces = '';
        $use = '';
        $properties = '';
        $modelName = Str($model)->singular()->snake();
        if ($this->option('soft-deletes')) {
            $namespaces .= "use Illuminate\Database\Eloquent\SoftDeletes;\n";
            $use .= "use SoftDeletes;\n";
        }
        if ($this->option('translatable')) {
            $namespaces .= "use Spatie\Translatable\HasTranslations;\n";
            $use .= "use HasTranslations;\n";
            $properties .= 'public $translatable = [];';
        }
        $this->copyStubToApp('Model', app_path("Models/{$model}.php"), [
            'modelClass' => $model,
            'modelName' => $modelName,
            'namespaces' => $namespaces,
            'use' => $this->indentString($use),
            'properties' => $this->indentString($properties),
        ]);
    }

    protected function copyStubToMigrations(string $tableName, ?string $migrationName): void
    {

        $columns = '';
        if ($this->option('soft-deletes')) {
            $columns .= "\n\$table->softDeletes();";
        }
        $fileName = ! empty($migrationName) ? $migrationName : now()->format('Y_m_d_His') . "_create_{$tableName}_table.php";
        $this->copyStubToApp('Migration', database_path("migrations/{$fileName}"), [
            'table_name' => $tableName,
            'columns' => $this->indentString($columns, 3),

        ]);
    }

    protected function migrationExist($tableName): bool | string
    {
        $fileName = "create_{$tableName}_table";
        $migrationFiels = File::files(database_path('migrations'));
        foreach ($migrationFiels as $file) {
            if (Str::contains($file->getFilename(), $fileName)) {
                return $file->getFilename();
            }
        }

        return false;
    }
}
