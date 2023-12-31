<?php

namespace Alareqi\FilamentExtend\Commands;

use Alareqi\FilamentExtend\Commands\Concerns\CanGenerateForms;
use Alareqi\FilamentExtend\Commands\Concerns\CanGenerateTables;
use Alareqi\FilamentExtend\Commands\Concerns\CanReadModelSchemas;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Commands\Concerns\CanIndentStrings;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeResourceCommand extends Command
{
    use CanGenerateForms;
    use CanGenerateTables;
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanReadModelSchemas;

    protected $description = 'Create  a new Filament resource class and default page classes';

    protected $signature = 'make:filament:resource {name?} {--SD|soft-deletes} {--view} {--G|generate} {--S|simple} {--panel=} {--F|force}';

    public function handle(): int
    {
        $model = (string) str($this->argument('name') ?? text(
            label: 'What is the model name?',
            placeholder: 'BlogPost',
            required: true,
        ))
            ->studly()
            ->beforeLast('Resource')
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        if (blank($model)) {
            $model = 'Resource';
        }

        $modelClass = (string) str($model)->afterLast('\\');
        $modelNamespace = str($model)->contains('\\') ?
            (string) str($model)->beforeLast('\\') :
            '';
        $pluralModelClass = (string) str($modelClass)->pluralStudly();

        $panel = $this->option('panel');

        if ($panel) {
            $panel = Filament::getPanel($panel);
        }

        if (!$panel) {
            $panels = Filament::getPanels();

            /** @var Panel $panel */
            $panel = (count($panels) > 1) ? $panels[select(
                label: 'Which panel would you like to create this in?',
                options: array_map(
                    fn (Panel $panel): string => $panel->getId(),
                    $panels,
                ),
                default: Filament::getDefaultPanel()->getId()
            )] : Arr::first($panels);
        }

        $resourceDirectories = $panel->getResourceDirectories();
        $resourceNamespaces = $panel->getResourceNamespaces();

        $namespace = (count($resourceNamespaces) > 1) ?
            select(
                label: 'Which namespace would you like to create this in?',
                options: $resourceNamespaces
            ) : (Arr::first($resourceNamespaces) ?? 'App\\Filament\\Resources');
        $path = (count($resourceDirectories) > 1) ?
            $resourceDirectories[array_search($namespace, $resourceNamespaces)] : (Arr::first($resourceDirectories) ?? app_path('Filament/Resources/'));

        $resource = "{$model}Resource";
        $resourceClass = "{$modelClass}Resource";
        $resourceNamespace = $modelNamespace;
        $namespace .= $resourceNamespace !== '' ? "\\{$resourceNamespace}" : '';
        $listResourcePageClass = "List{$pluralModelClass}";
        $manageResourcePageClass = "Manage{$pluralModelClass}";
        $createResourcePageClass = "Create{$modelClass}";
        $editResourcePageClass = "Edit{$modelClass}";
        $viewResourcePageClass = "View{$modelClass}";
        $resourceUsesamespaces = '';
        $resourceUses = '';
        $resourceListPageUsesamespaces = '';
        $resourceListPageUses = '';
        $resourceCreatePageUsesamespaces = '';
        $resourceCreatePageUses = '';
        $resourceEditPageUsesamespaces = '';
        $resourceEditPageUses = '';
        $resourceViewPageUsesamespaces = '';
        $resourceViewPageUses = '';
        $navigationIcon = null;

        $baseResourcePath =
            (string) str($resource)
                ->prepend('/')
                ->prepend($path)
                ->replace('\\', '/')
                ->replace('//', '/');

        $resourcePath = "{$baseResourcePath}.php";
        $resourcePagesDirectory = "{$baseResourcePath}/Pages";
        $listResourcePagePath = "{$resourcePagesDirectory}/{$listResourcePageClass}.php";
        $manageResourcePagePath = "{$resourcePagesDirectory}/{$manageResourcePageClass}.php";
        $createResourcePagePath = "{$resourcePagesDirectory}/{$createResourcePageClass}.php";
        $editResourcePagePath = "{$resourcePagesDirectory}/{$editResourcePageClass}.php";
        $viewResourcePagePath = "{$resourcePagesDirectory}/{$viewResourcePageClass}.php";

        if (!$this->option('force') && $this->checkForCollision([
            $resourcePath,
            $listResourcePagePath,
            $manageResourcePagePath,
            $createResourcePagePath,
            $editResourcePagePath,
            $viewResourcePagePath,
        ])) {
            return static::INVALID;
        }

        $pages = '';
        $pages .= '\'index\' => Pages\\' . ($this->option('simple') ? $manageResourcePageClass : $listResourcePageClass) . '::route(\'/\'),';

        if (!$this->option('simple')) {
            $pages .= PHP_EOL . "'create' => Pages\\{$createResourcePageClass}::route('/create'),";

            if ($this->option('view')) {
                $pages .= PHP_EOL . "'view' => Pages\\{$viewResourcePageClass}::route('/{record}'),";
            }

            $pages .= PHP_EOL . "'edit' => Pages\\{$editResourcePageClass}::route('/{record}/edit'),";
        }

        $tableActions = [];

        if ($this->option('view')) {
            $tableActions[] = 'Tables\Actions\ViewAction::make(),';
        }

        $tableActions[] = 'Tables\Actions\EditAction::make(),';

        $relations = '';

        if ($this->option('simple')) {
            $tableActions[] = 'Tables\Actions\DeleteAction::make(),';

            if ($this->option('soft-deletes')) {
                $tableActions[] = 'Tables\Actions\ForceDeleteAction::make(),';
                $tableActions[] = 'Tables\Actions\RestoreAction::make(),';
            }
        } else {
            $relations .= PHP_EOL . 'public static function getRelations(): array';
            $relations .= PHP_EOL . '{';
            $relations .= PHP_EOL . '    return [';
            $relations .= PHP_EOL . '        //';
            $relations .= PHP_EOL . '    ];';
            $relations .= PHP_EOL . '}' . PHP_EOL;
        }
        $modelObject = new ('App\\Models' . "\\{$modelClass}")();

        $tableActions = implode(PHP_EOL, $tableActions);

        $tableBulkActions = [];

        if ($modelObject->fillable)

            $tableBulkActions[] = '\App\Filament\Actions\Table\ActivateBulkAction::make(),';

        $tableBulkActions[] = '\App\Filament\Actions\Table\DeactivateBulkAction::make(),';

        $tableBulkActions[] = 'Tables\Actions\DeleteBulkAction::make(),';

        $eloquentQuery = '';

        if ($this->option('soft-deletes')) {
            $tableBulkActions[] = 'Tables\Actions\ForceDeleteBulkAction::make(),';
            $tableBulkActions[] = 'Tables\Actions\RestoreBulkAction::make(),';

            $eloquentQuery .= PHP_EOL . PHP_EOL . 'public static function getEloquentQuery(): Builder';
            $eloquentQuery .= PHP_EOL . '{';
            $eloquentQuery .= PHP_EOL . '    return parent::getEloquentQuery()';
            $eloquentQuery .= PHP_EOL . '        ->withoutGlobalScopes([';
            $eloquentQuery .= PHP_EOL . '            SoftDeletingScope::class,';
            $eloquentQuery .= PHP_EOL . '        ]);';
            $eloquentQuery .= PHP_EOL . '}';
        }

        $tableBulkActions = implode(PHP_EOL, $tableBulkActions);


        $translatable = false;
        if (count($modelObject->translatable ?? []) > 0) {
            $translatable = true;
        }
        if ($translatable) {
            $resourceUsesamespaces .= "use Filament\Resources\Concerns\Translatable;\n";
            $resourceUses .= "use Translatable;\n";
            $resourceListPageUses .= "use ListRecords\Concerns\Translatable;\n";
            $resourceEditPageUses .= "use EditRecord\Concerns\Translatable;\n";
            $resourceViewPageUses .= "use ViewRecord\Concerns\Translatable;\n";
            $resourceCreatePageUses .= "use CreateRecord\Concerns\Translatable;\n";
        }
        $navigationIcon = $modelObject->navigationIcon;

        $this->copyStubToApp('Resource', $resourcePath, [
            'eloquentQuery' => $this->indentString($eloquentQuery, 1),
            'formSchema' => $this->indentString($this->option('generate') ? $this->getResourceFormSchema(
                'App\\Models' . ($modelNamespace !== '' ? "\\{$modelNamespace}" : '') . '\\' . $modelClass,
            ) : '//', 5),
            'model' => $model === 'Resource' ? 'Resource as ResourceModel' : $model,
            'modelClass' => $model === 'Resource' ? 'ResourceModel' : $modelClass,
            'namespace' => $namespace,
            'pages' => $this->indentString($pages, 3),
            'relations' => $this->indentString($relations, 1),
            'resource' => "{$namespace}\\{$resourceClass}",
            'resourceClass' => $resourceClass,
            'tableActions' => $this->indentString($tableActions, 4),
            'tableBulkActions' => $this->indentString($tableBulkActions, 5),
            'tableColumns' => $this->indentString($this->option('generate') ? $this->getResourceTableColumns(
                'App\Models' . ($modelNamespace !== '' ? "\\{$modelNamespace}" : '') . '\\' . $modelClass,
            ) : '//', 4),
            'tableFilters' => $this->indentString(
                $this->option('soft-deletes') ? 'Tables\Filters\TrashedFilter::make(),' : '//',
                4,
            ),
            'namespaces' => $resourceUsesamespaces,
            'uses' => $this->indentString($resourceUses),
            'navigationIcon' => $navigationIcon ?? 'heroicon-o-rectangle-stack',
            'navigationSort' => $modelObject?->navigationSort ?? 0,
            'navigationGroup' => $modelObject->navigationGroup ?? 'common.group_navigations.general',
            'modelLabel' => $modelObject->modelLabel ?? 'model',
            'reorderable' => $modelObject->reorderable != null ? "'$modelObject->reorderable'" : 'null',
            'pluralModelLabel' => $modelObject->pluralModelLabel ?? 'models',
        ]);

        if ($this->option('simple')) {
            $this->copyStubToApp('ResourceManagePage', $manageResourcePagePath, [
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $manageResourcePageClass,
            ]);
        } else {
            $listPageActions = [];
            $createPageActions = [];
            if ($translatable) {
                $listPageActions[] = 'Actions\LocaleSwitcher::make(),';
                $createPageActions[] = 'Actions\LocaleSwitcher::make(),';
            }
            $listPageActions[] = 'Actions\CreateAction::make(),';
            $listPageActions = implode(PHP_EOL, $listPageActions);
            $createPageActions = implode(PHP_EOL, $createPageActions);

            $this->copyStubToApp('ResourceListPage', $listResourcePagePath, [
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $listResourcePageClass,
                'uses' => $this->indentString($resourceListPageUses),
                'actions' => $this->indentString($listPageActions, 3),
            ]);

            $this->copyStubToApp('ResourcePage', $createResourcePagePath, [
                'baseResourcePage' => 'Filament\\Resources\\Pages\\CreateRecord',
                'baseResourcePageClass' => 'CreateRecord',
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $createResourcePageClass,
                'uses' => $this->indentString($resourceCreatePageUses),
                'actions' => $this->indentString($createPageActions, 3),
            ]);

            $editPageActions = [];
            $viewPageActions = [];
            if ($translatable) {
                $editPageActions[] = 'Actions\LocaleSwitcher::make(),';
                $viewPageActions[] = 'Actions\LocaleSwitcher::make(),';
            }
            $viewPageActions[] = 'Actions\EditAction::make(),';
            $viewPageActions = implode(PHP_EOL, $viewPageActions);

            if ($this->option('view')) {
                $this->copyStubToApp('ResourceViewPage', $viewResourcePagePath, [
                    'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                    'resource' => "{$namespace}\\{$resourceClass}",
                    'resourceClass' => $resourceClass,
                    'resourcePageClass' => $viewResourcePageClass,
                    'uses' => $this->indentString($resourceViewPageUses),
                    'actions' => $this->indentString($viewPageActions, 3),
                ]);

                $editPageActions[] = 'Actions\ViewAction::make(),';
            }

            $editPageActions[] = 'Actions\DeleteAction::make(),';

            if ($this->option('soft-deletes')) {
                $editPageActions[] = 'Actions\ForceDeleteAction::make(),';
                $editPageActions[] = 'Actions\RestoreAction::make(),';
            }

            $editPageActions = implode(PHP_EOL, $editPageActions);

            $this->copyStubToApp('ResourceEditPage', $editResourcePagePath, [
                'actions' => $this->indentString($editPageActions, 3),
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $editResourcePageClass,
                'uses' => $this->indentString($resourceEditPageUses),
            ]);

            $langFileName =  Str($model)->singular()->snake();

            $langPath = lang_path('ar/' . $langFileName . '.php');
            if (!file_exists($langPath)) {
                $this->copyStubToApp('Translation', $langPath, []);
            }
        }

        $this->components->info("Successfully created {$resource}!");

        return static::SUCCESS;
    }
}
