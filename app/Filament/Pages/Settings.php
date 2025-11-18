<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $cluster = \App\Filament\Clusters\Settings::class;

    protected static ?int $navigationSort = 3;

    // Form fields
    public $currency;
    public $currency_symbol;
    public $tax_rate;
    public $receipt_footer;
    public $low_stock_threshold;
    public $mpesa_enabled;
    public $mpesa_paybill;
    public $mpesa_consumer_key;
    public $mpesa_consumer_secret;
    public $mpesa_passkey;
    public $mpesa_shortcode;
    public $mpesa_environment;
    
    // Bank Paybill Settings
    public $payment_type; // 'mpesa' or 'bank_stk'
    public $bank_code;
    public $bank_account_number;
    public $account_reference_type; // 'bank_account' or 'phone_number'

    public function mount(): void
    {
        // Load settings from database
        $this->currency = Setting::get('currency', 'USD');
        $this->currency_symbol = Setting::get('currency_symbol', '$');
        $this->tax_rate = Setting::get('tax_rate', '10');
        $this->receipt_footer = Setting::get('receipt_footer', 'Thank you for your business!');
        $this->low_stock_threshold = Setting::get('low_stock_threshold', '10');
        
        // Load M-Pesa enabled setting and ensure it's a boolean
        $mpesaEnabledValue = Setting::get('mpesa_enabled', false);
        $this->mpesa_enabled = filter_var($mpesaEnabledValue, FILTER_VALIDATE_BOOLEAN);
        $this->mpesa_paybill = Setting::get('mpesa_paybill', '');
        $this->mpesa_consumer_key = Setting::get('mpesa_consumer_key', '');
        $this->mpesa_consumer_secret = Setting::get('mpesa_consumer_secret', '');
        $this->mpesa_passkey = Setting::get('mpesa_passkey', '');
        $this->mpesa_shortcode = Setting::get('mpesa_shortcode', '');
        $this->mpesa_environment = Setting::get('mpesa_environment', 'sandbox');
        
        // Load Bank Paybill Settings
        $this->payment_type = Setting::get('payment_type', 'mpesa');
        $this->bank_code = Setting::get('bank_code', 'kcb');
        $this->bank_account_number = Setting::get('bank_account_number', '');
        $this->account_reference_type = Setting::get('account_reference_type', 'phone_number');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('General Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Currency & Pricing')
                                    ->description('Configure currency and pricing settings for your pharmacy')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->schema([
                                        Select::make('currency')
                                            ->label('Currency')
                                            ->options([
                                                'USD' => 'US Dollar (USD)',
                                                'KES' => 'Kenyan Shilling (KES)',
                                                'GBP' => 'British Pound (GBP)',
                                                'EUR' => 'Euro (EUR)',
                                                'TZS' => 'Tanzanian Shilling (TZS)',
                                                'UGX' => 'Ugandan Shilling (UGX)',
                                            ])
                                            ->default('USD')
                                            ->required()
                                            ->searchable()
                                            ->columnSpanFull(),
                                        
                                        TextInput::make('currency_symbol')
                                            ->label('Currency Symbol')
                                            ->default('$')
                                            ->required()
                                            ->maxLength(5)
                                            ->placeholder('e.g., $, KSh, £')
                                            ->columnSpan(1),
                                        
                                        TextInput::make('tax_rate')
                                            ->label('Tax Rate (%)')
                                            ->numeric()
                                            ->default(10)
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2),

                                Section::make('Inventory Settings')
                                    ->description('Configure inventory and stock management')
                                    ->icon('heroicon-o-cube')
                                    ->schema([
                                        TextInput::make('low_stock_threshold')
                                            ->label('Low Stock Alert Threshold')
                                            ->numeric()
                                            ->default(10)
                                            ->minValue(0)
                                            ->required()
                                            ->helperText('Get alerts when stock falls below this quantity')
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2),

                                Section::make('Receipt Settings')
                                    ->description('Customize receipt and invoice details')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        TextInput::make('receipt_footer')
                                            ->label('Receipt Footer Message')
                                            ->default('Thank you for your business!')
                                            ->maxLength(500)
                                            ->columnSpanFull()
                                            ->helperText('This message will appear at the bottom of receipts'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Payment Settings')
                            ->icon('heroicon-o-device-phone-mobile')
                            ->schema([
                                Section::make('Payment Type Configuration')
                                    ->description('Select your payment gateway type')
                                    ->icon('heroicon-o-credit-card')
                                    ->schema([
                                        Select::make('payment_type')
                                            ->label('Payment Gateway Type')
                                            ->options([
                                                'mpesa' => 'M-Pesa STK Push',
                                                'bank_stk' => 'Bank Paybill STK Push',
                                            ])
                                            ->default('mpesa')
                                            ->required()
                                            ->live()
                                            ->helperText('Choose whether to use M-Pesa or Bank Paybill for STK Push payments')
                                            ->columnSpanFull(),
                                    ]),
                                
                                Section::make('M-Pesa Configuration')
                                    ->description('Configure M-Pesa payment gateway integration')
                                    ->icon('heroicon-o-credit-card')
                                    ->visible(fn ($get) => $get('payment_type') === 'mpesa')
                                    ->schema([
                                        Toggle::make('mpesa_enabled')
                                            ->label('Enable M-Pesa Payments')
                                            ->default(false)
                                            ->helperText('Turn on to accept M-Pesa payments')
                                            ->columnSpanFull(),
                                        
                                        Select::make('mpesa_environment')
                                            ->label('Environment')
                                            ->options([
                                                'sandbox' => 'Sandbox (Testing)',
                                                'production' => 'Production (Live)',
                                            ])
                                            ->default('sandbox')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                
                                Section::make('Bank Paybill Configuration')
                                    ->description('Configure Bank Paybill STK Push settings')
                                    ->icon('heroicon-o-building-library')
                                    ->visible(fn ($get) => $get('payment_type') === 'bank_stk')
                                    ->schema([
                                        Select::make('bank_code')
                                            ->label('Bank')
                                            ->options([
                                                'kcb' => 'KCB Bank',
                                                'equity' => 'Equity Bank',
                                                'coop' => 'Co-operative Bank',
                                                'absa' => 'Absa Bank',
                                                'ncba' => 'NCBA Bank',
                                                'diamond' => 'Diamond Trust Bank',
                                            ])
                                            ->default('kcb')
                                            ->required()
                                            ->helperText('Select the bank for paybill payments')
                                            ->columnSpanFull(),
                                        
                                        TextInput::make('bank_account_number')
                                            ->label('Bank Account Number / Phone Number')
                                            ->placeholder('Enter account number or phone number')
                                            ->maxLength(255)
                                            ->required()
                                            ->helperText('This will be used as the account reference for payments')
                                            ->columnSpanFull(),
                                        
                                        Select::make('account_reference_type')
                                            ->label('Account Reference Type')
                                            ->options([
                                                'phone_number' => 'Phone Number',
                                                'bank_account' => 'Bank Account Number',
                                            ])
                                            ->default('phone_number')
                                            ->required()
                                            ->helperText('Choose whether to use phone number or bank account as reference')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Paybill Credentials')
                                    ->description('Enter your M-Pesa Paybill credentials from Safaricom')
                                    ->icon('heroicon-o-key')
                                    ->schema([
                                        TextInput::make('mpesa_paybill')
                                            ->label('Paybill Number')
                                            ->placeholder('e.g., 123456')
                                            ->maxLength(20)
                                            ->columnSpan(1),
                                        
                                        TextInput::make('mpesa_shortcode')
                                            ->label('Shortcode')
                                            ->placeholder('e.g., 174379')
                                            ->maxLength(20)
                                            ->helperText('Your M-Pesa business shortcode')
                                            ->columnSpan(1),
                                        
                                        TextInput::make('mpesa_consumer_key')
                                            ->label('Consumer Key')
                                            ->placeholder('Enter your consumer key')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        TextInput::make('mpesa_consumer_secret')
                                            ->label('Consumer Secret')
                                            ->placeholder('Enter your consumer secret')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        TextInput::make('mpesa_passkey')
                                            ->label('Passkey')
                                            ->placeholder('Enter your passkey')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255)
                                            ->helperText('Your M-Pesa online passkey')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Section::make('Important Information')
                                    ->schema([
                                        Placeholder::make('info')
                                            ->content('To obtain M-Pesa credentials:
                                            
1. Visit the Safaricom Daraja portal (https://developer.safaricom.co.ke)
2. Create an account or log in
3. Create a new app to get your Consumer Key and Secret
4. Get your Paybill number from your M-Pesa business account
5. Contact Safaricom to get your online passkey

**Security Note:** These credentials are sensitive. Keep them secure and never share them.')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsed()
                                    ->icon('heroicon-o-information-circle'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->activeTab(1),
            ]);
    }
    
    public function save(): void
    {
        try {
            // Save General Settings
            Setting::set('currency', $this->currency, 'general');
            Setting::set('currency_symbol', $this->currency_symbol, 'general');
            Setting::set('tax_rate', $this->tax_rate, 'general');
            Setting::set('receipt_footer', $this->receipt_footer, 'general');
            Setting::set('low_stock_threshold', $this->low_stock_threshold, 'general');

            // Save Payment Type
            Setting::set('payment_type', $this->payment_type ?? 'mpesa', 'payment');
            
            // Save M-Pesa Settings
            // Ensure boolean value is properly saved (cast to int then string for database storage)
            $mpesaEnabled = filter_var($this->mpesa_enabled ?? false, FILTER_VALIDATE_BOOLEAN);
            Setting::set('mpesa_enabled', $mpesaEnabled ? '1' : '0', 'mpesa');
            Setting::set('mpesa_paybill', $this->mpesa_paybill ?? '', 'mpesa');
            Setting::set('mpesa_consumer_key', $this->mpesa_consumer_key ?? '', 'mpesa');
            Setting::set('mpesa_consumer_secret', $this->mpesa_consumer_secret ?? '', 'mpesa');
            Setting::set('mpesa_passkey', $this->mpesa_passkey ?? '', 'mpesa');
            Setting::set('mpesa_shortcode', $this->mpesa_shortcode ?? '', 'mpesa');
            Setting::set('mpesa_environment', $this->mpesa_environment ?? 'sandbox', 'mpesa');
            
            // Save Bank Paybill Settings
            Setting::set('bank_code', $this->bank_code ?? 'kcb', 'bank_paybill');
            Setting::set('bank_account_number', $this->bank_account_number ?? '', 'bank_paybill');
            Setting::set('account_reference_type', $this->account_reference_type ?? 'phone_number', 'bank_paybill');

            // Clear cache
            Setting::clearCache();

            Notification::make()
                ->title('Settings Saved Successfully')
                ->success()
                ->body('Your settings have been saved and applied.')
                ->send();
        } catch (\Filament\Forms\ValidationException $e) {
            Notification::make()
                ->title('Validation Error')
                ->danger()
                ->body('Please check all required fields.')
                ->send();
            
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Saving Settings')
                ->danger()
                ->body('An error occurred while saving settings: ' . $e->getMessage())
                ->send();
        }
    }
}

