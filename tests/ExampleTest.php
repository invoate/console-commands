<?php

use function Pest\Laravel\artisan;

it('can test command', function () {
    artisan('make:pivot', ['table1' => 'test1', 'table2' => 'test2'])->assertSuccessful();
});

it('can test generate migration', function () {
    artisan('make:pivot', ['table1' => 'test', 'table2' => 'test'])
        ->assertSuccessful()
        ->expectsOutputToContain('created successfully');
});

it('can test generate migration with correct name', function () {
    artisan('make:pivot', ['table1' => 'alpha', 'table2' => 'bravo'])
        ->assertSuccessful()
        ->expectsOutputToContain('create_alphas_bravos_table');
});

it('can test alphabetise migration name', function () {
    artisan('make:pivot', ['table1' => 'bravo', 'table2' => 'alpha'])
        ->assertSuccessful()
        ->expectsOutputToContain('create_alphas_bravos_table');
});
