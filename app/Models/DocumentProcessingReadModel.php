<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class DocumentProcessingReadModel extends Model
{
    protected $table = 'document_uploads';

    /**
     * @return list<array<string, mixed>>
     */
    public function documents(int $companyId): array
    {
        return $this->db->table('document_uploads d')
            ->select('d.id, d.branch_id, d.original_filename, d.mime_type, d.file_size, d.sha256_hash, d.document_type, d.source_direction, d.status, d.confidence_score, d.error_message, d.created_at, b.code AS branch_code, b.name AS branch_name')
            ->join('branches b', 'b.id = d.branch_id AND b.company_id = d.company_id', 'left')
            ->where('d.company_id', $companyId)
            ->where('d.deleted_at', null)
            ->orderBy('d.id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
    }

    public function document(int $companyId, int $documentId): ?array
    {
        $document = $this->db->table('document_uploads d')
            ->select('d.*, b.code AS branch_code, b.name AS branch_name')
            ->join('branches b', 'b.id = d.branch_id AND b.company_id = d.company_id', 'left')
            ->where('d.company_id', $companyId)
            ->where('d.id', $documentId)
            ->where('d.deleted_at', null)
            ->get()
            ->getFirstRow('array');

        return $document === null ? null : $document;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function jobs(int $companyId, int $documentId): array
    {
        return $this->db->table('document_processing_jobs')
            ->where('company_id', $companyId)
            ->where('document_upload_id', $documentId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function latestOcr(int $companyId, int $documentId): ?array
    {
        $row = $this->db->table('document_ocr_results')
            ->where('company_id', $companyId)
            ->where('document_upload_id', $documentId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getFirstRow('array');

        return $row === null ? null : $row;
    }

    public function latestExtraction(int $companyId, int $documentId): ?array
    {
        $row = $this->db->table('document_ai_extractions')
            ->where('company_id', $companyId)
            ->where('document_upload_id', $documentId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getFirstRow('array');

        return $row === null ? null : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fields(int $companyId, int $extractionId): array
    {
        return $this->db->table('document_extraction_fields')
            ->where('company_id', $companyId)
            ->where('extraction_id', $extractionId)
            ->orderBy('field_path', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function items(int $companyId, int $extractionId): array
    {
        return $this->db->table('document_extraction_items i')
            ->select('i.*, p.sku AS product_sku, p.name AS product_name, u.code AS uom_code')
            ->join('products p', 'p.id = i.product_id AND p.company_id = i.company_id', 'left')
            ->join('units_of_measure u', 'u.id = i.uom_id AND u.company_id = i.company_id', 'left')
            ->where('i.company_id', $companyId)
            ->where('i.extraction_id', $extractionId)
            ->orderBy('i.line_no', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function validationLogs(int $companyId, int $documentId): array
    {
        return $this->db->table('document_validation_logs')
            ->where('company_id', $companyId)
            ->where('document_upload_id', $documentId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function conversionLinks(int $companyId, int $documentId): array
    {
        return $this->db->table('document_conversion_links')
            ->where('company_id', $companyId)
            ->where('document_upload_id', $documentId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
