<?php

namespace App\Mpesa;

use App\Models\MpesaSTK;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

// This Class is responsible for getting a response from Safaricom and Storing the Transaction Details to the Database
class STKPush
{
    public $failed = false;
    public $response = 'An Unkown Error Occured';

    public function confirm(Request $request)
    {
        $payload = json_decode($request->getContent());
        if (property_exists($payload, 'Body') && $payload->Body->stkCallback->ResultCode == '0') {
            $merchant_request_id = $payload->Body->stkCallback->MerchantRequestID;
            $checkout_request_id = $payload->Body->stkCallback->CheckoutRequestID;
            $result_desc = $payload->Body->stkCallback->ResultDesc;
            $result_code = $payload->Body->stkCallback->ResultCode;
            $amount = $payload->Body->stkCallback->CallbackMetadata->Item[0]->Value;
            $mpesa_receipt_number = $payload->Body->stkCallback->CallbackMetadata->Item[1]->Value;
            $transaction_date = $payload->Body->stkCallback->CallbackMetadata->Item[3]->Value;
            $phonenumber = $payload->Body->stkCallback->CallbackMetadata->Item[4]->Value;

            $stkPush = MpesaSTK::where('merchant_request_id', $merchant_request_id)
                ->where('checkout_request_id', $checkout_request_id)->first(); //fetch the transaction based on the merchant and checkout ids

            $data = [
                'result_desc' => $result_desc,
                'result_code' => $result_code,
                'merchant_request_id' => $merchant_request_id,
                'checkout_request_id' => $checkout_request_id,
                'amount' => $amount,
                'mpesa_receipt_number' => $mpesa_receipt_number,
                'transaction_date' => $transaction_date,
                'phonenumber' => $phonenumber,
            ];

            if ($stkPush) {
                $stkPush->fill($data)->save();

                // Update payment status and complete sale if sale_id exists
                if ($stkPush->sale_id) {
                    $sale = Sale::find($stkPush->sale_id);
                    if ($sale) {
                        $payment = Payment::where('sale_id', $sale->id)
                            ->where('payment_method', 'mpesa')
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
                                'notes' => 'POS Sale - M-Pesa Payment Completed',
                            ]);

                            // Update stock - decrement quantities
                            foreach ($sale->items as $saleItem) {
                                $medicine = $saleItem->medicine;
                                if ($medicine) {
                                    $medicine->decrement('stock_quantity', $saleItem->quantity);
                                }
                            }
                        }
                    }
                }
            } else {
                MpesaSTK::create($data);
            }
        } else {
            $this->failed = true;
        }

        return $this;
    }
}

