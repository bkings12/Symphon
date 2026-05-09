<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CUPS / raw ESC/POS
    |--------------------------------------------------------------------------
    |
    | If the physical slip is blank but the job "succeeds", the CUPS queue is
    | often not set to raw / ESC-POS passthrough. Use a driver or queue that
    | sends bytes unchanged to the thermal printer (see THERMAL_PRINTER_SETUP.md).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Printer Connection Type
    |--------------------------------------------------------------------------
    |
    | The type of connection to use for the thermal printer.
    | Options: 'cups', 'file', 'network', 'windows'
    |
    | - 'cups': CUPS printer (Linux - recommended)
    | - 'file': Direct connection via device file (Linux/Mac)
    | - 'network': Network printer via IP address
    | - 'windows': Windows printer name
    |
    */

    'type' => env('PRINTER_TYPE', 'cups'),

    /*
    |--------------------------------------------------------------------------
    | Printer Destination
    |--------------------------------------------------------------------------
    |
    | The destination for the printer connection.
    |
    | Examples:
    | - For 'cups': 'POS-80' (CUPS printer name - use 'lpstat -p' to list)
    | - For 'file': '/dev/usb/lp0', '/dev/ttyUSB0', or 'php://stdout'
    | - For 'network': '192.168.1.100' (IP address)
    | - For 'windows': 'POS-80' (printer name)
    |
    */

    'destination' => env('PRINTER_DESTINATION', 'POS-80'),

    /*
    |--------------------------------------------------------------------------
    | Network Printer Port
    |--------------------------------------------------------------------------
    |
    | The port number for network printers (usually 9100 for ESC/POS)
    |
    */

    'port' => env('PRINTER_PORT', 9100),

    /*
    |--------------------------------------------------------------------------
    | Auto Print
    |--------------------------------------------------------------------------
    |
    | Automatically print receipt after sale completion
    |
    */

    'auto_print' => env('PRINTER_AUTO_PRINT', false),

    /*
    |--------------------------------------------------------------------------
    | Print Copies
    |--------------------------------------------------------------------------
    |
    | Number of receipt copies to print
    |
    */

    'copies' => env('PRINTER_COPIES', 1),

    /*
    |--------------------------------------------------------------------------
    | Filament reprint uses HTTP route (optional)
    |--------------------------------------------------------------------------
    |
    | When true, Filament "Reprint receipt" POSTs to the same /thermal-print/{sale}
    | route as the POS (same middleware, CSRF, and JSON handling). Requires a web
    | session. Default false: in-process ThermalPrinterService::printSaleReceipt
    | is used (identical bytes; easier in tests and queues).
    |
    */

    'filament_reprint_via_http' => env('FILAMENT_THERMAL_REPRINT_VIA_HTTP', false),

];
