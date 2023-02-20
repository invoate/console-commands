<?php

use Illuminate\Database\Eloquent\Model;
use function Pest\Laravel\artisan;
use function Pest\Laravel\mock;

it('can run the command', function () {
    artisan('make:pivot', ['table1' => 'test1', 'table2' => 'test2'])->assertSuccessful();
});

it('can generate a migration', function () {
    artisan('make:pivot', ['table1' => 'test', 'table2' => 'test'])
        ->assertSuccessful()
        ->expectsOutputToContain('created successfully');
});

it('can generate a migration with the correct name', function () {
    artisan('make:pivot', ['table1' => 'alpha', 'table2' => 'bravo'])
        ->assertSuccessful()
        ->expectsOutputToContain('create_alphas_bravos_table');
});

it('can alphabetise the tables in the migration name', function () {
    artisan('make:pivot', ['table1' => 'bravo', 'table2' => 'alpha'])
        ->assertSuccessful()
        ->expectsOutputToContain('create_alphas_bravos_table');
});

it('can generate the table names from an Eloquent model', function () {
    $user = mock(Model::class);
    $user->shouldReceive('getTable')->andReturn('members');

    $organisation = mock(Model::class);
    $organisation->shouldReceive('getTable')->andReturn('orgs');

    app()->instance('App\Models\User', $user);
    app()->instance('App\Models\Organisation', $organisation);

    artisan('make:pivot', ['table1' => 'User', 'table2' => 'Organisation'])
        ->assertSuccessful()
        ->expectsOutputToContain('create_members_orgs_table');
});
