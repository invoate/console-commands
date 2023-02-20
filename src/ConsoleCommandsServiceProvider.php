<?php

namespace Invoate\ConsoleCommands;

use Invoate\ConsoleCommands\Commands\RefreshCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasCommand(RefreshCommand::class);
    }
}
