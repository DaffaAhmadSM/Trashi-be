<?php

namespace Database\Seeders;

use App\Models\CheckoutConfig;
use Illuminate\Database\Seeder;

class CheckoutConfigSeeder extends Seeder
{
    public function run(): void
    {
        CheckoutConfig::firstOrCreate(
            ['key' => 'price_per_km'],
            ['value' => '5000']
        );

        CheckoutConfig::firstOrCreate(
            ['key' => 'min_fee'],
            ['value' => '20000']
        );

        CheckoutConfig::firstOrCreate(
            ['key' => 'max_distance_km'],
            ['value' => '30']
        );

        CheckoutConfig::firstOrCreate(
            ['key' => 'currency'],
            ['value' => 'IDR']
        );
    }
}
