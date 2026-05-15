<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Keterlambatan {{ $monthName }} {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #4472C4;
            color: #FFFFFF;
            font-weight: bold;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 12px;
            text-align: center;
            margin-bottom: 15px;
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
    <div class="title">REKAP KETERLAMBATAN KARYAWAN</div>
    <div class="subtitle">Periode: {{ $monthName }} {{ $year }}</div>
    <div class="subtitle">Cabang & Departemen: {{ $branchName }} - {{ $departmentName }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama & Jabatan Pekerja</th>
                <th>Total Hari Terlambat Dalam Toleransi</th>
                <th>Total Hari Terlambat Lewat Toleransi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $emp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp['name'] }}{{ isset($emp['position_name']) && $emp['position_name'] ? ' - ' . $emp['position_name'] : '' }}</td>
                    <td>{{ $emp['late_within_tolerance_days'] }} Hari ({{ $emp['late_within_tolerance_minutes'] }}
                        Menit)</td>
                    <td>{{ $emp['late_exceed_tolerance_days'] }} Hari ({{ $emp['late_exceed_tolerance_minutes'] }}
                        Menit)</td>
                    <td>-</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
