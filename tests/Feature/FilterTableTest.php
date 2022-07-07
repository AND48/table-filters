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
            User::addFilters([
                ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
            ]);
            $filters = [['id' => 1, 'operator' => '~', 'values' => [1]]];
            User::filter($filters)->get();
        } catch (TableFiltersException $exception){
            $this->assertEquals(300, $exception->getCode());
        }

    }

    /** @test */
    function check_filter_table_number()
    {
        User::addFilters([
            ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
        ]);

        User::factory()->count(100)->create();
        $tests = [
            ['operator' => '=', 'values' => [3,48], 'assert_count' => 2],
            ['operator' => '!=', 'values' => [3,48], 'assert_count' => 98],
            ['operator' => '<', 'values' => [48], 'assert_count' => 47],
            ['operator' => '<=', 'values' => [48], 'assert_count' => 48],
            ['operator' => '>', 'values' => [48], 'assert_count' => 52],
            ['operator' => '>=', 'values' => [48], 'assert_count' => 53],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::filter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

    /** @test */
    function check_filter_table_string()
    {
        User::addFilters([
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
            $users = User::filter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }


    /** @test */
    function check_filter_table_boolean()
    {
        User::addFilters([
            ['field' =>'is_blocked', 'type' => Filter::TYPE_BOOLEAN, 'caption' => 'Is blocked'],
        ]);

        User::factory()->count(97)->create();

        User::factory()->create(['is_blocked' => true]);
        User::factory()->create(['is_blocked' => true]);
        User::factory()->create(['is_blocked' => true]);


        $tests = [
            ['operator' => '=', 'values' => [true], 'assert_count' => 3],
            ['operator' => '=', 'values' => [false], 'assert_count' => 97],
            ['operator' => '!=', 'values' => [true], 'assert_count' => 97],
            ['operator' => '!=', 'values' => [false], 'assert_count' => 3],
        ];

        foreach ($tests as $test) {
            $filters = [['id' => 1, 'operator' => $test['operator'], 'values' => $test['values']]];
            $users = User::filter($filters)->get();
            $this->assertCount($test['assert_count'], $users);
        }
    }

}

