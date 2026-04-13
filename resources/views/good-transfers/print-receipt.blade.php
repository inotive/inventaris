<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pemindahan Barang - {{ $goodTransfer->transfer_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .info-section {
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }

        .info-section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            min-width: 100px;
        }

        .info-value {
            color: #555;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        .items-table .number {
            text-align: center;
            width: 50px;
        }

        .items-table .quantity {
            text-align: center;
            width: 80px;
        }

        .items-table .unit {
            text-align: center;
            width: 80px;
        }

        .items-table .received-qty {
            text-align: center;
            width: 80px;
            color: #059669;
            font-weight: bold;
        }

        .items-table .note {
            font-style: italic;
            color: #666;
        }

        .ttd-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 48px;
        }
        .ttd-box {
            width: 230px;
            text-align: center;
        }
        .ttd-date {
            font-size: 12px;
            color: #444;
            margin-bottom: 8px;
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
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-shipped {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-received {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-canceled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background-color: #047857;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                font-size: 11px;
            }

            .container {
                padding: 10px;
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

        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Cetak</button>

    <div class="container">
        <div class="header">
            <div class="company-name">{{ $goodTransfer->fromBranch->name }}</div>
            <div class="company-address">
                {{ $goodTransfer->fromBranch->address }}<br>
                Telp: {{ $goodTransfer->fromBranch->phone }} | Email: {{ $goodTransfer->fromBranch->email }}
            </div>
            <div class="receipt-title">NOTA PEMINDAHAN BARANG</div>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <h3>Informasi Transfer</h3>
                <div class="info-row">
                    <span class="info-label">No. Transfer:</span>
                    <span class="info-value">{{ $goodTransfer->transfer_no }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($goodTransfer->transferred_at)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ strtolower($goodTransfer->status) }}">
                        @if($goodTransfer->status == 'Shipped')
                            Dikirim
                        @elseif($goodTransfer->status == 'Received')
                            Diterima
                        @else
                            Dibatalkan
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Catatan:</span>
                    <span class="info-value">{{ $goodTransfer->purpose ?: '-' }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Dari Cabang</h3>
                <div class="info-row">
                    <span class="info-label">Nama:</span>
                    <span class="info-value">{{ $goodTransfer->fromBranch->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dikirim oleh:</span>
                    <span class="info-value">{{ $goodTransfer->sentBy->name }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Ke Cabang</h3>
                <div class="info-row">
                    <span class="info-label">Nama:</span>
                    <span class="info-value">{{ $goodTransfer->toBranch->name }}</span>
                </div>
                @if($goodTransfer->receivedBy)
                <div class="info-row">
                    <span class="info-label">Diterima oleh:</span>
                    <span class="info-value">{{ $goodTransfer->receivedBy->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="number">No</th>
                    <th>Nama Item</th>
                    <th>Kode</th>
                    <th class="quantity">Qty Dikirim</th>
                    <th class="quantity">Qty Diterima</th>
                    <th class="unit">Satuan</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goodTransfer->items as $index => $item)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->item->code }}</td>
                    <td class="quantity">{{ $item->quantity_transferred }}</td>
                    <td class="received-qty">
                        @if($item->quantity_received !== null)
                            {{ $item->quantity_received }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="unit">{{ $item->unit }}</td>
                    <td class="note">
                        @if($item->note_received)
                            <strong>Penerimaan:</strong> {{ $item->note_received }}<br>
                        @endif
                        @if($item->note)
                            <strong>Transfer:</strong> {{ $item->note }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                        Tidak ada item
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Tanda Tangan Section -->
        <div class="ttd-section">
            <div class="ttd-box">
                <div class="ttd-date">{{ $goodTransfer->fromBranch->region}}, {{ \Carbon\Carbon::parse($goodTransfer->transferred_at)->format('d F Y') }}</div>
                <div class="ttd-label">Dikirim oleh,</div>
                <div class="ttd-name">
                    {{ $goodTransfer->sentBy->name ?? '___________________' }}
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
        // Auto print when page loads
        window.onload = function() {
            // Small delay to ensure all content is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
