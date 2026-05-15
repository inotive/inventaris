# Dokumentasi Formula Perhitungan Persentase Kehadiran

## Ringkasan

Dokumen ini menjelaskan formula yang digunakan untuk menghitung **Presentase Kehadiran** dan **% Tepat Waktu** di halaman Rekap Absensi (Attendance Recap).

**UPDATED: 2026-01-29** - Formula diperbaiki untuk menghitung dari total hari terjadwal (TANPA Cuti)

---

## 1. PRESENTASE KEHADIRAN (% Kehadiran)

### Lokasi Kode

- **File**: `resources/js/Pages/Admin/AttendanceRecap/components/MonthlyStatusTab.vue`
- **Function**: `calculateAttendancePercentage(row)`

### Formula Final (UPDATED 2026-01-29)

```javascript
// Total hadir = H (On Time) + B (Berjalan) + T (Terlambat) + S (Sakit) + I (Izin)
// Cuti TIDAK dihitung dalam presentase kehadiran
const totalHadir =
    (row.recap.H || 0) +
    (row.recap.B || 0) +
    (row.recap.T || 0) +
    (row.recap.S || 0) +
    (row.recap.I || 0);

// Persentase kehadiran = (Total Hadir / Hari Terjadwal) * 100
const percentage = (totalHadir / row.work_days) * 100;
```

### Penjelasan Detail

**Komponen yang DIHITUNG sebagai Hadir:**

- `H` = Hadir On Time (tepat waktu)
- `B` = Berjalan/Running (sedang berjalan)
- `T` = Terlambat
- `S` = Sakit (dengan surat keterangan)
- `I` = Izin Lainnya (yang disetujui)

**Komponen yang TIDAK DIHITUNG:**

- `A` = Alpha/Absen (tidak hadir tanpa keterangan)
- `C` = Cuti (tidak dihitung dalam presentase kehadiran)

**Penyebut:**

- `work_days` = Hari Terjadwal (dari kolom "Hari Terjadwal")

**Alasan Cuti Tidak Dihitung:**

- Cuti adalah hak karyawan yang sudah dijadwalkan
- Cuti tidak mencerminkan kehadiran fisik di tempat kerja
- Presentase kehadiran mengukur kehadiran AKTIF, bukan hak cuti

### Contoh Perhitungan

**Contoh 1: ADITYA (dari screenshot)**

- Hari Terjadwal: 24 hari
- On Time (H): 0, Berjalan (B): 5, Terlambat (T): 11, Cuti (C): 0, Sakit (S): 0, Izin (I): 0, Alpha (A): 8

```
Total Hadir = 0 + 5 + 11 + 0 + 0 = 16 hari
Hari Terjadwal = 24 hari

Presentase Kehadiran = (16 / 24) × 100 = 66.67% ≈ 67%
```

**Contoh 2: Dengan Cuti**

- Hari Terjadwal: 22 hari
- On Time (H): 18, Berjalan (B): 0, Terlambat (T): 2, Cuti (C): 2, Sakit (S): 0, Izin (I): 0, Alpha (A): 0

```
Total Hadir = 18 + 0 + 2 + 0 + 0 = 20 hari (Cuti tidak dihitung)
Hari Terjadwal = 22 hari

Presentase Kehadiran = (20 / 22) × 100 = 90.91% ≈ 91%
```

**Contoh 3: Dengan Sakit**

- Hari Terjadwal: 20 hari
- On Time (H): 15, Berjalan (B): 0, Terlambat (T): 3, Cuti (C): 0, Sakit (S): 1, Izin (I): 0, Alpha (A): 1

```
Total Hadir = 15 + 0 + 3 + 1 + 0 = 19 hari
Hari Terjadwal = 20 hari

Presentase Kehadiran = (19 / 20) × 100 = 95%
```

### Kategori Warna

```javascript
if (percentage >= 90)
    return "text-emerald-700"; // Excellent (hijau)
else if (percentage >= 75)
    return "text-blue-700"; // Good (biru)
else if (percentage >= 60)
    return "text-amber-700"; // Fair (kuning)
else return "text-rose-700"; // Poor (merah)
```

---

## 2. % TEPAT WAKTU (On-Time Percentage)

### Lokasi Kode

- **File**: `resources/js/Pages/Admin/AttendanceRecap/components/MonthlyStatusTab.vue`
- **Function**: `calculateOnTimePercentage(row)`

### Formula (TIDAK BERUBAH)

