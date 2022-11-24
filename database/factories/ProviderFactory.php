<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $companies = Company::count();

        return [
            'companies_id' => rand(1, $companies),
            'name' => $this->faker->name(),
            'document' => 53256985,
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'city' => $this->faker->country(),
            'state' => $this->faker->word(1),
            'description' => $this->faker->sentence(5),
        ];
    }
}
