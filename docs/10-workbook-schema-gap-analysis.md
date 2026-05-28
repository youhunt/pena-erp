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
| `item_master` | code/name aliases, shelf life, stock/purchase/selling UoM, warehouse, price, VAT, dimension, classification | `products`, `product_profiles`, `product_prices`, `product_categories`, `product_uom_conversions`, `product_tax_codes`, `stock_lots` | deeper classification dan advanced price-list policy |
| `pos_master` | company, site, department, warehouse, customer, transaction code, banks, currency | `pos_registers`, `pos_payment_methods` | register/default hierarchy dan payment account mapping Built |
| `purchaseorder` | PO date/revision, numbering, site, supplier, address snapshot, terms, discount, total | dirancang `purchase_orders` | T1 setelah master policy siap |
| `salesorder` | order/ref/customer/currency/terms, PO ref, address snapshot, warehouse, discount, total | dirancang `sales_orders` | T1 setelah master policy siap |
| `allocationorder` | allocation no/date, customer, ship date, warehouse | dirancang `sales_allocations` | T1 bersama stock reservation |
| `delivery` | delivery header/line reference | dirancang `deliveries` | T1 setelah allocation dan stock movement |
| `chart_of_account` | book type, company, site, code, remarks | `chart_of_accounts` | COA/postable Built foundation; GL book dan posting policy lanjutan |

## 4. Priority Gap Register

| Priority | Capability | Proposed Normalized Tables / Changes | Reason |
| --- | --- | --- | --- |
| Delivered M2.1 | Partner profile dan address completeness awal | `customer_profiles`, `supplier_profiles`; address type `mailing` | tersedia di UI Sales/Purchasing Master |
| Delivered M2.1 | Customer/Supplier tax default | `default_tax_code_id FK` dalam profile partner | tersedia dan diuji tenant isolation |
| Delivered M2.2 | Item operational attributes dan baseline price | `product_profiles`, `product_prices` | purchasing, sales, POS dan gudang memiliki reference awal terstruktur |
| Delivered M2.3 | POS Master register foundation | `pos_registers` terhubung Site/Department/Warehouse/Customer/Transaction Code/Currency | payment account ditunda sampai referensi Finance tersedia |
| Delivered M3.1 | Finance master foundation | `chart_of_accounts`, `cash_bank_accounts`, `exchange_rates` | membuka referensi payment/GL berikutnya secara tenant-scoped |
| Delivered M3.2 | POS payment mapping | `pos_payment_methods` linked register ke `cash_bank_accounts` | POS punya rekening penerimaan default sebelum transaksi kasir |
| Delivered T0.1 | POS shift foundation | `pos_shifts` open/close per register dan cashier | prerequisite receipt POS dan kontrol kasir |
| Delivered T0.2 | POS sales receipt MVP | `pos_sales`, `pos_sale_items`, `pos_sale_payments` paid dari open shift | belum posting stok/jurnal; dipakai untuk validasi transaksi kasir awal |
| Delivered T0.3 | Stock ledger foundation | `stock_balances`, `stock_movements`; POS issue dari receipt | negative stock ditolak; transfer/opname/costing lanjutan belum |
| Delivered T0.4 | Inventory stock visibility | `/inventory` grid saldo dan ledger | read-only, filter/reporting lanjutan belum |
| P1 | Commercial transaction foundation | `purchase_orders`, `sales_orders`, line tables dengan address snapshot dan numbering | mulai T1 tanpa kehilangan jejak dokumen |
| P2 | Organization profile/address | link address/contact untuk company/site/department/warehouse | diperlukan untuk cetak dokumen enterprise |
| P2 | Finance master lanjutan | GL book, currency rounding policy, fiscal close, posting setup | prerequisite posting AP/AR dan POS |

## 5. Recommended Delivery Sequence

| Next Delivery | Isi | Catatan |
| --- | --- | --- |
| M2.1 Commercial enrichment | contact/address type, tax default partner, partner policy minimal | Built: `customer_profiles`, `supplier_profiles`, `mailing` |
| M2.2 Item enrichment | alternate data, shelf life, dimension/packaging, price list baseline | Built: `product_profiles`, `product_prices` |
| M2.3 POS Master | register, default warehouse/customer, currency, transaction code; payment account setelah M3 | Built: `pos_registers`, `pos_payment_methods` |
| M3.1 Finance foundation | COA, cash bank, exchange rates | Built: grid CRUD/status dan validation tenant |
| T0.1 POS shift | open/close shift kasir | Built: `pos_shifts` |
| T0.2 POS receipt | paid receipt dari open shift, payment method, item dan VAT | Built MVP: `pos_sales`, `pos_sale_items`, `pos_sale_payments` |
| T0.3 Stock ledger | saldo on hand dan immutable movement untuk issue POS | Built foundation: `stock_balances`, `stock_movements` |
| T0.4 Stock visibility | saldo dan movement terlihat dari menu Inventory | Built read-only grid |
| T1.1 Sales/Purchase draft | PO/SO header/lines, numbering, address snapshot, approval draft | belum posting stok/GL |
| T1.2 Fulfilment stock | allocation, delivery, purchase receipt, immutable stock movement | baru dilakukan setelah workflow/locking diuji |
| M3 Finance lanjutan | GL book, fiscal/posting setup | sebelum invoice/payment/GL posting |

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