```javascript
// Total hadir (H + T)
const totalHadir = (row.recap.H || 0) + (row.recap.T || 0);

// Total on time (H)
const totalOnTime = row.recap.H || 0;

// Persentase tepat waktu = (Total On Time / Total Hadir) * 100
const percentage = (totalOnTime / totalHadir) * 100;
```

### Penjelasan Detail

**Pembilang (Numerator):**

- `H` = Hadir On Time (tepat waktu)

**Penyebut (Denominator):**

- Total Hadir = `H` + `T` (On Time + Terlambat)

**Catatan Penting:**

- Hanya menghitung dari hari-hari yang karyawan HADIR FISIK (baik tepat waktu maupun terlambat)
- Tidak termasuk: Alpha, Sakit, Izin, Cuti, atau Berjalan
- Ini mengukur KEDISIPLINAN dari hari-hari yang karyawan hadir

### Contoh Perhitungan

**Contoh 1:**

- Hadir On Time (H): 18 hari
- Terlambat (T): 2 hari

```
Total Hadir = 18 + 2 = 20 hari
Total On Time = 18 hari

% Tepat Waktu = (18 / 20) × 100 = 90%
```

**Contoh 2:**

- Hadir On Time (H): 15 hari
- Terlambat (T): 5 hari

```
Total Hadir = 15 + 5 = 20 hari
Total On Time = 15 hari

% Tepat Waktu = (15 / 20) × 100 = 75%
```

**Contoh 3: ADITYA (dari screenshot)**

- Hadir On Time (H): 0 hari
- Terlambat (T): 11 hari

```
Total Hadir = 0 + 11 = 11 hari
Total On Time = 0 hari

% Tepat Waktu = (0 / 11) × 100 = 0%
```

### Kategori Warna

```javascript
if (percentage >= 90)
    return "text-emerald-700"; // Excellent (hijau tua)
else if (percentage >= 75)
    return "text-green-700"; // Good (hijau)
else if (percentage >= 60)
    return "text-amber-700"; // Fair (kuning)
else return "text-rose-700"; // Poor (merah)
```

---

## 3. PERBEDAAN DENGAN EMPLOYEE SHOW PAGE

### Lokasi Kode

- **File**: `app/Http/Controllers/EmployeeController.php`
- **Method**: `show()`
- **Baris**: 754-761

### Formula di Employee Show Page

```php
// Rumus Skor Kehadiran:
// (Jumlah Hari Hadir + Cuti Sakit + Cuti Khusus + Cuti Tahunan yang disetujui) /
// Jumlah Hari Yang Telah dilalui tetapi yang ada shiftnya saja * 100
$numeratorKehadiran = $present + $sick + $specialLeaveDays + $annualLeaveDays;
$presentaseScorePresensi = $totalDates > 0 ? round(($numeratorKehadiran / $totalDates) * 100, 1) : 0;

// Rumus Skor Tepat Waktu:
// Jumlah Hari Hadir Tepat Waktu / Jumlah Hari Hadir * 100
$presentaseScoreOntime = $present > 0 ? round(($ontime / $present) * 100, 1) : 0;

// Rumus Skor Keseluruhan:
// (50% Skor Kehadiran + 50% Skor Tepat Waktu)
$presentaseAbsenMonth = round((0.5 * $presentaseScorePresensi) + (0.5 * $presentaseScoreOntime), 1);
```

### Perbandingan

| Aspek                    | Attendance Recap             | Employee Show                          |
| ------------------------ | ---------------------------- | -------------------------------------- |
| **Presentase Kehadiran** | TIDAK termasuk Cuti          | Termasuk Cuti (Sakit, Khusus, Tahunan) |
| **Penyebut**             | Hari Terjadwal (`work_days`) | Total Hari yang ada shiftnya           |
| **Filosofi**             | Kehadiran AKTIF              | Kehadiran + Hak Cuti                   |
| **Skor Keseluruhan**     | Tidak ada                    | Ada (50% Kehadiran + 50% Tepat Waktu)  |
| **Pembulatan**           | `Math.round()` (integer)     | `round(..., 1)` (1 desimal)            |

**Perbedaan Filosofi:**

- **Attendance Recap**: Mengukur kehadiran FISIK/AKTIF di tempat kerja
- **Employee Show**: Mengukur kehadiran + kepatuhan terhadap jadwal (termasuk cuti yang sah)

**Catatan**: Kedua formula ini BERBEDA dan itu WAJAR karena tujuannya berbeda:

- Attendance Recap untuk monitoring kehadiran harian
- Employee Show untuk evaluasi kinerja keseluruhan

---

## 4. KODE STATUS KEHADIRAN

### Mapping Status

