<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'phone' => '+1-555-2001',
                'email' => 'john.doe@email.com',
                'date_of_birth' => '1985-05-15',
                'gender' => 'Male',
                'address' => '123 Oak Street, Residential Area',
                'medical_history' => 'Hypertension, Type 2 Diabetes',
                'allergies' => 'Penicillin',
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '+1-555-2002',
                'email' => 'jane.smith@email.com',
                'date_of_birth' => '1990-08-22',
                'gender' => 'Female',
                'address' => '456 Maple Avenue, Suburb District',
                'medical_history' => 'Asthma',
                'allergies' => 'Sulfa drugs',
            ],
            [
                'name' => 'Robert Johnson',
                'phone' => '+1-555-2003',
                'email' => 'robert.j@email.com',
                'date_of_birth' => '1978-12-10',
                'gender' => 'Male',
                'address' => '789 Pine Road, Downtown',
                'medical_history' => 'High cholesterol',
                'allergies' => null,
            ],
            [
                'name' => 'Mary Williams',
                'phone' => '+1-555-2004',
                'email' => 'mary.williams@email.com',
                'date_of_birth' => '1992-03-25',
                'gender' => 'Female',
                'address' => '321 Elm Street, City Center',
                'medical_history' => null,
                'allergies' => 'Latex',
            ],
            [
                'name' => 'David Brown',
                'phone' => '+1-555-2005',
                'email' => 'david.brown@email.com',
                'date_of_birth' => '1988-07-18',
                'gender' => 'Male',
                'address' => '654 Cedar Lane, Business District',
                'medical_history' => 'Type 1 Diabetes',
                'allergies' => 'Iodine',
            ],
            [
                'name' => 'Sarah Davis',
                'phone' => '+1-555-2006',
                'email' => 'sarah.davis@email.com',
                'date_of_birth' => '1995-11-30',
                'gender' => 'Female',
                'address' => '987 Birch Court, Residential Zone',
                'medical_history' => 'COPD',
                'allergies' => null,
            ],
            [
                'name' => 'Michael Wilson',
                'phone' => '+1-555-2007',
                'email' => 'michael.w@email.com',
                'date_of_birth' => '1982-09-05',
                'gender' => 'Male',
                'address' => '147 Spruce Way, Uptown Area',
                'medical_history' => 'Hypertension',
                'allergies' => 'Aspirin',
            ],
            [
                'name' => 'Emily Martinez',
                'phone' => '+1-555-2008',
                'email' => 'emily.martinez@email.com',
                'date_of_birth' => '1991-01-14',
                'gender' => 'Female',
                'address' => '258 Willow Drive, Suburban',
                'medical_history' => null,
                'allergies' => null,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }
}

