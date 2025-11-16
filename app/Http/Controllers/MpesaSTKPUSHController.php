<?php

namespace App\Http\Controllers;

use App\Mpesa\STKPush;
use App\Models\MpesaSTK;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Http\Request;

class MpesaSTKPUSHController extends Controller
{
    public $result_code = 1;
    public $result_desc = 'An error occured';

    // Initiate Stk Push Request
    public function STKPush(Request $request)
    {
        $amount = $request->input('amount');
        $phoneno = $request->input('phonenumber');
        $account_number = $request->input('account_number');
        $sale_id = $request->input('sale_id');

        $response = Mpesa::stkpush(
            phonenumber: $phoneno,
            amount: $amount,
            account_number: $account_number,
            callbackurl: null, // Using callback URL from config file
            transactionType: Mpesa::PAYBILL
        );

        /** @var \Illuminate\Http\Client\Response $response */
        $result = $response->json();

        MpesaSTK::create([
            'merchant_request_id' => $result['MerchantRequestID'],
            'checkout_request_id' => $result['CheckoutRequestID'],
            'sale_id' => $sale_id,
        ]);

        return $result;
    }

    // This function is used to review the response from Safaricom once a transaction is complete
    public function STKConfirm(Request $request)
    {
        $stk_push_confirm = (new STKPush())->confirm($request);
        if ($stk_push_confirm) {
            $this->result_code = 0;
            $this->result_desc = 'Success';
        }

        return response()->json([
            'ResultCode' => $this->result_code,
            'ResultDesc' => $this->result_desc
        ]);
    }
}

