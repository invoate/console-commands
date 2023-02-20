<?php

namespace Invoate\ConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PivotMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:pivot {table1 : The name of the first table or model}
        {table2 : The name of the second table or model}
        {--c|columns=* : Additional columns to generate (column:type) }
        {--f|without-foreign-keys : Generate foreign key constraints}
        {--m|model : Generate a pivot model class}
        {--i|with-id : Generate an ID column}
        {--id-type=id : The laravel supported ID type}
        {--without-timestamps : Do not generate timestamp columns}
        {--without-columns : Do not generate any columns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a pivot table migration file';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tables = Arr::sort([
            $this->getTableName($this->argument('table1')),
            $this->getTableName($this->argument('table2')),
        ]);

        $foreignIds = Arr::map($tables, function (string $value) {
            return Str::singular($value);
        });

        if (! $this->option('without-columns')) {
            $columns = [];

            if ($this->option('with-id')) {
                $type = $this->option('id-type');
                $columns[] = "\$table->$type();";
            }

            if ($this->option('without-foreign-keys')) {
                $columns[] = "\$table->integer('$foreignIds[0]_id');";
                $columns[] = "\$table->integer('$foreignIds[1]_id');";
            } else {
                $columns[] = "\$table->foreignId('$foreignIds[0]_id')->constrained();";
                $columns[] = "\$table->foreignId('$foreignIds[1]_id')->constrained();";
            }

            if (! $this->option('without-timestamps')) {
                $columns[] = '$table->timestamps();';
            }

            $columnsOption = $this->getColumns();
            foreach ($columnsOption as $column) {
                $column = explode(':', $column);
                $type = $column[1] ?? 'string';
                $columns[] = "\$table->$type('$column[0]');";
            }
        }

        $tableName = implode('_', $tables);
        $migrationName = $this->getPath($tableName, $this->getMigrationsPath());
        $stub = $this->getStub();
        $columns = $this->generateColumns($columns ?? ['//']);

        $this->generateMigrationFile($stub, $migrationName, [$tableName, $columns]);

        if ($this->option('model')) {
            $modelName = Str::studly(implode('_', $foreignIds));
            $this->call('make:model', [
                'name' => $modelName,
                '--pivot' => true,
            ]);
        }
    }

    protected function getTableName(string $name): string
    {
        $modelsNamespace = 'App\\Models\\';
        $class = $modelsNamespace.Str::studly(Str::singular($name));

        if (class_exists($class)) {
            return (new $class)->getTable();
        }

        return Str::of($name)->lower($name)->plural();
    }

    protected function getColumns(): array
    {
        $columns = $this->option('columns');

        if (! is_array($columns)) {
            $columns = explode(',', (string) $columns);
        }

        if (count($columns) === 1 && Str::contains($columns[0], ',')) {
            $columns = explode(',', (string) $columns[0]);
        }

        return $columns;
    }

    protected function generateMigrationFile(string $stub, string $path, array $replacements)
    {
        $migration = str_replace(['{{ table }}', '{{ columns }}'], $replacements, $stub);

        $this->filesystem->put($path, $migration);

        $this->components->info(sprintf('Migration [%s] created successfully.', $path));
    }

    protected function getPath(string $name, string $path): string
    {
        return $path.'/'.date('Y_m_d_His').'_create_'.$name.'_table.php';
    }

    protected function getMigrationsPath(): string
    {
        return $this->laravel->databasePath().DIRECTORY_SEPARATOR.'migrations';
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__.'/stubs/migration.create.stub';

        return $this->filesystem->get($stubPath);
    }

    protected function generateColumns(array $columns): string
    {
        return implode("\n            ", $columns);
    }
}
