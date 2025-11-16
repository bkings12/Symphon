<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Antibiotics',
                'slug' => 'antibiotics',
                'description' => 'Medications used to treat bacterial infections',
                'is_active' => true,
            ],
            [
                'name' => 'Analgesics',
                'slug' => 'analgesics',
                'description' => 'Pain relief medications',
                'is_active' => true,
            ],
            [
                'name' => 'Cardiovascular',
                'slug' => 'cardiovascular',
                'description' => 'Medications for heart and blood pressure conditions',
                'is_active' => true,
            ],
            [
                'name' => 'Diabetes',
                'slug' => 'diabetes',
                'description' => 'Medications for diabetes management',
                'is_active' => true,
            ],
            [
                'name' => 'Respiratory',
                'slug' => 'respiratory',
                'description' => 'Medications for asthma, COPD, and respiratory conditions',
                'is_active' => true,
            ],
            [
                'name' => 'Vitamins & Supplements',
                'slug' => 'vitamins-supplements',
                'description' => 'Vitamins, minerals, and dietary supplements',
                'is_active' => true,
            ],
            [
                'name' => 'Gastrointestinal',
                'slug' => 'gastrointestinal',
                'description' => 'Medications for digestive system disorders',
                'is_active' => true,
            ],
            [
                'name' => 'Dermatology',
                'slug' => 'dermatology',
                'description' => 'Medications for skin conditions',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}

