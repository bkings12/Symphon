<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $provider;
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->provider = \App\Models\Setting::get('sms_provider', 'blessed_text');
        $this->apiKey = \App\Models\Setting::get('sms_api_key', '');
        $this->senderId = \App\Models\Setting::get('sms_sender_id', '');
    }

    /**
     * Send SMS using the configured provider
     */
    public function send(string $phone, string $message): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'SMS service is not enabled'
            ];
        }

        switch ($this->provider) {
            case 'blessed_text':
                return $this->sendViaBlessedText($phone, $message);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown SMS provider'
                ];
        }
    }

    /**
     * Send SMS to multiple recipients
     */
    public function sendBulk(array $phones, string $message): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'SMS service is not enabled'
            ];
        }

        switch ($this->provider) {
            case 'blessed_text':
                return $this->sendBulkViaBlessedText($phones, $message);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown SMS provider'
                ];
        }
    }

    /**
     * Send SMS via Blessed Text API
     */
    protected function sendViaBlessedText(string $phone, string $message): array
    {
        try {
            // Format phone number (ensure it's in 254 format)
            $phone = $this->formatPhoneNumber($phone);

            $response = Http::post('https://sms.blessedtexts.com/api/sms/v1/sendsms', [
                'api_key' => $this->apiKey,
                'sender_id' => $this->senderId,
                'message' => $message,
                'phone' => $phone,
            ]);

            $result = $response->json();

            if (is_array($result) && isset($result[0]['status_code']) && $result[0]['status_code'] === '1000') {
                Log::info('SMS sent successfully via Blessed Text', [
                    'phone' => $phone,
                    'message_id' => $result[0]['message_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $result[0]['message_id'] ?? null,
                    'cost' => $result[0]['message_cost'] ?? null,
                ];
            }

            $errorMessage = $result[0]['status_desc'] ?? 'Unknown error';
            Log::error('SMS sending failed via Blessed Text', [
                'phone' => $phone,
                'error' => $errorMessage,
                'response' => $result,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending exception via Blessed Text', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send bulk SMS via Blessed Text API
     */
    protected function sendBulkViaBlessedText(array $phones, string $message): array
    {
        try {
            // Format all phone numbers
            $formattedPhones = array_map([$this, 'formatPhoneNumber'], $phones);
            $phoneString = implode(',', $formattedPhones);

            $response = Http::post('https://sms.blessedtexts.com/api/sms/v1/sendsms', [
                'api_key' => $this->apiKey,
                'sender_id' => $this->senderId,
                'message' => $message,
                'phone' => $phoneString,
            ]);

            $result = $response->json();

            if (is_array($result) && isset($result[0]['status_code']) && $result[0]['status_code'] === '1000') {
                Log::info('Bulk SMS sent successfully via Blessed Text', [
                    'count' => count($phones),
                    'message_ids' => array_column($result, 'message_id'),
                ]);

                return [
                    'success' => true,
                    'message' => 'Bulk SMS sent successfully',
                    'sent_count' => count($result),
                    'results' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => $result[0]['status_desc'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('Bulk SMS sending exception via Blessed Text', [
                'count' => count($phones),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send bulk SMS: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get SMS balance from Blessed Text
     */
    public function getBalance(): array
    {
        if ($this->provider !== 'blessed_text') {
            return [
                'success' => false,
                'message' => 'Balance check only available for Blessed Text',
            ];
        }

        try {
            $response = Http::post('https://sms.blessedtexts.com/api/sms/v1/credit-balance', [
                'api_key' => $this->apiKey,
            ]);

            $result = $response->json();

            if (isset($result['status_code']) && $result['status_code'] === '1000') {
                return [
                    'success' => true,
                    'balance' => $result['balance'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'message' => $result['status_desc'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('SMS balance check exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to check balance: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to 254 format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any spaces or special characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to 254 format
        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '254')) {
            return '254' . $phone;
        }

        return $phone;
    }

    /**
     * Check if SMS service is enabled
     */
    public function isEnabled(): bool
    {
        $enabled = \App\Models\Setting::get('sms_enabled', false);
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN) && !empty($this->apiKey) && !empty($this->senderId);
    }
}

