<?php

namespace App\Console\Commands;

use App\Notifications\LowStockNotification;
use App\Services\SmsService;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low {--send-sms : Send SMS notifications for low stock items}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for medicines with low stock and optionally send SMS notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $smsService = new SmsService();
        $notification = new LowStockNotification($smsService);

        if ($this->option('send-sms')) {
            $this->info('Checking for low stock and sending SMS notifications...');
            $notification->checkAndNotify();
            $this->info('Low stock check completed.');
        } else {
            $this->info('Checking for low stock items...');
            
            $threshold = (int) \App\Models\Setting::get('low_stock_threshold', 10);
            $lowStockMedicines = \App\Models\Medicine::where('stock_quantity', '<=', $threshold)
                ->where('stock_quantity', '>', 0)
                ->get();

            if ($lowStockMedicines->isEmpty()) {
                $this->info('No medicines with low stock found.');
            } else {
                $this->warn("Found {$lowStockMedicines->count()} medicine(s) with low stock:");
                $this->table(
                    ['Name', 'Current Stock', 'Threshold'],
                    $lowStockMedicines->map(function ($medicine) use ($threshold) {
                        return [
                            $medicine->name,
                            $medicine->stock_quantity,
                            $threshold,
                        ];
                    })->toArray()
                );
            }
        }

        return Command::SUCCESS;
    }
}

