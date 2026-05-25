# CodeIgniter 4 Implementation Guide

## 1. Bootstrap Target

Repository saat ini belum berisi aplikasi CI4. Saat fase foundation dimulai,
buat aplikasi CI4 versi terkunci, import asset Skote berlisensi, lalu tempatkan
module di luar `app` dengan PSR-4.

Installed dependency baseline:

```json
{
  "require": {
    "php": "^8.2",
    "codeigniter4/framework": "^4.7",
    "codeigniter4/shield": "^1.3"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "Modules/"
    }
  }
}
```

Tahap 2 telah mengunci CodeIgniter Shield sebagai identity/session provider.
Redis dan integrasi pendukung ditambahkan ketika queue/cache mulai
diimplementasikan, bukan sebagai dependency bootstrap tanpa penggunaan.

## 2. HMVC Directory Structure

```text
Modules/
|-- Auth/
|-- Dashboard/
|-- Company/
|-- Branch/
|-- Users/
|-- Roles/
|-- Inventory/
|-- Purchasing/
|-- Sales/
|-- POS/
|-- Accounting/
|-- HRM/
|-- Production/
|-- QC/
|-- CashBank/
|-- Reports/
|-- Notifications/
|-- OCR/
|-- AI/
|-- DocumentProcessing/
|   |-- Config/Routes.php
|   |-- Controllers/Api/DocumentsController.php
|   |-- DTO/UploadDocumentData.php
|   |-- Events/DocumentValidated.php
|   |-- Models/DocumentUploadModel.php
|   |-- Repositories/DocumentUploadRepository.php
|   |-- Services/DocumentProcessingService.php
|   |-- Validation/DocumentRules.php
|   `-- Views/
|-- Workflow/
`-- Settings/

app/
|-- Config/Filters.php
|-- Filters/TenantContextFilter.php
|-- Libraries/Tenancy/TenantContext.php
`-- Views/layouts/                 # Skote shell
```

Each module follows:

```text
Config/ Controllers/ DTO/ Entities/ Events/ Models/ Repositories/
Services/ Validation/ Views/ Database/Migrations/ Database/Seeds/
```

Controller receives transport input; service owns use case; repository scopes
data; event/outbox communicates to other modules.

## 3. Routes, Filters and Authentication Adapter

```php
<?php
// Modules/DocumentProcessing/Config/Routes.php

$routes->group('api/v1/documents', [
    'namespace' => 'Modules\DocumentProcessing\Controllers\Api',
    'filter'    => 'login,tenant,permission:documents.view',
], static function ($routes): void {
    $routes->get('/', 'DocumentsController::index');
    $routes->post('/', 'DocumentsController::create', [
        'filter' => 'permission:documents.upload',
    ]);
    $routes->get('(:num)/mapping', 'DocumentsController::mapping/$1', [
        'filter' => 'permission:documents.validate',
    ]);
    $routes->patch('(:num)/mapping', 'DocumentsController::correct/$1', [
        'filter' => 'permission:documents.validate',
    ]);
    $routes->post('(:num)/draft', 'DocumentsController::createDraft/$1', [
        'filter' => 'permission:documents.create_draft',
    ]);
});
```

`AuthGatewayInterface` prevents ERP modules binding to provider package:

```php
<?php
namespace App\Auth;

interface AuthGatewayInterface
{
    public function userId(): ?int;
    public function isLoggedIn(): bool;
    public function hasPlatformPermission(string $permission): bool;
}

final class ShieldAuthGateway implements AuthGatewayInterface
{
    public function userId(): ?int
    {
        $id = auth()->id();
        return $id === null ? null : (int) $id;
    }

    public function isLoggedIn(): bool
    {
        return auth()->loggedIn();
    }

    public function hasPlatformPermission(string $permission): bool
    {
        return auth()->user()?->can($permission) ?? false;
    }
}
```

Tenant filter loads context from membership, never payload:

```php
<?php
namespace App\Filters;

use App\Auth\AuthGatewayInterface;
use App\Tenancy\TenantContextStore;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

