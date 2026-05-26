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
| Setup laptop kedua dan Git sync aman | `08-multi-laptop-development-guide.md` |
| Membandingkan workbook spesifikasi lama dengan schema baru | `10-workbook-schema-gap-analysis.md` |

Aturan kerja: ketika implementasi berbeda dari blueprint, jangan membiarkan
keduanya menyimpang. Revisi dokumen dan source code dalam task yang sama atau
catat keputusan sebagai perubahan yang masih menunggu approval.

Aturan sinkronisasi: GitHub menyimpan code, migration, seeder, dan dokumen.
Database development, `.env`, API token, encryption key, upload dan dump lokal
tidak masuk Git; laptop lain mereproduksi fondasi dengan migration/seeder dan
mengisi secret secara lokal menurut `08-multi-laptop-development-guide.md`.

## Progress Tahap

| Tahap | Fokus | Status |
| --- | --- | --- |
| 1 | Bootstrap CI4 dan environment development | Selesai, 25 Mei 2026 |
| 2 | Authentication provider dan base security | Selesai, 25 Mei 2026 |
| 3 | Skote shell dan module foundation | Selesai untuk shell awal, 25 Mei 2026 |
| 4 | Tenant/company/branch/RBAC migrations | Berjalan: CRUD + context switch + RBAC UI awal, 25 Mei 2026 |
| 5 | Inventory Master dan Warehouse | Berjalan: master tenant + UI, 26 Mei 2026 |
| 6 | Functional Menu dan Setup Master | Berjalan: setup/reference master + UI, 26 Mei 2026 |
| 7 | Commercial Master Customer/Supplier | Berjalan: terms/address/promo dasar + UI, 26 Mei 2026 |
| 8 | Commercial Enrichment dari Workbook | Berjalan: profile/VAT/warehouse/PIC/limit + mailing address, 26 Mei 2026 |
| 9+ | Domain transaction, workflow, AI/OCR, deploy | Belum dimulai |

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

## Tahap 3: Skote Application Shell

### Yang Sudah Dibuat

- Resource sumber Skote ditempatkan di `resources/skote/` dan asset runtime
  tersedia dari `public/assets/`.
- Halaman login dan magic-link memakai branding Pena/Skote serta kontrak field,
  route dan error handling CodeIgniter Shield.
- Route `/` kini menuju dashboard shell Skote dan dilindungi filter session.
- Dashboard awal hanya menampilkan menu yang sudah memiliki route aktif; menu
  domain ditambahkan setelah migration dan authorization policy tersedia.
- Feature test mencakup tampilan login/magic-link dan redirect dashboard bagi
  pengunjung anonim, dengan migration Settings/Shield pada database test.

### Verifikasi Tahap 3 Saat Ini

```bash
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage
```

## Tahap 4: Foundation Master dan Tenant

### Yang Sudah Dibuat

- Migration aplikasi membuat reference global `provinces`, `regencies`,
  `districts`, `villages`, serta tabel foundation `companies` dan `branches`.
- Migration aplikasi membuat session database `ci_sessions`, membership
  `user_company_memberships`/`user_branch_memberships`, dan tenant RBAC
  `roles`, `permissions`, `role_permissions`, `user_roles`.
- Migration kompatibilitas menambahkan kolom master yang dibutuhkan UI baru
  pada database development lama tanpa menghapus akun, company, atau grant
  yang telah dibuat; dump pra-migration disimpan lokal dan di-ignore Git.
- Migration audit `CreateAuditLogs` mempertahankan tabel audit yang telah ada
  atau membuat tabel baru pada instalasi bersih.
- `created_by`/`updated_by` mengikuti key Shield `users.id` (`INT UNSIGNED`);
  `company_id` dan master domain tetap menggunakan `BIGINT UNSIGNED`.
- Seeder development membuat satu jalur alamat bootstrap DKI Jakarta,
  `PT Pena Inovasi Sistem`, dan `Jakarta Head Office`. Data wilayah bootstrap
  ini dipakai sebelum atau ketika sinkronisasi API belum dijalankan.
