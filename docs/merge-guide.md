# Panduan Merge Project Teman

Dokumen ini menjelaskan cara mengambil fitur baru dari project teman dan menggabungkannya
ke project ini tanpa menghilangkan perubahan security hardening yang sudah ada.

---

## Konsep

Project ini punya dua sumber perubahan:
- **Project kamu** (project ini) — sudah ada security hardening (authorization, FormRequest, audit log)
- **Project teman** — punya fitur baru yang belum ada di project ini

Tujuan merge: ambil fitur baru dari teman, tapi tetap pertahankan security hardening di sini.

---

## Persiapan (Sekali Saja)

### 1. Pastikan project kamu sudah punya git

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/sipjlp
git status
```

Jika belum ada git, jalankan:
```bash
git init
git add .
git commit -m "Initial commit"
```

### 2. Minta project teman di-push ke GitHub/GitLab

Project teman harus tersimpan di remote repository (GitHub, GitLab, dll).
Minta URL repository-nya, contoh: `https://github.com/teman/sipjlp.git`

---

## Langkah Merge

### Step 1 — Tambahkan remote project teman

```bash
git remote add teman https://github.com/teman/sipjlp.git
```

Verifikasi:
```bash
git remote -v
```

### Step 2 — Fetch perubahan teman (tanpa langsung merge)

```bash
git fetch teman
```

Ini hanya mengambil data, belum mengubah apapun di project kamu.

### Step 3 — Lihat apa saja yang berubah di project teman

```bash
# Lihat daftar file yang berbeda
git diff HEAD teman/main --name-only

# Lihat detail perubahan per file (opsional)
git diff HEAD teman/main -- app/Http/Controllers/SomeController.php
```

### Step 4 — Buat branch khusus untuk merge

```bash
git checkout -b merge/fitur-dari-teman
```

### Step 5 — Merge project teman

```bash
git merge teman/main --no-commit --no-ff
```

Flag `--no-commit` artinya merge dilakukan tapi belum di-commit, sehingga kamu bisa
review dulu sebelum finalisasi.

### Step 6 — Selesaikan konflik (jika ada)

Konflik biasanya terjadi di file yang diubah kedua belah pihak.
Jalankan untuk melihat file yang konflik:

```bash
git status
```

File yang konflik akan ditandai `both modified`.

Buka file tersebut, cari marker konflik:
```
<<<<<<< HEAD
// Kode kamu (dengan security hardening)
=======
// Kode teman (fitur baru)
>>>>>>> teman/main
```

**Aturan saat menyelesaikan konflik:**

| File | Prioritas |
|------|-----------|
| `app/Http/Controllers/*.php` | Gabungkan: ambil fitur teman + pertahankan `$this->authorize()` dan `AuditLog::log()` |
| `app/Http/Requests/` | Pertahankan semua FormRequest yang sudah ada, tambahkan milik teman jika ada yang baru |
| `app/Policies/` | Pertahankan semua Policy yang sudah ada |
| `app/Providers/AppServiceProvider.php` | Gabungkan: pastikan semua `Gate::policy()` tetap ada |
| `routes/web.php` | Gabungkan: pastikan semua `can:` middleware tetap ada |
| `database/migrations/` | Ambil semua migration baru dari teman |
| `resources/views/` | Biasanya aman diambil dari teman (tidak ada security logic di views) |

### Step 7 — Cek file penting tidak kehilangan security

Setelah merge, verifikasi file-file kritis:

```bash
# Pastikan authorize() masih ada di UserController
grep -n "authorize" app/Http/Controllers/UserController.php

# Pastikan AuditLog masih ada
grep -rn "AuditLog::log" app/Http/Controllers/

# Pastikan FormRequest masih dipakai
grep -rn "FormRequest" app/Http/Controllers/

# Pastikan policies masih terdaftar
grep -n "Gate::policy" app/Providers/AppServiceProvider.php
```

### Step 8 — Jalankan migration jika ada yang baru

```bash
/Applications/XAMPP/bin/php artisan migrate
```

### Step 9 — Test aplikasi

Buka `http://localhost/sipjlp/public` dan cek:
- [ ] Login berhasil
- [ ] Fitur lama masih berjalan
- [ ] Fitur baru dari teman bisa diakses

### Step 10 — Commit hasil merge

```bash
git add .
git commit -m "Merge fitur baru dari teman - [deskripsi singkat fitur]"
```

---

## Jika Teman Belum Pakai Git

Jika project teman dalam bentuk folder ZIP atau folder biasa:

### Cara alternatif: copy manual

1. **Identifikasi file baru/berubah dari teman**

   Minta teman untuk memberitahu file apa saja yang berubah/ditambahkan.
   Atau bandingkan secara manual.

2. **Copy file baru yang tidak ada di project kamu**

   File baru (controller baru, migration baru, view baru) bisa langsung di-copy
   karena tidak ada konflik.

3. **Untuk file yang ada di kedua project, gabungkan manual**

   Buka file dari teman dan file kamu secara berdampingan.
   Ambil bagian fitur baru dari teman, tapi jangan hapus:
   - `$this->authorize()` calls
   - `AuditLog::log()` calls
   - FormRequest usage
   - `can:` middleware di routes

4. **Jalankan migration baru**

   ```bash
   /Applications/XAMPP/bin/php artisan migrate
   ```

---

## File yang TIDAK BOLEH Dioverwrite Begitu Saja

File berikut mengandung security hardening — jangan di-replace langsung dengan versi teman,
harus digabungkan secara manual:

| File | Alasan |
|------|--------|
| `app/Http/Controllers/UserController.php` | Sudah ada `authorize()` di semua method |
| `app/Http/Controllers/MasterAreaCsController.php` | Sudah ada audit log |
| `app/Http/Controllers/MasterPekerjaanCsController.php` | Sudah ada audit log |
| `app/Http/Controllers/JadwalShiftCsController.php` | Sudah ada audit log |
| `app/Http/Controllers/JadwalKerjaCsBulananController.php` | Sudah ada audit log |
| `app/Http/Controllers/LembarKerjaController.php` | Sudah ada audit log di addDetail/deleteDetail |
| `app/Http/Controllers/TarikAbsenController.php` | Sudah ada audit log di mapBadge |
| `app/Providers/AppServiceProvider.php` | Sudah ada registrasi UserPolicy |
| `routes/web.php` | Sudah ada `can:` middleware di route groups |

---

## Checklist Setelah Merge

- [ ] `php artisan route:list` tidak ada error
- [ ] Login berhasil dengan semua role
- [ ] Fitur baru dari teman berjalan
- [ ] Audit log tercatat saat ada operasi mutasi
- [ ] User tanpa permission tidak bisa akses halaman yang dibatasi
