<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainPharmacy = Pharmacy::where('code', 'PH-001')->first();
        $downtownPharmacy = Pharmacy::where('code', 'PH-002')->first();

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@pharmacy.com',
                'password' => Hash::make('password'),
                'pharmacy_id' => $mainPharmacy->id,
            ],
            [
                'name' => 'Pharmacist John',
                'email' => 'pharmacist@pharmacy.com',
                'password' => Hash::make('password'),
                'pharmacy_id' => $mainPharmacy->id,
            ],
            [
                'name' => 'Manager Sarah',
                'email' => 'manager@pharmacy.com',
                'password' => Hash::make('password'),
                'pharmacy_id' => $downtownPharmacy->id,
            ],
            [
                'name' => 'Staff Member',
                'email' => 'staff@pharmacy.com',
                'password' => Hash::make('password'),
                'pharmacy_id' => $downtownPharmacy->id,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}

