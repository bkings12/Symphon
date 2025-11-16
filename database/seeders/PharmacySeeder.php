<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pharmacies = [
            [
                'name' => 'Main Pharmacy',
                'code' => 'PH-001',
                'address' => '123 Main Street, City Center',
                'phone' => '+1-555-0101',
                'email' => 'main@pharmacy.com',
                'license_number' => 'PH-LIC-2024-001',
                'is_active' => true,
            ],
            [
                'name' => 'Downtown Branch',
                'code' => 'PH-002',
                'address' => '456 Commerce Avenue, Downtown',
                'phone' => '+1-555-0102',
                'email' => 'downtown@pharmacy.com',
                'license_number' => 'PH-LIC-2024-002',
                'is_active' => true,
            ],
        ];

        foreach ($pharmacies as $pharmacy) {
            Pharmacy::firstOrCreate(
                ['code' => $pharmacy['code']],
                $pharmacy
            );
        }
    }
}