final class TenantContextFilter implements FilterInterface
{
    public function __construct(
        private readonly AuthGatewayInterface $auth,
        private readonly TenantContextStore $contexts
    ) {
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = $this->auth->userId();
        $companyId = (int) session('active_company_id');
        $branchId = session('active_branch_id') ? (int) session('active_branch_id') : null;

        if ($userId === null || ! $this->contexts->canAccess($userId, $companyId, $branchId)) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'Tenant access denied']);
        }

        $this->contexts->activate($userId, $companyId, $branchId);
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
```

## 4. Example Migration: Document Intake and Queue

Migration berikut menjadi pola untuk tabel `T+A`; migration lanjutan membuat
delapan tabel AI lain dari katalog data.

```php
<?php
namespace Modules\DocumentProcessing\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateDocumentIntakeTables extends Migration
{
    private function tenantAuditFields(): array
    {
        return [
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'branch_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ];
    }

    public function up(): void
    {
        $this->forge->addField(array_merge([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'storage_key' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'file_size' => ['type' => 'BIGINT', 'unsigned' => true],
            'sha256' => ['type' => 'CHAR', 'constraint' => 64],
            'page_count' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'source' => ['type' => 'VARCHAR', 'constraint' => 20],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30],
        ], $this->tenantAuditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'uuid']);
        $this->forge->addKey(['company_id', 'sha256']);
        $this->forge->addKey(['company_id', 'status', 'created_at']);
        $this->forge->createTable('document_uploads');

        $this->forge->addField(array_merge([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'stage' => ['type' => 'VARCHAR', 'constraint' => 30],
            'job_key' => ['type' => 'CHAR', 'constraint' => 64],
            'priority' => ['type' => 'INT', 'default' => 100],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'attempts' => ['type' => 'INT', 'default' => 0],
            'available_at' => ['type' => 'DATETIME'],
            'locked_at' => ['type' => 'DATETIME', 'null' => true],
            'locked_by' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'last_error' => ['type' => 'TEXT', 'null' => true],
        ], $this->tenantAuditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'job_key']);
        $this->forge->addKey(['status', 'available_at', 'priority']);
        $this->forge->addForeignKey('document_upload_id', 'document_uploads', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('ocr_processing_queue');
    }

    public function down(): void
    {
        $this->forge->dropTable('ocr_processing_queue', true);
        $this->forge->dropTable('document_uploads', true);
    }
}
```

Production migrations add foreign keys for company/branch/audit actor after the
base tenant tables and auth provider tables exist.

## 5. DTO, Validation and API Resource

```php
<?php
namespace Modules\DocumentProcessing\DTO;

final readonly class UploadDocumentData
{
    public function __construct(
        public string $clientFilename,
        public string $mimeType,
        public int $fileSize,
        public string $source,
        public ?string $documentType
    ) {
    }
}
```

Validation rules applied before storage:

```php
protected array $uploadRules = [
    'document' => [
        'label' => 'Document',
        'rules' => 'uploaded[document]|max_size[document,20480]'
            . '|ext_in[document,pdf,jpg,jpeg,png,webp]'
            . '|mime_in[document,application/pdf,image/jpeg,image/png,image/webp]',
    ],
    'source' => 'required|in_list[web,mobile,api]',
    'document_type' => 'permit_empty|in_list[purchase_order,customer_order,supplier_invoice,delivery_order,receipt,surat_jalan,faktur,unknown]',
];
```

Do not rely on extension/MIME alone: service must MIME-sniff decoded bytes,
malware scan and re-encode images before OCR.

```php
<?php
namespace Modules\DocumentProcessing\Resources;

final class DocumentResource
{
    public static function make(array $document): array
    {
        return [
            'id' => (int) $document['id'],
            'uuid' => $document['uuid'],
            'type' => $document['document_type'],
            'status' => $document['status'],
            'filename' => $document['original_name'],
            'created_at' => $document['created_at'],
        ];
    }
}
```

## 6. Tenant-Scoped Repository and Service

Model CI4 mengaktifkan soft delete dan audit field; `company_id` tetap dipaksa
oleh repository/service, bukan diisi client:

```php
<?php
namespace Modules\DocumentProcessing\Models;

use CodeIgniter\Model;

final class DocumentUploadModel extends Model
{
    protected $table = 'document_uploads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'uuid', 'company_id', 'branch_id', 'document_type', 'original_name',
        'storage_key', 'mime_type', 'file_size', 'sha256', 'page_count',
        'source', 'status', 'created_by', 'updated_by',
    ];
}
```

```php
<?php
namespace Modules\DocumentProcessing\Repositories;

use App\Tenancy\TenantContext;
use Modules\DocumentProcessing\Models\DocumentUploadModel;

final class DocumentUploadRepository
{
    public function __construct(
        private readonly DocumentUploadModel $model,
        private readonly TenantContext $tenant
    ) {
    }

