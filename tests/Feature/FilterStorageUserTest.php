<?php

namespace AND48\TableFilters\Tests\Feature;

use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Models\FilterStorage;
use AND48\TableFilters\Tests\TestCase;
use AND48\TableFilters\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FilterStorageUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function check_user_filter_storages()
    {
        User::factory()->create([
            'name' => 'admin1',
            'email' => 'admin1@localhost.com',
            'password' => Hash::make('pass1')]);
        User::factory()->create([
            'name' => 'admin2',
            'email' => 'admin2@localhost.com',
            'password' => Hash::make('pass2')]);

        $this->assertCredentials(['email' => 'admin1@localhost.com', 'password' => 'pass1']);
        $this->assertCredentials(['email' => 'admin2@localhost.com', 'password' => 'pass2']);

        User::addTableFilters([
            ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
            ['field' =>'name', 'type' => Filter::TYPE_STRING, 'caption' => 'Name'],
        ]);

        $public_id = FilterStorage::create([
            'name' => 'public_filter',
            'model' => User::class,
            'causer_type' => \Illuminate\Foundation\Auth\User::class,
            'filters' => [
                ['id' => 1, 'operator' => '!=', 'values' => [2, 3]],
                ['id' => 2, 'operator' => '~', 'values' => ['and', 'dy']],
            ],
        ])->id;

        Auth::attempt(['email' => 'admin1@localhost.com', 'password' => 'pass1']);
        $response = (array)json_decode($this->post(route('filters.storages.store', [
            'name' => 'private_filter_1',
            'filters' => [
                ['id' => 1, 'operator' => '>', 'values' => [50]],
                ['id' => 2, 'operator' => '~', 'values' => ['and', 'dy']],
            ]]))->getContent())->data;
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('filters', $response);
        $private_id_1 = $response['id'];

        $this->post(route('filters.storages.store', [
            'name' => 'private_filter_2',
            'filters' => [
                ['id' => 1, 'operator' => '<', 'values' => [50]],
            ]]));

        Auth::attempt(['email' => 'admin2@localhost.com', 'password' => 'pass2']);
        $response = (array)json_decode($this->post(route('filters.storages.store', [
            'name' => 'private_filter_2',
            'filters' => [
                ['id' => 1, 'operator' => '=', 'values' => [1,2,3,4,5,6,7,8,9,10]],
                ['id' => 2, 'operator' => '~', 'values' => ['or', 'not']],
            ]]))->getContent())->data;
        $private_id_2 = $response['id'];

        Auth::attempt(['email' => 'admin1@localhost.com', 'password' => 'pass1']);

        $response = (array)json_decode($this->get(route('filters.storages.show', $public_id))->getContent())->data;
        $this->assertEquals('public_filter', $response['name']);

        $response = (array)json_decode($this->get(route('filters.storages.edit', $private_id_1))->getContent())->data;
        $this->assertEquals('private_filter_1', $response['name']);

        $response = $this->get(route('filters.storages.edit', $private_id_2));
        $this->assertEquals(404, $response->getStatusCode());

        $response = (array)json_decode($this->put(route('filters.storages.update', [$private_id_1,
            'name' => 'private_filter_one',
            'filters' => [
                ['id' => 1, 'operator' => '>', 'values' => [50]],
                ['id' => 2, 'operator' => '~', 'values' => ['and', 'dy']],
            ]]))->getContent())->data;
        $this->assertEquals('private_filter_one', $response['name']);

        $response = (array)json_decode($this->get(route('filters.storages.index').'?filter_id=1')->getContent())->data;
        $this->assertCount(3, $response);

        $this->delete(route('filters.storages.destroy', $private_id_1));
        $response = (array)json_decode($this->get(route('filters.storages.index').'?filter_id=1')->getContent())->data;
        $this->assertCount(2, $response);

        User::factory()->count(100)->create();

        $users = User::tableFilter($response[1]->filters)->get();
        $this->assertCount(49, $users);
    }


}

