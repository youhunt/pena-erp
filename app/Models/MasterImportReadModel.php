<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class MasterImportReadModel extends Model
{
    protected $table = 'master_import_batches';

    /**
     * @return list<array<string, mixed>>
     */
    public function batches(int $companyId): array
    {
        return $this->db->table('master_import_batches')
            ->where('company_id', $companyId)
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
    }

    public function batch(int $companyId, int $batchId): ?array
    {
        $row = $this->db->table('master_import_batches')
            ->where('company_id', $companyId)
            ->where('id', $batchId)
            ->get()
            ->getFirstRow('array');

        return $row === null ? null : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function rows(int $companyId, int $batchId): array
    {
        return $this->db->table('master_import_rows')
            ->where('company_id', $companyId)
            ->where('batch_id', $batchId)
            ->orderBy('row_number', 'ASC')
            ->limit(500)
            ->get()
            ->getResultArray();
    }
}
