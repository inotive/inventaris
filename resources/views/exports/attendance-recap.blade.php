<table>
    {{-- Logo & Header Row --}}
    <tr>
        <td colspan="10" style="text-align: center; vertical-align: middle;">
            <img src="{{ public_path('images/logo.png') }}" style="height: 60px;" />
        </td>
    </tr>
    
    {{-- Title Row --}}
    <tr>
        <td colspan="{{ 10 + $daysInMonth }}" style="text-align: center; font-weight: bold; font-size: 16px;">
            REKAP KEHADIRAN KARYAWAN
        </td>
    </tr>
    
    {{-- Period Row --}}
    <tr>
        <td colspan="{{ 10 + $daysInMonth }}" style="text-align: center; font-size: 12px;">
            Periode: {{ $monthName }} {{ $year }}
        </td>
    </tr>
    
    {{-- Empty Row --}}
    <tr></tr>
    
    {{-- Table Header --}}
    <tr style="background-color: #4472C4; color: white; font-weight: bold; text-align: center;">
        <td style="border: 1px solid black;">No</td>
        <td style="border: 1px solid black;">Nama Karyawan</td>
        <td style="border: 1px solid black;">Departemen</td>
        <td style="border: 1px solid black;">Jabatan</td>
        <td style="border: 1px solid black;">Cabang</td>
        <td style="border: 1px solid black;">Hadir</td>
        <td style="border: 1px solid black;">Terlambat</td>
        <td style="border: 1px solid black;">Alpa</td>
        <td style="border: 1px solid black;">Cuti</td>
        <td style="border: 1px solid black;">Sakit</td>
        
        @for ($d = 1; $d <= $daysInMonth; $d++)
            <td style="border: 1px solid black;">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</td>
        @endfor
    </tr>
    
    {{-- Data Rows --}}
    @foreach ($employees as $index => $employee)
    <tr>
        <td style="border: 1px solid black; text-align: center;">{{ $index + 1 }}</td>
        <td style="border: 1px solid black;">{{ $employee['name'] }}</td>
        <td style="border: 1px solid black;">{{ $employee['department'] }}</td>
        <td style="border: 1px solid black;">{{ $employee['position'] }}</td>
        <td style="border: 1px solid black;">{{ $employee['branch'] }}</td>
        <td style="border: 1px solid black; text-align: center;">{{ $employee['recap']['H'] }}</td>
        <td style="border: 1px solid black; text-align: center;">{{ $employee['recap']['T'] }}</td>
        <td style="border: 1px solid black; text-align: center;">{{ $employee['recap']['A'] }}</td>
        <td style="border: 1px solid black; text-align: center;">{{ $employee['recap']['C'] }}</td>
        <td style="border: 1px solid black; text-align: center;">{{ $employee['recap']['S'] }}</td>
        
        @for ($d = 1; $d <= $daysInMonth; $d++)
            @php
                $status = $employee['days'][$d] ?? '-';
                $bgColor = '';
                $textColor = '';
                
                // Color coding
                if ($status === 'H') {
                    $bgColor = '#92D050'; // Green - Hadir
                } elseif ($status === 'T') {
                    $bgColor = '#FFC000'; // Orange - Terlambat
                } elseif ($status === 'A') {
                    $bgColor = '#FF0000'; // Red - Alpa
                    $textColor = 'white';
                } elseif ($status === 'C') {
                    $bgColor = '#00B0F0'; // Blue - Cuti
                } elseif ($status === 'S') {
                    $bgColor = '#FFFF00'; // Yellow - Sakit
                } elseif ($status === 'I') {
                    $bgColor = '#C5E0B4'; // Light Green - Izin
                } elseif ($status === 'B') {
                    $bgColor = '#BDD7EE'; // Light Blue - Berjalan
                } elseif ($status === 'SH') {
                    $bgColor = '#E7E6E6'; // Gray - Shift
                }
            @endphp
            <td style="border: 1px solid black; text-align: center; background-color: {{ $bgColor }}; color: {{ $textColor }};">
                {{ $status }}
            </td>
        @endfor
    </tr>
    @endforeach
    
    {{-- Empty Row --}}
    <tr></tr>
    <tr></tr>
    
    {{-- Legend --}}
    <tr>
        <td colspan="{{ 10 + $daysInMonth }}" style="font-weight: bold; font-size: 12px;">
            KETERANGAN:
        </td>
    </tr>
    <tr>
        <td colspan="2" style="background-color: #92D050; border: 1px solid black; text-align: center;">H = Hadir</td>
        <td colspan="2" style="background-color: #FFC000; border: 1px solid black; text-align: center;">T = Terlambat</td>
        <td colspan="2" style="background-color: #FF0000; color: white; border: 1px solid black; text-align: center;">A = Alpa/Absen</td>
        <td colspan="2" style="background-color: #00B0F0; border: 1px solid black; text-align: center;">C = Cuti</td>
        <td colspan="2" style="background-color: #FFFF00; border: 1px solid black; text-align: center;">S = Sakit</td>
    </tr>
    <tr>
        <td colspan="2" style="background-color: #C5E0B4; border: 1px solid black; text-align: center;">I = Izin</td>
        <td colspan="2" style="background-color: #BDD7EE; border: 1px solid black; text-align: center;">B = Berjalan</td>
        <td colspan="2" style="background-color: #E7E6E6; border: 1px solid black; text-align: center;">SH = Shift</td>
        <td colspan="2" style="border: 1px solid black; text-align: center;">- = Libur</td>
        <td colspan="2"></td>
    </tr>
</table>
