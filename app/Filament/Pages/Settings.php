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

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $cluster = \App\Filament\Clusters\Settings::class;

    protected static ?int $navigationSort = 3;

    // Form fields
    public $currency;

    public $currency_symbol;

    public $tax_rate;

    public $receipt_footer;

    public $receipt_paybill;

    public $receipt_paybill_account;

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
    // Removed: account_reference_type - now always uses bank_account_number from settings

    // SMS Settings
    public $sms_enabled;

    public $sms_provider;

    public $sms_api_key;

    public $sms_sender_id;

    public $sms_notification_phone;

    // Pharmacy Information
    public $pharmacy_name;

    public $pharmacy_phone;

    public $pharmacy_email;

    public $pharmacy_address;

    public $pharmacy_tax_id;

    public $pharmacy_website;

    public function mount(): void
    {
        // Load settings from database
        $this->currency = Setting::get('currency', 'USD');
        $this->currency_symbol = Setting::get('currency_symbol', '$');
        $this->tax_rate = Setting::get('tax_rate', '10');
        $this->receipt_footer = Setting::get('receipt_footer', 'Thank you for your business!');
        $this->receipt_paybill = Setting::get('receipt_paybill', '');
        $this->receipt_paybill_account = Setting::get('receipt_paybill_account', '');
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
        // Removed: account_reference_type - now always uses bank_account_number from settings

        // Load SMS Settings
        $smsEnabledValue = Setting::get('sms_enabled', false);
        $this->sms_enabled = filter_var($smsEnabledValue, FILTER_VALIDATE_BOOLEAN);
        $this->sms_provider = Setting::get('sms_provider', 'blessed_text');
        $this->sms_api_key = Setting::get('sms_api_key', '');
        $this->sms_sender_id = Setting::get('sms_sender_id', '');
        $this->sms_notification_phone = Setting::get('sms_notification_phone', '');

        // Load Pharmacy Information
        $this->pharmacy_name = Setting::get('pharmacy_name', 'Symphony Pharmacy');
        $this->pharmacy_phone = Setting::get('pharmacy_phone', '');
        $this->pharmacy_email = Setting::get('pharmacy_email', '');
        $this->pharmacy_address = Setting::get('pharmacy_address', '');
        $this->pharmacy_tax_id = Setting::get('pharmacy_tax_id', '');
        $this->pharmacy_website = Setting::get('pharmacy_website', '');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Pharmacy Information')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Section::make('Business Details')
                                    ->description('Enter your pharmacy/business information that will appear on receipts and documents')
                                    ->icon('heroicon-o-building-storefront')
                                    ->schema([
                                        TextInput::make('pharmacy_name')
                                            ->label('Pharmacy/Business Name')
                                            ->placeholder('e.g., Symphony Pharmacy')
                                            ->maxLength(255)
                                            ->required()
                                            ->helperText('This name will appear on receipts and invoices')
                                            ->columnSpanFull(),

                                        TextInput::make('pharmacy_phone')
                                            ->label('Phone Number')
                                            ->placeholder('e.g., +254 712 345 678')
                                            ->tel()
                                            ->maxLength(20)
                                            ->helperText('Business contact phone number')
                                            ->columnSpan(1),

                                        TextInput::make('pharmacy_email')
                                            ->label('Email Address')
                                            ->placeholder('e.g., info@symphonypharmacy.com')
                                            ->email()
                                            ->maxLength(255)
                                            ->helperText('Business contact email')
                                            ->columnSpan(1),

                                        TextInput::make('pharmacy_address')
                                            ->label('Physical Address')
                                            ->placeholder('e.g., 123 Main Street, Nairobi, Kenya')
                                            ->maxLength(500)
                                            ->helperText('Full business address')
                                            ->columnSpanFull(),

                                        TextInput::make('pharmacy_tax_id')
                                            ->label('Tax ID / Registration Number')
                                            ->placeholder('e.g., P051234567A')
                                            ->maxLength(100)
                                            ->helperText('Business registration or tax identification number')
                                            ->columnSpan(1),

                                        TextInput::make('pharmacy_website')
                                            ->label('Website (Optional)')
                                            ->placeholder('e.g., https://www.symphonypharmacy.com')
                                            ->url()
                                            ->maxLength(255)
                                            ->helperText('Your business website URL')
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2),

                                Section::make('Usage Information')
                                    ->schema([
                                        Placeholder::make('usage_info')
                                            ->content('**Where this information appears:**

• **Receipts & Invoices:** Your pharmacy name, address, phone, and tax ID appear on all printed and digital receipts
• **Thermal Printer Receipts:** Business details are printed at the top of every transaction receipt
• **Customer Communications:** Email and phone number used for customer correspondence
• **Legal Documents:** Tax ID is included for compliance and record-keeping

**Important:** Keep this information up-to-date to ensure professional documentation and compliance with regulations.')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsed()
                                    ->icon('heroicon-o-information-circle'),
                            ]),

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

                                        TextInput::make('receipt_paybill')
                                            ->label('Paybill / Till number')
                                            ->placeholder('e.g., 123456')
                                            ->maxLength(30)
                                            ->columnSpan(1)
                                            ->helperText('Shown on thermal and POS receipts when filled.'),

                                        TextInput::make('receipt_paybill_account')
                                            ->label('Account (optional)')
                                            ->placeholder('e.g., business account for Lipa na M-Pesa')
                                            ->maxLength(50)
                                            ->columnSpan(1)
                                            ->helperText('Optional second line under paybill on receipts.'),
                                    ])
                                    ->columns(2),
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
                                            ->label('Business Account Number / Phone Number')
                                            ->placeholder('Enter your business account number or phone number')
                                            ->maxLength(255)
                                            ->required()
                                            ->helperText('⚠️ IMPORTANT: This is where customer payments will be credited. Enter your business account number or phone number here.')
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

                        Tabs\Tab::make('SMS Settings')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make('SMS Provider Configuration')
                                    ->description('Configure SMS provider for sending notifications')
                                    ->icon('heroicon-o-envelope')
                                    ->schema([
                                        Toggle::make('sms_enabled')
                                            ->label('Enable SMS Notifications')
                                            ->default(false)
                                            ->helperText('Turn on to enable SMS notifications for low stock alerts')
                                            ->columnSpanFull(),

                                        Select::make('sms_provider')
                                            ->label('SMS Provider')
                                            ->options([
                                                'blessed_text' => 'Blessed Text',
                                            ])
                                            ->default('blessed_text')
                                            ->required()
                                            ->disabled(fn ($get) => ! $get('sms_enabled'))
                                            ->helperText('Select your SMS service provider')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Blessed Text Configuration')
                                    ->description('Configure Blessed Text SMS API credentials')
                                    ->icon('heroicon-o-key')
                                    ->visible(fn ($get) => $get('sms_provider') === 'blessed_text')
                                    ->schema([
                                        TextInput::make('sms_api_key')
                                            ->label('API Key')
                                            ->placeholder('Enter your Blessed Text API key')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255)
                                            ->required(fn ($get) => $get('sms_enabled'))
                                            ->disabled(fn ($get) => ! $get('sms_enabled'))
                                            ->helperText('Get your API key from your Blessed Text profile')
                                            ->columnSpanFull(),

                                        TextInput::make('sms_sender_id')
                                            ->label('Sender ID')
                                            ->placeholder('e.g., 23107')
                                            ->maxLength(20)
                                            ->required(fn ($get) => $get('sms_enabled'))
                                            ->disabled(fn ($get) => ! $get('sms_enabled'))
                                            ->helperText('This must be a sender ID already assigned to you')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Notification Settings')
                                    ->description('Configure where to send low stock notifications')
                                    ->icon('heroicon-o-bell')
                                    ->schema([
                                        TextInput::make('sms_notification_phone')
                                            ->label('Notification Phone Number')
                                            ->placeholder('e.g., 254721XXXXXX or 0721XXXXXX')
                                            ->maxLength(20)
                                            ->required(fn ($get) => $get('sms_enabled'))
                                            ->disabled(fn ($get) => ! $get('sms_enabled'))
                                            ->helperText('Phone number to receive low stock alerts. Can be in format: 254721XXXXXX, 0721XXXXXX, or 721XXXXXX')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Important Information')
                                    ->schema([
                                        Placeholder::make('info')
                                            ->content('To obtain Blessed Text credentials:
                                            
1. Visit the Blessed Text website (https://blessedtexts.com)
2. Create an account or log in
3. Go to your Profile to get your API Key
4. Request a Sender ID from Blessed Text support
5. Enter your notification phone number to receive low stock alerts

**Low Stock Alerts:** When enabled, you will receive SMS notifications when any medicine stock falls below the threshold set in General Settings.

**Security Note:** Keep your API credentials secure and never share them.')
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
            Setting::set('receipt_paybill', $this->receipt_paybill ?? '', 'general');
            Setting::set('receipt_paybill_account', $this->receipt_paybill_account ?? '', 'general');
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
            // Removed: account_reference_type - now always uses bank_account_number from settings

            // Save SMS Settings
            $smsEnabled = filter_var($this->sms_enabled ?? false, FILTER_VALIDATE_BOOLEAN);
            Setting::set('sms_enabled', $smsEnabled ? '1' : '0', 'sms');
            Setting::set('sms_provider', $this->sms_provider ?? 'blessed_text', 'sms');
            Setting::set('sms_api_key', $this->sms_api_key ?? '', 'sms');
            Setting::set('sms_sender_id', $this->sms_sender_id ?? '', 'sms');
            Setting::set('sms_notification_phone', $this->sms_notification_phone ?? '', 'sms');

            // Save Pharmacy Information
            Setting::set('pharmacy_name', $this->pharmacy_name ?? 'Symphony Pharmacy', 'pharmacy');
            Setting::set('pharmacy_phone', $this->pharmacy_phone ?? '', 'pharmacy');
            Setting::set('pharmacy_email', $this->pharmacy_email ?? '', 'pharmacy');
            Setting::set('pharmacy_address', $this->pharmacy_address ?? '', 'pharmacy');
            Setting::set('pharmacy_tax_id', $this->pharmacy_tax_id ?? '', 'pharmacy');
            Setting::set('pharmacy_website', $this->pharmacy_website ?? '', 'pharmacy');

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
                ->body('An error occurred while saving settings: '.$e->getMessage())
                ->send();
        }
    }
}
