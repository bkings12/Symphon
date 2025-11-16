<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pharmacy_id')
                    ->label('Pharmacy')
                    ->relationship('pharmacy', 'name')
                    ->searchable()
                    ->preload(),
                
                Select::make('user_id')
                    ->label('Recorded By')
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('category')
                    ->options([
                        'rent' => 'Rent',
                        'utilities' => 'Utilities',
                        'salaries' => 'Salaries',
                        'marketing' => 'Marketing',
                        'maintenance' => 'Maintenance',
                        'supplies' => 'Supplies',
                        'insurance' => 'Insurance',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->searchable()
                    ->label('Category'),
                
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Title'),
                
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->label('Amount'),
                
                DatePicker::make('expense_date')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->label('Expense Date'),
                
                FileUpload::make('receipt_path')
                    ->label('Receipt')
                    ->directory('expenses/receipts')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120)
                    ->columnSpanFull(),
            ]);
    }
}
