<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainPharmacy = Pharmacy::where('code', 'PH-001')->first();
        $user = User::first();
        $customers = Customer::all();
        $prescriptions = Prescription::where('status', 'completed')->get();

        // Create sales with prescriptions
        foreach ($prescriptions as $prescription) {
            $invoiceNumber = 'SAL-' . str_pad($prescription->id, 6, '0', STR_PAD_LEFT);
            
            // Skip if sale already exists
            if (Sale::where('invoice_number', $invoiceNumber)->exists()) {
                continue;
            }
            
            $saleDate = $prescription->prescription_date->copy()->addDays(rand(1, 3));
            
            $sale = Sale::create([
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'customer_id' => $prescription->customer_id,
                'prescription_id' => $prescription->id,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $saleDate,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'status' => 'completed',
                'notes' => 'Prescription sale',
            ]);

            $subtotal = 0;
            $prescriptionItems = $prescription->items;

            foreach ($prescriptionItems as $prescriptionItem) {
                $medicine = $prescriptionItem->medicine;
                $quantity = $prescriptionItem->quantity;
                
                // Get available stock batch
                $stockBatch = StockBatch::where('medicine_id', $medicine->id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->first();

                if ($stockBatch && $stockBatch->remaining_quantity >= $quantity) {
                    $unitPrice = $medicine->selling_price;
                    $itemTotal = $quantity * $unitPrice;
                    $subtotal += $itemTotal;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'stock_batch_id' => $stockBatch->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0,
                        'total_price' => $itemTotal,
                    ]);

                    // Update stock
                    $stockBatch->decrement('remaining_quantity', $quantity);
                    $medicine->decrement('stock_quantity', $quantity);
                }
            }

            $taxAmount = $subtotal * 0.10;
            $discountAmount = $subtotal * 0.05;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);

            // Create payment
            Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => rand(0, 1) ? 'cash' : 'card',
                'amount' => $totalAmount,
                'reference_number' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Payment for prescription sale',
                'status' => 'completed',
            ]);
        }

        // Create some regular sales (without prescriptions)
        for ($i = 1; $i <= 5; $i++) {
            $invoiceNumber = 'SAL-REG-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            // Skip if sale already exists
            if (Sale::where('invoice_number', $invoiceNumber)->exists()) {
                continue;
            }
            
            $customer = $customers->random();
            $saleDate = now()->subDays(rand(1, 20));

            $sale = Sale::create([
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'customer_id' => $customer->id,
                'prescription_id' => null,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $saleDate,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'status' => 'completed',
                'notes' => 'Regular sale',
            ]);

            $medicines = Medicine::where('requires_prescription', false)
                ->inRandomOrder()
                ->limit(rand(1, 4))
                ->get();

            $subtotal = 0;

            foreach ($medicines as $medicine) {
                $quantity = rand(1, 5);
                
                // Get available stock batch
                $stockBatch = StockBatch::where('medicine_id', $medicine->id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->first();

                if ($stockBatch && $stockBatch->remaining_quantity >= $quantity) {
                    $unitPrice = $medicine->selling_price;
                    $itemTotal = $quantity * $unitPrice;
                    $subtotal += $itemTotal;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'stock_batch_id' => $stockBatch->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0,
                        'total_price' => $itemTotal,
                    ]);

                    // Update stock
                    $stockBatch->decrement('remaining_quantity', $quantity);
                    $medicine->decrement('stock_quantity', $quantity);
                }
            }

            $taxAmount = $subtotal * 0.10;
            $discountAmount = $subtotal * 0.02;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);

            // Create payment
            Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => rand(0, 1) ? 'cash' : 'card',
                'amount' => $totalAmount,
                'reference_number' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Payment for regular sale',
                'status' => 'completed',
            ]);
        }
    }
}

