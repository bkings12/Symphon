<?php

namespace App\Notifications;

use App\Models\Medicine;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class LowStockNotification
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Check for low stock medicines and send SMS notifications
     */
    public function checkAndNotify(): void
    {
        if (!$this->smsService->isEnabled()) {
            Log::info('SMS notifications disabled, skipping low stock check');
            return;
        }

        $threshold = (int) \App\Models\Setting::get('low_stock_threshold', 10);
        $notificationPhone = \App\Models\Setting::get('sms_notification_phone', '');

        if (empty($notificationPhone)) {
            Log::warning('SMS notification phone not configured');
            return;
        }

        // Get medicines with low stock
        $lowStockMedicines = Medicine::where('stock_quantity', '<=', $threshold)
            ->where('stock_quantity', '>', 0)
            ->get();

        if ($lowStockMedicines->isEmpty()) {
            return;
        }

        // Build message
        $message = $this->buildMessage($lowStockMedicines, $threshold);

        // Send SMS
        $result = $this->smsService->send($notificationPhone, $message);

        if ($result['success']) {
            Log::info('Low stock notification sent', [
                'phone' => $notificationPhone,
                'medicines_count' => $lowStockMedicines->count(),
            ]);
        } else {
            Log::error('Failed to send low stock notification', [
                'phone' => $notificationPhone,
                'error' => $result['message'] ?? 'Unknown error',
            ]);
        }
    }

    /**
     * Build the SMS message for low stock alert
     */
    protected function buildMessage($medicines, $threshold): string
    {
        $pharmacyName = \App\Models\Setting::get('pharmacy_name', 'Pharmacy');
        $message = "⚠️ LOW STOCK ALERT - {$pharmacyName}\n\n";
        $message .= "The following medicines are running low (threshold: {$threshold}):\n\n";

        foreach ($medicines->take(10) as $medicine) {
            $message .= "• {$medicine->name}: {$medicine->stock_quantity} units\n";
        }

        if ($medicines->count() > 10) {
            $message .= "\n... and " . ($medicines->count() - 10) . " more";
        }

        $message .= "\nPlease restock soon!";

        return $message;
    }
}

