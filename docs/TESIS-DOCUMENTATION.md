# SIPJLP - Sistem Informasi Pengelolaan PJLP
## Dokumentasi Lengkap untuk Laporan Tesis

---

## 1. GAMBARAN UMUM SISTEM

**SIPJLP** (Sistem Informasi Pengelolaan PJLP) adalah platform web berbasis Laravel yang dirancang untuk mengelola data, jadwal, dan laporan kerja Pekerja Jangka Panjang (PJLP) di RSUD Cipayung.

**URL:** http://localhost/sipjlp/public (development)

**Stack Teknologi:**
- Framework: Laravel 12
- Database: MySQL
- Frontend: Blade Template + Bootstrap
- Authentication: Spatie Permission (RBAC)
- Export: Maatwebsite Excel, DomPDF

---

## 2. SISTEM ROLE & PERMISSION

Sistem menggunakan Role-Based Access Control (RBAC) dengan 6 role utama:

### 2.1 Role dan Tanggung Jawab

| Role | Deskripsi | Unit | Akses |
|------|-----------|------|-------|
| **admin** | Administrator sistem | All | Akses penuh ke semua modul dan pengaturan |
| **manajemen** | Manajemen/Pimpinan | All | Laporan & rekap semua unit |
| **koordinator** | Koordinator unit CS/Security | CS/Security | Jadwal, validasi lembar kerja, rekap modul |
| **danru** | Deputy/Atasan langsung (Security) | Security | Validasi cuti anggota, lihat laporan unit |
| **chief** | Kepala security (Security) | Security | Final approval cuti, lihat rekap security |
| **pjlp** | Pekerja Jangka Panjang | CS/Security | Input data, absen, laporan, lihat jadwal sendiri |

### 2.2 Unit Kerja

Sistem terbagi 2 unit utama:

```
┌─────────────────────────────────┐
│   SIPJLP - Sistem Pengelolaan   │
└──────────────┬──────────────────┘
               │
        ┌──────┴──────┐
        │             │
    ┌───▼────┐   ┌────▼─────┐
    │   CS   │   │ Security  │
    │(Cleaning)│ │(Keamanan) │
    └────┬────┘   └────┬─────┘
         │             │
    Pj jaga Logbook   Patrol
    Lembar Kerja      Inspeksi
    Logbook           Pengawasan
    Absensi Selfie    Laporan Parkir
    Cuti              Cuti
    Absensi           Absensi
```

---

## 3. MODUL PER UNIT

### 3.1 MODUL CLEANING SERVICE (CS)

#### A. Jadwal
- **Akses**: Koordinator input, publikasi per minggu
- **Window Input**: Tgl 25–akhir bulan (input bulan depan), Tgl 1–5 (revisi bulan lalu)
- **Fitur**:
  - Input jadwal harian per PJLP
  - Pilih shift (Pagi, Siang, Malam)
  - Publikasi jadwal
  - Salin dari tanggal lain

#### B. Lembar Kerja CS (Konsep Baru 2026)
- **Akses**: PJLP input, Koordinator validasi/rekap
- **Input oleh PJLP**:
  - Tanggal kerja
  - Area pembersihan
  - Shift
  - Kegiatan periodik (checklist)
  - Kegiatan extra job (ad-hoc)
- **Rekap oleh Koordinator**:
  - Tabel per bulan: Petugas | Tanggal | Area | Shift | Periodik | Extra | Status
  - Export Excel/PDF ✅
  - Filter bulan, tahun, petugas

#### C. Logbook Limbah Domestik
- **Akses**: PJLP input, Koordinator rekap
- **Input**:
  - Tanggal
  - Berat limbah domestik (kg)
  - Berat kompos (kg)
  - Catatan
- **Rekap**: Per bulan, export Excel/PDF

#### D. Logbook B3 (Limbah Berbahaya)
- **Input**: Limbah Safety Box, Cair, Hepafilter, Non-Infeksius (kg)
- **Rekap**: Per bulan dengan filter area

#### E. Cleaning Hepafilter
- **Input**: Checklist ruangan pembersihan hepafilter
- **Rekap**: Per bulan, tabel checklist

#### F. Dekontaminasi
- **Input**: Tanggal, lokasi, catatan
- **Rekap**: Per bulan

#### G. Bank Sampah
- **Input**: Jenis sampah (kg): Plastik, Logam, Kertas, Organik, Gelas, dsb
- **Rekap**: Per bulan

#### H. Pengaturan Kegiatan LK CS
- **Master Data**: Input list kegiatan periodik & extra job
- **Akses**: Admin/Koordinator

#### I. Absensi Selfie
- **Fitur**: Foto selfie + GPS
- **Window**: Masuk (shift-1h s/d shift+1h), Pulang (shift+2h)
- **Status**: Hadir, Terlambat, Alpha

