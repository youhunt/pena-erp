# Tahap 26: Purchase Order dan Goods Receipt Hardening

Tahap ini melanjutkan posisi development setelah Sales/Purchase Order draft MVP dan Goods Receipt MVP. Fokusnya bukan OCR, melainkan memperkuat target transaksi yang nanti akan dipakai oleh AI/OCR document processing.

## Tujuan

1. Menyiapkan Purchase Order agar tidak terkunci pada satu line item.
2. Menyiapkan Goods Receipt agar create/posting dapat memproses banyak line.
3. Menjaga backward compatibility dengan form lama yang masih mengirim satu line.
4. Memastikan Goods Receipt mengurangi `purchase_order_items.qty_remaining` dan menambah stock ledger untuk seluruh line.
5. Menyiapkan fondasi 3-way matching PO -> GR -> AP Invoice.

## Perubahan Source Code

### `app/Controllers/CommercialOrder.php`

- Method `createSalesOrder()` dan `createPurchaseOrder()` sekarang membaca payload `lines[]`.
- Payload lama `product_id`, `qty`, dan `unit_price` tetap didukung melalui normalisasi fallback.
- Validasi header dipisahkan dari validasi line agar multi-line bisa berkembang tanpa mengubah kontrak header.
- Minimal satu line valid wajib ada.

### `app/Models/CommercialOrderWriteModel.php`

- `createSalesOrder()` dan `createPurchaseOrder()` sekarang menerima multi-line payload.
- Setiap line divalidasi terhadap produk tenant aktif.
- Subtotal, tax amount, dan total header dihitung dari seluruh line.
- Purchase Order line mengisi `qty_ordered` dan `qty_remaining` sebesar quantity awal.
- Audit event menyertakan `line_count`.
- Backward compatibility tetap dijaga: payload single-line lama tetap diproses sebagai satu item.

### `app/Models/GoodsReceiptWriteModel.php`

- `createDraftReceipt()` sekarang dapat menerima banyak item melalui `items[]`.
- Payload lama `purchase_order_item_id` dan `qty_received` tetap didukung.
- Validasi sisa PO dilakukan per line.
- `total_qty` dan `total_amount` header GR dihitung dari semua line.
- `postReceipt()` tidak lagi mengambil satu row pertama dari `goods_receipt_items`.
- Posting memproses seluruh item GR:
  - mengurangi `purchase_order_items.qty_remaining`,
  - menambah/membuat `stock_balances`,
  - membuat `stock_movements` immutable per item,
  - mengubah status setiap item menjadi `posted`,
  - mengubah status header menjadi `posted`.

## Status UI

Form Skote lama masih dapat dipakai untuk membuat satu line. Backend sudah siap untuk payload multi-line sehingga UI berikutnya dapat menambahkan dynamic line table tanpa perlu mengubah service utama lagi.

## Batas Tahap Ini

Belum dikerjakan pada tahap ini:

- Dynamic multi-line editor pada UI Sales/Purchase Order.
- Dynamic multi-line editor pada UI Goods Receipt.
- Approval submit/approve untuk PO dan GR.
- Status partial/closed pada PO setelah seluruh line diterima.
- AP Invoice dan 3-way matching aktual.
- Period lock purchase/inventory saat GR posting.
- Costing/GRNI journal posting.

## Verifikasi Manual

Jalankan:

```bash
php spark migrate --all
php spark db:seed App\Database\Seeds\MultiCompanyDemoSeeder
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```

Uji dari UI:

1. Login sebagai user purchasing/owner demo.
2. Pilih company `PENA`.
3. Buka `Purchasing > Purchase Order`.
4. Buat PO draft dari form lama satu item.
5. Buka `Purchasing > Goods Receipt`.
6. Buat GR dari PO tersebut.
7. Klik Post.
8. Pastikan:
   - status GR menjadi `posted`,
   - `qty_remaining` PO item berkurang,
   - `stock_balances.qty_on_hand` bertambah,
   - `stock_movements` memiliki movement `receipt_in`.

## Next Step

Tahap berikutnya yang disarankan:

1. Tambahkan dynamic multi-line editor pada Purchase Order dan Sales Order UI.
2. Tambahkan dynamic multi-line editor pada Goods Receipt UI.
3. Tambahkan PO status recalculation: `draft`, `partial_received`, `received`, `closed`.
4. Lanjut ke AP Invoice MVP agar flow PO -> GR -> AP Invoice siap untuk AI/OCR invoice extraction.
