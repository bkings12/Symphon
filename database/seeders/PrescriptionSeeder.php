<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $user = User::first();

        $prescriptions = [
            [
                'customer_id' => $customers->where('name', 'John Doe')->first()->id,
                'user_id' => $user->id,
                'prescription_number' => 'RX-000001',
                'prescription_date' => now()->subDays(10),
                'diagnosis' => 'Hypertension and Type 2 Diabetes',
                'notes' => 'Patient needs regular monitoring',
                'status' => 'completed',
            ],
            [
                'customer_id' => $customers->where('name', 'Jane Smith')->first()->id,
                'user_id' => $user->id,
                'prescription_number' => 'RX-000002',
                'prescription_date' => now()->subDays(5),
                'diagnosis' => 'Asthma',
                'notes' => 'Use inhaler as needed',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->where('name', 'Robert Johnson')->first()->id,
                'user_id' => $user->id,
                'prescription_number' => 'RX-000003',
                'prescription_date' => now()->subDays(3),
                'diagnosis' => 'High cholesterol',
                'notes' => 'Follow up in 3 months',
                'status' => 'completed',
            ],
            [
                'customer_id' => $customers->where('name', 'David Brown')->first()->id,
                'user_id' => $user->id,
                'prescription_number' => 'RX-000004',
                'prescription_date' => now()->subDays(1),
                'diagnosis' => 'Type 1 Diabetes',
                'notes' => 'Monitor blood glucose levels',
                'status' => 'active',
            ],
        ];

        foreach ($prescriptions as $prescriptionData) {
            // Skip if prescription already exists
            if (Prescription::where('prescription_number', $prescriptionData['prescription_number'])->exists()) {
                continue;
            }
            
            $prescription = Prescription::create($prescriptionData);

            // Add prescription items
            $medicines = Medicine::where('requires_prescription', true)
                ->inRandomOrder()
                ->limit(rand(1, 3))
                ->get();

            foreach ($medicines as $medicine) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $medicine->id,
                    'dosage' => '1 tablet',
                    'quantity' => rand(10, 30),
                    'instructions' => 'Take with food. Continue for the prescribed duration.',
                    'status' => $prescription->status === 'completed' ? 'dispensed' : 'pending',
                ]);
            }
        }
    }
}

