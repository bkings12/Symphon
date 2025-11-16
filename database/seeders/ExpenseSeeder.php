<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainPharmacy = Pharmacy::where('code', 'PH-001')->first();
        $user = User::first();

        $expenses = [
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Rent',
                'title' => 'Monthly Rent Payment',
                'description' => 'Rent payment for pharmacy location',
                'amount' => 2500.00,
                'expense_date' => now()->subDays(5),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Utilities',
                'title' => 'Electricity Bill',
                'description' => 'Monthly electricity bill',
                'amount' => 350.00,
                'expense_date' => now()->subDays(3),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Utilities',
                'title' => 'Water Bill',
                'description' => 'Monthly water bill',
                'amount' => 120.00,
                'expense_date' => now()->subDays(3),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Staff',
                'title' => 'Employee Salaries',
                'description' => 'Monthly payroll for staff',
                'amount' => 8500.00,
                'expense_date' => now()->subDays(7),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Equipment',
                'title' => 'Refrigerator Maintenance',
                'description' => 'Maintenance service for medicine refrigerator',
                'amount' => 450.00,
                'expense_date' => now()->subDays(10),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Marketing',
                'title' => 'Advertising Campaign',
                'description' => 'Local newspaper advertisement',
                'amount' => 300.00,
                'expense_date' => now()->subDays(15),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Insurance',
                'title' => 'Liability Insurance',
                'description' => 'Monthly insurance premium',
                'amount' => 280.00,
                'expense_date' => now()->subDays(2),
            ],
            [
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'category' => 'Maintenance',
                'title' => 'Cleaning Services',
                'description' => 'Professional cleaning service',
                'amount' => 200.00,
                'expense_date' => now()->subDays(1),
            ],
        ];

        foreach ($expenses as $expense) {
            Expense::create($expense);
        }
    }
}

