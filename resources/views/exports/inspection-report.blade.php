<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi {{ $general['inspection_number'] ?? '-' }}</title>
    <style>
        @media print {
            @page { margin: 1cm; }
            body { margin: 0; }
            .page-break { page-break-after: always; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0ea5e9; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #0ea5e9; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; }
        .title { font-size: 20px; font-weight: bold; margin: 20px 0; color: #333; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
        .info-item { padding: 10px; background: #f8f9fa; border-left: 3px solid #0ea5e9; }
        .info-label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .info-value { font-size: 14px; font-weight: 600; color: #333; }

        .employee-section { margin-bottom: 40px; }
        .employee-name { font-size: 18px; font-weight: bold; color: #0ea5e9; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0ea5e9; }
        .category-section { margin-bottom: 25px; }
        .category-title { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 10px; background: #f0f9ff; padding: 8px 12px; border-left: 4px solid #0ea5e9; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        th { background: #0ea5e9; color: white; padding: 12px; text-align: left; font-size: 13px; }
        td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 12px; }
        tr:hover { background: #f8f9fa; }

        .signature-section { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
        .signature-box { max-width: 300px; }
        .signature-label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .signature-name { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 10px; }
        .signature-line { border-bottom: 2px solid #333; margin-top: 10px; }

        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #666; border-top: 2px solid #0ea5e9; padding-top: 15px; }
    </style>
</head>
<body>
    @php
        $submitterSignatureUrl = $submitterSignatureUrl ?? null;
        $approverSignatureUrl = $approverSignatureUrl ?? null;
    @endphp
    <div class="header">
        <div class="logo">SISTEM PENYIMPANAN</div>
        <div class="subtitle">BackOffice Website</div>
    </div>

    <div class="title">Laporan Inspeksi {{ $general['inspection_number'] ?? '-' }}</div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Nomor Inspeksi</div>
            <div class="info-value">{{ $general['inspection_number'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">No. SOP</div>
            <div class="info-value">{{ $general['sop_code'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Checklist</div>
            <div class="info-value">{{ $general['checklist_name'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Kategori</div>
            <div class="info-value">{{ $general['category'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tipe Pengecekan</div>
            <div class="info-value">{{ $general['inspection_type'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Target Pengecekan</div>
            <div class="info-value">{{ $general['inspection_target'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Dibuat Oleh</div>
            <div class="info-value">{{ $general['submitted_by'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal Dibuat</div>
            <div class="info-value">{{ $general['created_at'] ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal Inspeksi</div>
            <div class="info-value">{{ $general['submit_date'] ?? '-' }}</div>
        </div>
    </div>

    {{-- Section Tanda Tangan Pengaju & Penyetuju --}}
    <div class="signature-section" style="margin-top: 10px; margin-bottom: 30px; display: flex; gap: 40px;">
        <div class="signature-box">
            <p class="signature-label">Pengaju</p>
            <p class="signature-name">{{ $general['submitted_by'] ?? '-' }}</p>
            @if(!empty($submitterSignatureUrl ?? null))
                <img src="{{ $submitterSignatureUrl ?? '' }}" alt="Tanda tangan pengaju" style="height: 60px; margin-top: 10px;">
            @else
                <div class="signature-line" style="margin-top: 30px;"></div>
            @endif
        </div>
        {{-- <div class="signature-box">
            <p class="signature-label">Penyetuju</p>
            <p class="signature-name">{{ $general['approved_by'] ?? '-' }}</p>
            @if(!empty($approverSignatureUrl ?? null))
                <img src="{{ $approverSignatureUrl ?? '' }}" alt="Tanda tangan penyetuju" style="height: 60px; margin-top: 10px;">
            @else
                <div class="signature-line" style="margin-top: 30px;"></div>
            @endif
        </div> --}}
    </div>

    @if(!empty($isMultiple))
        @php $employeeNames = $employees ?? []; @endphp

        @foreach(($questionsByCategory ?? []) as $categoryName => $questions)
            <div class="employee-section page-break">
                <h2 class="employee-name">{{ $categoryName }}</h2>

                <div class="category-section">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 40%;">Pertanyaan</th>
                                @foreach($employeeNames as $emp)
                                    <th>{{ 'Jawaban ' . $emp }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowIndex = 1; @endphp
                            @foreach($questions as $q)
                                <tr>
                                    <td>{{ $rowIndex++ }}</td>
                                    <td>{{ $q['title'] ?? '-' }}</td>
                                    @foreach($employeeNames as $emp)
                                        <td>{{ $q['answers'][$emp] ?? '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        @foreach($employeeGroups as $employeeName => $categories)
            <div class="employee-section page-break">
                <h2 class="employee-name">{{ $employeeName }}</h2>

                @foreach($categories as $categoryName => $items)
                    <div class="category-section">
                        <h3 class="category-title">{{ $categoryName }}</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 40%;">Pertanyaan</th>
                                    <th style="width: 20%;">Jawaban</th>
                                    <th style="width: 35%;">Tindak Lanjut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $i => $q)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $q['title'] ?? '-' }}</td>
                                        <td>{{ $q['answer'] ?? '-' }}</td>
                                        <td>{{ $q['note'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <div class="signature-section">
                    <div class="signature-box">
                        <p class="signature-label">Nama:</p>
                        <p class="signature-name">{{ $employeeName }}</p>
                        <p class="signature-label" style="margin-top: 60px;">Tanda Tangan:</p>
                        <div class="signature-line"></div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <p>&copy; {{ date('Y') }} Sistem Penyimpanan - BackOffice Website</p>
        <p>Diexport pada: {{ now()->format('d-m-Y H:i') }}</p>
    </div>
</body>
</html>
