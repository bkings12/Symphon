<?php

namespace App\Filament\Resources\Pharmacies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PharmacyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Pharmacy Name')
                            ->icon('heroicon-o-building-storefront')
                            ->size('lg')
                            ->weight('bold'),
                        
                        TextEntry::make('code')
                            ->label('Pharmacy Code')
                            ->badge()
                            ->color('info'),
                        
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(3),
                
                Section::make('Contact Information')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextEntry::make('phone')
                            ->label('Phone Number')
                            ->icon('heroicon-o-phone')
                            ->placeholder('Not provided')
                            ->copyable(),
                        
                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('Not provided')
                            ->copyable(),
                        
                        TextEntry::make('address')
                            ->label('Physical Address')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('No address provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Licensing Information')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('license_number')
                            ->label('License Number')
                            ->placeholder('Not provided')
                            ->badge()
                            ->color('warning'),
                        
                        TextEntry::make('tax_id')
                            ->label('Tax ID')
                            ->placeholder('Not provided')
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2),
                
                Section::make('Statistics')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        TextEntry::make('users_count')
                            ->label('Total Staff')
                            ->counts('users')
                            ->badge()
                            ->color('success'),
                        
                        TextEntry::make('sales_count')
                            ->label('Total Sales')
                            ->counts('sales')
                            ->badge()
                            ->color('info'),
                        
                        TextEntry::make('purchases_count')
                            ->label('Total Purchases')
                            ->counts('purchases')
                            ->badge()
                            ->color('warning'),
                    ])
                    ->columns(3),
                
                Section::make('System Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created Date')
                            ->dateTime()
                            ->placeholder('-'),
                        
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->since()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
