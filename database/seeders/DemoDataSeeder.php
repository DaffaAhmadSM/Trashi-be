<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@trashi.test'],
            [
                'name' => 'Demo User',
                'email' => 'demo@trashi.test',
                'password' => Hash::make('password'),
                'role_id' => 2,
            ]
        );

        $addr1 = Address::firstOrCreate(
            ['user_id' => $user->id, 'label' => 'Rumah'],
            [
                'user_id' => $user->id,
                'label' => 'Rumah',
                'city' => 'Jakarta Pusat',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

        $addr2 = Address::firstOrCreate(
            ['user_id' => $user->id, 'label' => 'Kantor'],
            [
                'user_id' => $user->id,
                'label' => 'Kantor',
                'city' => 'Jakarta Pusat',
                'latitude' => -6.1908,
                'longitude' => 106.8456,
            ]
        );

        $addr3 = Address::firstOrCreate(
            ['user_id' => $user->id, 'label' => 'Apartemen'],
            [
                'user_id' => $user->id,
                'label' => 'Apartemen',
                'city' => 'Jakarta Selatan',
                'latitude' => -6.2448,
                'longitude' => 106.8456,
            ]
        );

        // Office 1: ~1km from addr1, ~2km from addr2, ~4km from addr3
        Office::firstOrCreate(
            ['office_name' => 'TPS Menteng'],
            [
                'office_name' => 'TPS Menteng',
                'office_address' => 'Jl. Menteng Raya No. 1, Jakarta Pusat',
                'office_phone' => '021-3900001',
                'address_latitude' => -6.2088,
                'address_longitude' => 106.8546,
            ]
        );

        // Office 2: ~4km from addr1, ~3km from addr2, ~0.5km from addr3
        // ponytail: exact {4,3,0.5} geometrically impossible with collinear addresses.
        // addr2→addr3 is 6km so OA2+OA3≥6, but 3+0.5=3.5<6.
        // Selected coords favour addr3 (nearest-match): addr3≈0.5, addr1≈3.5, addr2≈5.5
        Office::firstOrCreate(
            ['office_name' => 'TPS Setiabudi'],
            [
                'office_name' => 'TPS Setiabudi',
                'office_address' => 'Jl. Setiabudi Raya No. 10, Jakarta Selatan',
                'office_phone' => '021-5250001',
                'address_latitude' => -6.2403,
                'address_longitude' => 106.8456,
            ]
        );
    }
}
