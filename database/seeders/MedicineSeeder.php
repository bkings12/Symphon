<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            // Antibiotics
            [
                'category_id' => Category::where('name', 'Antibiotics')->first()->id,
                'name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin',
                'barcode' => '1234567890123',
                'sku' => 'MED-AMOX-500',
                'description' => 'Broad-spectrum antibiotic for bacterial infections',
                'unit' => 'Tablet',
                'cost_price' => 2.50,
                'selling_price' => 5.00,
                'reorder_level' => 50,
                'stock_quantity' => 200,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            [
                'category_id' => Category::where('name', 'Antibiotics')->first()->id,
                'name' => 'Ciprofloxacin 500mg',
                'generic_name' => 'Ciprofloxacin',
                'barcode' => '1234567890124',
                'sku' => 'MED-CIPRO-500',
                'description' => 'Fluoroquinolone antibiotic for various infections',
                'unit' => 'Tablet',
                'cost_price' => 3.00,
                'selling_price' => 6.50,
                'reorder_level' => 40,
                'stock_quantity' => 150,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            // Analgesics
            [
                'category_id' => Category::where('name', 'Analgesics')->first()->id,
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'barcode' => '1234567890125',
                'sku' => 'MED-PARA-500',
                'description' => 'Pain reliever and fever reducer',
                'unit' => 'Tablet',
                'cost_price' => 0.50,
                'selling_price' => 1.00,
                'reorder_level' => 100,
                'stock_quantity' => 500,
                'requires_prescription' => false,
                'is_active' => true,
            ],
            [
                'category_id' => Category::where('name', 'Analgesics')->first()->id,
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'barcode' => '1234567890126',
                'sku' => 'MED-IBU-400',
                'description' => 'Non-steroidal anti-inflammatory drug for pain and inflammation',
                'unit' => 'Tablet',
                'cost_price' => 0.75,
                'selling_price' => 1.50,
                'reorder_level' => 80,
                'stock_quantity' => 400,
                'requires_prescription' => false,
                'is_active' => true,
            ],
            // Cardiovascular
            [
                'category_id' => Category::where('name', 'Cardiovascular')->first()->id,
                'name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine',
                'barcode' => '1234567890127',
                'sku' => 'MED-AMLO-5',
                'description' => 'Calcium channel blocker for hypertension',
                'unit' => 'Tablet',
                'cost_price' => 1.50,
                'selling_price' => 3.00,
                'reorder_level' => 60,
                'stock_quantity' => 250,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            [
                'category_id' => Category::where('name', 'Cardiovascular')->first()->id,
                'name' => 'Atorvastatin 20mg',
                'generic_name' => 'Atorvastatin',
                'barcode' => '1234567890128',
                'sku' => 'MED-ATOR-20',
                'description' => 'Statin medication for cholesterol management',
                'unit' => 'Tablet',
                'cost_price' => 2.00,
                'selling_price' => 4.50,
                'reorder_level' => 50,
                'stock_quantity' => 200,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            // Diabetes
            [
                'category_id' => Category::where('name', 'Diabetes')->first()->id,
                'name' => 'Metformin 500mg',
                'generic_name' => 'Metformin',
                'barcode' => '1234567890129',
                'sku' => 'MED-MET-500',
                'description' => 'First-line medication for type 2 diabetes',
                'unit' => 'Tablet',
                'cost_price' => 1.00,
                'selling_price' => 2.00,
                'reorder_level' => 70,
                'stock_quantity' => 300,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            [
                'category_id' => Category::where('name', 'Diabetes')->first()->id,
                'name' => 'Glipizide 5mg',
                'generic_name' => 'Glipizide',
                'barcode' => '1234567890130',
                'sku' => 'MED-GLIP-5',
                'description' => 'Sulfonylurea for diabetes management',
                'unit' => 'Tablet',
                'cost_price' => 1.25,
                'selling_price' => 2.50,
                'reorder_level' => 60,
                'stock_quantity' => 250,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            // Respiratory
            [
                'category_id' => Category::where('name', 'Respiratory')->first()->id,
                'name' => 'Salbutamol Inhaler',
                'generic_name' => 'Salbutamol',
                'barcode' => '1234567890131',
                'sku' => 'MED-SALB-INH',
                'description' => 'Bronchodilator for asthma and COPD',
                'unit' => 'Inhaler',
                'cost_price' => 8.00,
                'selling_price' => 15.00,
                'reorder_level' => 20,
                'stock_quantity' => 80,
                'requires_prescription' => true,
                'is_active' => true,
            ],
            // Vitamins
            [
                'category_id' => Category::where('name', 'Vitamins & Supplements')->first()->id,
                'name' => 'Vitamin D3 1000 IU',
                'generic_name' => 'Cholecalciferol',
                'barcode' => '1234567890132',
                'sku' => 'MED-VITD-1000',
                'description' => 'Vitamin D supplement for bone health',
                'unit' => 'Capsule',
                'cost_price' => 0.50,
                'selling_price' => 1.25,
                'reorder_level' => 100,
                'stock_quantity' => 400,
                'requires_prescription' => false,
                'is_active' => true,
            ],
            [
                'category_id' => Category::where('name', 'Vitamins & Supplements')->first()->id,
                'name' => 'Multivitamin Complex',
                'generic_name' => 'Multivitamin',
                'barcode' => '1234567890133',
                'sku' => 'MED-MVIT-COMP',
                'description' => 'Complete multivitamin and mineral supplement',
                'unit' => 'Tablet',
                'cost_price' => 1.00,
                'selling_price' => 2.50,
                'reorder_level' => 80,
                'stock_quantity' => 350,
                'requires_prescription' => false,
                'is_active' => true,
            ],
            // Gastrointestinal
            [
                'category_id' => Category::where('name', 'Gastrointestinal')->first()->id,
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole',
                'barcode' => '1234567890134',
                'sku' => 'MED-OMEP-20',
                'description' => 'Proton pump inhibitor for acid reflux',
                'unit' => 'Capsule',
                'cost_price' => 1.50,
                'selling_price' => 3.00,
                'reorder_level' => 60,
                'stock_quantity' => 250,
                'requires_prescription' => false,
                'is_active' => true,
            ],
            // Dermatology
            [
                'category_id' => Category::where('name', 'Dermatology')->first()->id,
                'name' => 'Hydrocortisone Cream 1%',
                'generic_name' => 'Hydrocortisone',
                'barcode' => '1234567890135',
                'sku' => 'MED-HC-1-CRM',
                'description' => 'Topical corticosteroid for skin inflammation',
                'unit' => 'Tube',
                'cost_price' => 3.00,
                'selling_price' => 6.00,
                'reorder_level' => 30,
                'stock_quantity' => 120,
                'requires_prescription' => false,
                'is_active' => true,
            ],
        ];

        foreach ($medicines as $medicine) {
            Medicine::firstOrCreate(
                ['sku' => $medicine['sku']],
                $medicine
            );
        }
    }
}

