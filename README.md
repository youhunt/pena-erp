# Pena ERP Enterprise

Blueprint arsitektur ERP enterprise berbasis CodeIgniter 4 untuk multi-company,
multi-branch, workflow approval, dan AI-assisted document processing.

Repository ini berisi aplikasi dasar CodeIgniter `4.7.3` dan baseline desain
implementasi. Dokumen menjadi kontrak untuk membuat migration, module,
integrasi Skote, worker OCR/AI, dan deployment production.

## Status Implementasi

- Tahap 1 selesai: appstarter CodeIgniter `4.7.3`, dependency lock, namespace
  HMVC `Modules\`, dan environment development.
- Tahap 2 selesai: koneksi MySQL `pena_erp`, CodeIgniter Shield `v1.3.0`,
  migration identity/settings, CSRF session, registrasi publik nonaktif, dan
  auth provider boundary.
- Tahap 3 selesai untuk shell awal: asset Skote, login/magic-link, dashboard,
  dan navigasi terlindungi telah dihubungkan ke Shield.
- Tahap 4 berjalan: master wilayah global dengan import CSV/sinkronisasi API,
  company/branch CRUD, membership dan role/permission tenant awal beserta
  grant UI, workspace berizin dengan context branch aktif, serta layar
  administrasi superadmin telah dibuat.

```bash
composer install
php spark serve
```

Buka `http://localhost:8080/`. File `.env` bersifat lokal/ignored; sebelum
migration nanti isi konfigurasi MySQL/MariaDB pada file tersebut.

Testing bawaan CI4 menggunakan SQLite. Pada instalasi XAMPP lokal ini,
aktifkan `extension=sqlite3` di `php.ini` (saat ini `pdo_sqlite` sudah aktif).
Untuk test harian tanpa laporan coverage jalankan:

```bash
vendor/bin/phpunit --no-coverage
```

Tanpa mengubah `php.ini`, pemeriksaan sementara yang sudah diverifikasi dapat
dijalankan dengan:

```bash
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage
```

Perintah `composer test` bawaan menjalankan laporan coverage dan memerlukan
driver seperti Xdebug atau PCOV; ini cocok diaktifkan pada pipeline CI nanti.

Root HMVC tersedia di [Modules/README.md](Modules/README.md); module bisnis
akan dibuat secara bertahap saat migration dan acceptance criteria-nya dimulai.

Untuk development lokal, login tersedia pada `/login` dan satu akun platform
admin telah diprovision melalui Shield. Assignment role operasional tenant
sudah dimulai melalui membership/RBAC per company; permission transaksi akan
ditambahkan saat modul transaksi dibangun.

## Dokumen Blueprint

| Dokumen | Isi |
| --- | --- |
| [00-build-guide.md](docs/00-build-guide.md) | Jurnal implementasi per tahap, hasil verifikasi dan panduan menjalankan aplikasi |
| [01-enterprise-blueprint.md](docs/01-enterprise-blueprint.md) | Sasaran, keputusan stack, arsitektur, tenancy, RBAC, security, roadmap |
| [02-ai-document-processing.md](docs/02-ai-document-processing.md) | OCR/AI architecture, queue, mapping, validation, learning loop |
| [03-data-model-catalog.md](docs/03-data-model-catalog.md) | ERD, standar tabel, katalog data lengkap dan index strategy |
| [04-api-ui-business-flows.md](docs/04-api-ui-business-flows.md) | API, Skote UI, inventory/purchasing/accounting/approval flow |
| [05-ci4-implementation-guide.md](docs/05-ci4-implementation-guide.md) | HMVC structure, contoh migration, DTO, repository, service, filter, worker |
| [06-deployment-saas-operations.md](docs/06-deployment-saas-operations.md) | Ubuntu deployment, OCR service, queue, backup, CI/CD dan SaaS scaling |
| [07-requirement-traceability.md](docs/07-requirement-traceability.md) | Matriks cakupan 24 keluaran dan definition of ready/done |

## Keputusan Penting

- Basis framework ditargetkan `codeigniter4/framework:^4.7` dan wajib dipin
  melalui `composer.lock` setelah compatibility test.
- Skote digunakan sebagai presentation shell; komponen ERP tetap berada
  dalam module/domain terpisah.
- Authentication implementation telah dikunci pada `CodeIgniter Shield v1.3.0`;
  Shield migration telah dijalankan pada database development `pena_erp`.
- Shield menangani identity/session dan otorisasi platform. Role/permission
  tenant dinamis ERP tetap dibuat terpisah agar aman untuk multi-company.
- Semua tabel transaksional tenant membawa `company_id`; sebagian yang
  branch-scoped juga membawa `branch_id`. Tenant context tidak diterima mentah
  dari payload client.

## Referensi Terverifikasi

Referensi diperiksa pada 25 Mei 2026:

- CodeIgniter 4 repository/release: <https://github.com/codeigniter4/CodeIgniter4>
- CodeIgniter user guide: <https://codeigniter.com/user_guide/>
- CodeIgniter Shield: <https://shield.codeigniter.com/>
- Resource Skote lokal: `resources/skote/` dan asset runtime `public/assets/`