- Superadmin dapat membuka menu `Company`, `Branch`, `Master Wilayah`, dan
  `Akses User`; company dan branch sudah dapat ditambah/diedit.
- Seed memberi administrator development membership `PENA`, role tenant
  `Owner`, serta permission `company.dashboard.view` dan
  `company.master.manage`. Halaman `/workspace/{companyId}` membuktikan akses
  tenant melalui membership dan role; akses tanpa grant ditolak `403`.
- Membership branch sekarang dapat diberikan dari layar `Akses User`.
  `/workspace` menyediakan pemilih company/branch aktif dan menyimpan context
  tervalidasi pada session; navbar dan dashboard menampilkan scope aktif.
- Layar `Role & Permission` dapat membuat role dan permission dinamis per
  company serta memberikan grant. Backend menolak grant lintas-company dan
  pengujian membuktikan permission baru dapat dipakai oleh user yang ditugaskan.
- Tabel `menus` dan `menu_permissions` kini digunakan oleh
  `TenantMenuService` untuk merender sidebar serta placeholder modul sesuai
  role/permission pada context company yang sedang aktif.
- Seeder development `MultiCompanyDemoSeeder` menyediakan simulasi tiga
  company (`PENA`, `NUSA`, `KARYA`), branch operasional, enam akun uji,
  role matrix, dan menu ERP awal secara idempotent.
- Mutation company, branch, role, permission, assignment access dan pergantian
  tenant context sekarang menulis event audit append-only.
- Menu administrasi `Audit Trail` menyediakan pencarian event berdasarkan
  company, event type dan isi aktivitas. Layar `Role & Permission` kini dapat
  melakukan revoke grant dengan event audit `ROLE_PERMISSION_REVOKED`.
- Status role dapat diedit; role nonaktif tidak lagi menerbitkan menu atau
  permission untuk user yang ditugaskan dan perubahan dicatat sebagai
  `ROLE_UPDATED`.
- Layar `Akses User` dapat mencabut assignment role. Jika role terakhir user
  pada company dicabut, membership company/branch tersebut dinonaktifkan,
  tanpa mengganggu membership user pada company lain; event dicatat sebagai
  `USER_ROLE_REVOKED`.
- Layar RBAC dapat menambah dan mencabut mapping menu-to-permission. Menu
  sidebar berubah berdasarkan mapping dan role tenant aktif; operasi mapping
  dicatat sebagai `MENU_PERMISSION_GRANTED`/`MENU_PERMISSION_REVOKED`.
- Layar `Akses User` dapat memprovision identitas login aktif melalui Shield.
  Akun baru tidak mendapat privilege platform/tenant otomatis; role company
  diberikan sesudahnya secara eksplisit. Audit `USER_PROVISIONED` hanya
  menyimpan username/email/provider, tidak menyimpan password.
- Lifecycle user pada layar yang sama mendukung aktif/nonaktif login dan
  penggantian password sementara. Ketiga jalur akses tenant (context,
  permission, dan dynamic menu) kini mensyaratkan user Shield masih aktif;
  event dicatat sebagai `USER_STATUS_UPDATED` dan `USER_PASSWORD_REPLACED`.
- Membership company dapat di-suspend/aktifkan dan scope branch dapat diatur
  status serta `can_switch`-nya. Suspend company otomatis menonaktifkan
  branch user tersebut, sedangkan reaktivasi branch harus eksplisit; event
  dicatat sebagai `USER_COMPANY_MEMBERSHIP_UPDATED` dan
  `USER_BRANCH_MEMBERSHIP_UPDATED`.
- Migration `CreateUserSessionSecurity` menyimpan nomor versi revokasi
  session per user. Event login mencap versi saat ini; filter
  `sessionsecurity` menolak session lama setelah password diganti atau akun
  dinonaktifkan dan mencatat `USER_SESSIONS_REVOKED`.
