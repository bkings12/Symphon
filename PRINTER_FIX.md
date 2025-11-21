# Printer Connection Fix

## Problem
You were getting "failed to connect" errors when clicking "Print (Thermal)".

## Solution
Your printer is registered in CUPS as "POS-80". The system now uses CUPS by default.

## Quick Setup

### Option 1: Use Default (Recommended)
The system is now configured to use CUPS with printer name "POS-80" by default. **No .env changes needed!**

Just try printing again - it should work now.

### Option 2: Configure in .env (If needed)
If you want to explicitly set it, add to your `.env`:

```env
PRINTER_TYPE=cups
PRINTER_DESTINATION=POS-80
```

## Verify Your Printer

Check your printer is available:
```bash
lpstat -p
```

You should see:
```
printer POS-80 now printing...
```

## Test the Connection

1. Complete a sale in POS
2. Click **"Print (Thermal)"** button
3. Receipt should print!

## If Still Not Working

### Check Diagnostics
Visit: `http://your-domain/thermal-print/diagnostics` (while logged in)

This will show:
- Current configuration
- Available CUPS printers
- Available USB devices

### Common Issues

1. **Printer not in CUPS**
   ```bash
   # Add printer to CUPS
   lpadmin -p POS-80 -E -v usb://Your/Printer/Path -m raw
   ```

2. **Permission issues**
   ```bash
   # Add user to lp group
   sudo usermod -a -G lp $USER
   # Log out and back in
   ```

3. **Printer offline**
   ```bash
   # Enable printer
   cupsenable POS-80
   # Accept jobs
   cupsaccept POS-80
   ```

## What Changed

- ✅ Added CUPS support (Linux standard)
- ✅ Default to CUPS instead of direct file access
- ✅ Better error messages
- ✅ Automatic device path detection
- ✅ Diagnostics endpoint

Your printer "POS-80" is already set up in CUPS, so it should work immediately!

