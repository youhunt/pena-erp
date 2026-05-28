# Requirement Traceability and Delivery Definition

## Blueprint Output Coverage

| Requested Output | Blueprint Location |
| --- | --- |
| 1. ERP enterprise blueprint | `01-enterprise-blueprint.md` sections 1-4 |
| 2. Roadmap development | `01-enterprise-blueprint.md` section 9 |
| 3. Multi-company architecture | `01-enterprise-blueprint.md` section 5 |
| 4. Multi-user architecture | `01-enterprise-blueprint.md` section 6 and data catalog |
| 5. Multi-role architecture | `01-enterprise-blueprint.md` section 6 |
| 6. AI OCR architecture | `02-ai-document-processing.md` sections 1-3 |
| 7. Document processing flow | `02-ai-document-processing.md` sections 3-9 |
| 8. HMVC structure | `05-ci4-implementation-guide.md` section 2 |
| 9. ERD lengkap | `03-data-model-catalog.md` sections 2 and 8 |
| 10. Dokumentasi semua tabel | `03-data-model-catalog.md` sections 1-9 |
| 11. OCR database structure | `03-data-model-catalog.md` section 8 |
| 12. AI mapping flow | `02-ai-document-processing.md` sections 5-8 |
| 13. API endpoint structure | `04-api-ui-business-flows.md` sections 1-2 |
| 14. Contoh migration | `05-ci4-implementation-guide.md` section 4 |
| 15. Contoh module CI4 | `05-ci4-implementation-guide.md` sections 2-7 |
| 16. OCR service example | `05-ci4-implementation-guide.md` section 8 |
| 17. AI extraction example | `05-ci4-implementation-guide.md` section 9 |
| 18. Dashboard UI structure | `04-api-ui-business-flows.md` section 3 |
| 19. Inventory flow | `04-api-ui-business-flows.md` section 4 |
| 20. Purchasing flow | `04-api-ui-business-flows.md` section 5 |
| 21. Accounting flow | `04-api-ui-business-flows.md` section 7 |
| 22. AI validation flow | `04-api-ui-business-flows.md` section 8 |
| 23. Deployment guide | `06-deployment-saas-operations.md` sections 1-10 |
| 24. SaaS scaling strategy | `06-deployment-saas-operations.md` section 11 |

## Scope Boundary

