<?php

namespace AND48\TableFilters\Database\Factories;

use AND48\TableFilters\Models\Filter;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilterFactory extends Factory
{
    protected $model = Filter::class;

    public function definition()
    {
        return [
            //
//            'model' => $this->faker->word,
//            'field' => $this->faker->word,
//            'type' => Filter::TYPES[rand(0,count(Filter::TYPES)-1)],
//            'caption' => $this->faker->word,
//            'related_model' => null,
        ];
    }
}

