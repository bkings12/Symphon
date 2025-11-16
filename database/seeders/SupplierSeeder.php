<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'MedSupply Co.',
                'code' => 'SUP-001',
                'contact_person' => 'John Smith',
                'phone' => '+1-555-1001',
                'email' => 'john@medsupply.com',
                'address' => '100 Industrial Blvd, Manufacturing District',
                'notes' => 'Primary supplier for antibiotics and cardiovascular medications',
                'is_active' => true,
            ],
            [
                'name' => 'Pharma Distributors Inc.',
                'code' => 'SUP-002',
                'contact_person' => 'Sarah Johnson',
                'phone' => '+1-555-1002',
                'email' => 'sarah@pharmadist.com',
                'address' => '200 Medical Way, Healthcare Park',
                'notes' => 'Specializes in diabetes and respiratory medications',
                'is_active' => true,
            ],
            [
                'name' => 'Global Health Supplies',
                'code' => 'SUP-003',
                'contact_person' => 'Michael Chen',
                'phone' => '+1-555-1003',
                'email' => 'michael@globalhealth.com',
                'address' => '300 Supply Chain Road, Logistics Center',
                'notes' => 'Wholesale distributor for vitamins and supplements',
                'is_active' => true,
            ],
            [
                'name' => 'Local Pharma Solutions',
                'code' => 'SUP-004',
                'contact_person' => 'Emily Davis',
                'phone' => '+1-555-1004',
                'email' => 'emily@localpharma.com',
                'address' => '50 Community Street, Local Business District',
                'notes' => 'Regional supplier for gastrointestinal and dermatology products',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['code' => $supplier['code']],
                $supplier
            );
        }
    }
}

