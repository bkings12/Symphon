<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'currency', 'value' => 'KES', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => 'KSh', 'group' => 'general'],
            ['key' => 'tax_rate', 'value' => '16', 'group' => 'general'],
            ['key' => 'receipt_footer', 'value' => 'Thank you for choosing Symphony Pharmacy. Your health is our priority!', 'group' => 'general'],
            ['key' => 'low_stock_threshold', 'value' => '10', 'group' => 'general'],
            
            // M-Pesa Settings
            ['key' => 'mpesa_enabled', 'value' => false, 'group' => 'mpesa'],
            ['key' => 'mpesa_paybill', 'value' => '', 'group' => 'mpesa'],
            ['key' => 'mpesa_consumer_key', 'value' => '', 'group' => 'mpesa'],
            ['key' => 'mpesa_consumer_secret', 'value' => '', 'group' => 'mpesa'],
            ['key' => 'mpesa_passkey', 'value' => '', 'group' => 'mpesa'],
            ['key' => 'mpesa_shortcode', 'value' => '', 'group' => 'mpesa'],
            ['key' => 'mpesa_environment', 'value' => 'sandbox', 'group' => 'mpesa'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}

