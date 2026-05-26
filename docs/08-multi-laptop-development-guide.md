# Multi-Laptop Development and Git Sync Guide

Dokumen ini menjadi checklist ketika proyek Pena ERP dikerjakan dari lebih
dari satu laptop. Tujuannya sederhana: source code, migration, seeder, dan
dokumentasi ikut GitHub; secret dan isi database lokal tidak ikut tersalin
secara tidak sengaja.

## 1. Apa yang Disimpan di GitHub

| Item | Ikut Git | Cara Tersinkron |
| --- | --- | --- |
| Source CI4, controller, service, view, route | Ya | Commit lalu push/pull |
| Migration database | Ya | Jalankan `php spark migrate --all` setelah pull |
| Seeder foundation dan data simulasi | Ya | Jalankan seeder pada database laptop baru |
| Dokumen `.md` dan keputusan arsitektur | Ya | Commit bersama perubahan code |
| Template konfigurasi `env` tanpa secret | Ya | Copy menjadi `.env` lokal |
| `.env`, token API, encryption key, password DB | Tidak | Diisi manual pada setiap laptop |
| Database MySQL lokal dan dump backup | Tidak | Dibuat ulang dari migration/seeder atau dipindah privat |
| Dataset bisnis/manual yang tidak ada di seeder | Tidak otomatis | Export/import privat bila benar-benar diperlukan |

Aturan kerja tim: sebuah perubahan dianggap bisa dipakai di laptop lain hanya
setelah source, migration/seeder yang relevan, dan pembaruan dokumentasi telah
di-commit serta di-push ke GitHub.

## 2. Bootstrap Laptop Baru dari GitHub

Prasyarat lokal:

- PHP `8.2+`, Composer, MySQL/MariaDB, dan ekstensi PHP yang dibutuhkan CI4.
- Database kosong bernama `pena_erp` atau nama lokal lain yang dipilih.
- `sqlite3` aktif untuk menjalankan automated tests.

Langkah setup:

```bash
git clone https://github.com/youhunt/pena-erp.git
cd pena-erp
composer install
```

Pada PowerShell, buat konfigurasi lokal:

```powershell
Copy-Item env .env
```

Edit `.env` lokal dengan konfigurasi development, database, dan credential API
yang tersedia pada workstation tersebut. Minimal:

```dotenv
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = pena_erp
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

regions.apiBaseUrl = 'https://api-wilayah.belajardisiniaja.com'
regions.apiToken =
```

Generate encryption key berbeda pada setiap lingkungan development:

```bash
php spark key:generate
```

Bangun schema dan data demo:

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
php spark serve
```

Buka `http://localhost:8080/login`. Seeder demo bersifat idempotent sehingga
aman dijalankan kembali setelah pull jika definisi role/menu demo berkembang.

## 3. Sinkronisasi Master Wilayah

Seeder demo hanya memastikan alamat bootstrap minimum tersedia. Untuk dataset
wilayah development yang lebih lengkap, jalankan salah satu metode berikut
setelah `.env` API telah diisi secara lokal:

```bash
php spark regions:sync-api development-api-YYYY-MM-DD
```

Atau gunakan sumber CSV versioned yang telah disetujui:

```bash
php spark regions:import <directory-csv> <source_version>
```

Token API tidak dicatat di dokumen, source code, commit, atau dump yang
dibagikan. Versi sumber wilayah dicatat pada command supaya isi master dapat
diaudit dan diulang.

## 4. Data Demo yang Dapat Direproduksi

Seeder `MultiCompanyDemoSeeder` mengisi data simulasi berikut:

| Jenis | Data |
| --- | --- |
| Company | `PENA`, `NUSA`, `KARYA` |
| User demo | Owner, Purchasing, Warehouse, Finance, Sales, Manager |
| Role/menu | Role per-company dan menu workspace sesuai permission |
| Setup master | Transaction Code, Department, Address, Currency dan VAT per company |
| Inventory master | Item, Warehouse/Location, UoM Conversion dan Item VAT per company |
| Commercial master | Customer/Supplier, Terms, Profile Policy, VAT/Warehouse default, Address link dan Promo dasar per company |
| Pengujian utama | Owner pindah company; Purchasing/Finance melihat menu berbeda; isolasi master tenant |

Password akun demo development tercatat di `README.md`. Seeder ini menolak
dijalankan pada environment production.

## 5. Workflow Harian Dua Laptop

Sebelum mulai coding pada laptop mana pun:

```bash
git status
git pull origin main
composer install
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
```

Sesudah menyelesaikan satu unit kerja:

```bash
git status
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
git add <file-source> <file-migration-seeder> <file-dokumentasi>
git commit -m "<ringkasan perubahan>"
git push origin main
```

Jika ada perubahan struktur data, jangan hanya mengubah database dari GUI:
buat migration. Jika membutuhkan data referensi atau simulasi berulang, buat
atau perbarui seeder. Jika keputusan desain berubah, perbarui dokumen `.md`
pada commit yang sama.

## 6. Hal yang Tidak Boleh Dipindahkan Melalui Git

- `.env` dan seluruh credential lokal.
- Token API wilayah atau credential AI/OCR di masa depan.
- Backup database pada `writable/backups/`.
- Upload dokumen, hasil OCR, log runtime, dan session lokal.
- Dump data real customer/supplier/dokumen tanpa prosedur keamanan tersendiri.

Apabila laptop kedua membutuhkan data development manual yang tidak tersedia
melalui seeder, transfer dump secara privat dan terkontrol, bukan melalui
repository GitHub.
