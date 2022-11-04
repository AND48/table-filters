<?php

namespace AND48\TableFilters\Tests\Unit;

use AND48\TableFilters\Exceptions\TableFiltersException;
use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Tests\TestCase;
use AND48\TableFilters\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_filter_has_a_data()
    {
        $filter = Filter::create([
            'model' => 'App/User',
            'field' => 'name',
            'type' => Filter::TYPE_STRING,
            'caption' => 'Name',
            'related_model' => 'App/Order',
        ]);
        $this->assertModelExists($filter);
    }

    /** @test */
    function a_model_has_a_filter()
    {
        User::addTableFilters([
            ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
            ['field' =>'name', 'type' => Filter::TYPE_STRING, 'caption' => 'Name'],
            ['field' =>'birthday', 'type' => Filter::TYPE_DATE, 'caption' => 'Birthday'],
            ['field' =>'is_blocked', 'type' => Filter::TYPE_BOOLEAN, 'caption' => 'Is blocked'],
            ['field' =>'balance', 'type' => Filter::TYPE_NUMBER, 'caption' => 'Balance'],
            ['field' =>'status', 'type' => Filter::TYPE_ENUM, 'caption' => 'Status'],
            ['field' =>'parent_id', 'type' => Filter::TYPE_SOURCE, 'caption' => 'Parent user', 'source_model' => User::class],
        ]);
//        dump(User::tableFilterList(true));
        $this->assertCount(7, User::tableFilterList(true));
    }

    /** @test */
    function check_source_exceptions()
    {
        try {
            User::addTableFilter([
                'field' =>'parent_id',
                'type' => Filter::TYPE_SOURCE,
                'caption' => 'Parent user',
            ]);

        } catch (TableFiltersException $exception){
            $this->assertEquals(100, $exception->getCode());
        }

        try {
            User::addTableFilter([
                'field' =>'parent_id',
                'type' => Filter::TYPE_SOURCE,
                'caption' => 'Parent user',
                'source_model' => 'Unknown/Model'
            ]);

        } catch (TableFiltersException $exception){
            $this->assertEquals(100, $exception->getCode());
        }

    }
}
