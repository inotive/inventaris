<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $goodTransfer->transfer_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            min-height: 750px;
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
            max-height: 110px;
            object-fit: contain;
            object-position: left top;
        }

        .company-info {
            flex: 1;
            text-align: left;
            padding-top: 2px;
        }

        .company-name {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #000;
            letter-spacing: 0.5px;
        }

        .company-address {
            font-size: 9.5px;
            color: #000;
            line-height: 1.35;
        }

        /* Title Styles */
        .title-section {
            text-align: center;
            margin: 15px 0 20px 0;
        }

        .document-title {
            font-size: 19px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Metadata & Recipient Section */
        .meta-recipient-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            gap: 20px;
        }

        .recipient-box {
            flex: 1;
            font-size: 10px;
        }

        .recipient-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 10.5px;
        }

        .recipient-details {
            font-weight: bold;
            line-height: 1.4;
            max-width: 400px;
        }

        .meta-box {
            border: 1.2px solid #000;
            font-size: 10px;
            width: 100%;
        }

        .meta-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .meta-table td {
            padding: 4px 8px;
            vertical-align: top;
            width: 50%;
        }

        .meta-table tr:first-child td {
            border-bottom: 1.2px dashed #000 !important;
        }

        .meta-table td:first-child {
            border-right: 1.2px dashed #000 !important;
        }

        .meta-label {
            font-weight: normal;
            color: #000;
            font-size: 8.5px;
            margin-bottom: 1px;
            display: block;
        }

        .meta-value {
            font-weight: bold;
            color: #000;
            font-size: 10px;
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
            font-size: 10px;
            background-clip: padding-box;
        }

        .items-table th {
            background-color: #7dadff !important;
            font-weight: bold;
            color: #000;
        }

        .items-table th.center,
        .items-table td.center {
            text-align: center;
        }

        /* Summary & Notes Section */
        .summary-notes-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            gap: 20px;
        }

        .notes-container {
            flex: 1;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 10px;
        }

        .notes-box {
            border: 1.2px solid #000;
            min-height: 75px;
            max-width: 410px;
            padding: 8px;
            font-size: 10px;
            border-radius: 0px;
        }

        .totals-container {
            flex: 0 0 160px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 6px 0;
            border-top: 1.2px solid #000 !important;
            border-bottom: 1.2px solid #000 !important;
            margin-top: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: bold;
        }

        .total-row span:last-child {
            padding-right: 5px;
        }

        /* Signatures Section */
        .signatures-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 45px;
            gap: 120px;
            padding-right: 20px;
        }

        .signature-box {
            width: 170px;
            text-align: center;
        }

        .signature-title {
            font-size: 10.5px;
            font-weight: normal;
            margin-bottom: 45px;
        }

        .signature-line {
            width: 100%;
            height: 1px;
            background-color: #000 !important;
            border: none !important;
            margin-bottom: 5px;
            display: block;
        }

        .signature-date {
            font-size: 9.5px;
            text-align: left;
            padding-left: 2px;
        }

        /* Page Number */
        .page-number-footer {
            position: absolute;
            bottom: 0;
            right: 0;
            font-size: 9.5px;
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
                padding: 15mm 20mm;
                background-color: #fff;
                font-size: 10px;
            }
            .container {
                min-height: 255mm;
            }
            .print-button-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button-container">
        <button class="print-btn" onclick="window.print()">🖨️ Cetak Surat Jalan</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <div class="logo-container">
                <img src="{{ asset('images/logo-spb-hitam.png') }}" alt="Sinergi Putra Borneo" onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='block';">
                <div id="logo-fallback" style="display: none; font-size: 14px; font-weight: bold; color: #4f81bd; letter-spacing: 1px;">
                    SINERGI PUTRA BORNEO
                </div>
            </div>
            <div class="company-info">
                <div class="company-name">PT SINERGI PUTRA BORNEO</div>
                <div class="company-address">
                    Jl. Karya Etam No 164 RT 12 Desa Sangatta Utara Kec. Sangatta Utara<br>
                    Kab. Kutai Timur Kalimantan Timur 75611<br>
                    Indonesia
                </div>
            </div>
        </div>

        <!-- Metadata & Recipient -->
        <div class="meta-recipient-section" style="margin-top: 20px;">
            <div class="recipient-box" style="margin-top: 32px;">
                <div class="recipient-title">Kepada</div>
                <div class="recipient-details">
                    {{ strtoupper($goodTransfer->toBranch->name) }}<br>
                    @if($goodTransfer->toBranch->address)
                        {{ $goodTransfer->toBranch->address }}
                    @endif
                </div>
            </div>
            <div class="meta-container" style="display: flex; flex-direction: column; align-items: center; width: 340px; flex: 0 0 340px;">
                <div class="document-title" style="font-size: 19px; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 8px; text-align: center;">Surat Jalan</div>
                <div class="meta-box" style="width: 100%;">
                    <table class="meta-table">
                        <tr>
                            <td>
                                <span class="meta-label">Tanggal</span>
                                <span class="meta-value">{{ \Carbon\Carbon::parse($goodTransfer->transferred_at)->locale('id')->translatedFormat('d F Y') }}</span>
                            </td>
                            <td>
                                <span class="meta-label">Nomor</span>
                                <span class="meta-value">{{ $goodTransfer->transfer_no }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="meta-label">Lampiran</span>
                                <span class="meta-value">-</span>
                            </td>
                            <td>
                                <span class="meta-label">PO No</span>
                                <span class="meta-value">-</span>
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
                    <th style="width: 55%;">Nama Barang</th>
                    <th style="width: 10%;" class="center">Qty</th>
                    <th style="width: 10%;" class="center">Satuan</th>
                    <th style="width: 20%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goodTransfer->items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td class="center">{{ $item->quantity_transferred }}</td>
                    <td class="center">{{ $item->item->unit->name ?? $item->unit ?? 'Unit' }}</td>
                    <td>
                        {{ $item->note }}
                        @if($item->note_received)
                            <div style="font-size: 8.5px; color: #555; margin-top: 2px;">
                                <em>Diterima: {{ $item->note_received }} (Qty: {{ $item->quantity_received ?? '-' }})</em>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 15px; color: #666;">
                        Tidak ada item barang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary & Notes -->
        <div class="summary-notes-section">
            <div class="notes-container">
                <div class="notes-title">Catatan :</div>
                <div class="notes-box">
                    {{ $goodTransfer->purpose ?: '-' }}
                </div>
            </div>
            <div class="totals-container">
                <div class="total-row">
                    <span>Total Kuantitas</span>
                    <span>{{ $goodTransfer->items->sum('quantity_transferred') }}</span>
                </div>
                <div class="total-row">
                    <span>Jumlah Barang</span>
                    <span>{{ $goodTransfer->items->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-title">Pengirim</div>
                <div class="signature-line"></div>
                <div class="signature-date">Tgl.</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Penerima</div>
                <div class="signature-line"></div>
                <div class="signature-date">Tgl.</div>
            </div>
        </div>

        <!-- Footer Page info -->
        <div class="page-number-footer">
            Halaman 1 dari 1
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
