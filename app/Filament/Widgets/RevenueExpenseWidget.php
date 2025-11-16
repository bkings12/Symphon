<?php

namespace App\Filament\Widgets;

use App\Helpers\SettingsHelper;
use App\Models\Expense;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RevenueExpenseWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $pharmacyId = $user->pharmacy_id;
        
        // Today's revenue and expenses
        $todayRevenue = Sale::where('status', 'completed')
            ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereDate('sale_date', today())
            ->sum('total_amount');
        
        $todayExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereDate('expense_date', today())
            ->sum('amount');
        
        // This month's revenue and expenses
        $monthRevenue = Sale::where('status', 'completed')
            ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month)
            ->sum('total_amount');
        
        $monthExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');
        
        // Get last 7 days revenue and expenses for charts
        $last7DaysRevenue = [];
        $last7DaysExpenses = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $dayRevenue = Sale::where('status', 'completed')
                ->when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->whereDate('sale_date', $date->toDateString())
                ->sum('total_amount');
            
            $dayExpenses = Expense::when($pharmacyId, fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->whereDate('expense_date', $date->toDateString())
                ->sum('amount');
            
            $last7DaysRevenue[] = round($dayRevenue, 2);
            $last7DaysExpenses[] = round($dayExpenses, 2);
        }
        
        return [
            Stat::make('Today\'s Revenue', SettingsHelper::formatCurrency($todayRevenue))
                ->description('Total sales today')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->chart($last7DaysRevenue),
            
            Stat::make('Today\'s Expenses', SettingsHelper::formatCurrency($todayExpenses))
                ->description('Total expenses today')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->color('warning')
                ->icon('heroicon-o-arrow-trending-down')
                ->chart($last7DaysExpenses),
            
            Stat::make('This Month\'s Revenue', SettingsHelper::formatCurrency($monthRevenue))
                ->description('Total sales this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->chart($last7DaysRevenue),
            
            Stat::make('This Month\'s Expenses', SettingsHelper::formatCurrency($monthExpenses))
                ->description('Total expenses this month')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->color('warning')
                ->icon('heroicon-o-arrow-trending-down')
                ->chart($last7DaysExpenses),
        ];
    }
}

