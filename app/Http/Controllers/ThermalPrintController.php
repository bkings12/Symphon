<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ThermalPrinterService;
use Illuminate\Http\Request;
use Exception;

class ThermalPrintController extends Controller
{
    /**
     * Print receipt to thermal printer
     */
    public function printReceipt(Request $request, Sale $sale)
    {
        // Disable all error reporting temporarily to force JSON response
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // Convert errors to exceptions
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            // Force CUPS configuration (working setup)
            $printerType = 'cups';
            $printerDestination = 'POS-80';
            $printerPort = 9100;

            // Initialize printer service
            $printer = new ThermalPrinterService($printerType, $printerDestination, $printerPort);
            
            // Print receipt (this handles closing internally)
            $printer->printReceipt($sale);

            restore_error_handler();
            
            return response()->json([
                'success' => true,
                'message' => 'Receipt printed successfully'
            ]);

        } catch (\Throwable $e) {
            restore_error_handler();
            
            // Return error without logging to avoid permission issues
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'config' => [
                    'type' => $printerType ?? 'unknown',
                    'destination' => $printerDestination ?? 'unknown'
                ]
            ], 200); // Return 200 instead of 500 to ensure we get the message
        }
    }

    /**
     * Test printer connection
     */
    public function testPrinter(Request $request)
    {
        try {
            $printerType = config('printing.type', 'cups');
            $printerDestination = config('printing.destination', 'POS-80');
            $printerPort = config('printing.port', 9100);

            $printer = new ThermalPrinterService($printerType, $printerDestination, $printerPort);
            
            // Print test page
            $printer->printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->printer->setTextSize(2, 2);
            $printer->printer->text("TEST PRINT\n");
            $printer->printer->setTextSize(1, 1);
            $printer->printer->feed();
            $printer->printer->text("Printer is working correctly!\n");
            $printer->printer->text(date('Y-m-d H:i:s') . "\n");
            $printer->printer->feed(3);
            $printer->printer->cut();
            
            $printer->close();

            return response()->json([
                'success' => true,
                'message' => 'Test print completed successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Printer test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get printer diagnostics
     */
    public function diagnostics(Request $request)
    {
        $diagnostics = [
            'config' => [
                'type' => config('printing.type'),
                'destination' => config('printing.destination'),
                'port' => config('printing.port'),
            ],
            'cups_printers' => [],
            'usb_devices' => [],
        ];

        // Check CUPS printers
        if (function_exists('exec')) {
            exec('lpstat -p 2>&1', $cupsOutput, $cupsReturn);
            if ($cupsReturn === 0) {
                $diagnostics['cups_printers'] = $cupsOutput;
            }
        }

        // Check USB devices
        $usbPaths = ['/dev/usb/lp0', '/dev/usb/lp1', '/dev/lp0', '/dev/lp1'];
        foreach ($usbPaths as $path) {
            if (file_exists($path)) {
                $diagnostics['usb_devices'][] = [
                    'path' => $path,
                    'readable' => is_readable($path),
                    'writable' => is_writable($path),
                ];
            }
        }

        return response()->json($diagnostics);
    }
}

