<?php

namespace App\Filament\Widgets;

use App\Helpers\SettingsHelper;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfitLossWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $pharmacyId = $user->pharmacy_id;
        
        // Helper function to calculate COGS (Cost of Goods Sold)
        $calculateCOGS = function ($query) {
            return SaleItem::whereHas('sale', $query)
                ->join('stock_batches', 'sale_items.stock_batch_id', '=', 'stock_batches.id')
                ->selectRaw('SUM(sale_items.quantity * stock_batches.cost_price) as cogs')
                ->value('cogs') ?? 0;
        };
        
        // Get today's data
        $todaySales = Sale::where('status', 'completed')
            ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereDate('sale_date', today())
            ->sum('total_amount');
        
        $todayCOGS = $calculateCOGS(fn($q) => $q->where('status', 'completed')
            ->when($pharmacyId, fn($q2) => $q2->where('pharmacy_id', $pharmacyId))
            ->whereDate('sale_date', today()));
        
        $todayExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereDate('expense_date', today())
            ->sum('amount');
        
        $todayGrossProfit = $todaySales - $todayCOGS;
        $todayNetProfit = $todayGrossProfit - $todayExpenses;
        
        // Get this month's data
        $monthSales = Sale::where('status', 'completed')
            ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month)
            ->sum('total_amount');
        
        $monthCOGS = $calculateCOGS(fn($q) => $q->where('status', 'completed')
            ->when($pharmacyId, fn($q2) => $q2->where('pharmacy_id', $pharmacyId))
            ->whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month));
        
        $monthExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');
        
        $monthGrossProfit = $monthSales - $monthCOGS;
        $monthNetProfit = $monthGrossProfit - $monthExpenses;
        
        // Get this year's data
        $yearSales = Sale::where('status', 'completed')
            ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereYear('sale_date', now()->year)
            ->sum('total_amount');
        
        $yearCOGS = $calculateCOGS(fn($q) => $q->where('status', 'completed')
            ->when($pharmacyId, fn($q2) => $q2->where('pharmacy_id', $pharmacyId))
            ->whereYear('sale_date', now()->year));
        
        $yearExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        
        $yearGrossProfit = $yearSales - $yearCOGS;
        $yearNetProfit = $yearGrossProfit - $yearExpenses;
        
        // Calculate profit margins
        $todayMargin = $todaySales > 0 ? (($todayNetProfit / $todaySales) * 100) : 0;
        $monthMargin = $monthSales > 0 ? (($monthNetProfit / $monthSales) * 100) : 0;
        $yearMargin = $yearSales > 0 ? (($yearNetProfit / $yearSales) * 100) : 0;
        
        // Get last 7 days profit data for charts
        $last7DaysProfit = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daySales = Sale::where('status', 'completed')
                ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->whereDate('sale_date', $date->toDateString())
                ->sum('total_amount');
            
            $dayCOGS = $calculateCOGS(fn($q) => $q->where('status', 'completed')
                ->when($pharmacyId, fn($q2) => $q2->where('pharmacy_id', $pharmacyId))
                ->whereDate('sale_date', $date->toDateString()));
            
            $dayExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->whereDate('expense_date', $date->toDateString())
                ->sum('amount');
            
            $last7DaysProfit[] = round($daySales - $dayCOGS - $dayExpenses, 2);
        }
        
        return [
            Stat::make('Today\'s Net Profit/Loss', SettingsHelper::formatCurrency($todayNetProfit))
                ->description($todayMargin >= 0 ? number_format($todayMargin, 1) . '% profit margin • Revenue: ' . SettingsHelper::formatCurrency($todaySales) : 'Loss incurred • Revenue: ' . SettingsHelper::formatCurrency($todaySales))
                ->descriptionIcon($todayNetProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down', 'before')
                ->color($todayNetProfit >= 0 ? 'success' : 'danger')
                ->icon($todayNetProfit >= 0 ? 'heroicon-o-chart-bar-square' : 'heroicon-o-exclamation-triangle')
                ->chart($last7DaysProfit),
            
            Stat::make('This Month\'s Net Profit/Loss', SettingsHelper::formatCurrency($monthNetProfit))
                ->description($monthMargin >= 0 ? number_format($monthMargin, 1) . '% profit margin • Revenue: ' . SettingsHelper::formatCurrency($monthSales) : 'Loss incurred • Revenue: ' . SettingsHelper::formatCurrency($monthSales))
                ->descriptionIcon($monthNetProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down', 'before')
                ->color($monthNetProfit >= 0 ? 'success' : 'danger')
                ->icon($monthNetProfit >= 0 ? 'heroicon-o-chart-bar' : 'heroicon-o-exclamation-triangle')
                ->chart($last7DaysProfit),
            
            Stat::make('This Year\'s Net Profit/Loss', SettingsHelper::formatCurrency($yearNetProfit))
                ->description($yearMargin >= 0 ? number_format($yearMargin, 1) . '% profit margin • Revenue: ' . SettingsHelper::formatCurrency($yearSales) : 'Loss incurred • Revenue: ' . SettingsHelper::formatCurrency($yearSales))
                ->descriptionIcon($yearNetProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down', 'before')
                ->color($yearNetProfit >= 0 ? 'success' : 'danger')
                ->icon($yearNetProfit >= 0 ? 'heroicon-o-banknotes' : 'heroicon-o-exclamation-triangle')
                ->chart($last7DaysProfit),
        ];
    }
}

