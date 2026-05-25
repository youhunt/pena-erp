# Build Guide: Dari Blueprint ke Aplikasi

Dokumen ini adalah jurnal implementasi hidup. Setiap tahap mengambil acuan dari
blueprint, mencatat apa yang dibuat di source code, cara verifikasi, dan
keputusan yang harus diambil sebelum tahap berikutnya.

## Cara Menggunakan Dokumentasi

| Kebutuhan Saat Coding | Baca / Perbarui |
| --- | --- |
| Menentukan arah modul atau security boundary | `01-enterprise-blueprint.md` |
| Membuat migration atau model | `03-data-model-catalog.md` |
| Membuat controller/route/UI flow | `04-api-ui-business-flows.md` |
| Menyalin pola class CI4 | `05-ci4-implementation-guide.md` |
| Memproses dokumen/OCR/AI | `02-ai-document-processing.md` |
| Deploy dan scaling | `06-deployment-saas-operations.md` |
| Memastikan requirement terpenuhi | `07-requirement-traceability.md` |

Aturan kerja: ketika implementasi berbeda dari blueprint, jangan membiarkan
keduanya menyimpang. Revisi dokumen dan source code dalam task yang sama atau
catat keputusan sebagai perubahan yang masih menunggu approval.

## Progress Tahap

| Tahap | Fokus | Status |
| --- | --- | --- |
| 1 | Bootstrap CI4 dan environment development | Selesai, 25 Mei 2026 |
| 2 | Authentication provider dan base security | Selesai, 25 Mei 2026 |
| 3 | Velzon shell dan module foundation | Belum dimulai |
| 4 | Tenant/company/branch/RBAC migrations | Belum dimulai |
| 5+ | Domain transaction, workflow, AI/OCR, deploy | Belum dimulai |

## Tahap 1: Bootstrap CI4

### Acuan Blueprint

- `01-enterprise-blueprint.md`: target backend dan module boundary.
- `05-ci4-implementation-guide.md`: PSR-4 `Modules\` dan HMVC root.
- `07-requirement-traceability.md`: scope bahwa aplikasi awal harus dibuat
  sebelum domain ERP dapat diimplementasikan.

### Yang Sudah Dibuat

- CodeIgniter appstarter/framework dikunci pada versi `4.7.3`.
- Struktur standar `app/`, `public/`, `tests/`, `writable/`, `spark`,
  `composer.json` dan `composer.lock`.
- Identitas package `pena/erp`.
- PSR-4 root `Modules\` dan petunjuk module pada `Modules/README.md`.
- `.env` lokal dengan `CI_ENVIRONMENT=development`, URL server development dan
  encryption key yang digenerate oleh Spark. File ini tidak masuk Git.
- `.gitignore` standar CI4 untuk menjaga secret, vendor dan runtime file.

### Menjalankan Aplikasi

```bash
composer install
php spark serve
```

Buka `http://localhost:8080/`.

### Verifikasi Tahap 1

Perintah yang telah berhasil:

```bash
composer validate --no-check-publish --strict
php spark env
php spark namespaces
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage
```

HTTP smoke test juga berhasil dengan status `200` terhadap route `/`.

Catatan workstation: file `E:\Master\xampp\php\php.ini` memiliki
`pdo_sqlite` aktif tetapi `sqlite3` masih dikomentari. Aktifkan
`extension=sqlite3` untuk menjalankan test SQLite tanpa opsi `-d`; aktifkan
Xdebug/PCOV hanya saat laporan code coverage dibutuhkan.

## Tahap 2: Database dan Authentication Security

### Keputusan

- Database development menggunakan MySQL/MariaDB database `pena_erp` dengan
  konfigurasi lokal di `.env`.
- CodeIgniter Shield `v1.3.0` menjadi identity/session provider resmi proyek,
  menggantikan rencana compatibility Myth/Auth.
- Group/permission Shield hanya untuk scope platform; role operasional tenant
  akan menggunakan membership/RBAC ERP agar setiap `company_id` terisolasi.
- Registrasi publik dimatikan. Akun pertama dibuat melalui proses admin/CLI
  setelah kebijakan user onboarding disepakati.

### Yang Sudah Dibuat

- Dependency `codeigniter4/shield:^1.3` dan `codeigniter4/settings` terpasang.
- Konfigurasi Shield dipublish: `Auth.php`, `AuthGroups.php`, `AuthToken.php`,
  route auth, helper autoload dan session-based CSRF.
- Migration Shield/Settings dijalankan ke `pena_erp`, menghasilkan identity,
  login/token/group/permission dan settings storage resmi package.
- CSRF diaktifkan secara global dan public registration route dihapus.
- `App\Auth\AuthGatewayInterface` dan `ShieldAuthGateway` disediakan sebagai
  boundary modul ERP terhadap provider auth.

### Verifikasi Tahap 2

```bash
php spark migrate:status --all
php spark db:table users
php spark db:table auth_identities
php spark routes
```

Migration Shield dan Settings tercatat pada batch pertama; Spark berhasil
membaca database `pena_erp` sebagai koneksi default.

## Input Untuk Tahap 3

Tahap berikutnya menyiapkan application shell: landing/dashboard terlindungi,
login branding sementara atau asset Velzon bila sudah tersedia, dan
navigation/layout dasar sebelum migration tenant ERP dimulai.
