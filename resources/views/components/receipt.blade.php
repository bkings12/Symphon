@props(['sale', 'saleItems', 'customer', 'payment'])

<div class="receipt-container" style="max-width: 80mm; margin: 0 auto; font-family: 'Courier New', monospace; background: white; padding: 1rem; border-radius: 0.5rem; color: #000 !important;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 1.5rem; border-bottom: 2px dashed #333; padding-bottom: 1rem; color: #000 !important;">
        @php
            // Try to get pharmacy from sale first, then from user, then fallback to settings
            $pharmacy = $sale->pharmacy ?? auth()->user()->pharmacy;
            // Prioritize pharmacy model data over settings
            $pharmacyName = $pharmacy?->name ?? setting('pharmacy_name', 'Symphony Pharmacy');
            $pharmacyEmail = $pharmacy?->email ?? setting('pharmacy_email', '');
            $pharmacyPhone = $pharmacy?->phone ?? setting('pharmacy_phone', '');
            $pharmacyAddress = $pharmacy?->address ?? setting('pharmacy_address', '');
            $pharmacyTaxId = $pharmacy?->tax_id ?? setting('pharmacy_tax_id', 'N/A');
        @endphp
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: bold; color: #000 !important;">{{ $pharmacyName }}</h2>
        @if($pharmacyAddress)
            <p style="margin: 0.25rem 0; font-size: 0.875rem; color: #000 !important;">{{ $pharmacyAddress }}</p>
        @endif
        @if($pharmacyPhone)
            <p style="margin: 0.25rem 0; font-size: 0.875rem; color: #000 !important;">Tel: {{ $pharmacyPhone }}</p>
        @endif
        @if($pharmacyEmail)
            <p style="margin: 0.25rem 0; font-size: 0.875rem; color: #000 !important;">Email: {{ $pharmacyEmail }}</p>
        @endif
        <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: #666 !important;">TAX ID: {{ $pharmacyTaxId }}</p>
    </div>

    <!-- Transaction Details -->
    <div style="margin-bottom: 1rem; font-size: 0.875rem; color: #000 !important;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
            <span style="font-weight: bold; color: #000 !important;">Invoice #:</span>
            <span style="color: #000 !important;">{{ $sale->invoice_number }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
            <span style="font-weight: bold; color: #000 !important;">Date:</span>
            <span style="color: #000 !important;">{{ $sale->sale_date->format('d/m/Y H:i') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
            <span style="font-weight: bold; color: #000 !important;">Cashier:</span>
            <span style="color: #000 !important;">{{ $sale->user->name }}</span>
        </div>
        @if($customer)
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                <span style="font-weight: bold; color: #000 !important;">Customer:</span>
                <span style="color: #000 !important;">{{ $customer->name }}</span>
            </div>
            @if($customer->phone)
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: bold; color: #000 !important;">Phone:</span>
                    <span style="color: #000 !important;">{{ $customer->phone }}</span>
                </div>
            @endif
        @endif
    </div>

    <!-- Items -->
    <div style="border-top: 2px dashed #333; border-bottom: 2px dashed #333; padding: 1rem 0; margin-bottom: 1rem; color: #000 !important;">
        <table style="width: 100%; font-size: 0.875rem; color: #000 !important;">
            <thead>
                <tr style="border-bottom: 1px solid #333;">
                    <th style="text-align: left; padding-bottom: 0.5rem; color: #000 !important;">Item</th>
                    <th style="text-align: center; padding-bottom: 0.5rem; color: #000 !important;">Qty</th>
                    <th style="text-align: right; padding-bottom: 0.5rem; color: #000 !important;">Price</th>
                    <th style="text-align: right; padding-bottom: 0.5rem; color: #000 !important;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleItems as $item)
                    <tr style="border-bottom: 1px dotted #ccc;">
                        <td style="padding: 0.5rem 0; max-width: 150px; word-wrap: break-word; color: #000 !important;">
                            <div style="font-weight: bold; color: #000 !important;">{{ $item->medicine->name }}</div>
                            @if($item->medicine->generic_name)
                                <div style="font-size: 0.75rem; color: #666 !important;">{{ $item->medicine->generic_name }}</div>
                            @endif
                        </td>
                        <td style="text-align: center; padding: 0.5rem 0; color: #000 !important;">{{ $item->quantity }}</td>
                        <td style="text-align: right; padding: 0.5rem 0; color: #000 !important;">{{ format_currency($item->unit_price) }}</td>
                        <td style="text-align: right; padding: 0.5rem 0; font-weight: bold; color: #000 !important;">{{ format_currency($item->total_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div style="margin-bottom: 1.5rem; font-size: 0.9375rem; color: #000 !important;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="color: #000 !important;">Subtotal:</span>
            <span style="color: #000 !important;">{{ format_currency($sale->subtotal) }}</span>
        </div>
        @if($sale->discount_amount > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #10b981 !important;">
                <span>Discount:</span>
                <span>-{{ format_currency($sale->discount_amount) }}</span>
            </div>
        @endif
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="color: #000 !important;">Tax ({{ tax_rate() }}%):</span>
            <span style="color: #000 !important;">{{ format_currency($sale->tax_amount) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 2px solid #333; font-size: 1.25rem; font-weight: bold;">
            <span style="color: #000 !important;">TOTAL:</span>
            <span style="color: #000 !important;">{{ format_currency($sale->total_amount) }}</span>
        </div>
    </div>

    <!-- Payment Info -->
    @if($payment)
        <div style="margin-bottom: 1.5rem; font-size: 0.875rem; background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; color: #000 !important;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-weight: bold; color: #000 !important;">Payment Method:</span>
                <span style="text-transform: uppercase; color: #000 !important;">{{ $payment->payment_method }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-weight: bold; color: #000 !important;">Amount Paid:</span>
                <span style="color: #000 !important;">{{ format_currency($payment->amount) }}</span>
            </div>
            @if(strtolower($payment->payment_method) === 'cash')
                @php
                    $change = max(0, $payment->amount - $sale->total_amount);
                @endphp
                <div style="display: flex; justify-content: space-between; font-weight: bold; color: #10b981 !important;">
                    <span>Change:</span>
                    <span>{{ format_currency($change) }}</span>
                </div>
            @elseif($payment->amount > $sale->total_amount)
                <div style="display: flex; justify-content: space-between; font-weight: bold; color: #10b981 !important;">
                    <span>Change:</span>
                    <span>{{ format_currency($payment->amount - $sale->total_amount) }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Footer -->
    <div style="text-align: center; font-size: 0.875rem; border-top: 2px dashed #333; padding-top: 1rem; margin-top: 1.5rem; color: #000 !important;">
        <p style="margin: 0 0 0.5rem 0; font-weight: bold; color: #000 !important;">{{ setting('receipt_footer', 'Thank you for your business!') }}</p>
        <p style="margin: 0; font-size: 0.75rem; color: #666 !important;">This is a valid receipt</p>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: #666 !important;">Powered by Symphony POS</p>
    </div>

    <!-- Barcode/QR Code placeholder -->
    <div style="text-align: center; margin-top: 1rem; color: #000 !important;">
        <svg style="height: 60px; margin: 0 auto;" viewBox="0 0 200 60">
            <!-- Simple barcode representation -->
            <rect x="10" y="0" width="3" height="60" fill="#000"/>
            <rect x="15" y="0" width="2" height="60" fill="#000"/>
            <rect x="20" y="0" width="4" height="60" fill="#000"/>
            <rect x="26" y="0" width="2" height="60" fill="#000"/>
            <rect x="30" y="0" width="3" height="60" fill="#000"/>
            <rect x="35" y="0" width="5" height="60" fill="#000"/>
            <rect x="42" y="0" width="2" height="60" fill="#000"/>
            <rect x="46" y="0" width="4" height="60" fill="#000"/>
            <rect x="52" y="0" width="3" height="60" fill="#000"/>
            <rect x="57" y="0" width="2" height="60" fill="#000"/>
            <rect x="61" y="0" width="5" height="60" fill="#000"/>
            <rect x="68" y="0" width="3" height="60" fill="#000"/>
            <rect x="73" y="0" width="2" height="60" fill="#000"/>
            <rect x="77" y="0" width="4" height="60" fill="#000"/>
            <rect x="83" y="0" width="3" height="60" fill="#000"/>
            <rect x="88" y="0" width="2" height="60" fill="#000"/>
            <rect x="92" y="0" width="5" height="60" fill="#000"/>
            <rect x="99" y="0" width="2" height="60" fill="#000"/>
            <rect x="103" y="0" width="4" height="60" fill="#000"/>
            <rect x="109" y="0" width="3" height="60" fill="#000"/>
            <rect x="114" y="0" width="2" height="60" fill="#000"/>
            <rect x="118" y="0" width="5" height="60" fill="#000"/>
            <rect x="125" y="0" width="3" height="60" fill="#000"/>
            <rect x="130" y="0" width="2" height="60" fill="#000"/>
            <rect x="134" y="0" width="4" height="60" fill="#000"/>
            <rect x="140" y="0" width="3" height="60" fill="#000"/>
            <rect x="145" y="0" width="2" height="60" fill="#000"/>
            <rect x="149" y="0" width="5" height="60" fill="#000"/>
            <rect x="156" y="0" width="2" height="60" fill="#000"/>
            <rect x="160" y="0" width="4" height="60" fill="#000"/>
            <rect x="166" y="0" width="3" height="60" fill="#000"/>
            <rect x="171" y="0" width="2" height="60" fill="#000"/>
            <rect x="175" y="0" width="5" height="60" fill="#000"/>
            <rect x="182" y="0" width="3" height="60" fill="#000"/>
            <rect x="187" y="0" width="2" height="60" fill="#000"/>
        </svg>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; font-family: 'Courier New', monospace; color: #000 !important;">{{ $sale->invoice_number }}</p>
    </div>
</div>

<style>
    @media print {
        /* Reset page layout for thermal printer (80mm width) */
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        html, body {
            width: 80mm;
            height: auto;
            margin: 0;
            padding: 0;
            background: white;
            overflow: visible;
        }
        
        /* Hide everything except receipt print clone */
        body > *:not(#receipt-print-clone) {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
        }
        
        /* Show receipt print clone */
        #receipt-print-clone {
            display: block !important;
            visibility: visible !important;
            position: relative !important;
            left: auto !important;
            top: 0 !important;
            transform: none !important;
            width: 80mm !important;
            max-width: 80mm !important;
            min-width: 80mm !important;
            margin: 0 !important;
            padding: 5mm !important;
            background: white !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
            font-size: 11pt !important;
            line-height: 1.3 !important;
        }
        
        /* Ensure all receipt content is visible and has proper colors */
        #receipt-print-clone,
        #receipt-print-clone * {
            visibility: visible !important;
            color: #000 !important;
        }
        
        /* Override any white text colors */
        #receipt-print-clone h2,
        #receipt-print-clone p,
        #receipt-print-clone span,
        #receipt-print-clone div,
        #receipt-print-clone td,
        #receipt-print-clone th {
            color: #000 !important;
        }
        
        #receipt-print-clone div,
        #receipt-print-clone p,
        #receipt-print-clone span,
        #receipt-print-clone h2,
        #receipt-print-clone svg {
            display: block !important;
        }
        
        #receipt-print-clone table {
            display: table !important;
        }
        
        #receipt-print-clone thead {
            display: table-header-group !important;
        }
        
        #receipt-print-clone tbody {
            display: table-row-group !important;
        }
        
        #receipt-print-clone tr {
            display: table-row !important;
            page-break-inside: avoid !important;
        }
        
        #receipt-print-clone td,
        #receipt-print-clone th {
            display: table-cell !important;
        }
        
        /* Prevent page breaks */
        #receipt-print-clone > div {
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
        }
        
        #receipt-print-clone table {
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
        }
        
        /* Ensure no extra spacing */
        #receipt-print-clone::after {
            display: none !important;
            content: none !important;
        }
    }
</style>


