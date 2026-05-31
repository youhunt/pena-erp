# Document Processing Implementation Foundation

Dokumen ini mencatat implementasi awal modul AI/OCR Document Processing pada Pena ERP.

## Status

Tahap foundation sudah ditambahkan ke `main` sebagai pondasi runtime, bukan sekadar blueprint.

## File Baru

- `app/Database/Migrations/2026-05-31-000001_CreateDocumentProcessingFoundation.php`
- `app/Database/Migrations/2026-05-31-000002_CreateDocumentProcessingReviewTables.php`
- `app/Services/DocumentProcessing/OcrEngineInterface.php`
- `app/Services/DocumentProcessing/NullOcrEngine.php`
- `app/Models/DocumentProcessingReadModel.php`
- `app/Controllers/DocumentProcessing.php`
- `app/Views/document_processing/index.php`
- `app/Views/document_processing/review.php`

## Tabel Foundation

- `document_uploads`
- `document_processing_jobs`
- `document_ocr_results`
- `document_ai_extractions`
- `document_extraction_fields`
- `document_extraction_items`
- `document_validation_logs`
- `document_conversion_links`

## Akses UI

Menu demo existing `AI Document Processing` masih memakai route `workspace/modules/documents`. Controller `Workspace::module()` sekarang merender view `document_processing/index` ketika module code adalah `documents`.

Route langsung `/documents` belum dipakai agar tidak perlu mengubah route utama terlalu besar pada tahap ini.

## Prinsip Keamanan

- Semua read model wajib memakai `company_id` dari tenant context aktif.
- AI/OCR tidak boleh langsung posting transaksi ERP.
- Output OCR dan AI hanya menjadi proposal untuk review dan conversion link.
- Conversion final ke SO/PO/AP/AR/GR/DO harus dilakukan melalui service transaksi existing.

## Command Verifikasi

```bash
php spark migrate --all
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result
```

## Next Step

1. Tambahkan write model upload yang menyimpan file ke private storage.
2. Tambahkan route POST upload apabila route patch sudah aman.
3. Tambahkan OCR worker command untuk memproses `document_processing_jobs`.
4. Tambahkan AI extraction adapter dengan schema-constrained output.
5. Tambahkan review correction form untuk `document_extraction_fields` dan `document_extraction_items`.
6. Tambahkan conversion service awal: customer order menjadi Sales Order draft.
