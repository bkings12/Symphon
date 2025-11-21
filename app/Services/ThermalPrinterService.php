<?php

namespace App\Services;

use App\Models\Sale;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\EscposImage;
use Exception;

class ThermalPrinterService
{
    protected $printer;
    protected $connector;
    protected $closed = false;

    /**
     * Initialize printer connection
     * 
     * @param string $type Type of connection: 'network', 'file', 'windows', 'cups'
     * @param string $destination IP address, file path, printer name, or CUPS printer name
     * @param int $port Port number for network printers (default 9100)
     */
    public function __construct($type = 'cups', $destination = 'POS-80', $port = 9100)
    {
        try {
            switch ($type) {
                case 'network':
                    $this->connector = new NetworkPrintConnector($destination, $port);
                    break;
                case 'windows':
                    $this->connector = new WindowsPrintConnector($destination);
                    break;
                case 'cups':
                    $this->connector = new CupsPrintConnector($destination);
                    break;
                case 'file':
                default:
                    // Try common device paths if destination doesn't exist
                    if (!file_exists($destination)) {
                        $commonPaths = [
                            '/dev/usb/lp0',
                            '/dev/usb/lp1',
                            '/dev/lp0',
                            '/dev/lp1',
                        ];
                        
                        $found = false;
                        foreach ($commonPaths as $path) {
                            if (file_exists($path)) {
                                $destination = $path;
                                $found = true;
                                break;
                            }
                        }
                        
                        if (!$found) {
                            throw new Exception("Printer device not found at: $destination. Tried: " . implode(', ', $commonPaths));
                        }
                    }
                    $this->connector = new FilePrintConnector($destination);
                    break;
            }
            
            $this->printer = new Printer($this->connector);
        } catch (Exception $e) {
            throw new Exception("Failed to connect to printer ($type/$destination): " . $e->getMessage());
        }
    }

