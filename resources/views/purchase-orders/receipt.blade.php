<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Pembelian - {{ $po['number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            background: #fff;
            padding: 30px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            min-height: 800px;
        }

        /* Header Styles */
        .header-section {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 15px;
        }

        .logo-container {
            flex: 0 0 240px;
            margin-right: 15px;
        }

        .logo-container img {
            width: 100%;
            height: auto;
            max-height: 100px;
            object-fit: contain;
            object-position: left center;
        }

        .company-info {
            flex: 1;
            text-align: left;
            padding-left: 10px;
        }

        .company-name {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #000;
            letter-spacing: 0.5px;
        }

        .company-address {
            font-size: 13px;
            color: #000;
            line-height: 1.35;
        }

        /* Metadata & Recipient Section */
        .meta-recipient-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .recipient-box {
            flex: 0 0 60%;
            width: 60%;
            font-size: 10px;
        }

        .recipient-title {
            font-weight: normal;
            margin-bottom: 1px;
            font-size: 11.5px;
        }

        .recipient-details {
            font-weight: bold;
            line-height: 1.4;
            max-width: 400px;
            font-size: 12px;
        }

        .meta-container {
            flex: 0 0 40%;
            width: 40%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .document-title {
            font-size: 22px;
            font-weight: normal !important;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            text-align: left;
        }

        /* Table Styles */
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 15px;
            border-top: 1.2px solid #000 !important;
            border-left: 1.2px solid #000 !important;
        }

        .items-table th,
        .items-table td {
            border-right: 1.2px solid #000 !important;
            border-bottom: 1.2px solid #000 !important;
            padding: 6px 8px;
            font-size: 11px;
            background-clip: padding-box;
        }

        .items-table th {
            background-color: #7dadff !important;
            font-weight: bold;
            color: #000;
            text-align: center !important;
        }

        .items-table th.center,
        .items-table td.center {
            text-align: center;
        }

        .items-table th.right,
        .items-table td.right {
            text-align: right;
        }

        /* Summary & Signature Section */
        .summary-signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .left-footer-container {
            flex: 0 0 60%;
            width: 60%;
            display: flex;
            flex-direction: column;
        }

        .notes-container {
            margin-bottom: 20px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
            border-top: 1.2px solid #000;
            border-bottom: 1.2px solid #000;
            padding: 4px 0;
            width: 300px;
        }

        .notes-content {
            font-size: 10.5px;
            width: 300px;
            min-height: 25px;
            line-height: 1.4;
            padding: 4px 0;
        }

        .signature-dashed-divider {
            border-top: 1.2px dashed #000 !important;
            width: 300px;
            height: 1px;
            margin-top: 15px;
            margin-bottom: 15px;
            display: block !important;
        }

        .signature-box {
            width: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-title {
            font-size: 11.5px;
            font-weight: normal;
            margin-bottom: 80px;
            text-align: center;
        }

        .signature-line {
            width: 250px;
            height: 1px;
            background-color: #000 !important;
            border: none !important;
            margin-bottom: 5px;
            display: block;
        }

        .signature-date {
            font-size: 10.5px;
            align-self: flex-start;
            padding-left: 25px;
        }

        /* Totals Box */
        .totals-box {
            flex: 0 0 40%;
            width: 40%;
            border-top: 1.2px solid #000;
            border-bottom: 1.2px solid #000;
            background-color: #d9d9d9 !important;
            display: flex;
            flex-direction: column;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: normal;
            color: #000;
        }

        .totals-row.grand-total {
            background-color: #7dadff !important;
            border-top: 1.2px solid #000;
            padding: 6px 8px;
            font-weight: bold;
        }

        /* Page Number */
        .page-number-footer {
            position: absolute;
            bottom: 0;
            right: 0;
            font-size: 10.5px;
            font-style: italic;
            color: #000;
        }

        /* Print Button */
        .print-button-container {
            margin-bottom: 20px;
            text-align: right;
        }

        .print-btn {
            background-color: #4f81bd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: background-color 0.2s;
        }

        .print-btn:hover {
            background-color: #3b6290;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }
            body {
                padding: 12mm !important;
                margin: 0 !important;
                background-color: #fff;
                font-size: 10px;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                min-height: 245mm !important;
            }
            .print-button-container {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-button-container">
        <button class="print-btn" onclick="window.print()">🖨️ Cetak Faktur Pembelian</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <div class="logo-container">
                <div id="logo-fallback" style="display: block; font-size: 18px; font-weight: bold; color: #4f81bd; letter-spacing: 1px;">
                    INVENTARIS
                </div>
            </div>
            <div class="company-info">
                <div class="company-name">INVENTARIS</div>
                <div class="company-address">
                    Jl. Sinarmas Land Boulevard Cluster Hyland Blok U7 No. 16, Batu Ampar<br>
                    Kota Balikpapan Kalimantan Timur 76136<br>
                    Indonesia
                </div>
            </div>
        </div>

        <!-- Metadata & Recipient -->
        <div class="meta-recipient-section" style="margin-top: 20px;">
            <div class="recipient-box" style="margin-top: 0;">
                <div class="recipient-title">Nama Distributor</div>
                <div class="recipient-details" style="font-weight: normal; font-size: 12px; text-transform: lowercase;">
                    {{ $po['vendor']['name'] ?? '-' }}
                </div>
            </div>
            <div class="meta-container">
                <div class="document-title">Faktur Pembelian</div>
                <div style="width: 100%;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                        <tr>
                            <td style="width: 80px; padding: 3px 0; font-weight: normal; color: #000;">No. Form</td>
                            <td style="width: 15px; padding: 3px 0; font-weight: normal; color: #000;">:</td>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">{{ $po['number'] }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">No. Faktur</td>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">:</td>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">{{ $po['number'] }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">Tanggal</td>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">:</td>
                            <td style="padding: 3px 0; font-weight: normal; color: #000;">
                                {{ \Carbon\Carbon::createFromFormat('d/m/Y', $po['date'])->locale('id')->translatedFormat('d F Y') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="center">No</th>
                    <th style="width: 45%;">Nama Barang</th>
                    <th style="width: 10%;" class="center">Qty</th>
                    <th style="width: 15%;" class="right">Harga</th>
                    <th style="width: 10%;" class="center">Satuan</th>
                    <th style="width: 15%;" class="right">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($po['items'] as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item['item_name'] }}</td>
                    <td class="center">{{ (float)$item['quantity_ordered'] }}</td>
                    <td class="right">{{ number_format($item['cost'], 0, ',', '.') }}</td>
                    <td class="center">{{ $item['unit'] ?? 'Unit' }}</td>
                    <td class="right">{{ number_format($item['quantity_ordered'] * $item['cost'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px; color: #666;">
                        Tidak ada item barang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Calculations & Left Footer -->
        @php
            $subtotal = collect($po['items'])->sum(function($item) {
                return $item['quantity_ordered'] * $item['cost'];
            });
            $discount   = $po['discount'] ?? 0;
            $other_cost = $po['other_cost'] ?? 0;
            $ppn        = ($subtotal - $discount) * 0.11;
            $grandTotal = $subtotal - $discount + $ppn + $other_cost;
        @endphp

        <div class="summary-signature-section">
            <div class="left-footer-container">
                <div class="notes-container">
                    <div class="notes-title">Keterangan</div>
                    <div class="notes-content">
                        {{ $po['note'] ?: '-' }}
                    </div>
                </div>

                <div class="signature-dashed-divider"></div>

                <div class="signature-box">
                    <div class="signature-title">Bagian Pembelian,</div>
                    <div class="signature-line"></div>
                    <div class="signature-date">Tgl.</div>
                </div>
            </div>

            <div class="totals-box">
                <div class="totals-row">
                    <span>Sub Total</span>
                    <span>{{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row">
                    <span>Diskon</span>
                    <span>{{ number_format($discount, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row">
                    <span>PPN (11%)</span>
                    <span>{{ number_format($ppn, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row">
                    <span>Biaya Lain-lain</span>
                    <span>{{ number_format($other_cost, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row grand-total">
                    <span>Total</span>
                    <span>{{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Page info -->
        <div class="page-number-footer">
            Halaman 1 dari 1
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