#### J. Cuti
- **Alur**: PJLP ajukan → Koordinator approve → Disetujui
- **Master**: Jenis cuti, durasi max per tahun

---

### 3.2 MODUL SECURITY

#### A. Jadwal Security
- **Akses**: Koordinator input (same window as CS)
- **Input**: PJLP, Tanggal, Shift, Lokasi (opsional)
- **Publikasi**: Per bulan
- **Salin dari tanggal**: Copy jadwal existing

#### B. Patrol Inspeksi
- **Akses**: PJLP input, Danru/Chief/Koordinator rekap
- **Input**:
  - Tanggal
  - Area patrol (Luar, Dalam, Parkir, etc)
  - Rekomendasi/catatan
- **Rekap**: Per bulan, tabel dengan filter area

#### C. Inspeksi Hydrant Outdoor
- **Input**: Per lokasi hydrant, checklist komponen (baik/buruk)
- **Lokasi**: Freon, Lobby, Basement, etc
- **Rekap**: Summary berapa item buruk per lokasi

#### D. Inspeksi Hydrant Indoor
- **Input**: Lokasi, status hydrant 1 & 2 (baik/buruk)
- **Rekap**: Per lokasi, status baik/buruk

#### E. Pengecekan APAR & APAB
- **Input**:
  - Lokasi (Building, Koridor, Ruang, etc)
  - Unit (Jenis APAR)
  - Kondisi (Baik/Cacat/Rusak)
  - Masa berlaku
- **Foto bukti**: Upload foto APAR
- **Rekap**: Per lokasi, masa berlaku

#### F. Pengawasan Proyek
- **Input**:
  - Tanggal
  - Nama proyek
  - Lokasi proyek
  - Pengawasan masalah/status
- **Rekap**: Per bulan

#### G. Laporan Parkir Menginap (NEW 2026)
- **Akses**: PJLP Security input (harus ada jadwal shift)
- **Input Form 1 - Roda 4**:
  - Jumlah kendaraan
  - Foto bukti (wajib 1-10 foto)
  - Catatan opsional
- **Input Form 2 - Roda 2**:
  - Jumlah kendaraan
  - Foto bukti (wajib 1-10 foto)
  - Catatan opsional
- **Rekap**: Per hari (admin/danru/chief view)
  - Tabel: Tanggal | Roda 4 | Roda 2 | Total
  - Detail per petugas per shift
  - Export Excel/PDF ✅

#### H. Jumat Sehat
- **Input**: PJLP input data kesehatan/olahraga (CS & Security)
- **Rekap**: Per bulan

#### I. Laporan Kecelakaan Kerja (K3)
- **Input**: Insiden, cedera, jenis bahaya, tindakan
- **Rekap**: Per bulan

#### J. Absensi Selfie (Sama dengan CS)
- **Window**: Masuk (shift-1h s/d shift+1h), Pulang (shift+2h)

#### K. Cuti (Dengan Approval Bertingkat)
- **Alur Anggota**:
  ```
  PJLP ajukan
  → Danru approve
  → Chief approve
  → Koordinator approve final
  → Disetujui
  ```
- **Alur Danru**: Danru ajukan → Chief → Koordinator → Disetujui
- **Alur Chief**: Chief ajukan → Koordinator → Disetujui

---

## 4. FITUR ABSENSI SELFIE

### 4.1 Alur Absen Masuk

```
PJLP buka /absen
    ↓
Sistem cek jadwal hari ini
    ├─ Tidak ada jadwal → Tampil "Tidak ada jadwal hari ini"
    └─ Ada jadwal → Lanjut
        ↓
    Cek window absen masuk (shift_start - 1h s/d shift_start + 1h)
        ├─ Belum buka → Info "Jam absen dibuka: XX:XX"
        ├─ Terbuka → Form masuk muncul
        │   ├─ Tombol aktivasi kamera
        │   ├─ Ambil foto selfie
        │   ├─ Preview foto
        │   └─ Submit → Simpan data + foto
        │       - jam_masuk = now()
        │       - foto_masuk = file.jpg
        │       - status = HADIR / TERLAMBAT
        │       - sumber_data = selfie
        └─ Sudah tutup → Info "Window absen masuk telah ditutup"
```

### 4.2 Alur Absen Pulang

```
Setelah absen masuk, window pulang (shift_end s/d shift_end + 2h)
    ├─ Belum buka → Info "Jam absen pulang dibuka: XX:XX"
    ├─ Terbuka → Form pulang muncul
    │   ├─ Ambil foto selfie
    │   └─ Submit → Update record
    │       - jam_pulang = now()
    │       - foto_pulang = file.jpg
    └─ Sudah tutup → Info "Window pulang telah ditutup"
```

