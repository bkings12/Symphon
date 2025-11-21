# Thermal Printer Setup Guide

This guide will help you set up your POS thermal printer (80mm) with Symphony POS.

## Requirements

- ESC/POS compatible thermal printer (80mm width)
- `mike42/escpos-php` package (already installed)

## Printer Connection Types

### 1. USB Connection (Linux/Mac) - Recommended for USB printers

```bash
# Find your printer device
ls -la /dev/usb/lp*
# or
ls -la /dev/ttyUSB*
```

Add to `.env`:
```env
PRINTER_TYPE=file
PRINTER_DESTINATION=/dev/usb/lp0
```

**Note:** You may need to grant permissions:
```bash
sudo chmod 666 /dev/usb/lp0
# or add your user to the lp group
sudo usermod -a -G lp $USER
```

### 2. Network Printer (IP Address)

If your printer is connected via network/WiFi:

Add to `.env`:
```env
PRINTER_TYPE=network
PRINTER_DESTINATION=192.168.1.100
PRINTER_PORT=9100
```

Replace `192.168.1.100` with your printer's IP address.

### 3. Windows Printer

If running on Windows:

Add to `.env`:
```env
PRINTER_TYPE=windows
PRINTER_DESTINATION=POS-80
```

Replace `POS-80` with your printer's name as shown in Windows Devices and Printers.

## Configuration

### 1. Add to your `.env` file:

```env
# Thermal Printer Configuration
PRINTER_TYPE=file
PRINTER_DESTINATION=/dev/usb/lp0
PRINTER_PORT=9100
PRINTER_AUTO_PRINT=false
PRINTER_COPIES=1
```

### 2. Test Your Printer

Access the test endpoint:
```bash
curl -X POST http://your-domain/thermal-print/test \
  -H "Authorization: Bearer your-token"
```

Or use the test button in the POS interface (coming soon).

## Usage

### In POS System

1. Complete a sale as normal
2. In the receipt modal, click **"Print (Thermal)"** button
3. Receipt will print directly to your thermal printer

### Programmatically

```php
use App\Services\ThermalPrinterService;
use App\Models\Sale;

$sale = Sale::find(1);
$printer = new ThermalPrinterService('file', '/dev/usb/lp0');
$printer->printReceipt($sale);
$printer->close();
```

## Troubleshooting

### Issue: Permission denied

**Solution:**
```bash
sudo chmod 666 /dev/usb/lp0
# Or permanently:
sudo usermod -a -G lp $USER
```

### Issue: Device not found

**Solution:** Check your printer connection:
```bash
lsusb  # List USB devices
dmesg | grep -i printer  # Check kernel messages
```

### Issue: Prints garbage/unknown characters

This means you're still using HTML printing instead of ESC/POS.

**Solution:**
- Make sure you're clicking the **"Print (Thermal)"** button, not "Print (Browser)"
- Verify printer type is set correctly in `.env`
- Test with the test print endpoint

### Issue: Nothing prints

**Solutions:**
1. Check printer is powered on and has paper
2. Verify the device path is correct
3. Test with: `echo "Test" > /dev/usb/lp0`
4. Check printer cable/network connection
5. Verify printer driver is ESC/POS compatible

### Issue: Barcode doesn't print

Some thermal printers don't support barcodes. The code gracefully handles this by skipping the barcode if unsupported.

## Supported Printers

This setup works with most ESC/POS compatible thermal printers including:

- Epson TM-T20
- Epson TM-T82
- Epson TM-T88
- Star TSP100/TSP143
- Citizen CT-S310
- Xprinter XP-80
- Any ESC/POS compatible 80mm thermal printer

## Features

- ✅ 80mm thermal receipt format
- ✅ Store info header
- ✅ Item details with quantities and prices
- ✅ Tax and discount calculations
- ✅ Payment information
- ✅ Barcode support (if printer supports it)
- ✅ Auto-cut paper (if printer supports it)
- ✅ Clean, professional receipt layout

## Advanced Configuration

### Multiple Printer Support

Edit `config/printing.php` to add support for different printers for different stores or purposes.

### Custom Receipt Format

Edit `app/Services/ThermalPrinterService.php` to customize the receipt layout, fonts, sizes, and content.

### Auto-Print on Sale Completion

Set in `.env`:
```env
PRINTER_AUTO_PRINT=true
```

This will automatically send receipts to the thermal printer when a sale is completed.

## Need Help?

- Check ESC/POS documentation: https://github.com/mike42/escpos-php
- Verify your printer is ESC/POS compatible
- Test with a simple echo command first
- Check Laravel logs: `storage/logs/laravel.log`

