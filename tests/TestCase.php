<?php

namespace Invoate\ConsoleCommands\Tests;

use Invoate\ConsoleCommands\ConsoleCommandsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ConsoleCommandsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        //
    }
}
