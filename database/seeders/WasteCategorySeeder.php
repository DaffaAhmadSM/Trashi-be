<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use Illuminate\Database\Seeder;

class WasteCategorySeeder extends Seeder
{
    public function run(): void
    {
        WasteCategory::firstOrCreate(['name_category' => 'Organik']);
        WasteCategory::firstOrCreate(['name_category' => 'Anorganik']);
        WasteCategory::firstOrCreate(['name_category' => 'B3']);
    }
}