- Password sementara dari admin ditandai `force_reset`. Filter
  `passwordrequired` mengarahkan user ke `/account/security/password` sampai
  ia membuat password final; penyelesaian reset kembali mencabut session agar
  login berikutnya menggunakan credential baru.
- Company nonaktif tidak dapat digunakan sebagai tenant context atau sumber
  permission. Branch nonaktif tidak lagi muncul sebagai context aktif, dan
  ownership company pada branch tidak dapat diubah melalui form edit biasa.
- Command `php spark regions:import <directory> <source_version>` tersedia
  untuk mengimpor empat CSV versioned (`provinces`, `regencies`, `districts`,
  `villages`) secara idempotent.
- Command `php spark regions:sync-api <source_version>` mengambil hierarchy
  yang tersedia dari API wilayah menggunakan `regions.apiBaseUrl` dan
  `regions.apiToken` di `.env`, lalu mengimpor secara idempotent dengan audit
  versi sumber yang sama.
- Sinkronisasi API development pada 25 Mei 2026 menghasilkan 36 provinsi,
  518 kabupaten/kota, 7.234 kecamatan, dan 80.694 desa/kelurahan. Jumlah ini
  sudah dapat dipakai untuk pengujian aplikasi, tetapi belum lolos validasi
  kelengkapan master resmi terbaru.
- Halaman master wilayah dan field alamat company/branch memakai pencarian
  terbatas 100 hasil agar dataset nasional tidak dirender seluruhnya ke HTML.
- Test database memverifikasi hierarchy seed serta sifat idempotent seeder.

### Verifikasi Tahap 4 Saat Ini

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\DevelopmentFoundationSeeder
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php spark regions:import <directory-csv-resmi> <versi-sumber>
php spark regions:sync-api <versi-sumber-api>
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```

Migration foundation dan audit telah dijalankan pada `pena_erp` dan tampilan
administrasi dan workspace berizin telah diverifikasi melalui login
superadmin. Regression suite kini mencakup audit mutation/context, pemblokiran
company atau branch nonaktif, perlindungan branch terhadap perpindahan
lintas-company, serta revoke permission menghilangkan menu role dan tercatat
di audit. Regression suite juga memverifikasi role nonaktif menghentikan menu,
revoke assignment satu company tidak menghapus akses company lain, perbedaan
menu Purchasing/Finance, owner demo dapat berpindah antara tiga company,
provisioning Shield menyimpan password sebagai hash tanpa bocor ke audit, dan
CRUD mapping menu memengaruhi sidebar sesuai permission. Test tambahan
memastikan user Shield nonaktif kehilangan seluruh akses tenant dan suspend
company tidak membuka kembali branch tanpa tindakan eksplisit. Suite juga
memverifikasi versioned session revocation dan siklus wajib ganti password.
Pekerjaan lanjutan Tahap 4 adalah mengganti atau melengkapi
dataset API hingga sesuai rujukan master resmi serta memperluas administrasi
untuk notifikasi credential, recovery policy, dan inventory perangkat/session.

### Keputusan Tenant pada Tahap 4

Model implementasi awal adalah **satu database, banyak company, banyak
branch**. Data global seperti wilayah Indonesia digunakan bersama; data tenant
dan transaksi wajib di-scope dengan `company_id`, sedangkan operasi cabang
atau gudang juga membawa `branch_id`.

Model ini dipilih karena efisien untuk SaaS dan holding company pada fase awal:
migration, reporting konsolidasi, deployment, dan operasional backup lebih
sederhana. Satu database per company tidak dijadikan default karena menambah
biaya provisioning dan reporting lintas perusahaan. Tenant enterprise yang
memerlukan isolasi fisik atau beban sangat besar dapat memakai dedicated
database melalui resolver koneksi tenant pada fase scaling.

## Tahap 5: Inventory Master dan Warehouse

### Yang Sudah Dibuat

- Migration `CreateInventoryMasterTables` membuat `units_of_measure`,
  `product_categories`, `products`, dan `warehouses` dengan audit columns,
  index tenant, soft delete, serta foreign key company/branch/user.
- `InventoryReadModel` selalu membaca master berdasarkan `company_id` dari
  context tenant aktif. `InventoryWriteModel` menolak UOM, kategori, atau
  branch milik company lain dan menulis event audit untuk create/status.
- Route `/inventory` menggantikan placeholder menu Inventory. Role dengan
  `inventory.stock.view` dapat membaca daftar produk/gudang; perubahan master
  memerlukan `inventory.master.manage`.
- Halaman Inventory menyediakan form UOM, kategori, produk, dan gudang, serta
  aktivasi/nonaktivasi produk dan gudang dalam company aktif.
- `MultiCompanyDemoSeeder` kini membuat satu produk dan satu gudang terisolasi
  untuk masing-masing `PENA`, `NUSA`, dan `KARYA`. Role `owner`, `manager`,
  dan `warehouse` dapat mengelola master; role operasional yang hanya mendapat
  view tidak dapat melakukan mutation.
- Regression test membuktikan data demo idempotent, pembacaan tidak bocor
  antar-company, reference lintas-tenant ditolak, dan perubahan status produk
  tercatat di audit trail.

### Verifikasi Tahap 5

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```

