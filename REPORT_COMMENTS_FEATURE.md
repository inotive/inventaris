# Fitur History Komentar Laporan (Read-Only)

## Deskripsi

Fitur ini memungkinkan user untuk **melihat history komentar** pada setiap laporan melalui web dashboard. Fitur ini bersifat **read-only** - user hanya bisa melihat komentar yang ada, sedangkan untuk menambah, edit, atau hapus komentar dilakukan melalui **mobile app**.

## Fitur yang Tersedia

### 1. Melihat History Komentar ✅

- Tampil di modal detail laporan
- Menampilkan semua komentar dengan informasi:
    - Nama pembuat komentar
    - Waktu pembuatan (relatif: "5 menit yang lalu", "2 jam yang lalu", dll)
    - Isi komentar
    - Avatar/inisial pembuat komentar

### 2. UI/UX Read-Only ✅

- Tidak ada form input untuk menambah komentar
- Tidak ada tombol edit/hapus
- Info footer yang menjelaskan bahwa aksi dilakukan di mobile app
- Empty state yang informatif

### 3. Real-time Display ✅

- Data komentar diambil fresh setiap kali modal dibuka
- Menampilkan jumlah total komentar di header

## Aksi yang TIDAK Tersedia di Web Dashboard

- ❌ Menambah komentar baru
- ❌ Mengedit komentar
- ❌ Menghapus komentar

> **Catatan**: Semua aksi CRUD komentar dilakukan melalui mobile app. Web dashboard hanya untuk viewing/monitoring.

## Struktur Database

### Tabel `report_comments`

```sql
- id (bigint, primary key)
- report_id (bigint, foreign key ke reports.id)
- user_id (bigint, foreign key ke users.id)
- parent_id (bigint, nullable, untuk reply - siap untuk future)
- content (text, isi komentar)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, untuk soft delete)
```

### Relasi

- `ReportComment` belongsTo `Report`
- `ReportComment` belongsTo `User`
- `Report` hasMany `ReportComment`

## API Endpoints (Backend Tetap Lengkap)

### GET `/api/v1/reports/{reportId}/comments`

Mengambil semua komentar untuk laporan tertentu

- **Digunakan oleh**: Web dashboard (read-only)
- Response: Array komentar dengan data user
- Sorting: Terbaru di atas

### POST `/api/v1/reports/{reportId}/comments`

Menambah komentar baru

- **Digunakan oleh**: Mobile app only

### PUT `/api/v1/reports/{reportId}/comments/{commentId}`

Mengedit komentar

- **Digunakan oleh**: Mobile app only

### DELETE `/api/v1/reports/{reportId}/comments/{commentId}`

Menghapus komentar (soft delete)

- **Digunakan oleh**: Mobile app only

### GET `/api/v1/reports/{reportId}/comments/{commentId}`

Mengambil detail komentar tertentu

- **Digunakan oleh**: Mobile app only

## Komponen Vue

### `ReportComments.vue` (Read-Only Version)

Komponen untuk menampilkan komentar (view-only)

- Props: `reportId` (Number, required)
- Features:
    - List komentar dengan scroll
    - Loading state
    - Empty state dengan info mobile app
    - Format tanggal relatif
    - Avatar dengan inisial

### Integrasi di `Reports/Index.vue`

- Ditambahkan di modal detail laporan
- Lebar modal diperbesar dari 720px ke 900px
- Posisi: Setelah Meta Info, sebelum Footer

## Permissions

### Web Dashboard Users

- Bisa melihat semua komentar
- Tidak bisa melakukan aksi apapun

### Mobile App Users

- Bisa melihat semua komentar
- Bisa menambah komentar baru
- Bisa edit komentar sendiri
- Bisa hapus komentar sendiri
- Admin bisa hapus komentar siapa saja

## UI/UX Features

### Design

- Konsisten dengan design system aplikasi
- Avatar menggunakan inisial nama user
- Clean, minimal interface untuk viewing
- Responsive design

### Informasi untuk User

- Header menampilkan jumlah total komentar
- Empty state menjelaskan cara menambah komentar (via mobile app)
- Footer info menjelaskan bahwa aksi dilakukan di mobile app

### Accessibility

- Proper semantic HTML
- Screen reader friendly
- Color contrast compliance

## Error Handling

### Frontend

- Network errors: Silent fail dengan console log
- Graceful degradation jika API tidak tersedia

### Backend

- API tetap lengkap untuk mobile app
- Comprehensive logging
- Proper HTTP status codes

## Performance

### Frontend

- Minimal JavaScript (hanya fetch data)
- No complex state management
- Efficient rendering dengan Vue 3

### Backend

- Efficient queries dengan eager loading
- Database indexes pada foreign keys
- Ready untuk pagination jika diperlukan

## Testing

### Manual Testing Checklist

- [ ] Buka modal detail laporan
- [ ] Lihat section "History Komentar"
- [ ] Verifikasi tidak ada form input
- [ ] Verifikasi tidak ada tombol edit/hapus
- [ ] Cek empty state message
- [ ] Cek info footer tentang mobile app
- [ ] Test responsive design
- [ ] Verifikasi format tanggal

## Deployment Notes

### Database Migration

- Migration sudah ada dan berjalan
- Data sample sudah tersedia

### Frontend Build

- Komponen sudah di-build
- No additional dependencies
- Compatible dengan existing setup

### Mobile App Integration

- API endpoints siap untuk mobile app
- Authentication & authorization sudah disetup
- CRUD operations tersedia lengkap

## Future Enhancements

### 1. Real-time Updates

- WebSocket untuk update otomatis ketika ada komentar baru dari mobile
- Push notifications ke web dashboard

### 2. Advanced Filtering

- Filter komentar berdasarkan user
- Filter berdasarkan tanggal
- Search dalam komentar

### 3. Export Features

- Export komentar ke PDF/Excel
- Include dalam laporan export

### 4. Analytics

- Statistik komentar per laporan
- Most active commenters
- Comment trends

## Troubleshooting

### Komentar tidak muncul

1. Cek network tab untuk API calls
2. Verify API routes di `routes/api.php`
3. Cek database connection
4. Pastikan ada data di tabel `report_comments`

### UI tidak sesuai

1. Clear browser cache
2. Rebuild assets: `npm run build`
3. Cek CSS conflicts

### Performance issues

1. Monitor database queries
2. Check for N+1 query problems
3. Consider pagination untuk laporan dengan banyak komentar

## Summary

Fitur ini memberikan **visibility** ke history komentar laporan melalui web dashboard, sambil menjaga **separation of concerns** dimana:

- **Web Dashboard**: Read-only monitoring & viewing
- **Mobile App**: Full CRUD operations & user interactions

Ini memberikan fleksibilitas untuk admin/supervisor untuk monitor diskusi tanpa mengacaukan workflow mobile app.