    /**
     * Print a sale receipt
     */
    public function printReceipt(Sale $sale)
    {
        try {
            $sale->load(['items.medicine', 'user', 'customer', 'payments', 'pharmacy']);
            
            // Get pharmacy from sale first, then from user
            $pharmacy = $sale->pharmacy ?? auth()->user()?->pharmacy;
            
            // Header - prioritize pharmacy model data over settings
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $pharmacyName = $pharmacy?->name ?? setting('pharmacy_name', "Symphony Pharmacy");
            $this->printer->text($pharmacyName . "\n");
            $this->printer->setTextSize(1, 1);
            
            $address = $pharmacy?->address ?? setting('pharmacy_address', '');
            if ($address) {
                $this->printer->text($address . "\n");
            }
            
            $phone = $pharmacy?->phone ?? setting('pharmacy_phone', '');
            if ($phone) {
                $this->printer->text("Tel: " . $phone . "\n");
            }
            
            $email = $pharmacy?->email ?? setting('pharmacy_email', '');
            if ($email) {
                $this->printer->text("Email: " . $email . "\n");
            }
            
            $taxId = $pharmacy?->tax_id ?? setting('pharmacy_tax_id', 'N/A');
            $this->printer->text("TAX ID: " . $taxId . "\n");
            $this->printer->feed();
            
            // Divider
            $this->printer->text(str_repeat("-", 48) . "\n");
            
            // Transaction Details
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Invoice #: " . $sale->invoice_number . "\n");
            $this->printer->text("Date: " . $sale->sale_date->format('d/m/Y H:i') . "\n");
            $this->printer->text("Cashier: " . $sale->user->name . "\n");
            
            if ($sale->customer) {
                $this->printer->text("Customer: " . $sale->customer->name . "\n");
                if ($sale->customer->phone) {
                    $this->printer->text("Phone: " . $sale->customer->phone . "\n");
                }
            }
            
            $this->printer->feed();
            $this->printer->text(str_repeat("-", 48) . "\n");
            
            // Items Header
            $this->printer->text(sprintf("%-20s %4s %8s %10s\n", "Item", "Qty", "Price", "Total"));
            $this->printer->text(str_repeat("-", 48) . "\n");
            
            // Items
            foreach ($sale->items as $item) {
                $name = substr($item->medicine->name, 0, 20);
                $qty = $item->quantity;
                $price = number_format($item->unit_price, 2);
                $total = number_format($item->total_price, 2);
                
                $this->printer->text(sprintf("%-20s %4d %8s %10s\n", $name, $qty, $price, $total));
                
                if ($item->medicine->generic_name) {
                    $generic = substr($item->medicine->generic_name, 0, 30);
                    $this->printer->text("  " . $generic . "\n");
                }
            }
            
            $this->printer->text(str_repeat("-", 48) . "\n");
            
            // Totals
            $this->printer->setEmphasis(false);
            $this->printer->text(sprintf("%-32s %12s\n", "Subtotal:", format_currency($sale->subtotal)));
            
            if ($sale->discount_amount > 0) {
                $this->printer->text(sprintf("%-32s %12s\n", "Discount:", "-" . format_currency($sale->discount_amount)));
            }
            
            $this->printer->text(sprintf("%-32s %12s\n", "Tax (" . tax_rate() . "%):", format_currency($sale->tax_amount)));
            
            $this->printer->feed();
            $this->printer->setEmphasis(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text(sprintf("%-20s %12s\n", "TOTAL:", format_currency($sale->total_amount)));
            $this->printer->setTextSize(1, 1);
            $this->printer->setEmphasis(false);
            
            $this->printer->feed();
            $this->printer->text(str_repeat("-", 48) . "\n");
            
            // Payment Info
            $payment = $sale->payments->first();
            if ($payment) {
                $this->printer->text("Payment Method: " . strtoupper($payment->payment_method) . "\n");
                $this->printer->text(sprintf("%-32s %12s\n", "Amount Paid:", format_currency($payment->amount)));
                
                // Always show change for cash payments, show for others only if amount > total
                $showChange = false;
                $change = 0;
                if (strtolower($payment->payment_method) === 'cash') {
                    $change = max(0, $payment->amount - $sale->total_amount);
                    $showChange = true;
                } elseif ($payment->amount > $sale->total_amount) {
                    $change = $payment->amount - $sale->total_amount;
                    $showChange = true;
                }
                
                if ($showChange) {
                    $this->printer->setEmphasis(true);
                    $this->printer->text(sprintf("%-32s %12s\n", "Change:", format_currency($change)));
                    $this->printer->setEmphasis(false);
                }
                
                $this->printer->feed();
            }
            
            // Footer
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text(str_repeat("-", 48) . "\n");
            $this->printer->setEmphasis(true);
            $this->printer->text(setting('receipt_footer', 'Thank you for your business!') . "\n");
            $this->printer->setEmphasis(false);
            $this->printer->text("This is a valid receipt\n");
            $this->printer->text("Powered by Symphony POS\n");
            
            // Barcode (if supported)
            try {
                $this->printer->feed();
                $this->printer->setJustification(Printer::JUSTIFY_CENTER);
                $this->printer->setBarcodeHeight(50);
                $this->printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
                $this->printer->barcode($sale->invoice_number, Printer::BARCODE_CODE39);
            } catch (Exception $e) {
                // Barcode not supported, skip
            }
            
            // Cut paper - this finalizes the print job
            $this->printer->feed(3);
            $this->printer->cut();
            
            // Close connection - this sends the job to CUPS
            $this->close();
            
        } catch (Exception $e) {
            // Try to close even on error
            try {
                if ($this->printer) {
                    $this->printer->close();
                }
            } catch (Exception $closeException) {
                // Ignore close errors during error handling
            }
            throw new Exception("Failed to print receipt: " . $e->getMessage());
        }
    }

    /**
     * Close printer connection
     */
    public function close()
    {
        if ($this->closed) {
            return; // Already closed
        }
        
        if ($this->printer && $this->connector) {
            try {
                $this->printer->close();
                $this->closed = true;
            } catch (\TypeError $e) {
                // Handle null buffer error in CUPS connector
                // This can happen if close() is called when buffer is already null
                $this->closed = true; // Mark as closed even on error
                // Silently ignore - this is expected in some cases
            } catch (Exception $e) {
                // Ignore other close errors - printer might already be closed
                $this->closed = true; // Mark as closed even on error
                // Silently ignore - printer may already be closed
            }
        }
    }

    /**
     * Destructor to ensure printer is closed
     */
    public function __destruct()
    {
        if (!$this->closed) {
            $this->close();
        }
    }
}

