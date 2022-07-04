<?php

namespace AND48\TableFilters\Tests\Feature;

use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Tests\TestCase;
use AND48\TableFilters\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

class GetSourceDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function check_source_paging()
    {
        $count = 16;

        User::addFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);
        User::factory()->count($count)->create();

        $response = $this->get(route('filters.source_data', ['filter_id' => 1, 'page' => 2]))->original;

        $this->assertCount($count - User::getFilterSourcePerPage(), $response);
        $this->assertArrayHasKey('id', Arr::first($response));
        $this->assertArrayHasKey(User::getFilterSourceField(), Arr::first($response));
    }

    /** @test */
    function check_source_search()
    {
        User::addFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);
        User::factory()->create(['name' => 'Andrii']);
        User::factory()->create(['name' => 'Alex']);
        User::factory()->create(['name' => 'Andy']);
        User::factory()->create(['name' => 'Mike']);
        User::factory()->create(['name' => 'Sandy']);

        $response = $this->get(route('filters.source_data', ['filter_id' => 1, 'query' => 'and']))->original;

        $this->assertCount(3, $response);
        $this->assertArrayHasKey('id', Arr::first($response));
        $this->assertArrayHasKey(User::getFilterSourceField(), Arr::first($response));
    }

    /** @test */
    function check_source_transform()
    {
        $count = 1;

        $filter = User::addFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);
        User::factory()->count($count)->create();
        $response = $filter->sourceData();
        $this->assertArrayHasKey('parent_user_name', Arr::first($response));
    }

    /** @test */
    function check_source_lazy_load()
    {
        $count = 5;

        $filter = User::addFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);
        User::factory()->count($count)->create();
        $users = User::all();
//        dd(User::all());
        foreach ($users as $user){
            $user->parent_id = rand(1, count($users));
            $user->save();
        }
//        dd(User::all());

        $counter = 0;
        \DB::listen(function($sql) use (&$counter){
            $counter ++;
        });
        $filter->sourceData();
        $this->assertEquals(2, $counter);
    }

}

