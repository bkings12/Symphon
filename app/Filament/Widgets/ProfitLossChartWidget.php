<?php

namespace App\Filament\Widgets;

use App\Helpers\SettingsHelper;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ProfitLossChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;
    
    public function getHeading(): string
    {
        return 'Profit & Loss Overview';
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $pharmacyId = $user->pharmacy_id;
        
        // Helper function to calculate COGS
        $calculateCOGS = function ($query) {
            return SaleItem::whereHas('sale', $query)
                ->join('stock_batches', 'sale_items.stock_batch_id', '=', 'stock_batches.id')
                ->selectRaw('SUM(sale_items.quantity * stock_batches.cost_price) as cogs')
                ->value('cogs') ?? 0;
        };
        
        // Get last 7 days data
        $labels = [];
        $revenueData = [];
        $cogsData = [];
        $expensesData = [];
        $profitData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateLabel = $date->format('M d');
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
            
            $revenueData[] = round($sales, 2);
            $cogsData[] = round($cogs, 2);
            $expensesData[] = round($expenses, 2);
            $profitData[] = round($profit, 2);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'COGS',
                    'data' => $cogsData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expensesData,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.2)',
                    'borderColor' => 'rgba(251, 146, 60, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Net Profit/Loss',
                    'data' => $profitData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 3,
                    'type' => 'line',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
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

