<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::create([
            'code' => 'cnt',
            'description' => 'Contado'
        ]);

        PaymentMethod::create([
            'code' => 'cdt',
            'description' => 'Credito'
        ]);
    }
}
