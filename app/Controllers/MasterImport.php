<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\MasterImportReadModel;
use App\Models\MasterImportWriteModel;
use App\Services\MasterImport\CsvMasterImportParser;
use App\Services\MasterImport\MasterImportCatalog;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

final class MasterImport extends BaseController
{
    public function index(): string
    {
        $context = $this->authorizedContext();

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'master-import']);
        }

        $companyId = (int) $context['company_id'];

        return view('master_import/index', [
            'tenantContext' => $context,
            'catalog' => (new MasterImportCatalog())->all(),
            'batches' => (new MasterImportReadModel())->batches($companyId),
        ]);
    }

    public function upload(): RedirectResponse
    {
        $context = $this->authorizedContext();

        if ($context === null) {
            return redirect()->to(site_url('workspace'))->with('error', 'Anda tidak memiliki akses import master.');
        }

        $type = (string) $this->request->getPost('import_type');
        $catalog = new MasterImportCatalog();
        $definition = $catalog->get($type);

        if ($definition === null || ! in_array($type, ['units_of_measure', 'product_categories'], true)) {
            return redirect()->to(site_url('workspace/modules/master-import'))->with('error', 'Jenis import belum didukung pada tahap ini.');
        }

        $file = $this->request->getFile('import_file');
        if ($file === null || ! $file->isValid()) {
            return redirect()->to(site_url('workspace/modules/master-import'))->with('error', 'File CSV wajib dipilih.');
        }

        $extension = strtolower($file->getExtension());
        if ($extension !== 'csv') {
            return redirect()->to(site_url('workspace/modules/master-import'))->with('error', 'Tahap ini baru mendukung file CSV.');
        }

        $dir = WRITEPATH . 'uploads/master-import/' . (int) $context['company_id'];
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $storedName = date('YmdHis') . '-' . $file->getRandomName();
        $file->move($dir, $storedName);
        $path = $dir . '/' . $storedName;
        $hash = hash_file('sha256', $path) ?: null;

        $parsed = (new CsvMasterImportParser())->parse($path);
        $missingHeaders = array_values(array_diff($definition['required_columns'], $parsed['headers']));

        if ($missingHeaders !== []) {
            return redirect()->to(site_url('workspace/modules/master-import'))->with('error', 'Header wajib tidak ditemukan: ' . implode(', ', $missingHeaders));
        }

        $write = new MasterImportWriteModel();
        $batchId = $write->createBatch(
            (int) $context['company_id'],
            $context['branch_id'] === null ? null : (int) $context['branch_id'],
            $type,
            $file->getClientName(),
            $path,
            $hash,
            (int) auth()->id(),
        );
        $write->stageRows((int) $context['company_id'], $batchId, $parsed['rows'], $definition['required_columns']);

        return redirect()->to(site_url('workspace/modules/master-import'))->with('message', 'File berhasil divalidasi sebagai batch import #' . $batchId . '.');
    }

    public function commit(int $batchId): RedirectResponse
    {
        $context = $this->authorizedContext();

        if ($context === null) {
            return redirect()->to(site_url('workspace'))->with('error', 'Anda tidak memiliki akses import master.');
        }

        $imported = (new MasterImportWriteModel())->commitBatch((int) $context['company_id'], $batchId);

        if ($imported <= 0) {
            return redirect()->to(site_url('workspace/modules/master-import'))->with('error', 'Batch tidak dapat di-commit. Pastikan tidak ada row error.');
        }

        return redirect()->to(site_url('workspace/modules/master-import'))->with('message', $imported . ' row berhasil di-import.');
    }

    public function template(string $type): ResponseInterface
    {
        $definition = (new MasterImportCatalog())->get($type);

        if ($definition === null) {
            return $this->response->setStatusCode(404)->setBody('Template tidak ditemukan.');
        }

        $headers = array_merge($definition['required_columns'], $definition['optional_columns']);
        $example = array_fill(0, count($headers), '');
        $content = implode(',', $headers) . "\n" . implode(',', $example) . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $type . '_template.csv"')
            ->setBody($content);
    }

    public function show(int $batchId): string
    {
        $context = $this->authorizedContext();

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'master-import']);
        }

        $companyId = (int) $context['company_id'];
        $model = new MasterImportReadModel();
        $batch = $model->batch($companyId, $batchId);

        if ($batch === null) {
            $this->response->setStatusCode(404);

            return view('workspace/module_denied', ['moduleCode' => 'master-import']);
        }

        return view('master_import/show', [
            'tenantContext' => $context,
            'batch' => $batch,
            'rows' => $model->rows($companyId, $batchId),
        ]);
    }

    private function authorizedContext(): ?array
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);

        if ($context === null) {
            return null;
        }

        $companyId = (int) $context['company_id'];
        $authz = new TenantAuthorizationService();

        foreach (['setup.master.manage', 'inventory.master.manage', 'sales.master.manage', 'purchasing.master.manage', 'finance.master.manage', 'master.import.manage'] as $permission) {
            if ($authz->can($userId, $companyId, $permission)) {
                return $context;
            }
        }

        return null;
    }
}
