<?php

namespace AND48\TableFilters\Tests;

use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

class UserFactory extends TestbenchUserFactory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'birthday' => $this->faker->dateTime,
            'is_blocked' => $this->faker->boolean,
            'balance' => $this->faker->randomFloat(),
            'status' => User::STATUSES[rand(0,count(User::STATUSES)-1)],
            'parent_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
