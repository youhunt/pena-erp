# Workbook Schema Gap Analysis

## 1. Source Reference

Dokumen ini mencatat pembacaan awal file referensi pengguna:
`Pena_ERP_1_Table_1_Sheet_No_ID.xlsx`, diterima pada 26 Mei 2026.
Workbook berada di storage lokal pengguna dan tidak dikomit karena merupakan
artefak sumber di luar source code aplikasi.

Ringkasan dari sheet `Summary` dan `Index`:

| Metric | Value |
| --- | ---: |
| Source files yang digabung | 50 |
| Table sheets | 257 |
| Fields tertulis tanpa `id` | 1.934 |
| Kolom `id` yang dikeluarkan dari sumber | 50 |

Workbook adalah referensi functional/data dictionary legacy. Ia bukan schema
runtime yang langsung dieksekusi. Pena ERP tetap menggunakan desain baru:
foreign key numerik, `company_id` isolation, audit trail, soft delete,
permission, dan nama tabel konsisten.

## 2. Normalization Decisions

| Legacy Pattern dalam Workbook | Keputusan Pena ERP |
| --- | --- |
| `company` atau `comp_code` tersimpan berulang sebagai teks | Gunakan `company_id FK` pada semua master/transaksi tenant |
| `site` tersimpan sebagai kode teks | Gunakan `branch_id FK`; label UI tetap `Site` |
| Office, billing, mailing, shipping address menjadi puluhan kolom partner | Gunakan `addresses` dan link `customer_addresses` / `supplier_addresses` bertipe |
| `terms`, `vat`, `currency`, `warehouse` menjadi code text | Gunakan FK ke master tenant dan validasi ownership |
| Field bank supplier/customer melekat pada master partner | Rancang tabel partner bank account terproteksi; jangan menaruh nomor rekening sensitif sembarang di master utama |
| Price dan cost melekat pada item | Pisahkan price list dan costing history agar efektif-date dan audit dapat ditangani |
| Audit actor bertipe string | Gunakan `created_by` / `updated_by` FK user; identitas sistem dikelola service |

## 3. Workbook to Current Schema Mapping

### Setup and Organization

| Workbook Table | Fields Penting Terbaca | Runtime Saat Ini | Gap yang Dicatat |
| --- | --- | --- | --- |
| `company_master` | code, name, PIC, tax ID, address, phone | `companies` + wilayah foundation | company profile/tax/contact/address link belum diperluas |
| `site_master` | company, site, PIC, tax ID, address variants, phones | `branches` | site profile dan multiple address relation belum ada |
| `department_master` | company, site, department, PIC, delivery/billing/mailing address | `departments.branch_id` | Site ownership Built; PIC dan relation address perlu desain lanjutan |
| `warehouse_master` | company, site, department, warehouse, PIC, address variants | `warehouses.department_id` | Department ownership Built; PIC dan address relation belum dinormalisasi |
| `location_master` | company, site, department, warehouse, location, PIC/address | `warehouse_bins` melalui parent warehouse | hierarchy Built; profile alamat hanya bila kebutuhan operasional terbukti |
| `transaction_code` | company, site, department, code, name, module, type, number, GL code | `transaction_codes` | `department_id`, transaction type/description dan default GL mapping belum ada |
| `currency` | code, name, rounding | `currencies` | tambahkan decimal/rounding policy saat finance dimulai |

### Commercial Master

| Workbook Table | Fields Penting Terbaca | Runtime Saat Ini | Gap yang Dicatat |
| --- | --- | --- | --- |
| `customer_master` | code/name/ref, contact, tax/VAT, terms, limits, sales PIC, bank, billing/mailing/shipping address, ship warehouse | `customers`, `customer_profiles`, `customer_terms`, `customer_addresses`, `customer_promotions` | bank account security dan richer contact directory masih menunggu |
| `supplier_master` | code/name/ref, contact, tax/VAT, terms, limits, purchasing PIC, bank, office/mailing/billing/shipping address | `suppliers`, `supplier_profiles`, `supplier_terms`, `supplier_addresses`, `supplier_promotions` | bank account security dan richer contact directory masih menunggu |
| `customer_terms` | company, site, terms, name, days, promo | `customer_terms` | optional site scope dan promo rule association |
| `supplier_terms` | company, site, terms, name, days, promo | `supplier_terms` | optional site scope dan promo/rebate rule association |

Alamat pada workbook diarahkan menjadi banyak record `addresses` yang
ditautkan melalui `customer_addresses` / `supplier_addresses`, bukan kolom
alamat berulang pada partner. UI saat ini mendukung tipe `office`, `billing`,
`shipping`, `mailing`, dan `pickup`; tipe `mailing` diwujudkan pada M2.1.

