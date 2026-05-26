# Functional Menu and Master Data Roadmap

## 1. Menu Vocabulary Decision

Daftar menu operasional ini menjadi kontrak functional ERP Pena. Istilah layar
boleh lebih familiar bagi user operasional, sementara nama tabel menjaga
konsistensi teknis:

| Label UI | Technical Ownership |
| --- | --- |
| `Site` | `branches`, karena satu legal company dapat mempunyai banyak lokasi operasional |
| `Transaction Code` | `transaction_codes`, menggantikan istilah awal `number_sequences` |
| `Item Master` | `products` |
| `Location` | `warehouse_bins`, lokasi rak/bin di bawah warehouse |
| `Batch Master` | `stock_lots`, mendukung lot/expiry trace |
| `VAT` / `Item VAT` | `tax_codes` / `product_tax_codes` |

Status: **Built** berarti migration dan jalur UI awal tersedia; **Designed**
berarti target tabel/alur sudah menjadi backlog resmi tetapi belum dipakai
runtime; **Existing label update** berarti capability tersedia tetapi label UI
masih diselaraskan bertahap.

## 2. Setup and General Master

| Menu | Target Tables / Design | Status |
| --- | --- | --- |
| Transaction Code | `transaction_codes` tenant/site scoped, locked counter per dokumen | Built |
| Company | `companies` | Built |
| Site | `branches` | Built; existing label update |
| Department | `departments` | Built |
| Warehouse | `warehouses` | Built |
| Location | `warehouse_bins` | Built |
| Country | `countries` global reference | Built |
| Province, City, Postal Code | `provinces`, `regencies`, `villages.postal_code` | Built |
| Unit of Measure | `units_of_measure` | Built |
| UoM Conversion | `product_uom_conversions` | Built |
| VAT | `tax_codes` | Built |
| Item VAT | `product_tax_codes` | Built |
| Address Master | `addresses`, reusable tenant address reference | Built |
| POS Master | `pos_registers`, payment/device/register mapping | Designed |

## 3. Sales and Purchase

| Module / Menu | Target Tables / Design | Status |
| --- | --- | --- |
| Customer Master | `customers`, `customer_profiles` | Built; enriched |
| Customer Terms | `customer_terms`, linked default pada customer/order | Built |
| Customer Promo | `customer_promotions`, date/customer eligibility awal | Built |
| Customer Address | `customer_addresses` linking `addresses`, including `mailing` | Built; enriched |
| Sales Order | `sales_orders`, `sales_order_items` | Designed |
| Allocation Order | `sales_allocations`, `sales_allocation_items`, reserves stock | Designed |
| Delivery Order | `deliveries`, `delivery_items` | Designed |
| Sales Period Close | `module_period_closes` type `sales` with `fiscal_periods` | Designed |
| Supplier Master | `suppliers`, `supplier_profiles` | Built; enriched |
| Supplier Terms | `supplier_terms` | Built |
| Supplier Promo | `supplier_promotions` / purchase rebate rules awal | Built |
| Supplier Address | `supplier_addresses` linking `addresses`, including `mailing` | Built; enriched |
| Purchase Order | `purchase_orders`, `purchase_order_items` | Designed |
| Purchase Intransit | `purchase_intransits`, `purchase_intransit_items` | Designed |
| Inventory Purchase Receipt | `goods_receipts`, `goods_receipt_items` | Designed |
| Cost Purchase Receipt | `landed_cost_receipts`, allocation lines | Designed |
| Purchase Period Close | `module_period_closes` type `purchase` | Designed |

## 4. Inventory, Planning and Production

| Module / Menu | Target Tables / Design | Status |
| --- | --- | --- |
| Item Master | `products`, `product_categories` | Built |
| Item UoM Conversion | `product_uom_conversions` | Built |
| Batch Master | `stock_lots` | Built |
| Inventory In Out | immutable `stock_movements`, balance projection | Designed |
| Inventory Transfer | `stock_transfers`, `stock_transfer_items` | Designed |
| Inventory Stock Opname | `inventory_adjustments`, detail counted quantity | Designed |
| Inventory Period Close | `module_period_closes` type `inventory` | Designed |
| Forecast | `forecasts`, `forecast_items` | Designed |
| Planned Released | `planned_orders`, release state/work order or PO proposal | Designed |
| MPS | `master_production_schedules`, lines | Designed |
| MRP | `mrp_runs`, demand/supply recommendation lines | Designed |
| BOM | `bill_of_materials`, `bom_items` | Designed |
| Work Center | `work_centers` | Designed |
| Routing | `routings`, `routing_operations` | Designed |
| Work Order | `work_orders` | Designed |
| Allocate Work Order | `work_order_allocations` | Designed |
| Work Order In / Out / In Out | production receipt/issue through `stock_movements` references | Designed |
| Work Order Labor | `work_order_labor_entries` | Designed |
| Production Period Close | `module_period_closes` type `production` | Designed |

