<?php

namespace AND48\TableFilters\Tests\Feature;

use AND48\TableFilters\Exceptions\TableFiltersException;
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

        $response = (array)json_decode($this->get(route('filters.source_data', ['filter_id' => 1, 'page' => 2]))->getContent())->data;
        $this->assertCount($count - User::getFilterSourcePerPage(), $response);
        $this->assertArrayHasKey('id', (array)Arr::first($response));
        $this->assertArrayHasKey('name', (array)Arr::first($response));
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
        User::factory()->create(['email' => 'Andrii@localhost.com']);
        User::factory()->create(['email' => 'Alex@localhost.com']);
        User::factory()->create(['email' => 'Andy@localhost.com']);
        User::factory()->create(['email' => 'Mike@localhost.com']);
        User::factory()->create(['email' => 'Sandy@localhost.com']);

        $response = (array)json_decode($this->get(route('filters.source_data', ['filter_id' => 1, 'query' => 'and']))->getContent())->data;

        $this->assertCount(3, $response);
        $this->assertArrayHasKey('id', (array)Arr::first($response));
        $this->assertArrayHasKey('name', (array)Arr::first($response));
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
        $response = $filter->sourceData();
        $response = $response->first()->append('parent_user_name')->toArray();
        $this->assertArrayHasKey('parent_user_name', $response);
        $this->assertEquals(2, $counter);
    }

    /** @test */
    function check_source_scope()
    {
        $filter = User::addFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);
        User::factory()->create(['is_blocked' => true]);
        User::factory()->create(['is_blocked' => false]);

        $users = $filter->sourceData();
        $this->assertFalse((boolean)User::find(Arr::first($users)['id'])->is_blocked);
    }

//    /** @test */
//    function check_source_exceptions()
//    {
//        try {
//            $filter = User::addFilter([
//                'field' =>'parent_id',
//                'type' => Filter::TYPE_STRING,
//                'caption' => 'Parent user',
//                'source_model' => User::class
//            ]);
//
//            $filter->sourceData();
//        } catch (TableFiltersException $exception){
//            $this->assertEquals(200, $exception->getCode());
//        }
//
//        try {
//            $filter = User::addFilter([
//                'field' =>'parent_id',
//                'type' => Filter::TYPE_SOURCE,
//                'caption' => 'Parent user',
//                'source_model' => 'Unknown/Model'
//            ]);
//
//            $filter->sourceData();
//        } catch (TableFiltersException $exception){
//            $this->assertEquals(201, $exception->getCode());
//        }
//
//    }

}

