<?php

namespace AND48\TableFilters\Tests\Unit;

use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Models\FilterStorage;
use AND48\TableFilters\Tests\TestCase;
use AND48\TableFilters\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterStorageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_filter_storage_common_data()
    {
        $filter = FilterStorage::create([
            'name' => 'testing',
            'model' => User::class,
            'rules' => [
                ['id' => 1, 'operator' => '!=', 'values' => [2, 3]],
                ['id' => 2, 'operator' => '~', 'values' => ['and', 'dy']],
            ],
        ]);
        $this->assertModelExists($filter);
    }
}
