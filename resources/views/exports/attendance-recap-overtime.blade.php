<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        .title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 10pt;
            text-align: center;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #000;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="title">REKAP LEMBUR KARYAWAN</div>
    <div class="subtitle">Periode: {{ $monthName }} {{ $year }}</div>
    <div class="subtitle">Cabang & Departemen: {{ $branchName }} - {{ $departmentName }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama & Jabatan Pekerja</th>
                <th>Total Pengajuan</th>
                <th>Total Disetujui</th>
                <th>Total Jam Lembur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $emp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp['name'] }}{{ isset($emp['position_name']) && $emp['position_name'] ? ' - ' . $emp['position_name'] : '' }}</td>
                    <td>{{ $emp['ot_requests'] }} Kali</td>
                    <td>{{ $emp['ot_approved'] }} Kali</td>
                    <td>{{ $emp['ot_hours'] }} Jam</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
