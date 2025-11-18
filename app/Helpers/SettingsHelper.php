<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper
{
    /**
     * Get currency symbol
     */
    public static function currencySymbol(): string
    {
        return Setting::get('currency_symbol', '$');
    }

    /**
     * Get currency code
     */
    public static function currency(): string
    {
        return Setting::get('currency', 'USD');
    }

    /**
     * Get tax rate
     */
    public static function taxRate(): float
    {
        return (float) Setting::get('tax_rate', 10);
    }

    /**
     * Get tax rate as decimal (e.g., 0.10 for 10%)
     */
    public static function taxRateDecimal(): float
    {
        return self::taxRate() / 100;
    }

    /**
     * Get low stock threshold
     */
    public static function lowStockThreshold(): int
    {
        return (int) Setting::get('low_stock_threshold', 10);
    }

    /**
     * Get receipt footer
     */
    public static function receiptFooter(): string
    {
        return Setting::get('receipt_footer', 'Thank you for your business!');
    }

    /**
     * Check if M-Pesa is enabled
     */
    public static function isMpesaEnabled(): bool
    {
        return filter_var(Setting::get('mpesa_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get M-Pesa credentials
     */
    public static function mpesaCredentials(): array
    {
        return [
            'enabled' => self::isMpesaEnabled(),
            'paybill' => Setting::get('mpesa_paybill', ''),
            'shortcode' => Setting::get('mpesa_shortcode', ''),
            'consumer_key' => Setting::get('mpesa_consumer_key', ''),
            'consumer_secret' => Setting::get('mpesa_consumer_secret', ''),
            'passkey' => Setting::get('mpesa_passkey', ''),
            'environment' => Setting::get('mpesa_environment', 'sandbox'),
        ];
    }

    /**
     * Get payment type (mpesa or bank_stk)
     */
    public static function paymentType(): string
    {
        return Setting::get('payment_type', 'mpesa');
    }

    /**
     * Check if bank paybill is enabled
     */
    public static function isBankPaybillEnabled(): bool
    {
        return self::paymentType() === 'bank_stk';
    }

    /**
     * Get bank paybill configuration
     */
    public static function bankPaybillConfig(): array
    {
        return [
            'bank_code' => Setting::get('bank_code', 'kcb'),
            'bank_account_number' => Setting::get('bank_account_number', ''),
        ];
    }

    /**
     * Format currency amount
     */
    public static function formatCurrency(float $amount): string
    {
        return self::currencySymbol() . ' ' . number_format($amount, 2);
    }

    /**
     * Check if SMS notifications are enabled
     */
    public static function isSmsEnabled(): bool
    {
        return filter_var(Setting::get('sms_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get SMS configuration
     */
    public static function smsConfig(): array
    {
        return [
            'enabled' => self::isSmsEnabled(),
            'provider' => Setting::get('sms_provider', 'blessed_text'),
            'api_key' => Setting::get('sms_api_key', ''),
            'sender_id' => Setting::get('sms_sender_id', ''),
            'notification_phone' => Setting::get('sms_notification_phone', ''),
        ];
    }
}

