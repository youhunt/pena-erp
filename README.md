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
  administrasi superadmin telah dibuat. Audit mutation/context dan pemblokiran
  context nonaktif sudah ditambahkan. Seeder simulasi multi-company dan menu
  workspace dinamis per role telah tersedia. Halaman Audit Trail serta revoke
  permission grant kini dapat digunakan oleh superadmin. Administrasi RBAC
  juga mendukung perubahan status role, pencabutan assignment user, dan
  pengelolaan mapping menu-permission. Layar akses dapat memprovision user
  login Shield aktif tanpa memberikan izin platform atau tenant otomatis,
  mengatur status login/password sementara, serta suspend/activate scope
  company dan branch. Password sementara memicu wajib ganti password,
  sedangkan perubahan credential atau deaktivasi akun mencabut session lama.
- Tahap 5 dimulai: master Inventory dan Warehouse tenant-scoped telah memiliki
  tabel UOM, kategori, produk, gudang, halaman kerja `/inventory`, permission
  view/manage per role, audit perubahan, dan data demo terpisah per company.
- Tahap 6 dimulai: functional menu pengguna telah dicatat sebagai roadmap
  resmi. Modul `Setup Master` menyediakan Transaction Code, Department,
  Currency, VAT dan Address Master; Inventory diperluas dengan Location,
  Item UoM Conversion, Item VAT, dan Batch Master.
- Tahap 7 dimulai: menu `Sales Master` dan `Purchasing Master` menyediakan
  Customer/Supplier Master, terms, relasi Address Master, serta promo dasar
  tenant-scoped dengan permission, audit trail dan data demo per company.

```bash
composer install
php spark serve
```

Buka `http://localhost:8080/`. File `.env` bersifat lokal/ignored dan menyimpan
konfigurasi database serta credential API development yang tidak boleh
dikomit.

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

Root HMVC tersedia di [Modules/README.md](Modules/README.md); modul bisnis
dibangun bertahap dari foundation aplikasi, dimulai dengan Inventory/Warehouse
setelah tenant context dan permission stabil.

Untuk development lokal, login tersedia pada `/login` dan satu akun platform
admin telah diprovision melalui Shield. Assignment role operasional tenant
sudah dimulai melalui membership/RBAC per company; permission transaksi akan
ditambahkan saat modul transaksi dibangun.

Pada menu `Administration > Akses User`, superadmin dapat membuat identitas
login aktif baru melalui Shield. Akun baru sengaja belum memiliki izin
platform maupun tenant sampai role company diberikan secara eksplisit; event
provisioning tersimpan di Audit Trail tanpa menyimpan password.
Status user Shield yang menjadi `inactive` langsung memutus context, sidebar,
dan permission tenant walaupun assignment role masih tersimpan. Suspend
membership company juga menonaktifkan branch switching; branch harus
diaktifkan kembali secara eksplisit sesudah membership company dibuka lagi.
Admin dapat menerbitkan password sementara dari layar akses; user diarahkan ke
`/account/security/password` pada login berikutnya dan harus login kembali
setelah menetapkan password final karena session sebelumnya dicabut.

### Simulasi Multi-Company dan Role

Untuk mengisi data demo yang idempotent pada environment development:

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
```

Seeder membuat tenant `PENA`, `NUSA`, `KARYA`, beberapa branch, role
operasional, mapping menu/permission, master inventory/gudang contoh, serta
akun uji berikut dengan password lokal yang sama: `Demo@Pena2026`.

| Email Demo | Simulasi Akses |
| --- | --- |
| `owner@demo.pena-erp.test` | Owner di tiga company, dapat mencoba company switching |
| `purchasing@demo.pena-erp.test` | Purchasing PENA |
| `warehouse@demo.pena-erp.test` | Warehouse PENA |
| `finance@demo.pena-erp.test` | Finance PENA dan NUSA |
| `sales@demo.pena-erp.test` | Sales NUSA |
| `manager@demo.pena-erp.test` | Manager KARYA |

Akun tersebut hanya untuk development/testing dan tidak boleh diprovision ke
database production.

Setelah login sebagai owner, manager, atau warehouse, menu `Inventory`
menampilkan produk/gudang untuk company aktif dan menyediakan form UOM,
kategori, produk, serta gudang. Role Purchasing dapat mengelola
`Purchasing Master` dan role Sales dapat mengelola `Sales Master` di tenant
masing-masing; inventory tetap hanya dapat diubah oleh role yang memiliki
`inventory.master.manage`.

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
| [08-multi-laptop-development-guide.md](docs/08-multi-laptop-development-guide.md) | Setup laptop baru, Git sync, migration/seeder, serta perlindungan secret/data lokal |
| [09-functional-menu-master-roadmap.md](docs/09-functional-menu-master-roadmap.md) | Pemetaan menu ERP operasional, target tabel, status implementasi dan urutan master/transaksi |
| [10-workbook-schema-gap-analysis.md](docs/10-workbook-schema-gap-analysis.md) | Pemetaan workbook referensi pengguna terhadap schema runtime dan backlog normalisasi |

## Bekerja dari Beberapa Laptop

GitHub menyimpan source, migration, seeder, asset, dan dokumentasi. GitHub
tidak menyimpan `.env`, token API, encryption key, database lokal, upload, log,
atau dump backup. Laptop kedua harus menjalankan migration dan seeder setelah
pull agar schema serta simulasi akses sama dengan laptop pertama.

Checklist lengkap tersedia di
[docs/08-multi-laptop-development-guide.md](docs/08-multi-laptop-development-guide.md).

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
