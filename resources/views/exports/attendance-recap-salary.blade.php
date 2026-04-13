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

        .has-slip {
            background-color: #D4EDDA;
            color: #155724;
            font-weight: bold;
        }

        .no-slip {
            background-color: #F8D7DA;
            color: #721C24;
        }
    </style>
</head>

<body>
    <div class="title">REKAP GAJI DAN TUNJANGAN KARYAWAN</div>
    <div class="subtitle">Tahun: {{ $year }}</div>
    <div class="subtitle">Cabang & Departemen: {{ $branchName }} - {{ $departmentName }}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Karyawan</th>
                <th rowspan="2">Departemen</th>
                <th colspan="12">Status Slip Gaji per Bulan</th>
            </tr>
            <tr>
                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>Mei</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Agu</th>
                <th>Sep</th>
                <th>Okt</th>
                <th>Nov</th>
                <th>Des</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $emp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp['name'] }}{{ isset($emp['position_name']) && $emp['position_name'] !== '-' ? ' - ' . $emp['position_name'] : '' }}</td>
                    <td>{{ $emp['department'] }}</td>
                    @for ($month = 1; $month <= 12; $month++)
                        <td class="{{ $emp['salary_slips'][$month] ? 'has-slip' : 'no-slip' }}">
                            {{ $emp['salary_slips'][$month] ? '✓' : '-' }}
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
