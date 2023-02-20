<?php

namespace Invoate\ConsoleCommands\Commands;

use Illuminate\Console\Command;

class RefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables, re-run all migrations and the seed task';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('migrate:fresh', ['--seed' => true]);
    }
}
