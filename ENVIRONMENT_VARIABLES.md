# Additional Environment Variables

Add these to your `.env` file for thermal printer support:

```env
# Thermal Printer Configuration
# Connection type: file (USB), network (IP), or windows (Windows printer)
PRINTER_TYPE=file

# Printer destination
# For file: /dev/usb/lp0, /dev/ttyUSB0
# For network: 192.168.1.100 (IP address)
# For windows: POS-80 (printer name)
PRINTER_DESTINATION=/dev/usb/lp0

# Network printer port (only for network type)
PRINTER_PORT=9100

# Auto-print receipts after sale completion (true/false)
PRINTER_AUTO_PRINT=false

# Number of receipt copies to print
PRINTER_COPIES=1
```

## Example Configurations

### USB Printer (Linux/Mac)
```env
PRINTER_TYPE=file
PRINTER_DESTINATION=/dev/usb/lp0
```

### Network Printer
```env
PRINTER_TYPE=network
PRINTER_DESTINATION=192.168.1.100
PRINTER_PORT=9100
```

### Windows Printer
```env
PRINTER_TYPE=windows
PRINTER_DESTINATION=POS-80
```