    public function findOwned(int $id): ?array
    {
        return $this->model
            ->where('company_id', $this->tenant->companyId)
            ->where('id', $id)
            ->where('deleted_at', null)
            ->first();
    }

    public function findDuplicate(string $sha256): ?array
    {
        return $this->model
            ->where('company_id', $this->tenant->companyId)
            ->where('sha256', $sha256)
            ->whereNotIn('status', ['rejected', 'quarantined'])
            ->first();
    }
}
```

```php
<?php
namespace Modules\DocumentProcessing\Services;

use App\Tenancy\TenantContext;
use Modules\DocumentProcessing\DTO\UploadDocumentData;
use Modules\DocumentProcessing\Repositories\DocumentUploadRepository;

final class DocumentProcessingService
{
    public function __construct(
        private readonly TenantContext $tenant,
        private readonly SecureDocumentStorage $storage,
        private readonly MalwareScanner $scanner,
        private readonly DocumentUploadRepository $documents,
        private readonly ProcessingQueue $queue,
        private readonly AuditWriter $audit
    ) {
    }

    public function upload(UploadDocumentData $data, string $temporaryPath): array
    {
        $verified = $this->storage->inspectAndNormalize($temporaryPath, $data->mimeType);
        $this->scanner->assertClean($verified->path);

        if ($this->documents->findDuplicate($verified->sha256) !== null) {
            throw new DuplicateDocumentException('The document was already uploaded.');
        }

        $document = $this->storage->persistMetadataAndPrivateObject($this->tenant, $data, $verified);
        $this->queue->enqueuePreprocess($this->tenant, (int) $document['id']);
        $this->audit->record('DOCUMENT_UPLOADED', 'document_uploads', (int) $document['id']);

        return $document;
    }
}
```

`persistMetadataAndPrivateObject()` must use a database transaction or outbox
compensation so an upload cannot silently exist without metadata/job.

## 7. Controller and Seeder Examples

The API controller delegates all security-sensitive work to validation and the
service:

```php
<?php
namespace Modules\DocumentProcessing\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\DocumentProcessing\DTO\UploadDocumentData;
use Modules\DocumentProcessing\Resources\DocumentResource;
use Modules\DocumentProcessing\Services\DocumentProcessingService;

final class DocumentsController extends ResourceController
{
    public function __construct(private readonly DocumentProcessingService $documents)
    {
    }

    public function create()
    {
        if (! $this->validate($this->uploadRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $file = $this->request->getFile('document');
        $data = new UploadDocumentData(
            $file->getClientName(),
            $file->getClientMimeType(),
            $file->getSize(),
            (string) $this->request->getPost('source'),
            $this->request->getPost('document_type')
        );

        $document = $this->documents->upload($data, $file->getTempName());
        return $this->respondCreated(DocumentResource::make($document));
    }
}
```

In final code, put `$uploadRules` on a request validator/rule provider injected
into the controller so it is shared by HTML and API flows.

```php
<?php
namespace Modules\Roles\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class DefaultRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'documents.upload', 'documents.view', 'documents.validate',
            'documents.create_draft', 'purchasing.po.view',
            'purchasing.po.approve', 'finance.journal.post',
        ];

