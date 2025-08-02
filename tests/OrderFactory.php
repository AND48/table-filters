<?php

namespace AND48\TableFilters\Tests;

use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

class OrderFactory extends TestbenchUserFactory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'I'.rand(100000,999999),
            'price' => $this->faker->randomFloat(),
            'status' => Order::STATUSES[rand(0,count(Order::STATUSES)-1)],
        ];
    }
}
