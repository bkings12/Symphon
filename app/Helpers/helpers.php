<?php

use App\Helpers\SettingsHelper;

if (!function_exists('setting')) {
    /**
     * Get a setting value
     */
    function setting(string $key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol
     */
    function currency_symbol(): string
    {
        return SettingsHelper::currencySymbol();
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     */
    function format_currency(float $amount): string
    {
        return SettingsHelper::formatCurrency($amount);
    }
}

if (!function_exists('tax_rate')) {
    /**
     * Get tax rate
     */
    function tax_rate(): float
    {
        return SettingsHelper::taxRate();
    }
}

if (!function_exists('format_phone_number')) {
    /**
     * Format Kenyan phone number to international format (254XXXXXXXXX)
     * Supports:
     * - Mobile: 07XXXXXXXX, 2547XXXXXXXX
     * - Landline: 0111XXXXXX, 254111XXXXXX
     * - Other formats: 020XXXXXXX, 25420XXXXXXX
     */
    function format_phone_number(string $phoneNumber): string
    {
        // Remove all spaces, dashes, and other non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Handle empty string
        if (empty($phoneNumber)) {
            return '';
        }
        
        // If already in international format (starts with 254 and has 12 digits)
        if (strlen($phoneNumber) === 12 && substr($phoneNumber, 0, 3) === '254') {
            return $phoneNumber;
        }
        
        // Handle mobile numbers starting with 0 (07XXXXXXXX)
        if (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) === '0') {
            return '254' . substr($phoneNumber, 1);
        }
        
        // Handle mobile numbers without leading 0 (9 digits)
        if (strlen($phoneNumber) === 9 && substr($phoneNumber, 0, 1) === '7') {
            return '254' . $phoneNumber;
        }
        
        // Handle landline numbers starting with 0 (0111XXXXXX, 020XXXXXXX, etc.)
        if (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) === '0') {
            // Landline: 0111XXXXXX -> 254111XXXXXX
            // Landline: 020XXXXXXX -> 25420XXXXXXX
            return '254' . substr($phoneNumber, 1);
        }
        
        // Handle landline numbers without leading 0
        if (strlen($phoneNumber) === 9 && (substr($phoneNumber, 0, 3) === '111' || substr($phoneNumber, 0, 2) === '20')) {
            return '254' . $phoneNumber;
        }
        
        // If it doesn't match any pattern, return as is (might be already formatted)
        return $phoneNumber;
    }
}

if (!function_exists('validate_phone_number')) {
    /**
     * Validate Kenyan phone number format
     * Returns true if the phone number is valid, false otherwise
     */
    function validate_phone_number(string $phoneNumber): bool
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (empty($phoneNumber)) {
            return false;
        }
        
        // Check if it's a valid mobile number (07XXXXXXXX or 2547XXXXXXXX)
        if (preg_match('/^(0|254)?7\d{8}$/', $phoneNumber)) {
            return true;
        }
        
        // Check if it's a valid landline number (0111XXXXXX, 020XXXXXXX, etc.)
        if (preg_match('/^(0|254)?(111|20)\d{6,7}$/', $phoneNumber)) {
            return true;
        }
        
        // Check if it's already in international format
        if (strlen($phoneNumber) === 12 && substr($phoneNumber, 0, 3) === '254') {
            return true;
        }
        
        return false;
    }
}

