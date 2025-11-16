<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Merge M-Pesa settings from database into config after boot
        try {
            if (app()->bound('db') && \Schema::hasTable('settings')) {
                config([
                    'mpesa.environment' => \App\Models\Setting::get('mpesa_environment', config('mpesa.environment')),
                    'mpesa.mpesa_consumer_key' => \App\Models\Setting::get('mpesa_consumer_key', config('mpesa.mpesa_consumer_key')),
                    'mpesa.mpesa_consumer_secret' => \App\Models\Setting::get('mpesa_consumer_secret', config('mpesa.mpesa_consumer_secret')),
                    'mpesa.passkey' => \App\Models\Setting::get('mpesa_passkey', config('mpesa.passkey')),
                    'mpesa.shortcode' => \App\Models\Setting::get('mpesa_shortcode', config('mpesa.shortcode')),
                    'mpesa.till_number' => \App\Models\Setting::get('mpesa_paybill', config('mpesa.till_number')),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if database isn't ready or settings table doesn't exist
        }
    }
}
