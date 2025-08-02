<?php

namespace AND48\TableFilters\Tests;

use AND48\TableFilters\TableFiltersServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            TableFiltersServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
        include_once __DIR__ . '/../database/migrations/create_filters_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_filter_storages_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_users_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_orders_table.php.stub';

        // run the up() method of that migration class
        (new \CreateFiltersTable)->up();
        (new \CreateFilterStoragesTable)->up();
        (new \CreateUsersTable)->up();
        (new \CreateOrdersTable)->up();
    }
}
