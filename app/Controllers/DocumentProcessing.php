<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\DocumentProcessingReadModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class DocumentProcessing extends BaseController
{
    public function index(): string
    {
        $context = $this->authorizedContext('documents.upload');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'documents']);
        }

        $companyId = (int) $context['company_id'];
        $model = new DocumentProcessingReadModel();

        return view('document_processing/index', [
            'tenantContext' => $context,
            'documents' => $model->documents($companyId),
        ]);
    }

    public function review(int $documentId): string|RedirectResponse
    {
        $context = $this->authorizedContext('documents.upload');

        if ($context === null) {
            return redirect()->to(site_url('workspace'))->with('error', 'Anda tidak memiliki akses Document Processing.');
        }

        $companyId = (int) $context['company_id'];
        $model = new DocumentProcessingReadModel();
        $document = $model->document($companyId, $documentId);

        if ($document === null) {
            return redirect()->to(site_url('workspace/modules/documents'))->with('error', 'Dokumen tidak ditemukan pada company aktif.');
        }

        $extraction = $model->latestExtraction($companyId, $documentId);
        $extractionId = $extraction === null ? 0 : (int) $extraction['id'];

        return view('document_processing/review', [
            'tenantContext' => $context,
            'document' => $document,
            'jobs' => $model->jobs($companyId, $documentId),
            'ocr' => $model->latestOcr($companyId, $documentId),
            'extraction' => $extraction,
            'fields' => $extractionId > 0 ? $model->fields($companyId, $extractionId) : [],
            'items' => $extractionId > 0 ? $model->items($companyId, $extractionId) : [],
            'validationLogs' => $model->validationLogs($companyId, $documentId),
            'conversionLinks' => $model->conversionLinks($companyId, $documentId),
        ]);
    }

    private function authorizedContext(string $permission): ?array
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);

        if ($context === null) {
            return null;
        }

        if (! (new TenantAuthorizationService())->can($userId, (int) $context['company_id'], $permission)) {
            return null;
        }

        return $context;
    }
}
