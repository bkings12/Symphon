<?php

namespace App\Filament\Widgets;

use App\Helpers\SettingsHelper;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ProfitLossTrendWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    
    public function getHeading(): string
    {
        return 'Profit/Loss Trend (Last 30 Days)';
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $pharmacyId = $user->pharmacy_id;
        
        // Helper function to calculate COGS
        $calculateCOGS = function ($query) {
            return SaleItem::whereHas('sale', $query)
                ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
                ->selectRaw('SUM(sale_items.quantity * medicines.cost_price) as cogs')
                ->value('cogs') ?? 0;
        };
        
        // Get last 30 days data
        $labels = [];
        $profitData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateLabel = $date->format('d M');
            $labels[] = $dateLabel;
            
            $sales = Sale::where('status', 'completed')
                ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->whereDate('sale_date', $date->toDateString())
                ->sum('total_amount');
            
            $cogs = $calculateCOGS(fn($q) => $q->where('status', 'completed')
                ->when($pharmacyId, fn($q2) => $q2->where('pharmacy_id', $pharmacyId))
                ->whereDate('sale_date', $date->toDateString()));
            
            $expenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->whereDate('expense_date', $date->toDateString())
                ->sum('amount');
            
            $profit = $sales - $cogs - $expenses;
            $profitData[] = round($profit, 2);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Net Profit/Loss',
                    'data' => $profitData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'ticks' => [
                        'callback' => 'function(value) { return "' . SettingsHelper::currencySymbol() . ' " + value.toLocaleString("en-US", {minimumFractionDigits: 2, maximumFractionDigits: 2}); }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + "' . SettingsHelper::currencySymbol() . ' " + context.parsed.y.toLocaleString("en-US", {minimumFractionDigits: 2, maximumFractionDigits: 2}); }',
                    ],
                ],
            ],
        ];
    }
}

