<?php

namespace App\Filament\Pages;

use App\Helpers\SettingsHelper;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MpesaSTK;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PointOfSale extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected string $view = 'filament.pages.point-of-sale';

    protected static ?string $navigationLabel = 'Point of Sale';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 0; // Place at the top of Sales group

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
    public $stkPhoneNumber = ''; // Alias for compatibility with PosComponent view
    public $mpesaProcessing = false;
    public $stkProcessing = false; // Alias for compatibility with PosComponent view
    public $mpesaStatus = null; // 'pending', 'success', 'failed'
    public $stkStatus = null; // Alias for compatibility with PosComponent view
    public $pendingMpesaSaleId = null; // Store sale ID for polling
    public $pendingStkSaleId = null; // Alias for compatibility with PosComponent view
    public $pendingStkCheckoutId = null; // For STK Push status polling

    // UI State
    public $showCustomerModal = false;
    public $showPaymentModal = false;
    public $showPhoneModal = false;
    public $processingSale = false;
    public $showReceiptModal = false;
    
    // Receipt Data
    public $lastSale = null;
    public $lastSaleItems = [];
    public $lastCustomer = null;
    public $lastPayment = null;

    protected $listeners = ['scanBarcode', 'clearCart'];

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function mount()
    {
        $this->taxRate = SettingsHelper::taxRateDecimal();
        $this->showPhoneModal = false;
        $this->showReceiptModal = false;
        $this->mpesaPhoneNumber = '';
        $this->stkPhoneNumber = '';
        $this->mpesaStatus = null;
        $this->stkStatus = null;
        $this->pendingMpesaSaleId = null;
        $this->pendingStkSaleId = null;
        $this->pendingStkCheckoutId = null;
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
            return $item['selling_price'] * $item['quantity'];
        });

        // Apply discount
        if ($this->discountValue > 0) {
            if ($this->discountType === 'percentage') {
                $this->discountAmount = ($this->cartSubtotal * $this->discountValue) / 100;
            } else {
                $this->discountAmount = min($this->discountValue, $this->cartSubtotal);
            }
        } else {
            $this->discountAmount = 0;
        }

        // Calculate tax
        $taxableAmount = $this->cartSubtotal - $this->discountAmount;
        $this->taxAmount = $taxableAmount * $this->taxRate;
        $this->cartTotal = $taxableAmount + $this->taxAmount;
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
            $this->stkPhoneNumber = $this->selectedCustomer['phone'];
        } else {
            $this->mpesaPhoneNumber = '';
            $this->stkPhoneNumber = '';
        }
        $this->mpesaStatus = null;
        $this->stkStatus = null;
        $this->showPhoneModal = false;
        $this->showPaymentModal = true;
    }
    
    public function updatedPaymentMethod()
    {
        if ($this->paymentMethod === 'mpesa' || $this->paymentMethod === 'bank_paybill') {
            // Pre-fill phone number if customer is selected, otherwise leave empty
            if ($this->selectedCustomer && isset($this->selectedCustomer['phone']) && !empty($this->selectedCustomer['phone'])) {
                $this->mpesaPhoneNumber = $this->selectedCustomer['phone'];
                $this->stkPhoneNumber = $this->selectedCustomer['phone'];
            } else {
                $this->mpesaPhoneNumber = '';
                $this->stkPhoneNumber = '';
            }
            $this->mpesaStatus = null;
            $this->stkStatus = null;
            $this->showPhoneModal = false;
        } else {
            // Clear M-Pesa related fields when switching to other payment methods
            $this->mpesaPhoneNumber = '';
            $this->stkPhoneNumber = '';
            $this->mpesaStatus = null;
            $this->stkStatus = null;
            $this->mpesaProcessing = false;
            $this->stkProcessing = false;
            $this->showPhoneModal = false;
        }
    }

    public function updatedStkPhoneNumber()
    {
        // Keep both properties in sync
        $this->mpesaPhoneNumber = $this->stkPhoneNumber;
    }

    public function updatedMpesaPhoneNumber()
    {
        // Keep both properties in sync
        $this->stkPhoneNumber = $this->mpesaPhoneNumber;
    }

    public function processSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Cart is empty', type: 'error');
            return;
        }

        // Check for M-Pesa or Bank Paybill payment method FIRST
        $currentPaymentMethod = trim((string)$this->paymentMethod);
        $paymentMethodLower = strtolower($currentPaymentMethod);
        
        if ($paymentMethodLower === 'mpesa' || $paymentMethodLower === 'bank_paybill') {
            // Route to STK Push payment processing (both M-Pesa and Bank Paybill)
            $this->updateCartTotals();
            
            // Validate cartTotal is NOT the phone number
            if ((isset($this->mpesaPhoneNumber) && 
                ($this->cartTotal == $this->mpesaPhoneNumber || 
                 (string)$this->cartTotal === (string)$this->mpesaPhoneNumber)) ||
                (isset($this->stkPhoneNumber) && 
                ($this->cartTotal == $this->stkPhoneNumber || 
                 (string)$this->cartTotal === (string)$this->stkPhoneNumber)) ||
                 !is_numeric($this->cartTotal)) {
                $this->dispatch('notify', message: 'Error: Cart total is invalid. Please refresh the page.', type: 'error');
                return;
            }
            
            $this->processMpesaPayment();
            return;
        }

        // Recalculate cart totals
        $this->updateCartTotals();
        
        // Calculate amount directly from cart to avoid property corruption
        $calculatedSubtotal = collect($this->cart)->sum(function ($item) {
            return (float)$item['selling_price'] * (int)$item['quantity'];
        });
        
        $calculatedDiscount = 0;
        if ($this->discountValue > 0) {
            if ($this->discountType === 'percentage') {
                $calculatedDiscount = ($calculatedSubtotal * (float)$this->discountValue) / 100;
            } else {
                $calculatedDiscount = min((float)$this->discountValue, $calculatedSubtotal);
            }
        }
        
        $taxableAmount = $calculatedSubtotal - $calculatedDiscount;
        $calculatedTax = $taxableAmount * (float)$this->taxRate;
        $calculatedTotal = $taxableAmount + $calculatedTax;
        
        // Validate calculated total
        if (!is_numeric($calculatedTotal) || $calculatedTotal <= 0) {
            $this->dispatch('notify', message: 'Invalid cart total. Please refresh and try again.', type: 'error');
            return;
        }
        
        // CRITICAL: Ensure calculated total is NOT the phone number
        if (isset($this->mpesaPhoneNumber) && 
            ($calculatedTotal == $this->mpesaPhoneNumber || 
             (string)$calculatedTotal === (string)$this->mpesaPhoneNumber)) {
            $this->dispatch('notify', message: 'Error: Cart total is invalid. Please refresh the page.', type: 'error');
            return;
        }

        if ((float)$this->amountPaid < (float)$calculatedTotal) {
            $this->dispatch('notify', message: 'Insufficient payment amount', type: 'error');
            return;
        }

        $this->processingSale = true;

        $saleData = null;
        
        try {
            $saleData = DB::transaction(function () use ($calculatedSubtotal, $calculatedTax, $calculatedDiscount, $calculatedTotal) {
                $user = Auth::user();
                $pharmacy = $user->pharmacy ?? Pharmacy::first();

                // Generate invoice number
                $invoiceNumber = 'SAL-POS-' . str_pad(Sale::max('id') + 1 ?? 1, 6, '0', STR_PAD_LEFT);

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

                // Create sale items and update stock
                foreach ($this->cart as $cartItem) {
                    $medicine = Medicine::find($cartItem['medicine_id']);
                    
                    if (!$medicine || $medicine->stock_quantity < $cartItem['quantity']) {
                        throw new \Exception("Insufficient stock for {$medicine->name}");
                    }

                    $quantity = $cartItem['quantity'];
                    $unitPrice = $cartItem['selling_price'];
                    $totalPrice = $quantity * $unitPrice;

                    // Create sale item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'stock_batch_id' => null,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0,
                        'total_price' => $totalPrice,
                    ]);

                    // Update medicine stock
                    $medicine->decrement('stock_quantity', $quantity);
                }

                // Create payment - use sale's total_amount, not amountPaid
                $paymentAmount = (float)$sale->total_amount;
                
                // CRITICAL: Validate payment amount is NOT the phone number
                if (isset($this->mpesaPhoneNumber) && 
                    ($paymentAmount == $this->mpesaPhoneNumber || 
                     (string)$paymentAmount === (string)$this->mpesaPhoneNumber)) {
                    throw new \Exception('Payment amount matches phone number! Amount: ' . $paymentAmount . ', Phone: ' . $this->mpesaPhoneNumber);
                }
                
                // Ensure payment method is not mpesa
                $paymentMethod = trim((string)$this->paymentMethod);
                if (strtolower($paymentMethod) === 'mpesa') {
                    throw new \Exception('M-Pesa payment should not use regular payment flow');
                }
                
                $payment = Payment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $paymentAmount,
                    'reference_number' => 'PAY-' . strtoupper(uniqid()),
                    'notes' => 'POS Payment',
                    'status' => 'completed',
                ]);

                return [
                    'sale' => $sale,
                    'payment' => $payment,
                ];
            });

            // Load receipt data
            $this->lastSale = Sale::with('user')->find($saleData['sale']->id);
            $this->lastSaleItems = SaleItem::with('medicine')->where('sale_id', $saleData['sale']->id)->get();
            $this->lastCustomer = $this->customerId ? Customer::find($this->customerId) : null;
            $this->lastPayment = $saleData['payment'];

            // Close payment modal and show receipt
            $this->showPaymentModal = false;
            $this->processingSale = false;
            $this->showReceiptModal = true;
            
            // Reset cart
            $this->resetCart();
            
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
        // Get phone number - check both properties
        $phoneNumber = trim($this->stkPhoneNumber ?? $this->mpesaPhoneNumber ?? '');
        
        // Validate phone number - show modal if empty
        if (empty($phoneNumber)) {
            $this->showPhoneModal = true;
            return;
        }

        // Validate phone number format
        if (!validate_phone_number($phoneNumber)) {
            $this->dispatch('notify', message: 'Invalid phone number format. Use 07XXXXXXXX, 0111XXXXXX, or 254XXXXXXXXX', type: 'error');
            return;
        }

        // Recalculate cart totals
        $this->updateCartTotals();
        
        // Ensure cartTotal is valid
        if (!is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
            $this->dispatch('notify', message: 'Invalid cart total. Please refresh and try again.', type: 'error');
            return;
        }

        // Format phone number using helper
        $phoneNumber = format_phone_number($phoneNumber);

        // Check if M-Pesa is enabled (required for both M-Pesa and Bank Paybill)
        if (!SettingsHelper::isMpesaEnabled()) {
            $this->dispatch('notify', message: 'M-Pesa payments are not enabled. Please configure in settings.', type: 'error');
            return;
        }

        $this->mpesaProcessing = true;
        $this->stkProcessing = true;
        $this->mpesaStatus = 'pending';
        $this->stkStatus = 'pending';

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $pharmacy = $user->pharmacy ?? Pharmacy::first();

            // Calculate totals directly from cart
            $calculatedSubtotal = collect($this->cart)->sum(function ($item) {
                return (float)$item['selling_price'] * (int)$item['quantity'];
            });
            
            $calculatedDiscount = 0;
            if ($this->discountValue > 0) {
                if ($this->discountType === 'percentage') {
                    $calculatedDiscount = ($calculatedSubtotal * (float)$this->discountValue) / 100;
                } else {
                    $calculatedDiscount = min((float)$this->discountValue, $calculatedSubtotal);
                }
            }
            
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

            // Create sale with pending status
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
                'status' => 'pending',
                'notes' => 'POS Sale - M-Pesa Payment Pending',
            ]);

            // Create sale items and update stock
            foreach ($this->cart as $cartItem) {
                $medicine = Medicine::find($cartItem['medicine_id']);
                
                if (!$medicine || $medicine->stock_quantity < $cartItem['quantity']) {
                    throw new \Exception("Insufficient stock for {$medicine->name}");
                }

                $quantity = $cartItem['quantity'];
                $unitPrice = $cartItem['selling_price'];
                $totalPrice = $quantity * $unitPrice;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $medicine->id,
                    'stock_batch_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'total_price' => $totalPrice,
                ]);

                // Reserve stock (don't decrement yet - will decrement when payment is confirmed)
                // Note: Stock is decremented when payment is confirmed in STK callback
            }

            // Create pending payment - ensure amount is numeric
            $paymentAmount = (float)$calculatedTotal;
            if (!is_numeric($paymentAmount) || $paymentAmount <= 0) {
                throw new \Exception('Invalid payment amount: ' . $calculatedTotal);
            }
            
            // Determine payment method (check both paymentMethod and handle legacy mpesaPhoneNumber)
            $paymentMethodLower = strtolower(trim((string)($this->paymentMethod ?? 'mpesa')));
            $isBankPaybill = $paymentMethodLower === 'bank_paybill';
            $paymentMethod = $isBankPaybill ? 'bank_paybill' : 'mpesa';
            
            $payment = Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => $paymentMethod,
                'amount' => $paymentAmount,
                'reference_number' => ($isBankPaybill ? 'BANK-PAYBILL-PENDING-' : 'MPESA-PENDING-') . strtoupper(uniqid()),
                'notes' => ($isBankPaybill ? 'Bank Paybill' : 'M-Pesa') . ' STK Push - Pending',
                'status' => 'pending',
            ]);

            DB::commit();

            // Store sale ID for polling
            $this->pendingMpesaSaleId = $sale->id;
            $this->pendingStkSaleId = $sale->id;
            
            // Initiate STK Push (M-Pesa or Bank Paybill)
            if ($isBankPaybill) {
                $this->initiateBankPaybillSTKPush($sale->id, $phoneNumber, $paymentAmount, $invoiceNumber);
            } else {
                $this->initiateSTKPush($sale->id, $phoneNumber, $paymentAmount, $invoiceNumber);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mpesaProcessing = false;
            $this->stkProcessing = false;
            $this->mpesaStatus = 'failed';
            $this->stkStatus = 'failed';
            $this->dispatch('notify', message: 'Error initiating payment: ' . $e->getMessage(), type: 'error');
        }
    }

    public function initiateBankPaybillSTKPush($saleId, $phoneNumber, $amount, $accountNumber)
    {
        try {
            // M-Pesa requires integer amounts (no decimals)
            $amount = (int) round((float) $amount);
            
            // Get bank configuration from settings
            $bankCode = \App\Models\Setting::get('bank_code', 'kcb');
            $bankAccountNumber = \App\Models\Setting::get('bank_account_number', '');
            
            // Always use the business account from settings as the account reference
            // The $phoneNumber is only for sending the STK push to the customer's phone
            $accountReference = $bankAccountNumber;
            
            $url = config('app.url') . '/api/bank-paybill/stk-push';
            
            $response = \Illuminate\Support\Facades\Http::post($url, [
                'amount' => $amount,
                'phonenumber' => $phoneNumber,
                'account_number' => $accountReference,
                'bank_code' => $bankCode,
                'sale_id' => $saleId,
            ]);

            $result = $response->json();

            // Store the merchant and checkout request IDs
            if (isset($result['checkout_request_id']) && isset($result['merchant_request_id'])) {
                $this->pendingStkSaleId = $saleId;
                $this->pendingStkCheckoutId = $result['checkout_request_id'];
                $this->pendingMpesaSaleId = $saleId; // For compatibility

                // Check if status is '0' (success) - ResponseCode from M-Pesa API
                $responseStatus = $result['status'] ?? $result['ResponseCode'] ?? null;
                if ($responseStatus == '0' || $responseStatus === 0) {
                    // Set status to pending so polling starts
                    $this->stkStatus = 'pending';
                    $this->mpesaStatus = 'pending';
                    $this->dispatch('notify', message: 'Payment request sent. Please check your phone.', type: 'success');
                } else {
                    $this->stkProcessing = false;
                    $this->mpesaProcessing = false;
                    $this->stkStatus = 'failed';
                    $this->mpesaStatus = 'failed';
                    $errorMessage = $result['message'] ?? $result['ResponseDescription'] ?? 'Failed to initiate payment';
                    $this->dispatch('notify', message: $errorMessage, type: 'error');
                }
            } else {
                $this->stkProcessing = false;
                $this->mpesaProcessing = false;
                $this->stkStatus = 'failed';
                $this->mpesaStatus = 'failed';
                $errorMessage = $result['message'] ?? $result['ResponseDescription'] ?? 'Failed to initiate payment';
                $this->dispatch('notify', message: $errorMessage, type: 'error');
            }
        } catch (\Exception $e) {
            $this->stkProcessing = false;
            $this->mpesaProcessing = false;
            $this->stkStatus = 'failed';
            $this->mpesaStatus = 'failed';
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    public function initiateSTKPush($saleId, $phoneNumber, $amount, $accountNumber)
    {
        try {
            // M-Pesa requires integer amounts (no decimals)
            $amount = (int) round((float) $amount);
            
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
                    // Set status to pending so polling starts
                    $this->mpesaStatus = 'pending';
                    $this->stkStatus = 'pending';
                    $this->dispatch('notify', message: 'M-Pesa payment request sent. Please check your phone.', type: 'success');
                    // Keep modal open to wait for callback
                } else {
                    $this->mpesaProcessing = false;
                    $this->stkProcessing = false;
                    $this->mpesaStatus = 'failed';
                    $this->stkStatus = 'failed';
                    $errorMessage = $result['errorMessage'] ?? $result['CustomerMessage'] ?? 'Failed to initiate M-Pesa payment';
                    $this->dispatch('notify', message: $errorMessage, type: 'error');
                }
            } else {
                $this->mpesaProcessing = false;
                $this->stkProcessing = false;
                $this->mpesaStatus = 'failed';
                $this->stkStatus = 'failed';
                $errorMessage = $result['errorMessage'] ?? $result['CustomerMessage'] ?? 'Failed to initiate M-Pesa payment';
                $this->dispatch('notify', message: $errorMessage, type: 'error');
            }
        } catch (\Exception $e) {
            $this->mpesaProcessing = false;
            $this->stkProcessing = false;
            $this->mpesaStatus = 'failed';
            $this->stkStatus = 'failed';
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    public function checkMpesaPaymentStatus()
    {
        // Alias for compatibility - call the main method
        $this->checkSTKPaymentStatus();
    }

    public function checkSTKPaymentStatus()
    {
        if ((!$this->pendingMpesaSaleId && !$this->pendingStkSaleId) || 
            ($this->mpesaStatus !== 'pending' && $this->stkStatus !== 'pending')) {
            return;
        }

        $saleId = $this->pendingMpesaSaleId ?? $this->pendingStkSaleId;

        // Check if payment has been completed (M-Pesa or Bank Paybill)
        $payment = Payment::where('sale_id', $saleId)
            ->whereIn('payment_method', ['mpesa', 'bank_paybill'])
            ->where('status', 'completed')
            ->first();

        if ($payment) {
            // Payment completed!
            $this->mpesaStatus = 'success';
            $this->stkStatus = 'success';
            $this->mpesaProcessing = false;
            $this->stkProcessing = false;
            $this->pendingMpesaSaleId = null;
            $this->pendingStkSaleId = null;

            // Load sale data for receipt
            $sale = Sale::with(['items.medicine', 'customer', 'payments'])->find($payment->sale_id);
            if ($sale) {
                $this->lastSale = $sale;
                $this->lastSaleItems = $sale->items;
                $this->lastCustomer = $sale->customer;
                $this->lastPayment = $payment;
                
                // Close payment modal and show receipt
                $this->showPaymentModal = false;
                $this->showReceiptModal = true;
                
                // Reset cart
                $this->resetCart();
                
                $this->dispatch('notify', message: 'M-Pesa payment completed successfully!', type: 'success');
            }
        } else {
            // Check if payment failed (check MpesaSTK for error)
            $stkPush = MpesaSTK::where('sale_id', $saleId)
                ->whereNotNull('result_code')
                ->where('result_code', '!=', '0')
                ->first();

            if ($stkPush) {
                // Payment failed
                $this->mpesaStatus = 'failed';
                $this->stkStatus = 'failed';
                $this->mpesaProcessing = false;
                $this->stkProcessing = false;
                $this->pendingMpesaSaleId = null;
                $this->pendingStkSaleId = null;
                $this->dispatch('notify', message: 'M-Pesa payment failed: ' . ($stkPush->result_desc ?? 'Unknown error'), type: 'error');
            }
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
        $this->stkPhoneNumber = '';
        $this->mpesaProcessing = false;
        $this->stkProcessing = false;
        $this->mpesaStatus = null;
        $this->stkStatus = null;
        $this->pendingMpesaSaleId = null;
        $this->pendingStkSaleId = null;
        $this->pendingStkCheckoutId = null;
        $this->showPhoneModal = false;
        $this->search = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function clearCart()
    {
        $this->resetCart();
    }

    public function printReceipt()
    {
        $this->dispatch('print-receipt');
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->lastSale = null;
        $this->lastSaleItems = [];
        $this->lastCustomer = null;
        $this->lastPayment = null;
    }
    
    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }
    
    public function closePhoneModal()
    {
        $this->showPhoneModal = false;
    }
}

