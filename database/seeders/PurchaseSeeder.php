<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainPharmacy = Pharmacy::where('code', 'PH-001')->first();
        $user = User::first();
        $suppliers = Supplier::all();

        // Create a few purchases
        for ($i = 1; $i <= 5; $i++) {
            $invoiceNumber = 'PUR-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            // Skip if purchase already exists
            if (Purchase::where('invoice_number', $invoiceNumber)->exists()) {
                continue;
            }
            
            $supplier = $suppliers->random();
            $purchaseDate = now()->subDays(rand(1, 30));

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'pharmacy_id' => $mainPharmacy->id,
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'purchase_date' => $purchaseDate,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'status' => 'completed',
                'notes' => 'Stock replenishment order #' . $i,
            ]);

            $medicines = Medicine::inRandomOrder()->limit(rand(3, 6))->get();
            $subtotal = 0;

            foreach ($medicines as $medicine) {
                $quantity = rand(20, 100);
                $unitCost = $medicine->cost_price;
                $itemTotal = $quantity * $unitCost;
                $subtotal += $itemTotal;

                $expiryDate = $purchaseDate->copy()->addMonths(rand(12, 36));
                $batchNumber = 'BATCH-' . strtoupper(uniqid());

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $itemTotal,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                ]);

                // Update medicine stock
                $medicine->increment('stock_quantity', $quantity);
            }

            $taxAmount = $subtotal * 0.10; // 10% tax
            $discountAmount = $subtotal * 0.05; // 5% discount
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $purchase->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);
        }
    }
}

