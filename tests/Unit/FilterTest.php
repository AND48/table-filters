<?php

namespace AND48\TableFilters\Tests\Unit;

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
//            'related_model' => null,
        ]);
        $this->assertModelExists($filter);
    }

    /** @test */
    function a_model_has_a_filter()
    {
        User::addFilters([
            ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
            ['field' =>'name', 'type' => Filter::TYPE_STRING, 'caption' => 'Name'],
            ['field' =>'birthday', 'type' => Filter::TYPE_DATE, 'caption' => 'Birthday'],
            ['field' =>'is_blocked', 'type' => Filter::TYPE_BOOLEAN, 'caption' => 'Is blocked'],
            ['field' =>'balance', 'type' => Filter::TYPE_NUMBER, 'caption' => 'Balance'],
            ['field' =>'status', 'type' => Filter::TYPE_ENUM, 'caption' => 'Status'],
            ['field' =>'parent_id', 'type' => Filter::TYPE_SOURCE, 'caption' => 'Parent user'],
        ]);
//        dump(User::filterList(true));
        $this->assertCount(7, User::filterList(true));
    }
}