### 4.3 Status Absensi

| Status | Kondisi | Aksi Sistem |
|--------|---------|------------|
| **hadir** | Absen masuk dalam window | Otomatis |
| **terlambat** | Absen masuk setelah shift_start | Otomatis |
| **alpha** | Tidak absen dalam window | Admin trigger: Menu Rekap Absensi → Mark Alpha |
| **cuti** | Approved leave | Sistem blok absensi, status = cuti |

### 4.4 Photo Upload Spec

- **Format**: JPG, PNG (max 5MB)
- **Lokasi simpan**: `storage/app/public/absensi-selfie/{YYYY-MM}/`
- **Accessible via**: `route('asset', 'storage/absensi-selfie/...')`

---

## 5. PROSES INPUT JADWAL

### 5.1 Jadwal CS - Workflow Koordinator

```
Step 1: Koordinator buka /jadwal-shift-cs
    - Lihat kalender per PJLP per bulan
    - Cek window: Tgl 25–akhir (input bulan depan), Tgl 1–5 (revisi lalu)
    - Jika outside window → Lihat status banner (merah)

Step 2: Klik cell tanggal → Modal muncul
    - Pilih PJLP
    - Pilih Shift (Pagi/Siang/Malam)
    - Submit → Simpan ke DB

Step 3: Publikasi jadwal
    - Sebelum publikasi: is_published = 0 (draft)
    - Klik tombol "Publikasikan" → is_published = 1
    - Petugas baru bisa lihat jadwal published

Step 4: Salin dari tanggal (Copy Helper)
    - Blok tanggal sumber
    - Klik "Salin dari..." → Pilih tanggal sumber
    - Otomatis copy semua jadwal sumber ke tanggal tujuan
```

### 5.2 Jadwal Security - Workflow Sama

Sama dengan CS, hanya tambahan: opsional pilih Lokasi (opsional, tidak wajib).

### 5.3 Admin Override Window

**Menu**: Pengaturan Sistem (Master Data)

```
┌─────────────────────────────────────┐
│ Window Input Jadwal CS & Security   │
├─────────────────────────────────────┤
│ Status: Otomatis (blue) ✓           │
│                                     │
│ ○ Otomatis (date-based logic)       │
│ ○ Paksa Buka (allow any month)      │
│ ○ Paksa Tutup (read-only)           │
│                                     │
│ [Simpan] [Tutup]                    │
└─────────────────────────────────────┘
```

**Logic**:
- **Otomatis**: Tgl 25–akhir = next month, Tgl 1–5 = prev month, else = closed
- **Paksa Buka**: Bisa input jadwal bulan apa saja
- **Paksa Tutup**: Semua jadwal read-only

---

## 6. EXPORT EXCEL & PDF

### 6.1 Fitur Export

Semua halaman rekap punya tombol di atas tabel:

```
┌─────────────────────────────────┐
│ [Excel] [PDF]                   │
└─────────────────────────────────┘
```

### 6.2 Modul yang Support Export

| Modul | URL Rekap | Export Route | Format |
|-------|-----------|--------------|--------|
| Lembar Kerja CS | `/lembar-kerja-cs/rekap` | `/export/lembar-kerja-cs?bulan=X&tahun=Y` | XLSX, PDF |
| Logbook Limbah | `/logbook-limbah/rekap` | `/export/logbook-limbah` | XLSX, PDF |
| Logbook B3 | `/logbook-b3/rekap` | `/export/logbook-b3` | XLSX, PDF |
| Hepafilter | `/logbook-hepafilter/rekap` | `/export/logbook-hepafilter` | XLSX, PDF |
| Dekontaminasi | `/logbook-dekontaminasi/rekap` | `/export/logbook-dekontaminasi` | XLSX, PDF |
| Bank Sampah | `/logbook-bank-sampah/rekap` | `/export/logbook-bank-sampah` | XLSX, PDF |
| Patrol Inspeksi | `/patrol-inspeksi/rekap` | `/export/patrol-inspeksi` | XLSX, PDF |
| Hydrant Outdoor | `/inspeksi-hydrant/rekap` | `/export/inspeksi-hydrant` | XLSX, PDF |
| Hydrant Indoor | `/inspeksi-hydrant-indoor/rekap` | `/export/inspeksi-hydrant-indoor` | XLSX, PDF |
| Pengecekan APAR | `/pengecekan-apar/rekap` | `/export/pengecekan-apar` | XLSX, PDF |
| Pengawasan Proyek | `/pengawasan-proyek/rekap` | `/export/pengawasan-proyek` | XLSX, PDF |
| **Laporan Parkir** | `/laporan-parkir/rekap` | `/export/laporan-parkir` | XLSX, PDF |

### 6.3 Cara Export

