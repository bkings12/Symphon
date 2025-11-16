<?php

namespace App\Livewire;

use App\Helpers\SettingsHelper;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MpesaSTK;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockBatch;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PosComponent extends Component
{
    // Search
    public $search = '';
    public $searchResults = [];
    public $showSearchResults = false;

    // Cart
    public $cart = [];
    public $cartTotal = 0;
    public $cartSubtotal = 0;
    public $taxRate = 0;
    public $taxAmount = 0;
    public $discountAmount = 0;
    public $discountType = 'fixed'; // 'fixed' or 'percentage'
    public $discountValue = 0;

    // Customer
    public $customerId = null;
    public $customerSearch = '';
    public $customerResults = [];
    public $selectedCustomer = null;

    // Payment
    public $paymentMethod = 'cash';
    public $amountPaid = 0;
    public $change = 0;
    public $mpesaPhoneNumber = '';
    public $mpesaProcessing = false;
    public $mpesaStatus = null; // 'pending', 'success', 'failed'

    // UI State
    public $showCustomerModal = false;
    public $showPaymentModal = false;
    public $showPhoneModal = false;
    public $showReceiptModal = false;
    public $processingSale = false;
    
    // Receipt Data
    public $lastSale = null;
    public $lastSaleItems = [];
    public $lastCustomer = null;
    public $lastPayment = null;

    public function getHasValidPhoneNumberProperty()
    {
        return !empty(trim($this->mpesaPhoneNumber ?? ''));
    }

    protected $listeners = ['scanBarcode', 'clearCart', 'sale-completed'];

    public function mount()
    {
        $this->taxRate = SettingsHelper::taxRateDecimal();
        $this->showPhoneModal = false;
        $this->showReceiptModal = false;
        $this->resetCart();
        $this->loadDefaultMedicines();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchMedicines();
        } else if (strlen($this->search) === 0) {
            // Show default medicines when search is empty
            $this->loadDefaultMedicines();
        } else {
            $this->searchResults = [];
            $this->showSearchResults = false;
        }
    }

    public function searchMedicines()
    {
        $query = Medicine::where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('generic_name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->where('stock_quantity', '>', 0)
            ->limit(10)
            ->get();

        $this->searchResults = $query->map(function ($medicine) {
            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'generic_name' => $medicine->generic_name,
                'sku' => $medicine->sku,
                'barcode' => $medicine->barcode,
                'selling_price' => $medicine->selling_price,
                'stock_quantity' => $medicine->stock_quantity,
                'unit' => $medicine->unit,
                'requires_prescription' => $medicine->requires_prescription,
            ];
        })->toArray();

        $this->showSearchResults = count($this->searchResults) > 0;
    }

    public function loadDefaultMedicines()
    {
        $query = Medicine::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $this->searchResults = $query->map(function ($medicine) {
            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'generic_name' => $medicine->generic_name,
                'sku' => $medicine->sku,
                'barcode' => $medicine->barcode,
                'selling_price' => $medicine->selling_price,
                'stock_quantity' => $medicine->stock_quantity,
                'unit' => $medicine->unit,
            ];
        })->toArray();

        $this->showSearchResults = count($this->searchResults) > 0;
    }

    public function scanBarcode($barcode)
    {
        $this->search = $barcode;
        $this->searchMedicines();
        
        if (count($this->searchResults) === 1) {
            $this->addToCart($this->searchResults[0]['id']);
        }
    }

    public function addToCart($medicineId)
    {
        try {
            $medicine = Medicine::find($medicineId);
            
            if (!$medicine || $medicine->stock_quantity <= 0) {
                $this->dispatch('notify', message: 'Medicine not available or out of stock', type: 'error');
                return;
            }

            // Check if already in cart
            $existingIndex = collect($this->cart)->search(function ($item) use ($medicineId) {
                return $item['medicine_id'] == $medicineId;
            });

            if ($existingIndex !== false) {
                // Increment quantity
                $this->cart[$existingIndex]['quantity']++;
            } else {
                // Add new item
                $this->cart[] = [
                    'medicine_id' => $medicine->id,
                    'name' => $medicine->name,
                    'generic_name' => $medicine->generic_name,
                    'sku' => $medicine->sku,
                    'unit' => $medicine->unit,
                    'selling_price' => $medicine->selling_price,
                    'quantity' => 1,
                    'stock_available' => $medicine->stock_quantity,
                ];
            }

            $this->updateCartTotals();
            $this->search = '';
            $this->searchResults = [];
            $this->showSearchResults = false;
            $this->dispatch('notify', message: $medicine->name . ' added to cart', type: 'success');
            $this->dispatch('focus-search');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->updateCartTotals();
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);
            return;
        }

        if ($quantity > $this->cart[$index]['stock_available']) {
            $this->dispatch('notify', message: 'Insufficient stock available', type: 'error');
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->updateCartTotals();
    }

    public function updateCartTotals()
    {
        $this->cartSubtotal = collect($this->cart)->sum(function ($item) {
            return (float)$item['selling_price'] * (int)$item['quantity'];
        });

        // Apply discount
        if ($this->discountValue > 0) {
            if ($this->discountType === 'percentage') {
                $this->discountAmount = ($this->cartSubtotal * (float)$this->discountValue) / 100;
            } else {
                $this->discountAmount = min((float)$this->discountValue, $this->cartSubtotal);
            }
        } else {
            $this->discountAmount = 0;
        }

        // Calculate tax
        $taxableAmount = $this->cartSubtotal - $this->discountAmount;
        $this->taxAmount = $taxableAmount * (float)$this->taxRate;
        $this->cartTotal = (float)($taxableAmount + $this->taxAmount);
    }

    public function updatedDiscountValue()
    {
        $this->updateCartTotals();
    }

    public function updatedDiscountType()
    {
        $this->updateCartTotals();
    }

    public function updatedAmountPaid()
    {
        $this->change = max(0, (float)$this->amountPaid - (float)$this->cartTotal);
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) >= 2) {
            $this->customerResults = Customer::where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                ->limit(10)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                    ];
                })->toArray();
        } else {
            $this->customerResults = [];
        }
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customerId = $customer->id;
            $this->selectedCustomer = [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ];
            $this->customerSearch = '';
            $this->customerResults = [];
            $this->showCustomerModal = false;
        }
    }

    public function removeCustomer()
    {
        $this->customerId = null;
        $this->selectedCustomer = null;
        $this->customerSearch = '';
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Cart is empty', type: 'error');
            return;
        }

        $this->amountPaid = $this->cartTotal;
        $this->change = 0;
        // Only set phone number if customer has one, otherwise keep it empty
        if ($this->selectedCustomer && isset($this->selectedCustomer['phone']) && !empty($this->selectedCustomer['phone'])) {
            $this->mpesaPhoneNumber = $this->selectedCustomer['phone'];
        } else {
            $this->mpesaPhoneNumber = '';
        }
        $this->mpesaStatus = null;
        $this->showPhoneModal = false;
        $this->showPaymentModal = true;
    }

    public function updatedPaymentMethod()
    {
        if ($this->paymentMethod === 'mpesa') {
            // Pre-fill phone number if customer is selected, otherwise leave empty
            if ($this->selectedCustomer && isset($this->selectedCustomer['phone']) && !empty($this->selectedCustomer['phone'])) {
                $this->mpesaPhoneNumber = $this->selectedCustomer['phone'];
            } else {
                $this->mpesaPhoneNumber = '';
            }
            $this->mpesaStatus = null;
            $this->showPhoneModal = false;
        } else {
            // Clear M-Pesa related fields when switching to other payment methods
            $this->mpesaPhoneNumber = '';
            $this->mpesaStatus = null;
            $this->mpesaProcessing = false;
            $this->showPhoneModal = false;
        }
    }

    public function processSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Cart is empty', type: 'error');
            return;
        }

        // ALWAYS open payment modal first if it's not already open
        // This ensures user can select payment method and enter details
        if (!$this->showPaymentModal) {
            $this->openPaymentModal();
            // Stop here - let user interact with modal first
            return;
        }

        // Only proceed if modal is open and user has interacted with it
        // For M-Pesa, handle differently - check FIRST before any other processing
        // Use strict comparison and ensure it's a string
        $currentPaymentMethod = trim((string)$this->paymentMethod);
        $paymentMethodLower = strtolower($currentPaymentMethod);
        
        // Debug: Log the payment method
        Log::info('processSale called', [
            'paymentMethod' => $currentPaymentMethod,
            'paymentMethodLower' => $paymentMethodLower,
            'isMpesa' => ($paymentMethodLower === 'mpesa'),
            'cartTotal' => $this->cartTotal,
            'mpesaPhoneNumber' => $this->mpesaPhoneNumber,
        ]);
        
        // CRITICAL: Check for M-Pesa FIRST and handle separately - this MUST return
        if ($paymentMethodLower === 'mpesa') {
            Log::info('Routing to M-Pesa payment flow');
            // Ensure cartTotal is recalculated before processing M-Pesa
            $this->updateCartTotals();
            
            // Final validation - ensure cartTotal is NOT the phone number
            if ($this->cartTotal == $this->mpesaPhoneNumber || 
                (string)$this->cartTotal === (string)$this->mpesaPhoneNumber ||
                !is_numeric($this->cartTotal)) {
                Log::error('Cart total is invalid for M-Pesa', [
                    'cartTotal' => $this->cartTotal,
                    'mpesaPhoneNumber' => $this->mpesaPhoneNumber,
                ]);
                $this->dispatch('notify', message: 'Error: Cart total is invalid. Please refresh the page.', type: 'error');
                return;
            }
            
            $this->processMpesaPayment();
            return; // CRITICAL: Must return here
        }
        
        // If we reach here, it's NOT M-Pesa, so proceed with regular payment
        Log::info('Proceeding with regular payment flow', ['paymentMethod' => $currentPaymentMethod]);

        // Recalculate cart totals to ensure they're correct
        $this->updateCartTotals();
        
        // CRITICAL: Check if cartTotal has been corrupted with phone number
        if ($this->cartTotal == $this->mpesaPhoneNumber || 
            (string)$this->cartTotal === (string)$this->mpesaPhoneNumber ||
            !is_numeric($this->cartTotal) || 
            $this->cartTotal <= 0) {
            // If corrupted, recalculate from cart
            $this->updateCartTotals();
            // Check again
            if ($this->cartTotal == $this->mpesaPhoneNumber || !is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
                $this->dispatch('notify', message: 'Error: Cart total is invalid. CartTotal: ' . $this->cartTotal . ', Phone: ' . $this->mpesaPhoneNumber . '. Please refresh the page.', type: 'error');
                return;
            }
        }

        if ((float)$this->amountPaid < (float)$this->cartTotal) {
            $this->dispatch('notify', message: 'Insufficient payment amount', type: 'error');
            return;
        }

        $this->processingSale = true;

        try {
            DB::transaction(function () {
                $user = Auth::user();
                $pharmacy = $user->pharmacy ?? Pharmacy::first();

                // Calculate totals directly from cart to avoid any property corruption
                $calculatedSubtotal = collect($this->cart)->sum(function ($item) {
                    return (float)$item['selling_price'] * (int)$item['quantity'];
                });
                
                // Apply discount
                $calculatedDiscount = 0;
                if ($this->discountValue > 0) {
                    if ($this->discountType === 'percentage') {
                        $calculatedDiscount = ($calculatedSubtotal * (float)$this->discountValue) / 100;
                    } else {
                        $calculatedDiscount = min((float)$this->discountValue, $calculatedSubtotal);
                    }
                }
                
                // Calculate tax
                $taxableAmount = $calculatedSubtotal - $calculatedDiscount;
                $calculatedTax = $taxableAmount * (float)$this->taxRate;
                $calculatedTotal = $taxableAmount + $calculatedTax;
                
                // Validate calculated total
                if (!is_numeric($calculatedTotal) || $calculatedTotal <= 0) {
                    throw new \Exception('Invalid calculated total: ' . $calculatedTotal);
                }
                
                // CRITICAL: Ensure calculated total is NOT the phone number
                if ($calculatedTotal == $this->mpesaPhoneNumber || (string)$calculatedTotal === (string)$this->mpesaPhoneNumber) {
                    throw new \Exception('Calculated total matches phone number! Total: ' . $calculatedTotal . ', Phone: ' . $this->mpesaPhoneNumber);
                }

                // Generate invoice number
                $invoiceNumber = 'SAL-POS-' . str_pad(Sale::max('id') + 1 ?? 1, 6, '0', STR_PAD_LEFT);

                // Log before creating sale for debugging
                Log::info('Creating sale', [
                    'calculatedSubtotal' => $calculatedSubtotal,
                    'calculatedTax' => $calculatedTax,
                    'calculatedDiscount' => $calculatedDiscount,
                    'calculatedTotal' => $calculatedTotal,
                    'mpesaPhoneNumber' => $this->mpesaPhoneNumber,
                    'paymentMethod' => $this->paymentMethod,
                ]);
                
                // Create sale using calculated values
                $sale = Sale::create([
                    'pharmacy_id' => $pharmacy->id,
                    'user_id' => $user->id,
                    'customer_id' => $this->customerId,
                    'prescription_id' => null,
                    'invoice_number' => $invoiceNumber,
                    'sale_date' => now(),
                    'subtotal' => $calculatedSubtotal,
                    'tax_amount' => $calculatedTax,
                    'discount_amount' => $calculatedDiscount,
                    'total_amount' => $calculatedTotal,
                    'status' => 'completed',
                    'notes' => 'POS Sale',
                ]);
                
                // Refresh to get the actual saved value
                $sale->refresh();
                
                // Log after creation
                Log::info('Sale created', [
                    'sale_id' => $sale->id,
                    'sale_total_amount' => $sale->total_amount,
                    'calculatedTotal' => $calculatedTotal,
                ]);

                // Create sale items and update stock
                foreach ($this->cart as $cartItem) {
                    $medicine = Medicine::find($cartItem['medicine_id']);
                    
                    if (!$medicine || $medicine->stock_quantity < $cartItem['quantity']) {
                        throw new \Exception("Insufficient stock for {$medicine->name}");
                    }

                    // Get available stock batch (FIFO - First In First Out)
                    $stockBatch = StockBatch::where('medicine_id', $medicine->id)
                        ->where('remaining_quantity', '>', 0)
                        ->where('expiry_date', '>', now())
                        ->orderBy('expiry_date', 'asc')
                        ->first();

                    if (!$stockBatch || $stockBatch->remaining_quantity < $cartItem['quantity']) {
                        throw new \Exception("Insufficient stock batch for {$medicine->name}");
                    }

                    $quantity = $cartItem['quantity'];
                    $unitPrice = $cartItem['selling_price'];
                    $totalPrice = $quantity * $unitPrice;

                    // Create sale item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'stock_batch_id' => $stockBatch->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0,
                        'total_price' => $totalPrice,
                    ]);

                    // Update stock batch
                    $stockBatch->decrement('remaining_quantity', $quantity);

                    // Update medicine stock
                    $medicine->decrement('stock_quantity', $quantity);
                }

                // Use the sale's total_amount which was just created, ensuring it's fresh
                $paymentAmount = (float)$sale->total_amount;
                
                // CRITICAL: Multiple validation checks
                // Check 1: Ensure it's numeric
                if (!is_numeric($paymentAmount) || $paymentAmount <= 0) {
                    throw new \Exception('Invalid payment amount from sale: ' . $sale->total_amount);
                }
                
                // Check 2: Ensure it's not the phone number (compare as both string and number)
                $phoneAsNumber = (float)$this->mpesaPhoneNumber;
                if ($paymentAmount == $phoneAsNumber || 
                    (string)$paymentAmount === (string)$this->mpesaPhoneNumber ||
                    abs($paymentAmount - $phoneAsNumber) < 0.01) {
                    throw new \Exception('Payment amount matches phone number! Amount: ' . $paymentAmount . ', Phone: ' . $this->mpesaPhoneNumber . ', CalculatedTotal: ' . $calculatedTotal);
                }
                
                // Check 3: Sanity check - reasonable amount range
                if ($paymentAmount > 1000000 || $paymentAmount < 0.01) {
                    throw new \Exception('Payment amount out of reasonable range: ' . $paymentAmount);
                }
                
                // Check 4: Ensure payment method is not mpesa in regular flow
                $paymentMethod = trim((string)$this->paymentMethod);
                if (strtolower($paymentMethod) === 'mpesa') {
                    throw new \Exception('M-Pesa payment should use processMpesaPayment method. Current method: "' . $paymentMethod . '"');
                }
                
                // Final validation: Compare with calculated total to ensure they match
                if (abs($paymentAmount - $calculatedTotal) > 0.01) {
                    throw new \Exception('Payment amount mismatch. Sale total: ' . $paymentAmount . ', Calculated: ' . $calculatedTotal);
                }
                
                $payment = Payment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $paymentAmount,
                    'reference_number' => 'PAY-' . strtoupper(uniqid()),
                    'notes' => 'POS Payment',
                    'status' => 'completed',
                ]);

                // Store receipt data - reload with relationships
                $sale->refresh();
                $this->lastSale = $sale->load('items.medicine', 'customer', 'payments');
                $this->lastSaleItems = $sale->items()->with('medicine')->get()->toArray();
                $this->lastCustomer = $sale->customer;
                $this->lastPayment = $payment;
            });

            $this->resetCart();
            $this->showPaymentModal = false;
            $this->showPhoneModal = false;
            $this->showReceiptModal = true;
            $this->processingSale = false;
            
            $this->dispatch('notify', message: 'Sale completed successfully!', type: 'success');
            
        } catch (\Exception $e) {
            $this->processingSale = false;
            $this->dispatch('notify', message: 'Error processing sale: ' . $e->getMessage(), type: 'error');
        }
    }

    public function submitPhoneNumber()
    {
        // Validate phone number
        if (empty(trim($this->mpesaPhoneNumber))) {
            $this->dispatch('notify', message: 'Please enter M-Pesa phone number', type: 'error');
            return;
        }

        // Validate phone number format
        if (!validate_phone_number($this->mpesaPhoneNumber)) {
            $this->dispatch('notify', message: 'Invalid phone number format. Use 07XXXXXXXX, 0111XXXXXX, or 254XXXXXXXXX', type: 'error');
            return;
        }

        // Close phone modal and proceed with payment
        $this->showPhoneModal = false;
        $this->processMpesaPayment();
    }

    public function processMpesaPayment()
    {
        // Validate phone number - show modal if empty
        if (empty(trim($this->mpesaPhoneNumber))) {
            $this->showPhoneModal = true;
            return;
        }

        // Validate phone number format
        if (!validate_phone_number($this->mpesaPhoneNumber)) {
            $this->dispatch('notify', message: 'Invalid phone number format. Use 07XXXXXXXX, 0111XXXXXX, or 254XXXXXXXXX', type: 'error');
            return;
        }

        // Recalculate cart totals to ensure they're correct
        $this->updateCartTotals();
        
        // Ensure cartTotal is valid
        if (!is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
            $this->dispatch('notify', message: 'Invalid cart total. Please refresh and try again.', type: 'error');
            return;
        }

        // Format phone number using helper
        $phoneNumber = format_phone_number($this->mpesaPhoneNumber);

        // Check if M-Pesa is enabled
        if (!SettingsHelper::isMpesaEnabled()) {
            $this->dispatch('notify', message: 'M-Pesa payments are not enabled. Please configure in settings.', type: 'error');
            return;
        }

        $this->mpesaProcessing = true;
        $this->mpesaStatus = 'pending';

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $pharmacy = $user->pharmacy ?? Pharmacy::first();

            // Recalculate again inside transaction
            $this->updateCartTotals();
            
            // Final validation
            if (!is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
                throw new \Exception('Invalid cart total: ' . $this->cartTotal);
            }

            // Generate invoice number
            $invoiceNumber = 'SAL-POS-' . str_pad(Sale::max('id') + 1 ?? 1, 6, '0', STR_PAD_LEFT);

            // Create sale with pending status
            $sale = Sale::create([
                'pharmacy_id' => $pharmacy->id,
                'user_id' => $user->id,
                'customer_id' => $this->customerId,
                'prescription_id' => null,
                'invoice_number' => $invoiceNumber,
                'sale_date' => now(),
                'subtotal' => $this->cartSubtotal,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discountAmount,
                'total_amount' => $this->cartTotal,
                'status' => 'pending',
                'notes' => 'POS Sale - M-Pesa Payment Pending',
            ]);

            // Create sale items and update stock
            foreach ($this->cart as $cartItem) {
                $medicine = Medicine::find($cartItem['medicine_id']);
                
                if (!$medicine || $medicine->stock_quantity < $cartItem['quantity']) {
                    throw new \Exception("Insufficient stock for {$medicine->name}");
                }

                // Get available stock batch (FIFO - First In First Out)
                $stockBatch = StockBatch::where('medicine_id', $medicine->id)
                    ->where('remaining_quantity', '>', 0)
                    ->where('expiry_date', '>', now())
                    ->orderBy('expiry_date', 'asc')
                    ->first();

                if (!$stockBatch || $stockBatch->remaining_quantity < $cartItem['quantity']) {
                    throw new \Exception("Insufficient stock batch for {$medicine->name}");
                }

                $quantity = $cartItem['quantity'];
                $unitPrice = $cartItem['selling_price'];
                $totalPrice = $quantity * $unitPrice;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $medicine->id,
                    'stock_batch_id' => $stockBatch->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'total_price' => $totalPrice,
                ]);

                // Reserve stock (don't decrement yet - will decrement when payment is confirmed)
            }

            // Create pending payment - ensure amount is numeric
            $paymentAmount = (float)$this->cartTotal;
            if (!is_numeric($paymentAmount) || $paymentAmount <= 0) {
                throw new \Exception('Invalid payment amount: ' . $this->cartTotal);
            }
            
            $payment = Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => 'mpesa',
                'amount' => $paymentAmount,
                'reference_number' => 'MPESA-PENDING-' . strtoupper(uniqid()),
                'notes' => 'M-Pesa STK Push - Pending',
                'status' => 'pending',
            ]);

            DB::commit();

            // Initiate STK Push
            $this->initiateSTKPush($sale->id, $phoneNumber, (float)$this->cartTotal, $invoiceNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mpesaProcessing = false;
            $this->mpesaStatus = 'failed';
            $this->dispatch('notify', message: 'Error initiating M-Pesa payment: ' . $e->getMessage(), type: 'error');
        }
    }

    public function initiateSTKPush($saleId, $phoneNumber, $amount, $accountNumber)
    {
        try {
            $response = Mpesa::stkpush(
                phonenumber: $phoneNumber,
                amount: $amount,
                account_number: $accountNumber,
                callbackurl: null, // Using callback URL from config file
                transactionType: Mpesa::PAYBILL
            );

            /** @var \Illuminate\Http\Client\Response $response */
            $result = $response->json();

            // Store the merchant and checkout request IDs
            if (isset($result['MerchantRequestID']) && isset($result['CheckoutRequestID'])) {
                MpesaSTK::create([
                    'merchant_request_id' => $result['MerchantRequestID'],
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'sale_id' => $saleId,
                ]);

                if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                    $this->dispatch('notify', message: 'M-Pesa payment request sent. Please check your phone.', type: 'success');
                    // Keep modal open to wait for callback
                } else {
                    $this->mpesaProcessing = false;
                    $this->mpesaStatus = 'failed';
                    $errorMessage = $result['errorMessage'] ?? $result['CustomerMessage'] ?? 'Failed to initiate M-Pesa payment';
                    $this->dispatch('notify', message: $errorMessage, type: 'error');
                }
            } else {
                $this->mpesaProcessing = false;
                $this->mpesaStatus = 'failed';
                $errorMessage = $result['errorMessage'] ?? $result['CustomerMessage'] ?? 'Failed to initiate M-Pesa payment';
                $this->dispatch('notify', message: $errorMessage, type: 'error');
            }
        } catch (\Exception $e) {
            $this->mpesaProcessing = false;
            $this->mpesaStatus = 'failed';
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    public function resetCart()
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->cartSubtotal = 0;
        $this->taxAmount = 0;
        $this->discountAmount = 0;
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->amountPaid = 0;
        $this->change = 0;
        $this->mpesaPhoneNumber = '';
        $this->mpesaProcessing = false;
        $this->mpesaStatus = null;
        $this->showPhoneModal = false;
        $this->search = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function clearCart()
    {
        $this->resetCart();
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->lastSale = null;
        $this->lastSaleItems = [];
        $this->lastCustomer = null;
        $this->lastPayment = null;
    }

    public function render()
    {
        return view('livewire.pos-component');
    }
}

