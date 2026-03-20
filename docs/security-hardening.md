# Security Hardening — SIPJLP

Dokumen ini mencatat semua perubahan yang dilakukan dalam sesi security hardening.
Tujuan: menutup celah authorization, validasi, dan audit logging yang ditemukan saat audit.

---

## Priority 1 — Authorization

### 1.1 UserPolicy (BARU)
**File:** `app/Policies/UserPolicy.php`

Policy baru untuk melindungi semua operasi CRUD pada User.
Semua method mengecek permission `user.manage`.
Method `delete` juga mencegah user menghapus akun dirinya sendiri.

**Registrasi:** ditambahkan di `app/Providers/AppServiceProvider.php`

```php
Gate::policy(User::class, UserPolicy::class);
```

### 1.2 UserController — authorize()
**File:** `app/Http/Controllers/UserController.php`

Ditambahkan `$this->authorize()` ke semua 7 method:

| Method | Authorize call |
|--------|---------------|
| index | `$this->authorize('viewAny', User::class)` |
| create | `$this->authorize('create', User::class)` |
| store | `$this->authorize('create', User::class)` |
| show | `$this->authorize('view', $user)` |
| edit | `$this->authorize('update', $user)` |
| update | `$this->authorize('update', $user)` |
| destroy | `$this->authorize('delete', $user)` |

### 1.3 Controller lain (sudah terlindungi di route level)
Controller berikut sudah punya `can:` middleware di `routes/web.php` sejak sebelumnya:

| Controller | Route Middleware |
|---|---|
| MasterAreaCsController | `can:master.manage` |
| MasterPekerjaanCsController | `can:cs.pekerjaan.manage` |
| JadwalShiftCsController | `can:jadwal-cs.manage` |
| JadwalKerjaCsBulananController | `can:jadwal-cs.manage` |

---

## Priority 2 — FormRequest

Semua inline `$request->validate()` dipindahkan ke FormRequest class.
Folder baru: `app/Http/Requests/`

| FormRequest | Digunakan di |
|---|---|
| `StoreUserRequest` | `UserController@store` |
| `UpdateUserRequest` | `UserController@update` |
| `StorePjlpRequest` | `PjlpController@store` |
| `UpdatePjlpRequest` | `PjlpController@update` |
| `StoreCutiRequest` | `CutiController@store` |
| `RejectCutiRequest` | `CutiController@reject` |
| `AddLembarKerjaDetailRequest` | `LembarKerjaController@addDetail` |
| `RejectLembarKerjaRequest` | `LembarKerjaController@reject` |
| `StoreMasterAreaCsRequest` | `MasterAreaCsController@store` |
| `UpdateMasterAreaCsRequest` | `MasterAreaCsController@update` |
| `StoreMasterPekerjaanCsRequest` | `MasterPekerjaanCsController@store` |
| `UpdateMasterPekerjaanCsRequest` | `MasterPekerjaanCsController@update` |
| `UpdateJadwalShiftCsRequest` | `JadwalShiftCsController@update` |
| `BulkUpdateJadwalShiftCsRequest` | `JadwalShiftCsController@bulkUpdate` |
| `CopyJadwalShiftCsRequest` | `JadwalShiftCsController@copyFromDate` |
| `StoreJadwalKerjaCsBulananRequest` | `JadwalKerjaCsBulananController@store` |
| `UpdateJadwalKerjaCsBulananRequest` | `JadwalKerjaCsBulananController@update` |
| `CopyJadwalKerjaCsBulananRequest` | `JadwalKerjaCsBulananController@copy` |
| `BulkCopyJadwalKerjaCsBulananRequest` | `JadwalKerjaCsBulananController@bulkCopy` |
| `PullAbsenRequest` | `TarikAbsenController@pull` |
| `MapBadgeRequest` | `TarikAbsenController@mapBadge` |
| `SummaryAbsenRequest` | `TarikAbsenController@summary` |

### Aturan untuk controller baru
Setiap controller baru yang memiliki method `store` atau `update` **wajib** menggunakan FormRequest, bukan inline `$request->validate()`.

---

## Priority 3 — Audit Logging

Ditambahkan `AuditLog::log()` pada semua operasi mutasi yang belum tercatat.

| Controller | Method | Aktivitas yang dicatat |
|---|---|---|
| `MasterAreaCsController` | store | `'Menambah area CS'` |
| `MasterAreaCsController` | update | `'Update area CS'` |
| `MasterAreaCsController` | destroy | `'Menghapus area CS'` |
| `MasterPekerjaanCsController` | store | `'Menambah master pekerjaan CS'` |
| `MasterPekerjaanCsController` | update | `'Update master pekerjaan CS'` |
| `MasterPekerjaanCsController` | destroy | `'Menghapus master pekerjaan CS'` |
| `MasterPekerjaanCsController` | toggleStatus | `'Toggle status pekerjaan CS'` |
| `JadwalShiftCsController` | update | `'Update jadwal shift CS'` |
| `JadwalShiftCsController` | bulkUpdate | `'Bulk update jadwal shift CS'` |
| `JadwalShiftCsController` | copyFromDate | `'Copy jadwal shift CS (N entri)'` |
| `JadwalKerjaCsBulananController` | store | `'Menambah jadwal kerja CS bulanan'` |
| `JadwalKerjaCsBulananController` | update | `'Update jadwal kerja CS bulanan'` |
| `JadwalKerjaCsBulananController` | destroy | `'Menghapus jadwal kerja CS bulanan'` |
| `JadwalKerjaCsBulananController` | copy | `'Copy jadwal kerja CS bulanan'` |
| `JadwalKerjaCsBulananController` | bulkCopy | `'Bulk copy jadwal kerja CS bulanan (N entri)'` |
| `TarikAbsenController` | mapBadge | `'Map badge X ke PJLP Y'` |
| `LembarKerjaController` | addDetail | `'Menambah detail lembar kerja'` |
| `LembarKerjaController` | deleteDetail | `'Menghapus detail lembar kerja'` |

### Aturan untuk fitur baru
Setiap operasi yang mengubah data (create, update, delete, approve, reject) **wajib** memanggil `AuditLog::log()`.

Format standar:
```php
// Create
AuditLog::log('Menambah X', $model, null, $model->toArray());

// Update
$dataLama = $model->toArray();
$model->update($data);
AuditLog::log('Update X', $model, $dataLama, $model->fresh()->toArray());

// Delete
$dataLama = $model->toArray();
$model->delete();
AuditLog::log('Menghapus X', null, $dataLama, null);
```

---

## Checklist untuk Fitur Baru

Setiap developer yang menambahkan fitur baru wajib memastikan:

- [ ] Setiap route mutasi dilindungi `can:permission.name` middleware di `web.php`
- [ ] Setiap method store/update menggunakan FormRequest (bukan inline `$request->validate()`)
- [ ] Setiap operasi mutasi memanggil `AuditLog::log()`
- [ ] Policy baru diregistrasikan di `AppServiceProvider`
