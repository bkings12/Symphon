<?php

namespace App\Mpesa;

use App\Models\BankPaybillStk;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// This Class is responsible for getting a response from Safaricom for Bank Paybill STK Push and Storing the Transaction Details to the Database
class BankPaybillSTKPush
{
    public $failed = false;
    public $response = 'An Unknown Error Occurred';

    public function confirm(Request $request)
    {
        try {
            $payload = json_decode($request->getContent());
            
            if (!property_exists($payload, 'Body') || !property_exists($payload->Body, 'stkCallback')) {
                Log::error('Invalid Bank Paybill STK callback payload structure', [
                    'payload' => $request->getContent()
                ]);
                $this->failed = true;
                return $this;
            }

            $callback = $payload->Body->stkCallback;
            $resultCode = $callback->ResultCode ?? null;
            $merchant_request_id = $callback->MerchantRequestID ?? null;
            $checkout_request_id = $callback->CheckoutRequestID ?? null;
            $result_desc = $callback->ResultDesc ?? null;

            $stkPush = BankPaybillStk::where('merchant_request_id', $merchant_request_id)
                ->where('checkout_request_id', $checkout_request_id)->first();

            if ($resultCode == '0') {
                // Payment successful
                $amount = $callback->CallbackMetadata->Item[0]->Value ?? null;
                $mpesa_receipt_number = $callback->CallbackMetadata->Item[1]->Value ?? null;
                $transaction_date = $callback->CallbackMetadata->Item[3]->Value ?? null;
                $phonenumber = $callback->CallbackMetadata->Item[4]->Value ?? null;

                $data = [
                    'result_desc' => $result_desc,
                    'result_code' => $resultCode,
                    'merchant_request_id' => $merchant_request_id,
                    'checkout_request_id' => $checkout_request_id,
                    'amount' => $amount,
                    'mpesa_receipt_number' => $mpesa_receipt_number,
                    'transaction_date' => $transaction_date,
                    'phonenumber' => $phonenumber,
                    'status' => 'Completed',
                ];

                if ($stkPush) {
                    $stkPush->fill($data)->save();

                    // Update payment status and complete sale if sale_id exists
                    if ($stkPush->sale_id) {
                        $sale = Sale::find($stkPush->sale_id);
                        if ($sale) {
                            $payment = Payment::where('sale_id', $sale->id)
                                ->where('payment_method', 'bank_paybill')
                                ->where('status', 'pending')
                                ->first();

                            if ($payment) {
                                $payment->update([
                                    'status' => 'completed',
                                    'reference_number' => $mpesa_receipt_number,
                                ]);

                                // Update sale status to completed
                                $sale->update([
                                    'status' => 'completed',
                                    'notes' => 'POS Sale - Bank Paybill Payment Completed',
                                ]);

                                // Update stock - decrement quantities
                                foreach ($sale->items as $saleItem) {
                                    $stockBatch = $saleItem->stockBatch;
                                    if ($stockBatch) {
                                        $stockBatch->decrement('remaining_quantity', $saleItem->quantity);
                                    }
                                    $medicine = $saleItem->medicine;
                                    if ($medicine) {
                                        $medicine->decrement('stock_quantity', $saleItem->quantity);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    BankPaybillStk::create($data);
                }
            } else {
                // Payment failed
                $data = [
                    'result_desc' => $result_desc,
                    'result_code' => $resultCode,
                    'merchant_request_id' => $merchant_request_id,
                    'checkout_request_id' => $checkout_request_id,
                    'status' => 'Failed',
                ];

                if ($stkPush) {
                    $stkPush->fill($data)->save();

                    // Update payment status if sale_id exists
                    if ($stkPush->sale_id) {
                        $sale = Sale::find($stkPush->sale_id);
                        if ($sale) {
                            $payment = Payment::where('sale_id', $sale->id)
                                ->where('payment_method', 'bank_paybill')
                                ->where('status', 'pending')
                                ->first();

                            if ($payment) {
                                $payment->update([
                                    'status' => 'failed',
                                ]);
                            }
                        }
                    }
                } else {
                    BankPaybillStk::create($data);
                }

                $this->failed = true;
            }
        } catch (\Exception $e) {
            Log::error('Bank Paybill STK Push confirmation error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'payload' => $request->getContent(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->failed = true;
        }

        return $this;
    }
}

