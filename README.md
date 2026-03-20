# SIPJLP - Sistem Informasi Pengelolaan PJLP
## RSUD Cipayung Jakarta Timur

### Deskripsi
Sistem informasi untuk mengelola:
- PJLP Security
- PJLP Cleaning Service

### Tech Stack
- **Framework**: Laravel 11
- **Database**: MySQL
- **Frontend**: Blade + Bootstrap 5 + Tabler Admin
- **Auth**: Laravel + Spatie Permission (RBAC)
- **Export**: Laravel Excel, DomPDF

### Instalasi

1. **Buat Database MySQL**
   ```sql
   CREATE DATABASE sipjlp;
   ```

2. **Konfigurasi .env**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sipjlp
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Jalankan Migration & Seeder**
   ```bash
   php artisan migrate --seed
   ```

4. **Buat Storage Link**
   ```bash
   php artisan storage:link
   ```

5. **Jalankan Server**
   ```bash
   php artisan serve
   ```

   Atau via XAMPP: `http://localhost/sipjlp/public`

### Akun Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sipjlp.rsudcipayung.id | password |
| Manajemen | manajemen@sipjlp.rsudcipayung.id | password |
| Koordinator Security | koordinator.security@sipjlp.rsudcipayung.id | password |
| Koordinator CS | koordinator.cs@sipjlp.rsudcipayung.id | password |
| PJLP Security | ahmad.security@sipjlp.rsudcipayung.id | password |
| PJLP CS | siti.cleaning@sipjlp.rsudcipayung.id | password |

### Modul

1. **Dashboard** - Role-specific dashboard
2. **Data PJLP** - Manajemen data PJLP
3. **Absensi** - Integrasi dengan mesin absensi (ODBC)
4. **Jadwal Kerja** - Penjadwalan shift
5. **Pengajuan Cuti** - Workflow pengajuan & persetujuan
6. **Lembar Kerja Harian** - Daily worksheet dengan foto bukti
7. **Laporan** - Rekap & export PDF/Excel
8. **Master Data** - Shift, Jenis Cuti, Lokasi
9. **Manajemen User** - CRUD user dengan RBAC
10. **Audit Log** - Tracking aktivitas user

### Role & Permissions

| Role | Akses |
|------|-------|
| PJLP | Dashboard, Absensi (pribadi), Jadwal (pribadi), Ajukan Cuti, Isi Lembar Kerja |
| Koordinator | Data PJLP (unit), Validasi Cuti, Validasi Lembar Kerja, Laporan (unit) |
| Admin | Semua fitur, Master Data, User Management, Audit Log |
| Manajemen | Dashboard, Laporan (read-only), Export |

### Alur Pengajuan Cuti

1. PJLP mengajukan cuti melalui form
2. Tanggal permohonan otomatis dari server (read-only)
3. Field: Jenis cuti, Alasan, No. Telp, Periode (bisa > 1 hari)
4. Jumlah hari dihitung otomatis
5. Status awal: MENUNGGU
6. Data terkunci setelah submit
7. Koordinator menyetujui/menolak
8. Jika menolak, wajib isi alasan penolakan

### Integrasi Mesin Absensi (ODBC)

Data absensi dari mesin dalam format MS Access (.mdb/.accdb) dikonversi via ODBC ke MySQL.

1. Setup ODBC Data Source di Windows (nama: AbsensiMesin)
2. Jalankan command sync:
   ```bash
   php artisan absensi:sync --date=2024-01-15
   ```

### Struktur Folder

```
app/
├── Console/Commands/    # Artisan commands
├── Enums/              # Status enums
├── Http/Controllers/   # Controllers
├── Models/             # Eloquent models
├── Policies/           # Authorization policies
└── Providers/          # Service providers

database/
├── migrations/         # Database migrations
└── seeders/           # Database seeders

resources/views/
├── layouts/           # Base layouts
├── auth/              # Auth views
├── dashboard/         # Dashboard views
├── pjlp/              # PJLP CRUD views
├── cuti/              # Cuti views
├── lembar-kerja/      # Lembar kerja views
├── master/            # Master data views
└── laporan/           # Report views
```

### Dokumentasi

| Dokumen | Keterangan |
|---------|------------|
| [docs/security-hardening.md](docs/security-hardening.md) | Catatan lengkap perubahan authorization, FormRequest, dan audit logging |
| [docs/merge-guide.md](docs/merge-guide.md) | Panduan merge fitur baru dari project lain tanpa menghilangkan security hardening |

### Standar Pengembangan

Setiap fitur baru wajib memenuhi:
1. **Route** — dilindungi `can:permission.name` middleware
2. **Validasi** — menggunakan FormRequest (bukan inline `$request->validate()`)
3. **Audit** — setiap operasi mutasi memanggil `AuditLog::log()`

Lihat [docs/security-hardening.md](docs/security-hardening.md) untuk detail dan contoh.

### Pengembangan Selanjutnya

Sistem ini dirancang modular dan scalable untuk dikembangkan ke SIMRS di masa depan.

### License
Proprietary - RSUD Cipayung Jakarta Timur
