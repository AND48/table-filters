<?php

namespace AND48\TableFilters\Tests\Feature;

use AND48\TableFilters\Exceptions\TableFiltersException;
use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Tests\TestCase;
use AND48\TableFilters\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function check_filter_exceptions()
    {
        try {
            User::addTableFilters([
                ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
            ]);
            $filters = [['id' => 1, 'operator' => '~', 'values' => [1]]];
            User::tableFilter($filters)->get();
        } catch (TableFiltersException $exception){
            $this->assertEquals(300, $exception->getCode());
        }

    }

    /** @test */
    function check_filter_table_null()
    {
        User::addTableFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);

        User::factory()->count(97)->create();

        User::factory()->create(['parent_id' => 8]);
        User::factory()->create(['parent_id' => 48]);
        User::factory()->create(['parent_id' => 88]);


        $tests = [
            ['operator' => '=', 'values' => [], 'assert_count' => 97],
            ['operator' => '!=', 'values' => [], 'assert_count' => 3],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_number()
    {
        User::addTableFilters([
            ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
        ]);

        User::factory()->count(100)->create();
        $tests = [
            ['operator' => '=', 'values' => [3,48], 'assert_count' => 2],
            ['operator' => '!=', 'values' => [3,48], 'assert_count' => 98],
            ['operator' => '<', 'values' => 48, 'assert_count' => 47],
            ['operator' => '<=', 'values' => 48, 'assert_count' => 48],
            ['operator' => '>', 'values' => [48], 'assert_count' => 52],
            ['operator' => '>=', 'values' => [48], 'assert_count' => 53],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_string()
    {
        User::addTableFilters([
            ['field' =>'name', 'type' => Filter::TYPE_STRING, 'caption' => 'Name'],
        ]);

        User::factory()->create(['name' => 'Andrii']);
        User::factory()->create(['name' => 'Alex']);
        User::factory()->create(['name' => 'Andy']);
        User::factory()->create(['name' => 'Mike']);
        User::factory()->create(['name' => 'Sandy']);

        $tests = [
            ['operator' => '=', 'values' => ['Andrii', 'Andy'], 'assert_count' => 2],
            ['operator' => '!=', 'values' => ['Andrii', 'Andy'], 'assert_count' => 3],
            ['operator' => '~', 'values' => ['ndy', 'ke'], 'assert_count' => 3],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }


    /** @test */
    function check_filter_table_boolean()
    {
        User::addTableFilters([
            ['field' =>'is_blocked', 'type' => Filter::TYPE_BOOLEAN, 'caption' => 'Is blocked'],
        ]);

        User::factory()->count(97)->create();

        User::factory()->count(3)->create(['is_blocked' => true]);

        $tests = [
            ['operator' => '=', 'values' => true, 'assert_count' => 3],
            ['operator' => '=', 'values' => [false], 'assert_count' => 97],
            ['operator' => '!=', 'values' => [true], 'assert_count' => 97],
            ['operator' => '!=', 'values' => [false], 'assert_count' => 3],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_date()
    {
        User::addTableFilters([
            ['field' =>'birthday', 'type' => Filter::TYPE_DATE, 'caption' => 'Birthday'],
        ]);

        User::factory()->create(['birthday' => '1986-06-06']);
        User::factory()->create(['birthday' => '1986-06-08']);
        User::factory()->create(['birthday' => '1986-06-12']);
        User::factory()->create(['birthday' => '1986-06-14']);
        User::factory()->create(['birthday' => '1986-06-16']);
        User::factory()->create(['birthday' => null]);
        $tests = [
            ['operator' => '=', 'values' => ['1986-06-06', '1986-06-12'], 'assert_count' => 2],
            ['operator' => '!=', 'values' => ['1986-06-06', '1986-06-12'], 'assert_count' => 3],
            ['operator' => '<', 'values' => ['1986-06-08'], 'assert_count' => 1],
            ['operator' => '<=', 'values' => ['1986-06-08'], 'assert_count' => 2],
            ['operator' => '>', 'values' => ['1986-06-08'], 'assert_count' => 3],
            ['operator' => '>=', 'values' => ['1986-06-08'], 'assert_count' => 4],
            ['operator' => '=', 'values' => [], 'assert_count' => 1],
            ['operator' => '!=', 'values' => [], 'assert_count' => 5],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_enum()
    {
        User::addTableFilters([
            ['field' =>'status', 'type' => Filter::TYPE_ENUM, 'caption' => 'Status'],
        ]);

        User::factory()->count(10)->create(['status' => User::STATUS_NEW]);
        User::factory()->count(10)->create(['status' => User::STATUS_VERIFIED]);
        User::factory()->count(10)->create(['status' => User::STATUS_SUSPENDED]);

        $tests = [
            ['operator' => '=', 'values' => [User::STATUS_NEW, User::STATUS_VERIFIED], 'assert_count' => 20],
            ['operator' => '!=', 'values' => [User::STATUS_SUSPENDED], 'assert_count' => 20],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_source()
    {
        User::addTableFilter([
            'field' =>'parent_id',
            'type' => Filter::TYPE_SOURCE,
            'caption' => 'Parent user',
            'source_model' => User::class
        ]);

        User::factory()->count(97)->create();

        User::factory()->create(['parent_id' => 8]);
        User::factory()->create(['parent_id' => 48]);
        User::factory()->create(['parent_id' => 88]);


        $tests = [
            ['operator' => '=', 'values' => [48], 'assert_count' => 1],
            ['operator' => '!=', 'values' => [48], 'assert_count' => 2],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::tableFilter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_multiple()
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

        $filters = [
            ['id' => 1, 'operator' => '!=', 'values' => [2,3]],
            ['id' => 2, 'operator' => '~', 'values' => ['and', 'dy']],
            ['id' => 3, 'operator' => '>=', 'values' => ['1986-06-06']],
            ['id' => 4, 'operator' => '=', 'values' => [false]],
            ['id' => 6, 'operator' => '=', 'values' => [User::STATUS_NEW, User::STATUS_VERIFIED]],
            ['id' => 7, 'operator' => '=', 'values' => []],
        ];
        User::factory()->create([
            'name' => 'Andy',
            'birthday' => '1986-06-06',
            'is_blocked' => false,
            'status' => User::STATUS_NEW,
            'parent_id' => null,]);
        User::factory()->create([
            'name' => 'Andy',
            'birthday' => '1986-06-06',
            'is_blocked' => false,
            'status' => User::STATUS_NEW,
            'parent_id' => 1,]);
        $users = User::tableFilter($filters)->get();
        $this->assertCount(1, $users);
    }

}