### Batas Tahap Ini

Tahap ini menyiapkan master yang aman untuk transaksi. Pada lanjutan Tahap 6,
`warehouse_bins` diwujudkan sebagai Location Master; `stock_balances`,
`stock_movements`, stock opening, transfer, dan adjustment belum diposting
agar aturan ledger immutable dan approval dapat dibangun serta diuji sebagai
tranche transaksi berikutnya.

## Tahap 6: Functional Menu dan Setup Master

### Keputusan Cakupan

- Daftar menu ERP operasional yang diberikan pengguna telah ditetapkan sebagai
  kontrak di `09-functional-menu-master-roadmap.md`, lengkap dengan istilah
  layar, target tabel, status dan urutan pengembangan.
- Label bisnis diselaraskan: tabel `branches` ditampilkan sebagai **Site**,
  `products` sebagai **Item Master**, `warehouse_bins` sebagai **Location**,
  dan numbering transaksi menjadi **Transaction Code**.
- Implementasi kode tahap ini sengaja fokus pada master referensi yang menjadi
  prasyarat commercial, inventory transaction, finance dan production.

### Yang Sudah Dibuat

- Migration `CreateSetupAndExtendedInventoryMasterTables` membuat reference
  global `countries` serta master tenant `departments`, `transaction_codes`,
  `addresses`, `currencies`, `tax_codes`, `warehouse_bins`,
  `product_uom_conversions`, `product_tax_codes`, dan `stock_lots`.
- Menu `/setup` memakai pola list-first: tabel bertab untuk Transaction Code,
  Department, Currency, VAT serta Address Master pada company context aktif,
  dengan modal tambah/edit dan aksi aktif/nonaktif.
- Halaman `/inventory` kini juga menangani Location, Item UoM Conversion,
  Item VAT dan Batch Master.
- Permission demo `setup.master.view` dan `setup.master.manage` memisahkan
  akses baca/kelola, sedangkan mutation inventory tetap menggunakan
  `inventory.master.manage`.
- Seeder multi-company membuat master contoh per tenant: Indonesia, IDR,
  department Operations, PPN11, address utama, transaction code Sales Order,
  lokasi gudang default dan mapping VAT item.
- Semua write model memvalidasi ownership referensi tenant dan merekam event
  audit sebelum master dipakai oleh transaksi.
- Kode master yang telah dibuat tidak diedit melalui UI; perubahan dilakukan
  pada atribut operasional, sedangkan penghapusan diganti status `inactive`
  agar FK dokumen lama dan audit trail tidak terputus.
- Hirarki operasional diselaraskan menjadi `Company -> Site -> Department ->
  Warehouse -> Location`: migration alignment menambah `departments.branch_id`
  dan `warehouses.department_id`, dengan validasi parent tenant/Site pada
  setiap pembuatan master baru.
