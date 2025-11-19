<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule low stock check to run daily at 9 AM
Schedule::command('stock:check-low --send-sms')
    ->dailyAt('09:00')
    ->description('Check for low stock and send SMS notifications');