```javascript
'H'  = Hadir On Time (COMPLETE)
'B'  = Berjalan/Running (RUNNING)
'T'  = Terlambat (status_masuk === 'TERLAMBAT')
'A'  = Alpha/Absen (ABSEN)
'C'  = Cuti (CUTI)
'S'  = Sakit (SAKIT)
'I'  = Izin Lainnya (IZIN)
'SH' = Shift (dijadwalkan tapi belum terjadi)
'-'  = Tidak dijadwalkan
```

### Logika Penentuan Status

Dari `AttendanceRecapController.php` method `monthlyStatus()`:

1. **Cek Shift**: Jika ada shift dan tanggal > hari ini → `SH`
2. **Cek Attendance**: Jika ada data attendance → gunakan status dari database
3. **Cek Terlambat**: Jika hadir, cek `status_masuk`:
    - `status_masuk === 'TERLAMBAT'` → `T`
    - `status_masuk === 'ON TIME'` → `H`
4. **Tidak Ada Data**: Jika dijadwalkan tapi tidak ada attendance → `A` (Alpha)

---

## 5. TESTING

### Test Case 1: Normal Case (ADITYA)

```
Input:
- Hari Terjadwal: 24
- H: 0, B: 5, T: 11, C: 0, S: 0, I: 0, A: 8

Expected Output:
- % Kehadiran: (16 / 24) × 100 = 67%
- % Tepat Waktu: (0 / 11) × 100 = 0%
```

### Test Case 2: Perfect Attendance

```
Input:
- Hari Terjadwal: 20
- H: 20, B: 0, T: 0, C: 0, S: 0, I: 0, A: 0

Expected Output:
- % Kehadiran: (20 / 20) × 100 = 100%
- % Tepat Waktu: (20 / 20) × 100 = 100%
```

### Test Case 3: With Leave (Cuti tidak dihitung)

```
Input:
- Hari Terjadwal: 22
- H: 18, B: 0, T: 2, C: 2, S: 0, I: 0, A: 0

Expected Output:
- % Kehadiran: (20 / 22) × 100 = 91% (Cuti tidak dihitung)
- % Tepat Waktu: (18 / 20) × 100 = 90%
```

### Test Case 4: With Sick Leave (Sakit dihitung)

```
Input:
- Hari Terjadwal: 20
- H: 15, B: 0, T: 3, C: 0, S: 1, I: 0, A: 1

Expected Output:
- % Kehadiran: (19 / 20) × 100 = 95% (Sakit dihitung)
- % Tepat Waktu: (15 / 18) × 100 = 83%
```

### Test Case 5: No Attendance

```
Input:
- Hari Terjadwal: 0
- H: 0, B: 0, T: 0, C: 0, S: 0, I: 0, A: 0

Expected Output:
- % Kehadiran: 0%
- % Tepat Waktu: 0%
```

---

## 6. RINGKASAN KEPUTUSAN BISNIS

### Yang DIHITUNG sebagai Hadir:

✅ H (On Time) - Hadir tepat waktu
✅ B (Berjalan) - Sedang berjalan (belum checkout)
✅ T (Terlambat) - Hadir tapi terlambat
✅ S (Sakit) - Sakit dengan surat keterangan
✅ I (Izin) - Izin yang disetujui

### Yang TIDAK DIHITUNG:

❌ A (Alpha) - Tidak hadir tanpa keterangan
❌ C (Cuti) - Cuti tidak dihitung dalam presentase kehadiran

### Alasan:

- **Cuti** adalah hak karyawan yang sudah dijadwalkan, bukan kehadiran aktif
- **Sakit** dan **Izin** adalah kehadiran yang sah dengan keterangan
- **Alpha** adalah ketidakhadiran tanpa alasan yang sah

---

## 7. CHANGELOG

### 2026-01-29 (Update 3 - FINAL)

- **KEPUTUSAN FINAL**: Cuti TIDAK dihitung dalam presentase kehadiran
- **Formula**: `(H + B + T + S + I) / work_days × 100`
- **Alasan**: Cuti adalah hak karyawan, bukan kehadiran aktif
- **Perbedaan dengan Employee Show**: Ini wajar karena tujuan berbeda

### 2026-01-29 (Update 2)

- Formula Presentase Kehadiran diperbaiki
- Menggunakan `work_days` sebagai penyebut
- Termasuk Cuti dalam perhitungan (REVERTED)

### 2026-01-29 (Update 1)

- Dokumentasi awal formula perhitungan persentase
- Identifikasi perbedaan dengan Employee Show page
- Menambahkan contoh perhitungan dan test cases
