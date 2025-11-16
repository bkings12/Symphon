<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PharmacySeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            MedicineSeeder::class,
            CustomerSeeder::class,
            UserSeeder::class,
            PurchaseSeeder::class,
            PrescriptionSeeder::class,
            SaleSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
