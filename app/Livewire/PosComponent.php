<?php

namespace App\Livewire;

use App\Concerns\ComputesCartQuantityFromMoneyAmount;
use App\Helpers\SettingsHelper;
use App\Models\BankPaybillStk;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MpesaSTK;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PosComponent extends Component
{
    use ComputesCartQuantityFromMoneyAmount;

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

    public $stkPhoneNumber = ''; // Phone number for both M-Pesa and Bank STK

    public $stkProcessing = false;

    public $stkStatus = null; // 'pending', 'success', 'failed'

    public $pendingStkSaleId = null;

    public $pendingStkCheckoutId = null;

    // UI State
    public $showCustomerModal = false;

    public $showPaymentModal = false;

    public $showReceiptModal = false;

    public $processingSale = false;

    // Receipt Data
    public $lastSale = null;

    public $lastSaleItems = [];

    public $lastCustomer = null;

    public $lastPayment = null;

    public function getHasValidPhoneNumberProperty()
    {
        return ! empty(trim($this->mpesaPhoneNumber ?? ''));
    }

    protected $listeners = ['scanBarcode', 'clearCart', 'sale-completed'];

    public function mount()
    {
        $this->taxRate = SettingsHelper::taxRateDecimal();
        $this->showPhoneModal = false;
        $this->showReceiptModal = false;
        $this->stkPhoneNumber = '';
        $this->stkStatus = null;
        $this->resetCart();
        $this->loadDefaultMedicines();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchMedicines();
        } elseif (strlen($this->search) === 0) {
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
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('generic_name', 'like', '%'.$this->search.'%')
                    ->orWhere('barcode', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%');
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

            if (! $medicine || $medicine->stock_quantity <= 0) {
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
            $this->dispatch('notify', message: $medicine->name.' added to cart', type: 'success');
            $this->dispatch('focus-search');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error: '.$e->getMessage(), type: 'error');
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
            return (float) $item['selling_price'] * (int) $item['quantity'];
        });

        // Apply discount
        if ($this->discountValue > 0) {
            if ($this->discountType === 'percentage') {
                $this->discountAmount = ($this->cartSubtotal * (float) $this->discountValue) / 100;
            } else {
                $this->discountAmount = min((float) $this->discountValue, $this->cartSubtotal);
            }
        } else {
            $this->discountAmount = 0;
        }

        // Calculate tax
        $taxableAmount = $this->cartSubtotal - $this->discountAmount;
        $this->taxAmount = $taxableAmount * (float) $this->taxRate;
        $this->cartTotal = (float) ($taxableAmount + $this->taxAmount);
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
        $this->change = max(0, (float) $this->amountPaid - (float) $this->cartTotal);
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) >= 2) {
            $this->customerResults = Customer::where('name', 'like', '%'.$this->customerSearch.'%')
                ->orWhere('phone', 'like', '%'.$this->customerSearch.'%')
                ->orWhere('email', 'like', '%'.$this->customerSearch.'%')
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
        if ($this->selectedCustomer && isset($this->selectedCustomer['phone']) && ! empty($this->selectedCustomer['phone'])) {
            $this->stkPhoneNumber = $this->selectedCustomer['phone'];
        } else {
            $this->stkPhoneNumber = '';
        }
        $this->stkStatus = null;
        $this->showPaymentModal = true;
    }

    public function updatedPaymentMethod()
    {
        if ($this->paymentMethod === 'mpesa' || $this->paymentMethod === 'bank_paybill') {
            // Pre-fill phone number if customer is selected, otherwise leave empty
            if ($this->selectedCustomer && isset($this->selectedCustomer['phone']) && ! empty($this->selectedCustomer['phone'])) {
                $this->stkPhoneNumber = $this->selectedCustomer['phone'];
            } else {
                $this->stkPhoneNumber = '';
            }
            $this->stkStatus = null;
        } else {
            // Clear STK related fields when switching to other payment methods
            $this->stkPhoneNumber = '';
            $this->stkStatus = null;
            $this->stkProcessing = false;
        }
    }

    public function updatedStkPhoneNumber()
    {
        // This ensures Livewire tracks changes to stkPhoneNumber
        // The property is already reactive via wire:model.live, but this helps with button state
    }

    public function processSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Cart is empty', type: 'error');

            return;
        }

        // ALWAYS open payment modal first if it's not already open
        // This ensures user can select payment method and enter details
        if (! $this->showPaymentModal) {
            $this->openPaymentModal();

            // Stop here - let user interact with modal first
            return;
        }

        // Only proceed if modal is open and user has interacted with it
        // For M-Pesa, handle differently - check FIRST before any other processing
        // Use strict comparison and ensure it's a string
        // Force refresh the payment method property
        $this->paymentMethod = $this->paymentMethod ?? 'cash';
        $currentPaymentMethod = trim((string) $this->paymentMethod);
        $paymentMethodLower = strtolower($currentPaymentMethod);

        // Debug: Log the payment method with full context
        Log::info('processSale called', [
            'raw_paymentMethod' => $this->paymentMethod,
            'paymentMethod_type' => gettype($this->paymentMethod),
            'paymentMethod' => $currentPaymentMethod,
            'paymentMethodLower' => $paymentMethodLower,
            'isSTK' => ($paymentMethodLower === 'mpesa' || $paymentMethodLower === 'bank_paybill'),
            'cartTotal' => $this->cartTotal,
            'stkPhoneNumber' => $this->stkPhoneNumber,
            'showPaymentModal' => $this->showPaymentModal,
            'all_properties' => [
                'paymentMethod' => $this->paymentMethod,
                'stkPhoneNumber' => $this->stkPhoneNumber,
            ],
        ]);

        // CRITICAL: Check for STK Push payments (M-Pesa or Bank Paybill) - this MUST return
        // Use in_array for more reliable checking
        $stkPaymentMethods = ['mpesa', 'bank_paybill'];
        $isSTKPayment = in_array($paymentMethodLower, $stkPaymentMethods) ||
                       in_array($currentPaymentMethod, $stkPaymentMethods) ||
                       in_array($this->paymentMethod, $stkPaymentMethods);

        if ($isSTKPayment) {
            Log::info('Routing to STK Push payment flow', [
                'paymentMethod' => $paymentMethodLower,
                'stkPhoneNumber' => $this->stkPhoneNumber,
                'cartTotal' => $this->cartTotal,
            ]);

            // Ensure cartTotal is recalculated before processing
            $this->updateCartTotals();

            // Final validation - ensure cartTotal is NOT the phone number
            if ($this->cartTotal == $this->stkPhoneNumber ||
                (string) $this->cartTotal === (string) $this->stkPhoneNumber ||
                ! is_numeric($this->cartTotal)) {
                Log::error('Cart total is invalid for STK Push', [
                    'cartTotal' => $this->cartTotal,
                    'stkPhoneNumber' => $this->stkPhoneNumber,
                ]);
                $this->dispatch('notify', message: 'Error: Cart total is invalid. Please refresh the page.', type: 'error');

                return;
            }

            Log::info('Calling processSTKPayment', [
                'paymentMethod' => $paymentMethodLower,
                'stkPhoneNumber' => $this->stkPhoneNumber,
            ]);

            $this->processSTKPayment();

            Log::info('processSTKPayment completed, returning from processSale');

            return; // CRITICAL: Must return here
        }

        // If we reach here, it's NOT M-Pesa or Bank Paybill, so proceed with regular payment
        // Safety check: If payment method is STK-related but we're here, something is wrong
        if (in_array($paymentMethodLower, ['mpesa', 'bank_paybill'])) {
            Log::error('CRITICAL: Payment method is STK but reached regular flow!', [
                'paymentMethod' => $currentPaymentMethod,
                'paymentMethodLower' => $paymentMethodLower,
            ]);
            $this->dispatch('notify', message: 'Error: Payment method mismatch. Please refresh and try again.', type: 'error');

            return;
        }

        Log::info('Proceeding with regular payment flow', ['paymentMethod' => $currentPaymentMethod]);

        // Recalculate cart totals to ensure they're correct
        $this->updateCartTotals();

        // CRITICAL: Check if cartTotal has been corrupted with phone number
        if ($this->cartTotal == $this->stkPhoneNumber ||
            (string) $this->cartTotal === (string) $this->stkPhoneNumber ||
            ! is_numeric($this->cartTotal) ||
            $this->cartTotal <= 0) {
            // If corrupted, recalculate from cart
            $this->updateCartTotals();
            // Check again
            if ($this->cartTotal == $this->stkPhoneNumber || ! is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
                $this->dispatch('notify', message: 'Error: Cart total is invalid. CartTotal: '.$this->cartTotal.', Phone: '.$this->stkPhoneNumber.'. Please refresh the page.', type: 'error');

                return;
            }
        }

        // Recalculate cart totals to ensure they're up to date
        $this->updateCartTotals();

        // For cash payments, ensure amountPaid is set and valid
        if (strtolower(trim((string) $this->paymentMethod)) === 'cash') {
            // If amountPaid is 0 or equals cartTotal (default), it might not have been updated
            if ($this->amountPaid <= 0 || $this->amountPaid == $this->cartTotal) {
                // Check if user actually entered a different amount - if not, use cartTotal (no change)
                // But log it for debugging
                Log::info('Cash payment - amountPaid check', [
                    'amountPaid' => $this->amountPaid,
                    'cartTotal' => $this->cartTotal,
                    'paymentMethod' => $this->paymentMethod,
                ]);
            }
        }

        if ((float) $this->amountPaid < (float) $this->cartTotal) {
            $this->dispatch('notify', message: 'Insufficient payment amount', type: 'error');

            return;
        }

        $this->processingSale = true;

        // Capture the amountPaid value before entering the transaction
        // to avoid issues with Livewire property hydration
        $amountPaidByCustomer = (float) $this->amountPaid;
        $paymentMethodSelected = trim((string) $this->paymentMethod);

        try {
            DB::transaction(function () use ($amountPaidByCustomer, $paymentMethodSelected) {
                $user = Auth::user();
                $pharmacy = $user->pharmacy ?? Pharmacy::first();

                // Calculate totals directly from cart to avoid any property corruption
                $calculatedSubtotal = collect($this->cart)->sum(function ($item) {
                    return (float) $item['selling_price'] * (int) $item['quantity'];
                });

                // Apply discount
                $calculatedDiscount = 0;
                if ($this->discountValue > 0) {
                    if ($this->discountType === 'percentage') {
                        $calculatedDiscount = ($calculatedSubtotal * (float) $this->discountValue) / 100;
                    } else {
                        $calculatedDiscount = min((float) $this->discountValue, $calculatedSubtotal);
                    }
                }

                // Calculate tax
                $taxableAmount = $calculatedSubtotal - $calculatedDiscount;
                $calculatedTax = $taxableAmount * (float) $this->taxRate;
                $calculatedTotal = $taxableAmount + $calculatedTax;

                // Validate calculated total
                if (! is_numeric($calculatedTotal) || $calculatedTotal <= 0) {
                    throw new \Exception('Invalid calculated total: '.$calculatedTotal);
                }

                // CRITICAL: Ensure calculated total is NOT the phone number
                if ($calculatedTotal == $this->stkPhoneNumber || (string) $calculatedTotal === (string) $this->stkPhoneNumber) {
                    throw new \Exception('Calculated total matches phone number! Total: '.$calculatedTotal.', Phone: '.$this->stkPhoneNumber);
                }

                // Generate invoice number
                $invoiceNumber = 'SAL-POS-'.str_pad(Sale::max('id') + 1 ?? 1, 6, '0', STR_PAD_LEFT);

                // Log before creating sale for debugging
                Log::info('Creating sale', [
                    'calculatedSubtotal' => $calculatedSubtotal,
                    'calculatedTax' => $calculatedTax,
                    'calculatedDiscount' => $calculatedDiscount,
                    'calculatedTotal' => $calculatedTotal,
                    'stkPhoneNumber' => $this->stkPhoneNumber,
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

                    if (! $medicine || $medicine->stock_quantity < $cartItem['quantity']) {
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

                // Determine payment amount based on payment method
                // For cash payments, use the actual amount paid (which may include change)
                // For other payment methods, use the sale total
                $paymentMethod = $paymentMethodSelected;
                $paymentMethodLower = strtolower($paymentMethod);

                // Check 4: Ensure payment method is not mpesa or bank_paybill in regular flow
                if ($paymentMethodLower === 'mpesa' || $paymentMethodLower === 'bank_paybill') {
                    throw new \Exception('STK Push payment should use processSTKPayment method. Current method: "'.$paymentMethod.'"');
                }

                // For cash payments, use the amount paid by customer (includes change)
                // For other payment methods, use the sale total
                if ($paymentMethodLower === 'cash') {
                    // Use the captured amount (not from $this to avoid hydration issues)
                    $paymentAmount = $amountPaidByCustomer;

                    // Log for debugging
                    Log::info('Cash payment processing - NEW CODE', [
                        'amountPaidByCustomer' => $amountPaidByCustomer,
                        'cartTotal' => $this->cartTotal,
                        'calculatedTotal' => $calculatedTotal,
                        'paymentAmount' => $paymentAmount,
                        'change_calculated' => $paymentAmount - $calculatedTotal,
                    ]);

                    // Temporary validation to ensure value is captured
                    if ($amountPaidByCustomer <= 0 || $amountPaidByCustomer == $calculatedTotal) {
                        Log::warning('AmountPaid might not have been captured correctly', [
                            'amountPaidByCustomer' => $amountPaidByCustomer,
                            'calculatedTotal' => $calculatedTotal,
                        ]);
                    }
                } else {
                    $paymentAmount = (float) $sale->total_amount;
                }

                // CRITICAL: Multiple validation checks
                // Check 1: Ensure it's numeric
                if (! is_numeric($paymentAmount) || $paymentAmount <= 0) {
                    throw new \Exception('Invalid payment amount: '.$paymentAmount);
                }

                // Check 2: Ensure it's not the phone number (compare as both string and number)
                $phoneAsNumber = (float) $this->stkPhoneNumber;
                if ($paymentAmount == $phoneAsNumber ||
                    (string) $paymentAmount === (string) $this->stkPhoneNumber ||
                    abs($paymentAmount - $phoneAsNumber) < 0.01) {
                    throw new \Exception('Payment amount matches phone number! Amount: '.$paymentAmount.', Phone: '.$this->stkPhoneNumber.', CalculatedTotal: '.$calculatedTotal);
                }

                // Check 3: Sanity check - reasonable amount range
                if ($paymentAmount > 1000000 || $paymentAmount < 0.01) {
                    throw new \Exception('Payment amount out of reasonable range: '.$paymentAmount);
                }

                // Check 5: For cash payments, ensure amount paid is at least the sale total
                if ($paymentMethodLower === 'cash' && $paymentAmount < $calculatedTotal) {
                    throw new \Exception('Amount paid ('.$paymentAmount.') is less than total amount ('.$calculatedTotal.')');
                }

                $payment = Payment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $paymentAmount,
                    'reference_number' => 'PAY-'.strtoupper(uniqid()),
                    'notes' => ($paymentMethodLower === 'cash' && $paymentAmount > $calculatedTotal)
                        ? "POS Payment - Cash: Paid {$paymentAmount}, Change: ".($paymentAmount - $calculatedTotal)
                        : 'POS Payment',
                    'status' => 'completed',
                ]);

                // Log payment creation for debugging
                Log::info('Payment created - NEW CODE', [
                    'payment_id' => $payment->id,
                    'payment_amount' => $payment->amount,
                    'payment_amount_used' => $paymentAmount,
                    'sale_total' => $sale->total_amount,
                    'change' => $payment->amount - $sale->total_amount,
                    'payment_method' => $paymentMethod,
                ]);

                // Store receipt data - reload with relationships
                $sale->refresh();
                $this->lastSale = $sale->load('items.medicine', 'customer', 'payments', 'pharmacy');
                $this->lastSaleItems = $sale->items()->with('medicine')->get()->toArray();
                $this->lastCustomer = $sale->customer;
                // Get payment from sale's payments relationship to ensure we have the correct amount
                $this->lastPayment = $sale->payments->first();

                // Log the payment that will be used for receipt
                if ($this->lastPayment) {
                    Log::info('Payment for receipt', [
                        'payment_id' => $this->lastPayment->id,
                        'payment_amount' => $this->lastPayment->amount,
                        'sale_total' => $this->lastSale->total_amount,
                        'change' => $this->lastPayment->amount - $this->lastSale->total_amount,
                    ]);
                }
            });

            $this->resetCart();
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;
            $this->processingSale = false;

            $this->dispatch('notify', message: 'Sale completed successfully!', type: 'success');

        } catch (\Exception $e) {
            $this->processingSale = false;
            $this->dispatch('notify', message: 'Error processing sale: '.$e->getMessage(), type: 'error');
        }
    }

    // Removed submitPhoneNumber - phone number is now entered directly in payment modal

    public function processSTKPayment()
    {
        Log::info('processSTKPayment called', [
            'stkPhoneNumber' => $this->stkPhoneNumber,
            'paymentMethod' => $this->paymentMethod,
        ]);

        // Validate phone number - show error if empty (no separate modal needed)
        if (empty(trim($this->stkPhoneNumber))) {
            Log::info('Phone number is empty');
            $this->dispatch('notify', message: 'Please enter a phone number', type: 'error');

            return;
        }

        // Validate phone number format
        if (! validate_phone_number($this->stkPhoneNumber)) {
            $this->dispatch('notify', message: 'Invalid phone number format. Use 07XXXXXXXX, 0111XXXXXX, or 254XXXXXXXXX', type: 'error');

            return;
        }

        // Recalculate cart totals to ensure they're correct
        $this->updateCartTotals();

        // Ensure cartTotal is valid
        if (! is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
            $this->dispatch('notify', message: 'Invalid cart total. Please refresh and try again.', type: 'error');

            return;
        }

        // Format phone number using helper
        $phoneNumber = format_phone_number($this->stkPhoneNumber);

        // Determine payment method from user's selection (not settings)
        $selectedPaymentMethod = strtolower(trim((string) ($this->paymentMethod ?? 'mpesa')));
        $isBankPaybill = $selectedPaymentMethod === 'bank_paybill';

        // Check if M-Pesa is enabled (required for both M-Pesa and Bank STK)
        if (! SettingsHelper::isMpesaEnabled()) {
            $this->dispatch('notify', message: 'M-Pesa payments are not enabled. Please configure in settings.', type: 'error');

            return;
        }

        $this->stkProcessing = true;
        $this->stkStatus = 'pending';

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $pharmacy = $user->pharmacy ?? Pharmacy::first();

            // Recalculate again inside transaction
            $this->updateCartTotals();

            // Final validation
            if (! is_numeric($this->cartTotal) || $this->cartTotal <= 0) {
                throw new \Exception('Invalid cart total: '.$this->cartTotal);
            }

            // Generate invoice number
            $invoiceNumber = 'SAL-POS-'.str_pad(Sale::max('id') + 1 ?? 1, 6, '0', STR_PAD_LEFT);

            // Determine payment method and notes based on user's selection
            $paymentMethod = $isBankPaybill ? 'bank_paybill' : 'mpesa';
            $notes = $isBankPaybill ? 'POS Sale - Bank Paybill Payment Pending' : 'POS Sale - M-Pesa Payment Pending';

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
                'notes' => $notes,
            ]);

            // Create sale items and update stock
            foreach ($this->cart as $cartItem) {
                $medicine = Medicine::find($cartItem['medicine_id']);

                if (! $medicine || $medicine->stock_quantity < $cartItem['quantity']) {
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
                // Note: Stock is decremented when payment is confirmed in confirmSTKPayment
            }

            // Create pending payment - ensure amount is numeric
            $paymentAmount = (float) $this->cartTotal;
            if (! is_numeric($paymentAmount) || $paymentAmount <= 0) {
                throw new \Exception('Invalid payment amount: '.$this->cartTotal);
            }

            $paymentNotes = $isBankPaybill ? 'Bank Paybill STK Push - Pending' : 'M-Pesa STK Push - Pending';
            $referencePrefix = $isBankPaybill ? 'BANK-PAYBILL-PENDING-' : 'MPESA-PENDING-';

            $payment = Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => $paymentMethod,
                'amount' => $paymentAmount,
                'reference_number' => $referencePrefix.strtoupper(uniqid()),
                'notes' => $paymentNotes,
                'status' => 'pending',
            ]);

            DB::commit();

            Log::info('Sale and payment created, initiating STK Push', [
                'sale_id' => $sale->id,
                'selected_payment_method' => $selectedPaymentMethod,
                'is_bank_paybill' => $isBankPaybill,
                'payment_method' => $paymentMethod,
                'phone_number' => $phoneNumber,
                'amount' => $this->cartTotal,
            ]);

            // Initiate STK Push based on user's payment method selection
            if ($isBankPaybill) {
                Log::info('Initiating Bank Paybill STK Push');
                $this->initiateBankPaybillSTKPush($sale->id, $phoneNumber, (float) $this->cartTotal, $invoiceNumber);
            } else {
                Log::info('Initiating M-Pesa STK Push');
                $this->initiateMpesaSTKPush($sale->id, $phoneNumber, (float) $this->cartTotal, $invoiceNumber);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->stkProcessing = false;
            $this->stkStatus = 'failed';
            $this->dispatch('notify', message: 'Error initiating payment: '.$e->getMessage(), type: 'error');
        }
    }

    public function initiateMpesaSTKPush($saleId, $phoneNumber, $amount, $accountNumber)
    {
        try {
            // Explicitly set the callback URL to ensure it points to the correct endpoint
            $callbackUrl = config('app.url').'/api/v1/confirm';

            // M-Pesa requires integer amounts (no decimals)
            $amount = (int) round((float) $amount);

            Log::info('Initiating M-Pesa STK Push', [
                'saleId' => $saleId,
                'phoneNumber' => $phoneNumber,
                'amount' => $amount,
                'callbackUrl' => $callbackUrl,
            ]);

            $response = Mpesa::stkpush(
                phonenumber: $phoneNumber,
                amount: $amount,
                account_number: $accountNumber,
                callbackurl: $callbackUrl,
                transactionType: Mpesa::PAYBILL
            );

            /** @var \Illuminate\Http\Client\Response $response */
            $result = $response->json();

            Log::info('M-Pesa STK Push response received', [
                'status_code' => $response->status(),
                'result' => $result,
            ]);

            // Store the merchant and checkout request IDs
            if (isset($result['MerchantRequestID']) && isset($result['CheckoutRequestID'])) {
                MpesaSTK::create([
                    'merchant_request_id' => $result['MerchantRequestID'],
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'sale_id' => $saleId,
                ]);

                $this->pendingStkSaleId = $saleId;
                $this->pendingStkCheckoutId = $result['CheckoutRequestID'];

                if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                    // Set status to pending so polling starts
                    $this->stkStatus = 'pending';
                    $this->dispatch('notify', message: 'Payment request sent. Please check your phone.', type: 'success');
                    // Keep modal open to wait for callback
                } else {
                    $this->stkProcessing = false;
                    $this->stkStatus = 'failed';
                    $errorMessage = $result['errorMessage'] ?? $result['CustomerMessage'] ?? 'Failed to initiate payment';
                    $this->dispatch('notify', message: $errorMessage, type: 'error');
                }
            } else {
                $this->stkProcessing = false;
                $this->stkStatus = 'failed';
                $errorMessage = $result['errorMessage'] ?? $result['CustomerMessage'] ?? 'Failed to initiate payment';
                Log::error('M-Pesa STK Push failed - missing required fields', [
                    'result' => $result,
                ]);
                $this->dispatch('notify', message: $errorMessage, type: 'error');
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push error: '.$e->getMessage(), [
                'saleId' => $saleId,
                'phoneNumber' => $phoneNumber,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->stkProcessing = false;
            $this->stkStatus = 'failed';
            $this->dispatch('notify', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function initiateBankPaybillSTKPush($saleId, $phoneNumber, $amount, $accountNumber)
    {
        try {
            Log::info('initiateBankPaybillSTKPush called', [
                'saleId' => $saleId,
                'phoneNumber' => $phoneNumber,
                'amount' => $amount,
            ]);

            // Get bank configuration from settings
            $bankCode = Setting::get('bank_code', 'kcb');
            $bankAccountNumber = Setting::get('bank_account_number', '');

            // Always use the business account from settings as the account reference
            // The $phoneNumber is only for sending the STK push to the customer's phone
            $accountReference = $bankAccountNumber;

            // M-Pesa requires integer amounts (no decimals)
            $amount = (int) round((float) $amount);

            $url = config('app.url').'/api/bank-paybill/stk-push';
            Log::info('Making HTTP request to Bank Paybill STK Push', [
                'url' => $url,
                'data' => [
                    'amount' => $amount,
                    'phonenumber' => $phoneNumber,
                    'account_number' => $accountReference,
                    'bank_code' => $bankCode,
                    'sale_id' => $saleId,
                ],
            ]);

            $response = Http::post($url, [
                'amount' => $amount,
                'phonenumber' => $phoneNumber,
                'account_number' => $accountReference,
                'bank_code' => $bankCode,
                'sale_id' => $saleId,
            ]);

            $result = $response->json();

            Log::info('Bank Paybill STK Push response received', [
                'status_code' => $response->status(),
                'result' => $result,
            ]);

            // Store the merchant and checkout request IDs
            if (isset($result['checkout_request_id']) && isset($result['merchant_request_id'])) {
                $this->pendingStkSaleId = $saleId;
                $this->pendingStkCheckoutId = $result['checkout_request_id'];

                // Check if status is '0' (success) - ResponseCode from M-Pesa API
                $responseStatus = $result['status'] ?? $result['ResponseCode'] ?? null;
                if ($responseStatus == '0' || $responseStatus === 0) {
                    // Set status to pending so polling starts
                    $this->stkStatus = 'pending';
                    $this->dispatch('notify', message: 'Payment request sent. Please check your phone.', type: 'success');
                    // Keep modal open to wait for callback
                } else {
                    $this->stkProcessing = false;
                    $this->stkStatus = 'failed';
                    $errorMessage = $result['message'] ?? $result['ResponseDescription'] ?? 'Failed to initiate payment';
                    $this->dispatch('notify', message: $errorMessage, type: 'error');
                }
            } else {
                $this->stkProcessing = false;
                $this->stkStatus = 'failed';
                $errorMessage = $result['message'] ?? $result['ResponseDescription'] ?? 'Failed to initiate payment';
                Log::error('Bank Paybill STK Push failed - missing checkout_request_id or merchant_request_id', [
                    'result' => $result,
                ]);
                $this->dispatch('notify', message: $errorMessage, type: 'error');
            }
        } catch (\Exception $e) {
            $this->stkProcessing = false;
            $this->stkStatus = 'failed';
            $this->dispatch('notify', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function checkSTKPaymentStatus()
    {
        if (! $this->pendingStkSaleId || $this->stkStatus !== 'pending') {
            return;
        }

        Log::info('Checking STK payment status', [
            'pendingStkSaleId' => $this->pendingStkSaleId,
            'paymentMethod' => $this->paymentMethod,
        ]);

        // Check based on the actual payment method used, not settings
        $selectedPaymentMethod = strtolower(trim((string) ($this->paymentMethod ?? 'mpesa')));
        $isBankPaybill = $selectedPaymentMethod === 'bank_paybill';

        if ($isBankPaybill) {
            // Check bank paybill status
            if (! $this->pendingStkCheckoutId) {
                return;
            }

            try {
                $response = Http::get(config('app.url').'/api/bank-paybill/status/'.$this->pendingStkCheckoutId);
                $result = $response->json();

                if (isset($result['status'])) {
                    if ($result['status'] === 'success') {
                        $this->stkStatus = 'success';
                        $this->stkProcessing = false;

                        // Find and update the transaction
                        $stkPush = BankPaybillStk::where('sale_id', $this->pendingStkSaleId)->first();
                        if ($stkPush && $stkPush->status === 'Completed') {
                            $this->handlePaymentSuccess('bank_paybill');
                        }
                    } elseif ($result['status'] === 'failed') {
                        $this->stkStatus = 'failed';
                        $this->stkProcessing = false;
                        $this->pendingStkSaleId = null;
                        $this->pendingStkCheckoutId = null;
                        $this->dispatch('notify', message: 'Payment failed: '.($result['message'] ?? 'Unknown error'), type: 'error');
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error checking Bank Paybill payment status: '.$e->getMessage());
            }
        } else {
            // Check M-Pesa status (also handle bank_paybill if not explicitly bank)
            $payment = Payment::where('sale_id', $this->pendingStkSaleId)
                ->whereIn('payment_method', ['mpesa', 'bank_paybill'])
                ->where('status', 'completed')
                ->first();

            if ($payment) {
                $this->handlePaymentSuccess('mpesa', $payment);
            } else {
                // Check if payment failed
                $stkPush = MpesaSTK::where('sale_id', $this->pendingStkSaleId)
                    ->whereNotNull('result_code')
                    ->where('result_code', '!=', '0')
                    ->first();

                if ($stkPush) {
                    $this->stkStatus = 'failed';
                    $this->stkProcessing = false;
                    $this->pendingStkSaleId = null;
                    $this->dispatch('notify', message: 'Payment failed: '.($stkPush->result_desc ?? 'Unknown error'), type: 'error');
                }
            }
        }
    }

    private function handlePaymentSuccess($paymentMethod, $payment = null)
    {
        if (! $payment) {
            $payment = Payment::where('sale_id', $this->pendingStkSaleId)
                ->where('payment_method', $paymentMethod)
                ->where('status', 'completed')
                ->first();
        }

        if ($payment) {
            $this->stkStatus = 'success';
            $this->stkProcessing = false;

            // Load sale data for receipt
            $sale = Sale::with(['items.medicine', 'customer', 'payments', 'pharmacy'])->find($payment->sale_id);
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

                $this->dispatch('notify', message: 'Payment completed successfully!', type: 'success');
            }

            $this->pendingStkSaleId = null;
            $this->pendingStkCheckoutId = null;
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
        $this->stkPhoneNumber = '';
        $this->stkProcessing = false;
        $this->stkStatus = null;
        $this->pendingStkSaleId = null;
        $this->pendingStkCheckoutId = null;
        $this->search = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
        $this->resetLineSpendByMedicine();
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

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->amountPaid = 0;
        $this->change = 0;
        $this->stkPhoneNumber = '';
        $this->stkProcessing = false;
        $this->stkStatus = null;
    }

    public function printThermal($saleId)
    {
        try {
            $sale = Sale::findOrFail($saleId);

            // Dispatch event to frontend to make API call
            $this->dispatch('print-thermal', saleId: $saleId);

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Failed to print: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.pos-component');
    }
}
