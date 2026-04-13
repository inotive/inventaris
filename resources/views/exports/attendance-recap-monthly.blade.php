<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Kehadiran {{ $monthName }} {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
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
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 12px;
            text-align: center;
            margin-bottom: 20px;
        }

        .text-left {
            text-align: left;
        }

        .bg-green {
            background-color: #92D050;
        }

        .bg-yellow {
            background-color: #FFFF00;
        }

        .bg-red {
            background-color: #FF0000;
            color: #FFFFFF;
        }

        .bg-blue {
            background-color: #00B0F0;
        }

        .bg-orange {
            background-color: #FFC000;
        }

        .bg-purple {
            background-color: #C27BA0;
        }

        .legend {
            margin-top: 20px;
        }

        .legend-item {
            display: inline-block;
            margin-right: 15px;
            padding: 3px 8px;
            border: 1px solid #000;
        }
    </style>
</head>

<body>
    <div class="title">REKAP KEHADIRAN KARYAWAN</div>
    <div class="subtitle">Bulan: {{ $monthName }} {{ $year }}</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Karyawan</th>
                <th rowspan="2">Departemen</th>
                <th rowspan="2">Jabatan</th>
                <th rowspan="2">Cabang</th>
                <th colspan="6">Rekap</th>
                <th colspan="{{ $daysInMonth }}">Tanggal</th>
            </tr>
            <tr>
                <th>H</th>
                <th>T</th>
                <th>A</th>
                <th>C</th>
                <th>S</th>
                <th>I</th>
                @for ($d = 1; $d <= $daysInMonth; $d++)
                    <th>{{ $d }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $emp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp['name'] }}</td>
                    <td class="text-left">{{ $emp['department'] }}</td>
                    <td class="text-left">{{ $emp['position'] }}</td>
                    <td class="text-left">{{ $emp['branch'] }}</td>
                    <td>{{ $emp['recap']['H'] }}</td>
                    <td>{{ $emp['recap']['T'] }}</td>
                    <td>{{ $emp['recap']['A'] }}</td>
                    <td>{{ $emp['recap']['C'] }}</td>
                    <td>{{ $emp['recap']['S'] }}</td>
                    <td>{{ $emp['recap']['I'] }}</td>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $status = $emp['days'][$d] ?? '-';
                            $bgClass = '';
                            if ($status === 'H') {
                                $bgClass = 'bg-green';
                            } elseif ($status === 'T') {
                                $bgClass = 'bg-yellow';
                            } elseif ($status === 'A') {
                                $bgClass = 'bg-red';
                            } elseif ($status === 'C') {
                                $bgClass = 'bg-blue';
                            } elseif ($status === 'S') {
                                $bgClass = 'bg-orange';
                            } elseif ($status === 'I') {
                                $bgClass = 'bg-purple';
                            }
                        @endphp
                        <td class="{{ $bgClass }}">{{ $status }}</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="legend">
        <strong>KETERANGAN:</strong><br>
        <span class="legend-item bg-green">H = Hadir</span>
        <span class="legend-item bg-yellow">T = Terlambat</span>
        <span class="legend-item bg-red">A = Alpa</span>
        <span class="legend-item bg-blue">C = Cuti</span>
        <span class="legend-item bg-orange">S = Sakit</span>
        <span class="legend-item bg-purple">I = Izin</span>
    </div>
</body>

</html>
