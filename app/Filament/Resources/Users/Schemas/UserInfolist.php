<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),
                        
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        
                        TextEntry::make('pharmacy.name')
                            ->label('Pharmacy'),
                        
                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime()
                            ->placeholder('Not verified'),
                    ])
                    ->columns(2),
                
                Section::make('Roles & Permissions')
                    ->schema([
                        TextEntry::make('roles.name')
                            ->badge()
                            ->separator(',')
                            ->label('Roles')
                            ->placeholder('No roles assigned'),
                    ]),
                
                Section::make('Activity')
                    ->schema([
                        TextEntry::make('sales_count')
                            ->label('Total Sales')
                            ->counts('sales'),
                        
                        TextEntry::make('purchases_count')
                            ->label('Total Purchases')
                            ->counts('purchases'),
                        
                        TextEntry::make('expenses_count')
                            ->label('Total Expenses')
                            ->counts('expenses'),
                        
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}