1. **Buka halaman rekap modul**, misal `/logbook-limbah/rekap`
2. **Filter bulan/tahun/search** (opsional)
3. **Klik tombol Excel** → Download file `.xlsx`
   - Atau **Klik tombol PDF** → Buka file PDF (target="_blank")

### 6.4 Struktur Export

#### Excel
- Header row (bold): Tanggal, Petugas, Area, Shift, Berat (kg), Catatan
- Data rows: Sesuai filter
- Footer: Total rows

#### PDF
- Title: Nama modul + Bulan Tahun
- Print date: "Dicetak pada: 19 Apr 2026, 08:15"
- Table: Landscape A4, header blue (#1a56db), alternating row colors
- Footer: Total baris + copyright SIPJLP

### 6.5 Template Tombol Export

```blade
@include('exports.partials.buttons', [
    'route'  => 'export.logbook-limbah',
    'params' => ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search],
])
```

---

## 7. USER FLOW DIAGRAM

### 7.1 Flow PJLP CS

```
┌─────────────────────────────────────┐
│ Login sebagai PJLP CS               │
└──────────────┬──────────────────────┘
               │
        ┌──────┴──────────┐
        │                 │
    ┌───▼────────────┐   ┌──▼─────────────┐
    │ Jadwal Saya    │   │ Absen Hari Ini  │
    │                │   │ (Selfie + GPS)  │
    │ - Lihat jadwal │   │                 │
    │   minggu ini   │   │ - Absen masuk   │
    │ - Export       │   │ - Absen pulang  │
    └────────────────┘   └────┬────────────┘
                              │
                ┌─────────────┴─────────────┐
                │                           │
            ┌───▼────────────┐   ┌──────────▼──┐
            │ Lembar Kerja   │   │ Cuti / Izin │
            │                │   │             │
            │ - Input LK     │   │ - Ajukan    │
            │ - Kegiatan     │   │ - Track     │
            │   periodik     │   │             │
            │ - Kegiatan     │   └─────────────┘
            │   extra job    │
            │ - Upload foto  │
            └────────────────┘
                     │
            ┌────────┴────────┐
            │                 │
        ┌───▼──────────┐  ┌───▼──────────────┐
        │ Logbook      │  │ Gerakan Jumat    │
        │ Limbah       │  │ Sehat             │
        │              │  │                  │
        │ - Input harian│  │ - Input data     │
        │ - Berat kg   │  │   kesehatan      │
        └──────────────┘  └──────────────────┘
```

### 7.2 Flow PJLP Security

```
┌─────────────────────────────────────┐
│ Login sebagai PJLP Security         │
└──────────────┬──────────────────────┘
               │
        ┌──────┴────────────────┐
        │                       │
    ┌───▼────────────┐   ┌──────▼─────────────┐
    │ Jadwal Saya    │   │ Absen Hari Ini      │
    │                │   │ (Selfie + GPS)      │
    │ - Lihat jadwal │   │                     │
    │   minggu ini   │   │ - Absen masuk       │
    │ - Export       │   │ - Absen pulang      │
    └────────────────┘   └──────┬──────────────┘
                                │
    ┌──────────────────┬────────┴────────┬──────────────────┐
    │                  │                 │                  │
┌───▼──────┐  ┌────────▼──────┐  ┌──────▼──────┐  ┌───────▼──┐
│Patrol    │  │Inspeksi       │  │Pengawasan   │  │Laporan   │
│Inspeksi  │  │Hydrant        │  │Proyek       │  │Parkir    │
│          │  │               │  │             │  │          │
│-Input    │  │-Area patrol   │  │-Nama proyek │  │-Roda 4   │
│ report   │  │-Komponen OK/  │  │-Lokasi      │  │ jumlah   │
│         │  │ BURUK         │  │-Status      │  │-Foto 1-10│
│          │  │-Checklist     │  │             │  │-Catatan  │
└──────────┘  └───────────────┘  └─────────────┘  │          │
                     │                             │-Roda 2   │
                ┌────┴────┐                        │ jumlah   │
                │          │                        │-Foto 1-10│
            ┌───▼───┐  ┌───▼────┐                  │-Catatan  │
            │Hydrant│  │Pengecekan                 └──────────┘
            │Indoor │  │APAR&APAB
            │       │  │
            │-Lokasi│  │-Lokasi
            │-H1 OK │  │-Unit
            │-H2 OK │  │-Kondisi
            │       │  │-Masa berlaku
            │       │  │-Foto APAR
            └───────┘  └────────┘
                     │
        ┌────────────┴─────────────┐
        │                          │
    ┌───▼──────────┐   ┌──────────▼──┐
    │Cuti          │   │Gerakan      │
    │(Bertingkat)  │   │Jumat Sehat  │
    │              │   │             │
    │1. Ajukan     │   │-Input data  │
    │2. Danru OK   │   │ kesehatan   │
    │3. Chief OK   │   │             │
    │4. Koord OK   │   └─────────────┘
    │5. Disetujui  │
    └──────────────┘
```

### 7.3 Flow Koordinator CS

```
┌────────────────────────────────────────────────┐
│ Login sebagai Koordinator CS                   │
└────────────────┬─────────────────────────────┘
                 │
    ┌────────────┴──────────────┐
    │                           │
┌───▼──────────────┐   ┌───────▼─────────────┐
│Jadwal CS         │   │Master Data          │
│                  │   │                     │
│-Input jadwal     │   │-Kegiatan LK CS      │
│ harian per PJLP  │   │-Lokasi              │
│-Publikasi        │   │-Pengaturan Sistem   │
│-Salin dari       │   │ (Window Override)   │
│ tanggal lain     │   │                     │
│-Export jadwal    │   └─────────────────────┘
└────┬─────────────┘
     │
     ├──────┬──────┬──────┬──────┬──────┬──────┐
     │      │      │      │      │      │      │
┌────▼─┐┌───▼──┐┌─▼──┐┌──▼──┐┌───▼┐┌──▼──┐┌──▼───┐
│Lembar│ │Logb.│ │Logb│ │Hepa │ │Deko │ │Bank  │ │Gerakan│
│Kerja │ │Lim  │ │B3  │ │filter│ │tamin│ │Sampah│ │Jumat  │
│CS    │ │Domes│ │     │      │ │    │ │      │ │Sehat  │
│      │ │tik  │ │     │      │ │    │ │      │ │       │
│-View │ │     │ │     │      │ │    │ │      │ │       │
│ list │ │     │ │     │      │ │    │ │      │ │       │
│-Valid│ │     │ │     │      │ │    │ │      │ │       │
│ate  │ │     │ │     │      │ │    │ │      │ │       │
│-Rekap│ │     │ │     │      │ │    │ │      │ │       │
│ tabel│ │     │ │     │      │ │    │ │      │ │       │
│-Expor│ │     │ │     │      │ │    │ │      │ │       │
│t     │ │     │ │     │      │ │    │ │      │ │       │
└──────┘ └─────┘ └─────┘      └─────┘ └──────┘ └───────┘
```

### 7.4 Flow Koordinator Security

```
┌────────────────────────────────────────────────┐
│ Login sebagai Koordinator Security             │
└────────────────┬─────────────────────────────┘
                 │
    ┌────────────┴──────────────┐
    │                           │
┌───▼──────────────┐   ┌───────▼─────────────┐
│Jadwal Security   │   │Master Data          │
│                  │   │                     │
│-Input jadwal     │   │-Lokasi              │
│-Publikasi        │   │-Pengaturan Sistem   │
│-Salin dari       │   │ (Window Override)   │
│                  │   │                     │
└────┬─────────────┘   └─────────────────────┘
     │
     ├──────┬──────┬──────┬──────┬──────┬──────────┐
     │      │      │      │      │      │          │
┌────▼─┐┌───▼──┐┌─▼──┐┌──▼──┐┌───▼┐┌──▼──┐┌──────▼─┐
│Patrol│ │Hydra │ │Hydra│ │APAR │ │Penga│ │Laporan │ │Gerakan│
│Insp. │ │ntOUT │ │ntIN │ │&APAB│ │wasan│ │Parkir  │ │Jumat  │
│      │ │      │ │     │      │ │Proy │ │        │ │Sehat  │
│-Lihat│ │      │ │     │      │ │ek   │ │        │ │       │
│ rekap│ │      │ │     │      │ │     │ │        │ │       │
│ tabel│ │      │ │     │      │ │     │ │        │ │       │
│-Expor│ │      │ │     │      │ │     │ │        │ │       │
│t     │ │      │ │     │      │ │     │ │        │ │       │
└──────┘ └──────┘ └─────┘      └─────┘ └───────┘ └───────┘
  │        │        │            │        │          │
  └────────┴────────┴────────────┴────────┴──────────┘
           Filter: Bulan, Tahun, Search
           [Excel] [PDF]
```

### 7.5 Flow Admin/Manajemen

```
┌────────────────────────────────────────────────┐
│ Login sebagai Admin / Manajemen                │
└────────────────┬─────────────────────────────┘
                 │
    ┌────────────┴──────────────────┐
    │                               │
┌───▼──────────────────┐   ┌───────▼─────────────┐
│ALL Modul Rekap       │   │Master Data          │
│                      │   │                     │
│-CS Modul (7)         │   │-PJLP                │
│-Security Modul (11)  │   │-Shift               │
│-Absensi              │   │-Lokasi              │
│-Cuti                 │   │-User                │
│-Audit Log            │   │-Roles & Permissions│
│-Export semua ke      │   │-Pengaturan Sistem   │
│ Excel/PDF            │   │                     │
│                      │   │-JenisCuti           │
└──────────────────────┘   └─────────────────────┘
```

---

## 8. STRUKTUR DATABASE RINGKAS

### 8.1 Tabel Utama

```sql
-- User & Auth
users              -- Email, password, telegram_chat_id
roles              -- admin, koordinator, pjlp, danru, chief
permissions        -- absensi.view-self, jadwal.manage, etc

-- Master Data
pjlp               -- Data pekerja (id, nama, unit, user_id)
shifts             -- Shift kerja (Pagi, Siang, Malam)
lokasi             -- Lokasi area kerja
jenis_cuti         -- Jenis cuti (cuti tahunan, sakit, dll)

-- Jadwal
jadwal_shift_cs    -- Jadwal CS (pjlp_id, shift_id, tanggal, is_published)
jadwal             -- Jadwal Security (pjlp_id, shift_id, tanggal, is_published)

-- Absensi
absensi            -- Data absen (pjlp_id, jam_masuk, jam_pulang, foto_masuk, foto_pulang, status, sumber_data)

-- Cuti
cuti               -- Permohonan cuti (pjlp_id, tgl_mulai, tgl_selesai, status, approved_by, dll)

-- CS Modul
lembar_kerja_cs    -- Lembar kerja CS (pjlp_id, shift_id, tanggal)
logbook_limbah     -- Limbah domestik (pjlp_id, berat_domestik, berat_kompos)
logbook_b3         -- Limbah B3 (pjlp_id, safety_box_kg, cair_kg, hepa_kg, dll)
logbook_hepafilter -- Checklist hepafilter per ruangan
logbook_dekontaminasi
logbook_bank_sampah

-- Security Modul
jadwal_security    -- Jadwal security (pjlp_id, shift_id, lokasi_id, tanggal)
patrol_inspeksi    -- Patrol (pjlp_id, shift_id, area, rekomendasi)
inspeksi_hydrant   -- Hydrant outdoor (pjlp_id, shift_id, lokasi, komponen_data)
inspeksi_hydrant_indoor
pengecekan_apar    -- APAR check (pjlp_id, lokasi, unit, kondisi, masa_berlaku)
pengawasan_proyek  -- Proyek (pjlp_id, nama_proyek, lokasi)
laporan_parkir     -- Parkir (pjlp_id, shift_id, jenis, jumlah_kendaraan, tanggal)
laporan_parkir_foto -- Foto parkir (laporan_parkir_id, path)

-- Other
app_settings       -- Konfigurasi (jadwal_window_override, value)
audit_log          -- Log aktivitas (user_id, action, model, changes)
```

---

## 9. TABEL REKAP FITUR

| Fitur | Input oleh | Dilihat oleh | Export | Notes |
|-------|-----------|--------------|--------|-------|
| Jadwal CS | Koordinator | PJLP, Koord, Admin | Ya | Window: tgl 25-akhir, 1-5 |
| Jadwal Security | Koordinator | PJLP, Koord, Admin | Ya | Same window as CS |
| Lembar Kerja CS | PJLP CS | Koord, Admin | Ya | Kegiatan periodik & extra |
| Logbook Limbah | PJLP CS | Koord, Admin | Ya | Per lokasi area |
| Logbook B3 | PJLP CS | Koord, Admin | Ya | Per jenis limbah |
| Hepafilter | PJLP CS | Koord, Admin | Ya | Checklist ruangan |
| Dekontaminasi | PJLP CS | Koord, Admin | Ya | Tanggal & lokasi |
| Bank Sampah | PJLP CS | Koord, Admin | Ya | Per jenis sampah kg |
| Patrol Inspeksi | PJLP Sec | Danru, Chief, Koord, Admin | Ya | Per area |
| Hydrant OUT | PJLP Sec | Danru, Chief, Koord, Admin | Ya | Per lokasi + komponen |
| Hydrant IN | PJLP Sec | Danru, Chief, Koord, Admin | Ya | Per lokasi |
| APAR & APAB | PJLP Sec | Danru, Chief, Koord, Admin | Ya | Per lokasi + masa berlaku |
| Pengawasan Proyek | PJLP Sec | Danru, Chief, Koord, Admin | Ya | Per proyek |
| Laporan Parkir | PJLP Sec | Danru, Chief, Koord, Admin | Ya | Roda 4 & Roda 2 + foto |
| Jumat Sehat | PJLP CS/Sec | Koord, Admin | - | Data kesehatan |
| Laporan Kecelakaan | PJLP/Koord | Admin | - | K3 report |
| Absensi | PJLP (selfie) | Koord, Admin | - | Foto + GPS |
| Cuti | PJLP | Danru, Chief, Koord | - | Approval bertingkat |
| Audit Log | Sistem | Admin | - | Tracking aktivitas |

---

## 10. PERMISSION & ROLE MAPPING

```
┌─────────────┬────────────────────────────────────────────┐
│ Role        │ Permissions                                │
├─────────────┼────────────────────────────────────────────┤
│ admin       │ * (akses semua)                            │
├─────────────┼────────────────────────────────────────────┤
│ manajemen   │ absensi.view-all, jadwal.view,             │
│             │ logbook.view-all, cuti.view-all            │
├─────────────┼────────────────────────────────────────────┤
│ koordinator │ jadwal.manage, jadwal-cs.manage,           │
│             │ lembar-kerja-cs.validate,                  │
│             │ logbook.view-unit, absensi.view-unit,      │
│             │ cuti.approve, cuti.view-unit               │
├─────────────┼────────────────────────────────────────────┤
│ danru       │ cuti.view-unit, cuti.approve (danru only), │
│             │ laporan.view-unit                          │
├─────────────┼────────────────────────────────────────────┤
│ chief       │ cuti.view-unit, cuti.approve (chief only), │
│             │ laporan.view-unit (security)               │
├─────────────┼────────────────────────────────────────────┤
│ pjlp        │ absensi.view-self, cuti.create,            │
│             │ cuti.view-self, lembar-kerja.create        │
└─────────────┴────────────────────────────────────────────┘
```

---

## 11. TEKNOLOGI & ARSITEKTUR

### 11.1 Tech Stack

| Layer | Teknologi | Versi |
|-------|-----------|--------|
| Framework | Laravel | 12 |
| Database | MySQL | 8.0+ |
| Auth | Spatie Permission | v6 |
| Export | Maatwebsite Excel | v3.11 |
| PDF | DomPDF | v2 |
| Frontend | Blade + Bootstrap | 5 |
| Frontend Icons | Tabler Icons | v3 |

### 11.2 Arsitektur MVC

```
App/
├── Http/
│   ├── Controllers/
│   │   ├── CutiController.php
│   │   ├── LembarKerjaCsController.php
│   │   ├── JadwalShiftCsController.php
│   │   ├── JadwalSecurityController.php
│   │   ├── ExportController.php (PDF/Excel)
│   │   ├── LaporanParkirController.php
│   │   └── ... (18 controllers total)
│   ├── Requests/ (Form Validation)
│   │   ├── StoreCutiRequest.php
│   │   ├── StoreLembarKerjaCsRequest.php
│   │   └── StoreLaporanParkirRequest.php
│   └── Middleware/
│       └── RestrictToShiftHours.php
├── Models/
│   ├── Cuti.php
│   ├── LembarKerjaCs.php
│   ├── Jadwal.php
│   ├── LaporanParkir.php
│   ├── Absensi.php
│   └── ... (20 models total)
├── Services/
│   ├── AbsensiSelfieService.php (Logika absen)
│   ├── TelegramService.php (Notif)
│   └── ...
├── Enums/
│   ├── StatusCuti.php (menunggu, disetujui, dll)
│   ├── StatusAbsensi.php (hadir, terlambat, alpha)
│   ├── UnitType.php (cleaning, security)
│   └── ...
└── Policies/
    ├── CutiPolicy.php
    ├── LembarKerjaCsPolicy.php
    └── ...

resources/views/
├── lembar-kerja-cs/
│   ├── index.blade.php (list)
│   ├── show.blade.php (detail)
│   └── rekap.blade.php (rekap + export)
├── laporan-parkir/
│   ├── index.blade.php (form roda 4 & 2)
│   └── rekap.blade.php (rekap + export)
├── logbook-*/
│   ├── index.blade.php (input form)
│   └── rekap.blade.php (tabel + export)
├── exports/
│   ├── pdf/table.blade.php (template PDF)
│   └── partials/buttons.blade.php (tombol export)
└── layouts/
    ├── app.blade.php (layout utama)
    └── sidebar.blade.php (menu navigasi)

database/
├── migrations/
│   ├── 2026_04_16_000001_reset_lembar_kerja_cs_new_concept.php
│   ├── 2026_04_16_000002_create_app_settings_table.php
│   ├── 2026_04_17_000001_make_lokasi_id_nullable_in_jadwal_table.php
│   └── 2026_04_17_000002_create_laporan_parkir_table.php
└── seeders/
    ├── RolePermissionSeeder.php
    └── DanruChiefRoleSeeder.php
```

---

## 12. WORKFLOW OVERVIEW (RINGKAS)

### Input Data Flow

```
PJLP Input
    ↓
Validasi FormRequest
    ↓
Save ke Database
    ↓
Store Photo/File → storage/public/{modul}/{YYYY-MM}/
    ↓
Audit Log (user_id, action, model, changes)
    ↓
(Opsional) Notif Telegram ke Koordinator/Danru
```

### Rekap & Export Flow

```
Koordinator buka /modul/rekap
    ↓
Filter bulan, tahun, search
    ↓
Query data dari DB + relationships
    ↓
GROUP BY tanggal / unit / kategori
    ↓
Tampil tabel + stats
    ↓
Klik [Excel] atau [PDF]
    ↓
ExportController.method()
    ├─ Excel: Convert to XLSX (Maatwebsite)
    └─ PDF: Render Blade → PDF (DomPDF)
    ↓
Download file / Buka browser
```

### Approval Flow (Cuti)

```
PJLP Ajukan Cuti
    ├─ CS → [MENUNGGU] → Koordinator OK → [DISETUJUI]
    │
    └─ Security → [MENUNGGU_DANRU] → Danru OK
                  → [MENUNGGU_CHIEF] → Chief OK
                  → [MENUNGGU_KOORDINATOR] → Koordinator OK
                  → [DISETUJUI]

Status Change → Notif Telegram + Audit Log
```

---

## 13. PANDUAN TESTING MODUL

### Test Scenario: Input & Export Logbook Limbah

```
1. Login: koordinator-cs@sipjlp.id (pass: password)
2. Buka: /logbook-limbah/rekap
3. Filter: Bulan = April, Tahun = 2026
4. Lihat tabel data
5. Klik [Excel] → Download file → Buka di Spreadsheet
6. Klik [PDF] → Buka tab baru → Print preview
7. Verifikasi: Header, data, total, footer
```

### Test Scenario: Input & Validasi Lembar Kerja CS

```
1. Login: pjlp-cs@sipjlp.id (PJLP yang punya jadwal hari ini)
2. Buka: /lembar-kerja-cs
3. Klik [+ Input Baru]
4. Pilih tanggal, area, shift
5. Checklist kegiatan periodik (minimal 1)
6. Add kegiatan extra job (opsional)
7. Submit → Simpan
8. Login: koordinator-cs@sipjlp.id
9. Buka: /lembar-kerja-cs/rekap
10. Lihat status "Menunggu Validasi"
11. Klik eye icon → Validasi / Tolak
```

### Test Scenario: Absen Selfie

```
1. Login: pjlp-security@sipjlp.id (punya jadwal hari ini)
2. Buka: /absen
3. Lihat shift aktif: "Pagi (06:00 – 14:00)"
4. Check window: Dalam window masuk?
   - Ya → Muncul form foto masuk
5. Klik [Aktifkan Kamera]
6. Allow browser permission → Kamera aktif
7. Klik [Ambil Foto]
8. Preview muncul, klik [Lanjut]
9. Upload form + validasi
10. Submit → Foto + GPS + jam_masuk tersimpan
11. Tunggu jam pulang
12. Repeat step 3-9 untuk pulang (tab berbeda)
```

---

## 14. DEPLOYMENT & MAINTENANCE

### Server Setup Requirements

```
- Apache 2.4+
- PHP 8.1+ (rekomendasikan 8.2)
- MySQL 8.0+
- Composer
- Git
```

### Production Deployment

```
1. Clone repo: git clone https://github.com/dinoanns/sipjlp.git
2. cd sipjlp
3. composer install --no-dev --optimize-autoloader
4. cp .env.example .env
5. php artisan key:generate
6. php artisan migrate --force
7. php artisan db:seed --force
8. php artisan storage:link
9. chmod -R 775 storage/ bootstrap/cache/
10. Restart Apache: sudo systemctl restart apache2
```

### Regular Maintenance

```
- Daily: Monitor error logs (storage/logs/laravel.log)
- Weekly: Backup database
- Monthly: Review audit logs
- Quarterly: Update dependencies (composer update)
```

---

## 15. KESIMPULAN

SIPJLP adalah sistem terintegrasi yang mengelola seluruh aspek kegiatan PJLP:

✅ **Absensi modern**: Selfie + GPS (tidak perlu mesin absen)
✅ **Jadwal fleksibel**: Window input controllable, publikasi terpisah
✅ **Laporan lengkap**: 18 modul, semua bisa export Excel/PDF
✅ **Approval bertingkat**: Cuti ada flow danru→chief→koordinator
✅ **Audit trail**: Semua aktivitas tercatat di audit_log
✅ **Multi-role**: RBAC penuh untuk admin, koordinator, danru, chief, PJLP
✅ **Mobile-friendly**: Absensi selfie bisa via browser mobile

---

**Dokumentasi ini dibuat untuk mendukung laporan Tesis mengenai implementasi sistem manajemen PJLP berbasis web.**

*Generated: 19 April 2026*
*Versi: 1.0*
