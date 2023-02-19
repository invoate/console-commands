<?php

namespace Invoate\ConsoleCommands;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Invoate\ConsoleCommands\Commands\ConsoleCommandsCommand;

class ConsoleCommandsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('console-commands')
            ->hasConfigFile()
            ->hasCommand(ConsoleCommandsCommand::class);
    }
}