        foreach ($permissions as $code) {
            $this->db->table('permissions')->ignore(true)->insert([
                'company_id' => 1, // Replaced by ProvisionTenantService in production.
                'code' => $code,
                'name' => $code,
                'module' => strtok($code, '.'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
```

Tenant provisioning, not a hard-coded seeder ID, creates production tenant
permissions and roles transactionally.

## 8. OCR Service Contract

CI4 speaks to a private OCR service over authenticated internal HTTP or a queue
result callback. The OCR worker does not expose a public arbitrary-file URL.

```php
<?php
namespace Modules\OCR\Services;

final readonly class OcrRequest
{
    public function __construct(
        public string $signedInternalObjectKey,
        public array $languages,
        public bool $detectTables = true,
        public bool $autoRotate = true
    ) {
    }
}

interface OcrEngineInterface
{
    public function extract(OcrRequest $request): OcrResult;
}

final class PaddleOcrEngine implements OcrEngineInterface
{
    public function __construct(private readonly InternalOcrHttpClient $client)
    {
    }

    public function extract(OcrRequest $request): OcrResult
    {
        return $this->client->postExtraction([
            'object_key' => $request->signedInternalObjectKey,
            'languages' => $request->languages,
            'preprocess' => ['rotate' => $request->autoRotate, 'deskew' => true, 'denoise' => true],
            'tables' => $request->detectTables,
        ]);
    }
}
```

## 9. AI Extraction Service Contract

The application should not scatter provider calls through controllers. The
provider adapter receives sanitized OCR data and must return schema-validated
structured extraction with model/prompt version metadata.

```php
<?php
namespace Modules\AI\Services;

final readonly class ExtractionRequest
{
    public function __construct(
        public string $documentTypeHint,
        public string $ocrText,
        public array $tables,
        public array $allowedSchema,
        public string $promptVersion
    ) {
    }
}

interface DocumentExtractorInterface
{
    public function extract(ExtractionRequest $request): ExtractionResult;
}

final class OpenAiDocumentExtractor implements DocumentExtractorInterface
{
    public function __construct(
        private readonly StructuredLlmClient $client,
        private readonly ExtractionSchemaValidator $validator
    ) {
    }

    public function extract(ExtractionRequest $request): ExtractionResult
    {
        $payload = $this->client->structuredExtract(
            instruction: 'Extract only visible ERP document fields. Do not infer missing amounts.',
            input: ['ocr_text' => $request->ocrText, 'tables' => $request->tables],
            jsonSchema: $request->allowedSchema,
            metadata: ['prompt_version' => $request->promptVersion]
        );

        return $this->validator->toResult($payload);
    }
}
```

`StructuredLlmClient` is an infrastructure adapter configured using secret
environment variables, timeout, cost/token cap, redaction policy and provider
response audit. Model IDs and transport syntax must be selected against current
official provider documentation during implementation rather than hard-coded
into domain services.

## 10. Queue Worker, Events and Draft Generation

```php
<?php
namespace Modules\DocumentProcessing\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class WorkDocuments extends BaseCommand
{
    protected $group = 'Documents';
    protected $name = 'documents:work';
    protected $description = 'Processes queued document OCR and AI stages.';

    public function run(array $params): void
    {
        $worker = service('documentWorker');
        while ($job = $worker->claimNext()) {
            try {
                $worker->process($job);
                $worker->complete($job);
            } catch (\Throwable $exception) {
                $worker->failOrRetry($job, $exception);
                CLI::error($exception->getMessage());
            }
        }
    }
}
```

```php
<?php
namespace Modules\DocumentProcessing\Events;

final readonly class DocumentValidated
{
    public function __construct(
        public int $companyId,
        public int $documentId,
        public int $mappingId,
        public string $targetType,
        public string $idempotencyKey
    ) {
    }
}
```

`ValidationService` saves deterministic outcomes and an outbox
`DocumentValidated`. A consumer calls `PurchasingInvoiceService::createDraft()`
or `SalesOrderService::createDraft()` idempotently. Posting remains inside the
target module after workflow approval.

## 11. Seeders and Tests

Seeders required:

- Versioned global Indonesian regional reference: provinces, regencies
  (kabupaten/kota), districts (kecamatan), and villages (desa/kelurahan).
- Platform admin and demo company/branches.
- Default roles/permission matrix and menu permission mapping.
- Base currencies, taxes, UOM, COA, numbering sequences and workflow definitions.
- Optional anonymized OCR validation fixtures; never seed customer production files.

Import master wilayah dilakukan dengan command:

```bash
php spark regions:import <directory-csv-resmi> <source_version>
```

Direktori input memuat `provinces.csv`, `regencies.csv`, `districts.csv`, dan
`villages.csv`. Import bersifat idempotent berdasarkan kode wilayah dan wajib
menyimpan identitas versi Kepmendagri/dataset resmi yang telah disetujui.

Minimum automated tests:

| Test Suite | Must Prove |
| --- | --- |
| Reference data tests | Regional hierarchy import is idempotent and address FK rejects invalid village references |
| Tenancy feature tests | Cross-company read/update/file preview always denied |
| Permission tests | Route and service enforce permission and approval amount |
| Accounting tests | Every posting balances; locked periods reject writes; reversal works |
| Inventory tests | Movement idempotency and concurrent quantity handling |
| Document tests | file validation, duplicate hashing, retries, draft idempotency |
| AI evaluation tests | Fixed documents meet agreed field accuracy/tolerance; correction history preserved |
| Security tests | CSRF/AJAX flow, unsafe uploads quarantined, audit written |