## 5. AP, AR, Costing, Cash Bank and GL

| Module / Menu | Target Tables / Design | Status |
| --- | --- | --- |
| Manual A/P Invoice | `ap_invoices` source `manual` | Designed |
| Purchase Invoice | `ap_invoices` source `purchase_order` / legacy target `purchase_invoices` | Designed |
| Inventory Purchase Invoice | `ap_invoices` source `goods_receipt` | Designed |
| Advanced A/P Invoice | `ap_advances` | Designed |
| Payment Invoice | `payments`, `payment_allocations` direction payable | Designed |
| A/P Period Close | `module_period_closes` type `ap` | Designed |
| Manual A/R Invoice | `ar_invoices` source `manual` | Designed |
| Proforma Invoice | `proforma_invoices` | Designed |
| Sales Invoice | `ar_invoices` source `sales_order` / legacy target `sales_invoices` | Designed |
| Inventory Sales Invoice | `ar_invoices` source `delivery` | Designed |
| Advanced A/R Receipt | `ar_advances` | Designed |
| Payment Receipt | `payments`, `payment_allocations` direction receivable | Designed |
| A/R Period Close | `module_period_closes` type `ar` | Designed |
| Cost Type | `cost_types` | Designed |
| Item Cost | `item_costs` history by type/period | Designed |
| Calculate Cost | `cost_calculation_runs`, result lines | Designed |
| Cash Bank ID | `cash_bank_accounts` linked COA/currency | Designed |
| Currency / Rate Master | `currencies`, future `exchange_rates` | Currency Built; Rate Designed |
| Employee ID | `employees`, usable sebagai custodian/advance requester | Designed |
| Cash Entry / Bank Entry | `cash_bank_entries`, lines | Designed |
| Bank Reconcile | `bank_reconciliations`, matching lines | Designed |
| GL Book | `gl_books` | Designed |
| GL Column | `gl_columns` reporting mapping | Designed |
| Account No. / Chart of Account | `chart_of_accounts` | Designed |
| Recurring / Recurring Posting | `recurring_journals`, posting runs | Designed |
| GL Entry | `journal_entries`, `journal_entry_lines` immutable after posting | Designed |
| GL Period Close | `module_period_closes` type `gl`, locks upstream posting | Designed |

## 6. Delivery Order for Master Data

Master dibangun berdasarkan dependency agar tidak menghasilkan setup yang
tidak bisa dipakai transaksi:

| Tranche | Deliverable |
| --- | --- |
| M1 Foundation master | Transaction Code, Department, Country/Address, Currency, VAT, Location, UoM Conversion, Item VAT, Batch Master |
| M2 Commercial master | Customer/Supplier, profile policy, VAT/warehouse default, terms, address links, promo dasar (Built); POS register (Designed) |
| M3 Finance master | COA, GL Book/Column, Cash Bank, Rate, Cost Type/Item Cost, fiscal close authority |
| M4 Manufacturing master | BOM, Work Center, Routing, Forecast/MPS/MRP setup |
| T1 Transactions | Purchase, Sales, Inventory movements with approval and immutable posting |
| T2 Financial transactions | AP/AR, payment, cash/bank, GL posting and period close |

Master pada tranche selanjutnya tetap wajib mengikuti aturan yang sudah
berjalan: `company_id` tenant scoped, optional `branch_id` untuk Site,
audit column, permission per role, audit trail, dan automated cross-company
isolation test.

## 7. Workbook Reference Alignment

Workbook referensi pengguna yang dibaca pada 26 Mei 2026 mencakup 257 table
sheets dan memperinci master serta transaksi di roadmap ini. Analisis gap
field dan keputusan normalisasi dicatat di
`10-workbook-schema-gap-analysis.md`.

Field tambahan dari workbook tidak disalin secara literal: address variants,
tax, terms, currency, warehouse dan financial account harus tetap mengikuti
relation/FK tenant pada arsitektur Pena ERP.

M2.1 telah mengimplementasikan `customer_profiles`, `supplier_profiles`,
VAT/default warehouse terverifikasi tenant, PIC/limit policy, dan address
type `mailing`. Partner bank account tetap menunggu desain security khusus.
