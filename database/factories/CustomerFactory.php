<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
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
            'document' => $this->faker->randomNumber(8, true),            
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