- Kolom alignment dibuat nullable untuk menghindari kegagalan migrasi pada
  data legacy tanpa parent; seeder/backfill mengisi data demo dan write
  service tidak menerima Department/Warehouse baru tanpa parent hirarki.

### Master Berikutnya

Tranche M2 membangun customer/supplier, terms, address relation, promo dasar,
dan POS Master. Customer/Supplier portion telah dilanjutkan pada Tahap 7;
POS Master masih berada pada antrean M2 berikutnya. Tranche M3 membangun
master Finance/GL, Cash Bank dan Costing; M4 membangun BOM, Work Center,
Routing serta referensi Planning.

### Verifikasi Tahap 6

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```

## Tahap 7: Commercial Master Customer dan Supplier

### Yang Sudah Dibuat

- Migration `CreateCommercialMasterTables` membuat `customers`, `suppliers`,
  `customer_terms`, `supplier_terms`, `customer_addresses`,
  `supplier_addresses`, `customer_promotions`, dan `supplier_promotions`.
- Halaman `/sales/master` menangani Customer Master, Terms, Customer Address
  berbasis Address Master, dan Customer Promo dasar.
- Halaman `/purchasing/master` menyediakan jalur yang setara untuk supplier.
- Permission `sales.master.view/manage` dan
  `purchasing.master.view/manage` mengendalikan menu serta mutation per role.
- Write model memeriksa bahwa currency, terms, partner, dan address berasal
  dari company aktif; create dan linking terekam dalam Audit Trail.
- Seeder demo menyediakan customer/supplier, terms, mapping alamat dan promo
  terisolasi untuk masing-masing company agar pengujian dua laptop konsisten.

### Batas Tahap Ini

Master promo saat ini menyimpan periode dan nilai diskon dasar. Rule item,
price list, approval promo, Purchase Order, Sales Order, receipt/delivery,
posting stok, serta POS System belum diaktifkan sebagai transaksi.

### Verifikasi Tahap 7

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```

### Referensi Workbook Setelah Tahap 7

Workbook `Pena_ERP_1_Table_1_Sheet_No_ID.xlsx` telah dibaca sebagai input
requirement tambahan. File tersebut berisi 257 table sheets dan 1.934 field
tanpa `id`. Gap analysis dan keputusan normalisasinya disimpan pada
`10-workbook-schema-gap-analysis.md`.

Keputusan penting: field legacy tidak otomatis ditambahkan ke migration.
Alamat berulang customer/supplier/site tetap dinormalisasi melalui Address
Master dan relation table; code text seperti currency, VAT, terms dan
warehouse tetap dipetakan ke foreign key tenant. Implementasi lanjutan
diprioritaskan pada commercial enrichment, item enrichment, lalu POS Master.

## Tahap 8: Commercial Enrichment dari Workbook

### Yang Sudah Dibuat

- Migration `CreateCommercialPartnerProfileTables` menambahkan tabel ekstensi
  satu-record-per-partner: `customer_profiles` dan `supplier_profiles`.
- Profile menyimpan reference/contact name, description, default VAT,
  default warehouse, PIC operasional, serta policy limit dasar. Supplier
  mendapat `amount_limit`; customer memakai `credit_limit` pada master inti.
- Form `Profile & Policy` pada `/sales/master` dan `/purchasing/master`
  melakukan create/update profil dengan audit event dan validasi FK tenant.
- Address mapping menerima tipe `mailing` sehingga office, billing, shipping,
  mailing dan pickup tidak perlu menjadi kolom berulang pada partner.
- Seeder demo mengisi profile dan mailing address per company. Regression
  test memastikan VAT lintas-company ditolak dan update profile tercatat.

### Batas Tahap Ini

Partner bank account dari workbook belum diimplementasikan karena membutuhkan
masking, permission khusus dan kebijakan data sensitif. Item enrichment serta
POS Master menjadi kelanjutan sebelum transaksi Purchase/Sales Order.

### Verifikasi Tahap 8

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```
