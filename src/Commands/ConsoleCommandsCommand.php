<?php

namespace Invoate\ConsoleCommands\Commands;

use Illuminate\Console\Command;

class ConsoleCommandsCommand extends Command
{
    public $signature = 'console-commands';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
