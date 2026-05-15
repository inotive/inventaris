<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Purchase Order - {{ $po['number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11.5px;
            line-height: 1.5;
            color: #222;
            background: #fff;
        }

        .container {
            max-width: 790px;
            min-height: 90vh;
            margin: 24px auto 24px auto;
            padding: 24px 24px 10px 24px;
            border: 1.5px solid #aaa;
            border-radius: 7px;
            background: #fff;
        }

        .header {
            margin-bottom: 16px;
            border-bottom: 1.5px solid #333;
            padding-bottom: 12px;
        }

        .company-row {
            display: flex;
            align-items: top;
            justify-content: space-between;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .company-address {
            font-size: 13px;
            color: #555;
        }

        .logo-col {
            width: 90px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .receipt-title {
            font-size: 18.5px;
            font-weight: bold;
            margin: 20px 0 18px 0;
            text-align: center;
            letter-spacing: 1px;
        }

        .info-section {
            display: flex;
            gap: 16px;
            margin-bottom: 10px;
        }

        .info-left, .info-right {
            width: 50%;
            min-width: 0;
        }
        .info-left {
            border-right: 1px dotted #bdbdbd;
            padding-right: 18px;
        }
        .info-right {
            padding-left: 18px;
        }

        .info-group {
            margin-bottom: 9px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 1px;
            font-size: 11.5px;
            letter-spacing: 0.1px;
        }

        .info-value {
            color: #262626;
            font-size: 12px;
            /* to add ellipsis if too long */
            overflow-wrap: anywhere;
            word-break: break-all;
        }

        .badge-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0 8px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 6.5px 6px;
            font-size: 11.5px;
        }

        .items-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.01em;
        }

        .items-table td {
            vertical-align: top;
            background: #fff;
        }

        .items-table .number {
            text-align: center;
            width: 36px;
        }

        .items-table .qty {
            text-align: center;
            width: 55px;
        }
        .items-table .price {
            text-align: right;
            width: 80px;
        }
        .items-table .total {
            text-align: right;
            width: 85px;
        }
        .items-table .unit {
            width: 60px;
            text-align: center;
        }
        .items-table .catatan {
            min-width: 80px;
        }

        /* Remove summary-section and footer margin bottom because we remove it */

        .ttd-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 48px;
        }
        .ttd-box {
            width: 230px;
            text-align: center;
        }
        .ttd-label {
            margin-bottom: 52px;
            font-size: 12px;
            color: #444;
        }
        .ttd-name {
            font-weight: 600;
            font-size: 13px;
            border-top: 1px solid #bdbdbd;
            display: inline-block;
            min-width: 140px;
            margin-top: 13px;
            padding-top: 4px;
            color: #2d2d2d;
            letter-spacing: 1.5px;
        }

        .footer {
            margin-top: 38px;
            text-align: right;
            font-size: 10.8px;
            color: #666;
            border-top: 1px dashed #bcbcbc;
            padding-top: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-delivered {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }

        .payment-badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .payment-belum-dibayar {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .payment-belum-lunas {
            background-color: #fed7aa;
            color: #ea580c;
        }

        .payment-lunas {
            background-color: #dcfce7;
            color: #166534;
        }

        @media print {
            html, body {
                background: white !important;
            }
            body {
                font-size: 11px !important;
            }

            .container {
                margin: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 7px 10px 0 10px !important;
                max-width: 100vw !important;
                min-height: unset;
            }
            .footer {
                padding-top: 4px;
                margin-top: 32px !important;
                border-color: #999 !important;
            }
            .ttd-section {
                margin-top: 32px !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-row">
                <div>
                    <div class="company-name">{{ $po['branch']['name'] }}</div>
                    <div class="company-address">
                        {{ $po['branch']['address'] }}<br>
                        Telp: {{ $po['branch']['phone'] }} &nbsp;|&nbsp; Email: {{ $po['branch']['email'] }}
                    </div>
                </div>
                <!-- Optionally add logo here on future revisions:
                <div class="logo-col"><img src="/logo.png" style="height:55px;" /></div>
                -->
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">NOTA PURCHASE ORDER</div>

        <!-- Information Section -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-group">
                    <span class="info-label">No. PO</span><br>
                    <span class="info-value">{{ $po['number'] }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Tanggal PO</span><br>
                    <span class="info-value">{{ $po['date'] }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Vendor</span><br>
                    <span class="info-value">{{ $po['vendor']['name'] ?? '-' }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Dibuat oleh</span><br>
                    <span class="info-value">{{ $po['ordered_by'] ?? '-' }}</span>
                </div>
                @if($po['from_pr'])
                <div class="info-group">
                    <span class="info-label">Referensi PR</span><br>
                    <span class="info-value">{{ $po['from_pr']['number'] }}</span>
                </div>
                @endif
            </div>
            <div class="info-right">
                <div class="info-group">
                    <span class="info-label">Status Pembayaran</span><br>
                    <span class="info-value badge-group">
                        <span class="payment-badge payment-{{ $po['status_invoice'] }}">
                            @if($po['status_invoice'] == 'belum_dibayar')
                                Belum Dibayar
                            @elseif($po['status_invoice'] == 'belum_lunas')
                                Belum Lunas
                            @elseif($po['status_invoice'] == 'lunas')
                                Lunas
                            @else
                                {{ $po['status_invoice'] }}
                            @endif
                        </span>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Total Order</span><br>
                    <span class="info-value">Rp {{ number_format($po['total'], 0, ',', '.') }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Jumlah Dibayar</span><br>
                    <span class="info-value">Rp {{ number_format($po['paid_amount'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Sisa Pembayaran</span><br>
                    <span class="info-value">
                        Rp {{ number_format(($po['total'] ?? 0) - ($po['paid_amount'] ?? 0), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        @if($po['note'] || $po['receive_notes'])
        <div class="notes-section">
            @if($po['note'])
                <div class="notes-title">Catatan Purchase Order:</div>
                <div class="notes-content">{{ $po['note'] }}</div>
            @endif

            @if($po['receive_notes'])
                <div class="notes-title" style="margin-top: 11px;">Catatan Penerimaan:</div>
                <div class="notes-content">{{ $po['receive_notes'] }}</div>
            @endif
        </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="number">No</th>
                    <th>Nama Item</th>
                    <th class="qty">Qty Dipesan</th>
                    <th class="price">Harga Satuan</th>
                    <th class="total">Subtotal</th>
                    <th class="unit">Satuan</th>
                    <th class="catatan">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($po['items'] as $index => $item)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>
                        <span style="font-weight:600;">{{ $item['item_name'] }}</span><br>
                        <span style="color:#888;font-size:11px">{{ $item['item_code'] }}</span>
                    </td>
                    <td class="qty">
                        <span>{{ $item['quantity_ordered'] }}</span>
                    </td>
                    <td class="price">Rp {{ number_format($item['cost'], 0, ',', '.') }}</td>
                    <td class="total">Rp {{ number_format($item['quantity_ordered'] * $item['cost'], 0, ',', '.') }}</td>
                    <td class="unit">{{ $item['unit'] }}</td>
                    <td class="catatan">
                        @if($item['note'])
                            <span style="font-size:11px;color:#444">{{ $item['note'] }}</span>
                        @else
                            <span style="color: #bbb;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 22px; color: #6b7280;">
                        Tidak ada item
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Tanda Tangan Section -->
        <div class="ttd-section">
            <div class="ttd-box">
                <div class="ttd-date">{{ $po['branch']['region'] }}, {{ $po['date'] }}</div>
                <div class="ttd-label">Dibuat oleh,</div>
                <div class="ttd-name">
                    {{ $po['ordered_by'] ?? '___________________' }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}<br>
            <span>Terima kasih atas kepercayaan Anda!</span>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
