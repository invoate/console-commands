<?php

namespace Invoate\ConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

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
        {--i|with-id : Generate an ID column}
        {--t|without-timestamps : Do not generate timestamp columns}
        {--f|without-foreign-keys : Generate foreign key constraints}
        {--p|pivot-model : Generate a pivot model class}';

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

        $columns = [];

        if ($this->option('with-id')) {
            $columns[] = '$table->id();';
        }

        if (! $this->option('without-foreign-keys')) {
            $columns[] = "\$table->foreignId('$tables[0]_id')->constrained()->onDelete('cascade');";
            $columns[] = "\$table->foreignId('$tables[1]_id')->constrained()->onDelete('cascade');";
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

        $tableName = implode('_', $tables);
        $migrationName = $this->getPath($tableName, $this->getMigrationsPath());
        $stub = $this->getStub();
        $columns = $this->generateColumns($columns);

        $this->generateMigrationFile($stub, $migrationName, [$tableName, $columns]);
    }

    protected function getTableName(string $name): string
    {
        $modelsNamespace = 'App\\Models\\';
        $class = $modelsNamespace . Str::studly(Str::singular($name));

        if (class_exists($class)) {
            return (new $class)->getTable();
        }

        return $name;
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
