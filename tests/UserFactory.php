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
            'email' => $this->faker->email,
            'birthday' => $this->faker->dateTime,
            'is_blocked' => false,
            'balance' => $this->faker->randomFloat(),
            'status' => User::STATUSES[rand(0,count(User::STATUSES)-1)],
            'parent_id' => null,
        ];
    }
}