### Inventory, POS and Transactions

| Workbook Table | Fields Penting Terbaca | Runtime Saat Ini | Gap yang Dicatat |
| --- | --- | --- | --- |
| `item_master` | code/name aliases, shelf life, stock/purchase/selling UoM, warehouse, price, VAT, dimension, classification | `products`, `product_categories`, `product_uom_conversions`, `product_tax_codes`, `stock_lots` | alternate codes/name, shelf life policy, default warehouse, dimension/packaging, price lists, deeper classification |
| `pos_master` | company, site, department, warehouse, customer, transaction code, banks, currency | belum dibangun | kandidat M2 berikutnya: `pos_registers` dan payment mappings |
| `purchaseorder` | PO date/revision, numbering, site, supplier, address snapshot, terms, discount, total | dirancang `purchase_orders` | T1 setelah master policy siap |
| `salesorder` | order/ref/customer/currency/terms, PO ref, address snapshot, warehouse, discount, total | dirancang `sales_orders` | T1 setelah master policy siap |
| `allocationorder` | allocation no/date, customer, ship date, warehouse | dirancang `sales_allocations` | T1 bersama stock reservation |
| `delivery` | delivery header/line reference | dirancang `deliveries` | T1 setelah allocation dan stock movement |
| `chart_of_account` | book type, company, site, code, remarks | dirancang `chart_of_accounts` | M3 finance perlu COA hierarki dan posting policy |

## 4. Priority Gap Register

| Priority | Capability | Proposed Normalized Tables / Changes | Reason |
| --- | --- | --- | --- |
| Delivered M2.1 | Partner profile dan address completeness awal | `customer_profiles`, `supplier_profiles`; address type `mailing` | tersedia di UI Sales/Purchasing Master |
| Delivered M2.1 | Customer/Supplier tax default | `default_tax_code_id FK` dalam profile partner | tersedia dan diuji tenant isolation |
| P1 | Item operational attributes | tambah alternate name/code, shelf life; rancang `product_dimensions` dan `product_price_lists` | purchasing, sales, POS dan gudang akan bergantung padanya |
| P1 | POS Master | `pos_registers`, `pos_register_payment_methods` terhubung Site/Warehouse/Transaction Code/Currency | tercantum eksplisit di workbook dan daftar menu pengguna |
| P1 | Commercial transaction foundation | `purchase_orders`, `sales_orders`, line tables dengan address snapshot dan numbering | mulai T1 tanpa kehilangan jejak dokumen |
| P2 | Organization profile/address | link address/contact untuk company/site/department/warehouse | diperlukan untuk cetak dokumen enterprise |
| P2 | Finance master | COA, GL book, cash bank, currency rounding/exchange rates | prerequisite posting AP/AR dan POS |

## 5. Recommended Delivery Sequence

| Next Delivery | Isi | Catatan |
| --- | --- | --- |
| M2.1 Commercial enrichment | contact/address type, tax default partner, partner policy minimal | Built: `customer_profiles`, `supplier_profiles`, `mailing` |
| M2.2 Item enrichment | alternate data, shelf life, dimension/packaging, price list baseline | diperlukan sebelum transaksi dan POS |
| M2.3 POS Master | register, default warehouse/customer, currency, transaction code, payment account mapping | menutup daftar master commercial/POS |
| T1.1 Sales/Purchase draft | PO/SO header/lines, numbering, address snapshot, approval draft | belum posting stok/GL |
| T1.2 Fulfilment stock | allocation, delivery, purchase receipt, immutable stock movement | baru dilakukan setelah workflow/locking diuji |
| M3 Finance | COA, cash bank, rate, fiscal/posting setup | sebelum invoice/payment/GL posting |

## 6. Acceptance Rule for Workbook Fields

Setiap field workbook yang akan dimasukkan ke aplikasi harus melewati aturan:

1. Tidak menduplikasi `company_id`, `branch_id`, address, currency, terms,
   tax, user, warehouse, atau account yang sudah memiliki master/FK.
2. Data transaksi menyimpan snapshot yang diperlukan untuk histori cetak
   dokumen, sedangkan default tetap berasal dari master.
3. Field sensitif seperti bank account membutuhkan masking, audit, dan
   permission khusus.
4. Migration baru harus tenant-scoped, memiliki audit columns, indeks yang
   relevan, seeder demo, serta test cross-company isolation.
5. Workbook menjadi sumber requirement dan terminology, sedangkan
   `03-data-model-catalog.md` tetap menjadi kontrak schema aplikasi.
