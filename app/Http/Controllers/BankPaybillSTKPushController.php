<?php

namespace App\Http\Controllers;

use App\Mpesa\BankPaybillSTKPush;
use App\Models\BankPaybillStk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BankPaybillSTKPushController extends Controller
{
    public $result_code = 1;
    public $result_desc = 'An error occurred';

    /**
     * Get bank paybill number based on bank code
     */
    private function getBankPaybill(string $bankCode): ?string
    {
        $bankPaybills = [
            'kcb' => '522522',
            'equity' => '247247',
            'coop' => '400200',
            'absa' => '303030',
            'ncba' => '880100',
            'diamond' => '880100',
        ];

        return $bankPaybills[$bankCode] ?? null;
    }

    /**
     * Get bank name based on bank code
     */
    private function getBankName(string $bankCode): string
    {
        $bankNames = [
            'kcb' => 'KCB Bank',
            'equity' => 'Equity Bank',
            'coop' => 'Co-operative Bank',
            'absa' => 'Absa Bank',
            'ncba' => 'NCBA Bank',
            'diamond' => 'Diamond Trust Bank',
        ];

        return $bankNames[$bankCode] ?? 'Unknown Bank';
    }

    /**
     * Get M-Pesa access token
     */
    private function getAccessToken(string $consumerKey, string $consumerSecret, string $environment): string
    {
        $url = $environment === 'production'
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get($url);

        $data = $response->json();
        return $data['access_token'] ?? '';
    }

    /**
     * Initiate Bank Paybill STK Push with custom PartyB
     */
    private function initiateBankPaybillSTKPush(string $phonenumber, float $amount, string $bankPaybill, string $accountNumber, ?string $callbackurl = null)
    {
        // Get M-Pesa configuration
        $environment = config('mpesa.environment');
        $consumerKey = config('mpesa.mpesa_consumer_key');
        $consumerSecret = config('mpesa.mpesa_consumer_secret');
        $shortcode = config('mpesa.shortcode');
        $passkey = config('mpesa.passkey');

        // Generate timestamp and password
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);

        // Prepare STK Push data
        $stkPushData = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phonenumber,
            'PartyB' => $bankPaybill, // Bank's paybill number
            'PhoneNumber' => $phonenumber,
            'CallBackURL' => $callbackurl ?? config('mpesa.callbacks.callback_url'),
            'AccountReference' => $accountNumber, // Customer's bank account number
            'TransactionDesc' => 'Bank Paybill Payment'
        ];

        Log::info('Bank Paybill STK Push Data', [
            'stk_push_data' => $stkPushData,
            'bank_paybill' => $bankPaybill,
            'account_reference' => $accountNumber
        ]);

        // Make API call to M-Pesa STK Push
        $url = $environment === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken($consumerKey, $consumerSecret, $environment),
            'Content-Type' => 'application/json'
        ])->post($url, $stkPushData);

        return $response;
    }

    /**
     * Initiate Bank Paybill STK Push
     */
    public function STKPush(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'phonenumber' => 'required|string',
            'account_number' => 'required|string',
            'bank_code' => 'required|string|in:kcb,equity,coop,absa,ncba,diamond',
            'sale_id' => 'nullable|exists:sales,id',
        ]);

        $amount = (int) round((float) $request->input('amount')); // M-Pesa requires integer amounts
        $phoneno = $request->input('phonenumber');
        $accountNumber = $request->input('account_number');
        $bankCode = $request->input('bank_code');
        $saleId = $request->input('sale_id');

        // Format phone number for M-Pesa (add country code if not present)
        if (!str_starts_with($phoneno, '254')) {
            if (str_starts_with($phoneno, '0')) {
                $phoneno = '254' . substr($phoneno, 1);
            } else {
                $phoneno = '254' . $phoneno;
            }
        }

        // Get bank paybill number based on bank code
        $bankPaybill = $this->getBankPaybill($bankCode);
        if (!$bankPaybill) {
            Log::error("Invalid bank code: $bankCode");
            return response()->json(['message' => 'Invalid bank selected'], 400);
        }

        Log::info('Initiating Bank Paybill STK Push', [
            'phone' => $phoneno,
            'amount' => $amount,
            'bank_code' => $bankCode,
            'bank_paybill' => $bankPaybill,
            'account_number' => $accountNumber,
            'sale_id' => $saleId,
        ]);

        try {
            $callbackUrl = config('app.url') . '/api/bank-paybill/stk-confirm';
            $response = $this->initiateBankPaybillSTKPush(
                phonenumber: $phoneno,
                amount: $amount,
                bankPaybill: $bankPaybill,
                accountNumber: $accountNumber,
                callbackurl: $callbackUrl
            );

            /** @var \Illuminate\Http\Client\Response $response */
            $result = $response->json();

            // Log the full STK Push response
            Log::info('Bank Paybill STK Push Response', [
                'phone' => $phoneno,
                'amount' => $amount,
                'bank_code' => $bankCode,
                'bank_paybill' => $bankPaybill,
                'account_number' => $accountNumber,
                'full_response' => $result,
                'merchant_request_id' => $result['MerchantRequestID'] ?? 'not_set',
                'checkout_request_id' => $result['CheckoutRequestID'] ?? 'not_set',
                'response_code' => $result['ResponseCode'] ?? 'not_set',
                'response_description' => $result['ResponseDescription'] ?? 'not_set'
            ]);

            // Check if the response indicates an error
            if (isset($result['errorCode']) || isset($result['errorMessage'])) {
                Log::error('Bank Paybill STK Push failed with error', [
                    'error_code' => $result['errorCode'] ?? 'unknown',
                    'error_message' => $result['errorMessage'] ?? 'unknown',
                    'full_response' => $result
                ]);

                return response()->json([
                    'message' => $result['errorMessage'] ?? 'Bank Paybill STK Push failed',
                    'status' => $result['errorCode'] ?? 'error',
                ], 400);
            }

            // Check if we have the required response fields
            if (!isset($result['MerchantRequestID']) || !isset($result['CheckoutRequestID'])) {
                Log::error('Bank Paybill STK Push response missing required fields', [
                    'full_response' => $result
                ]);

                return response()->json([
                    'message' => 'Invalid response from payment gateway',
                    'status' => 'error',
                ], 500);
            }

            // Save transaction
            BankPaybillStk::create([
                'merchant_request_id' => $result['MerchantRequestID'],
                'checkout_request_id' => $result['CheckoutRequestID'],
                'sale_id' => $saleId,
                'phonenumber' => $request->input('phonenumber'),
                'amount' => $amount,
                'bank_code' => $bankCode,
                'bank_paybill' => $bankPaybill,
                'account_number' => $accountNumber,
                'status' => 'Pending',
            ]);

            return response()->json([
                'message' => $result['ResponseDescription'] ?? 'Bank Paybill STK Push initiated.',
                'status' => $result['ResponseCode'] ?? null,
                'checkout_request_id' => $result['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $result['MerchantRequestID'] ?? null,
                'bank_code' => $bankCode,
                'bank_name' => $this->getBankName($bankCode),
            ]);
        } catch (\Exception $e) {
            Log::error('Bank Paybill STK Push failed: ' . $e->getMessage(), [
                'phone' => $phoneno,
                'amount' => $amount,
                'bank_code' => $bankCode,
                'account_number' => $accountNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Bank Paybill STK Push failed. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Bank Paybill STK Push callback
     */
    public function STKConfirm(Request $request)
    {
        // Log the full callback request data
        Log::info('Bank Paybill STK Confirm Callback Received', [
            'full_request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $stk_push_confirm = (new BankPaybillSTKPush())->confirm($request);

        Log::info('Bank Paybill STK Confirm Processing Result', [
            'confirm_result' => $stk_push_confirm,
            'result_code' => $this->result_code,
            'result_desc' => $this->result_desc
        ]);

        if (!$stk_push_confirm->failed) {
            $this->result_code = 0;
            $this->result_desc = 'Success';
        }

        Log::info('Bank Paybill STK Confirm Response', [
            'final_result_code' => $this->result_code,
            'final_result_desc' => $this->result_desc
        ]);

        return response()->json([
            'ResultCode' => $this->result_code,
            'ResultDesc' => $this->result_desc
        ]);
    }

    /**
     * Check Bank Paybill transaction status
     */
    public function checkStatus(Request $request, $checkoutId)
    {
        try {
            Log::info('Bank Paybill checkStatus called', [
                'checkoutId' => $checkoutId,
                'request_data' => $request->all(),
            ]);

            $transaction = BankPaybillStk::where('checkout_request_id', $checkoutId)->first();

            if (!$transaction) {
                Log::warning('Bank Paybill transaction not found', ['checkoutId' => $checkoutId]);
                return response()->json(['status' => 'Pending', 'message' => 'Transaction not found yet']);
            }

            Log::info('Bank Paybill transaction found', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status,
                'result_code' => $transaction->result_code,
                'result_desc' => $transaction->result_desc
            ]);

            // If transaction is completed
            if ($transaction->status === 'Completed') {
                Log::info('Bank Paybill transaction completed', [
                    'checkoutId' => $checkoutId,
                    'bank_code' => $transaction->bank_code
                ]);

                return response()->json([
                    'status' => 'success',
                    'bank_code' => $transaction->bank_code,
                    'bank_name' => $this->getBankName($transaction->bank_code),
                ]);
            }

            // If transaction failed
            if ($transaction->status === 'Failed') {
                Log::info('Bank Paybill transaction failed', [
                    'checkoutId' => $checkoutId,
                    'result_code' => $transaction->result_code,
                    'result_desc' => $transaction->result_desc
                ]);
                return response()->json(['status' => 'failed', 'message' => 'Payment failed']);
            }

            // Default response for pending status
            return response()->json([
                'status' => $transaction->status ?? 'Pending',
                'message' => 'Payment status checked'
            ]);
        } catch (\Exception $e) {
            Log::error('Bank Paybill checkStatus Error', [
                'error' => $e->getMessage(),
                'checkoutId' => $checkoutId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to check transaction status'], 500);
        }
    }
}