This package is a solution blueprint and starter implementation specification,
not a completed ERP. Tahap 1 telah memasang CodeIgniter 4.7.3 appstarter dan
namespace root `Modules\`. Tahap 3 telah memasukkan resource Skote dan asset
runtime, menghubungkan UI auth ke Shield, serta membuat dashboard shell awal.
Tahap 4 telah dimulai dengan migration master wilayah global, importer CSV dan
sinkronisasi API wilayah versioned (dataset API development sudah termuat,
validasi kelengkapan resmi masih tertunda), company/branch,
database session, membership/RBAC tenant awal, importer CSV versioned,
company/branch CRUD, assignment role/branch, dan workspace tenant dengan
context company/branch aktif tersimpan dalam session. UI RBAC awal sekarang
mendukung pembuatan role/permission dan grant yang divalidasi per-company.
Menu tenant simulasi kini dirender dari permission per role dan
`MultiCompanyDemoSeeder` dapat membangun tiga company serta akun uji yang sama
di setiap workstation development. Prosedur sinkronisasi lintas laptop,
migration/seeder dan perlindungan secret dicatat pada
`08-multi-laptop-development-guide.md`.
Audit Trail UI dan revoke grant permission tenant telah tersedia dengan event
append-only. Status role, revoke assignment user terisolasi per-company,
provisioning login Shield tanpa privilege tersirat, serta CRUD mapping
menu-permission kini tersedia. Lifecycle user awal (status login dan password
sementara), status membership company/branch, serta pemutusan akses tenant
bagi user Shield nonaktif juga sudah diuji. Password sementara dengan wajib
ganti, endpoint reset pribadi, dan versioned session revocation kini tersedia.
Tahap 5 telah memulai domain bisnis dengan tabel serta UI master UOM,
kategori produk, produk, dan gudang yang tenant-scoped, dilengkapi permission
manage/view, data demo multi-company, audit, dan pengujian penolakan reference
lintas-company. Import dataset nasional resmi lengkap, notifikasi/recovery
credential, inventory perangkat/session, stock ledger/transaksi, deployment
manifests dan provider credentials masih mengikuti gate roadmap.
Functional menu operasional terbaru dikendalikan melalui
`09-functional-menu-master-roadmap.md`. Tahap 6 menambahkan Setup Master
(`transaction_codes`, `departments`, `countries`, `addresses`, `currencies`,
`tax_codes`) serta perluasan Inventory Master (`warehouse_bins`,
`product_uom_conversions`, `product_tax_codes`, `stock_lots`) dengan UI,
permission tenant dan seed demo. Tahap 7 telah memulai Commercial Master:
`customers`, `suppliers`, masing-masing terms, relasi ke Address Master dan
promo dasar, dengan UI Sales/Purchasing, permission, audit, seed demo serta
uji isolasi tenant. POS, transaksi commercial, finance dan manufacturing
berikutnya tetap dibangun mengikuti dependency roadmap tersebut.
Workbook data dictionary pengguna yang memuat 257 table sheets telah dicatat
sebagai sumber requirement tambahan melalui `10-workbook-schema-gap-analysis.md`;
hasilnya menjadi backlog normalisasi, bukan perubahan schema otomatis.
Tahap 8 mengimplementasikan prioritas M2.1 hasil workbook melalui
`customer_profiles` dan `supplier_profiles`: contact/reference, VAT dan
warehouse default, PIC, policy limit, serta address type `mailing`, dengan
audit dan penolakan referensi lintas-company.
Tahap 9 menyelaraskan ownership organisasi sesuai urutan operasional:
`Company -> Site -> Department -> Warehouse -> Location`, melalui
`departments.branch_id`, `warehouses.department_id`, backfill migration,
validasi hierarchy pada write model, dan UI pemilihan parent.
Tahap 10 mengimplementasikan M2.2 Item Enrichment melalui
`product_profiles` dan `product_prices`: alternate item data, default
warehouse, shelf life, dimension/packaging dan baseline harga efektif
per currency/UOM dengan validasi tenant/status dan audit.
Tahap 11 mengimplementasikan M2.3 POS Master foundation melalui
`pos_registers`: register berizin per Site/Department/Warehouse, default
customer/currency/transaction code, grid tambah/edit/nonaktifkan, audit dan
uji isolasi tenant. Mapping rekening pembayaran dilanjutkan setelah referensi
Finance Master tersedia.
Tahap 12 memulai M3.1 Finance Master dengan `chart_of_accounts`,
`cash_bank_accounts`, dan `exchange_rates`, halaman grid CRUD/status,
permission tenant, seed demo, serta validasi referensi lintas-company.
Tahap 13 menambahkan `pos_payment_methods` untuk mapping register POS ke
Cash/Bank Account tenant aktif, termasuk UI CRUD/status, seed demo, audit dan
uji penolakan referensi lintas-company. GL posting dan closing masih menjadi
kelanjutan.
Tahap 14 menambahkan `pos_shifts` sebagai fondasi transaksi kasir: open/close
shift per register, validasi membership site cashier, proteksi shift open
ganda, UI pada POS Master, dan audit open/close.
Tahap 15 menambahkan POS Sales Receipt MVP melalui `pos_sales`,
`pos_sale_items`, dan `pos_sale_payments`: receipt hanya bisa dibuat dari shift
open milik cashier aktif, memakai item aktif tenant, payment method register
yang sama, VAT item, Transaction Code receipt, dan audit `POS_SALE_PAID`.
Tahap 16 menambahkan stock ledger foundation: `stock_balances` dan
`stock_movements`, opening balance demo untuk item stock, penolakan negative
stock pada POS, serta posting movement `pos_sale_issue` dari receipt POS.
Tahap 17 menambahkan visibility inventory: `/inventory` membaca dan
menampilkan saldo `stock_balances` serta ledger `stock_movements`.
Tahap 18 menambahkan Stock Opname / Adjustment MVP: draft counted quantity,
posting variance ke `stock_movements`, update `stock_balances`, audit create
dan post, serta proteksi tenant pada warehouse/product.
Tahap 19 menambahkan Inventory Transfer MVP: draft transfer antar warehouse
aktif, validasi saldo source, posting `transfer_out`/`transfer_in`, update
saldo source/destination, audit transfer, dan proteksi tenant.

## Definition of Ready for Implementation

- Authentication decision is complete: CodeIgniter Shield is the selected
  provider; implement ERP access only behind `AuthGatewayInterface`.
- Validate licensed Skote assets and finalize branding requirements.
- Confirm accounting policy (GRNI/perpetual stock/tax/fiscal lock), document
  retention and approval authority limits.
- Select OCR deployment profile (CPU/GPU, languages, page throughput) and AI
  data-processing/privacy policy.
- Approve tenancy tier: shared schema initially and criteria for dedicated
  tenant database/storage.
- Approve the versioned source/import process for global Indonesian regional
  master data (provinsi, kabupaten/kota, kecamatan, desa/kelurahan).

## Definition of Done for First Production Release

- Every tenant-owned table has enforced context scope, audit fields and tests
  for cross-company denial.
- Commercial and ledger posting reconciles against approved scenarios.
- AI/OCR pilot produces draft-only outcomes, evaluation metrics, manual review
  path and secure document retention.
- Authentication, permission, approval, CSRF/upload security, monitoring,
  backups and restore exercises pass release approval.
