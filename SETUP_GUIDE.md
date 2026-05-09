# Symphony Pharmacy Management System - Setup Guide

## First-Time Installation Steps

### 1. Install Dependencies
```bash
composer install
npm install && npm run build
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and configure your database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=symphony
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Seed Initial Data (Required for First-Time Setup)
```bash
# Seed pharmacies (creates default "Main Pharmacy")
php artisan db:seed --class=PharmacySeeder

# Seed other initial data
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=SettingSeeder
```

### 5. Create Admin User
```bash
php artisan make:filament-user
```
Follow the prompts to create your admin account.

**IMPORTANT:** After creating the user, assign them to a pharmacy:

#### Option A: Via Database
```bash
php artisan tinker
```
Then run:
```php
$user = App\Models\User::where('email', 'your-email@example.com')->first();
$pharmacy = App\Models\Pharmacy::first();
$user->pharmacy_id = $pharmacy->id;
$user->save();
```

#### Option B: Via Admin Panel
1. Login to the admin panel
2. Go to **Pharmacies** (System menu)
3. Create a new pharmacy or use the existing "Main Pharmacy"
4. Go to **Users**
5. Edit your user account
6. Select a pharmacy from the dropdown
7. Save

### 6. Configure Pharmacy Information
1. Go to **Settings** → **Pharmacy Information** tab
2. Fill in your business details:
   - Pharmacy Name
   - Phone Number
   - Email Address
   - Physical Address
   - Tax ID / Registration Number
   - Website (optional)
3. Click **Save Settings**

This information will appear on all receipts and invoices.

### 7. Configure System Settings
1. **General Settings Tab:**
   - Set currency and tax rate
   - Configure low stock threshold
   - Set receipt footer message

2. **Payment Settings Tab:**
   - Configure M-Pesa or Bank Paybill (if needed)
   - Add your payment gateway credentials

3. **SMS Settings Tab:**
   - Enable SMS notifications (if needed)
   - Configure Blessed Text API credentials

### 8. Start Using the System

#### Managing Pharmacies
- Navigate to **Pharmacies** in the System menu
- Create, edit, or view pharmacy branches
- Each pharmacy can have multiple users assigned

#### Point of Sale
- Access POS at `/pos` route
- Add items to cart
- Process cash, M-Pesa, or bank paybill payments
- Print receipts

## Common Issues

### Issue: "Pharmacy property ID is null"
**Solution:** Assign users to a pharmacy using one of the methods in Step 5 above.

### Issue: Receipts showing "N/A" for pharmacy info
**Solution:** 
1. Go to Settings → Pharmacy Information
2. Fill in all required fields
3. Click Save Settings

### Issue: Cannot create transactions
**Solution:** Ensure:
1. At least one pharmacy exists
2. Your user is assigned to a pharmacy
3. You have medicines with stock

## Additional Configuration

### Thermal Printer Setup
See `THERMAL_PRINTER_SETUP.md` for detailed thermal printer configuration.

### Environment Variables
See `ENVIRONMENT_VARIABLES.md` for all available environment variables.

## Support

For issues or questions, check the documentation files:
- `THERMAL_PRINTER_SETUP.md` - Thermal printer configuration
- `PRINTER_FIX.md` - Printer troubleshooting
- `ENVIRONMENT_VARIABLES.md` - Environment configuration

---

**Important Notes:**
- Always run migrations before seeding
- Back up your database before major updates
- Keep your `.env` file secure and never commit it
- Default pharmacies are created by the seeder for convenience


