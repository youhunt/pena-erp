<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class MasterImportWriteModel extends Model
{
    protected $table = 'master_import_batches';

    public function createBatch(int $companyId, ?int $branchId, string $type, string $filename, ?string $path, ?string $hash, int $userId): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('master_import_batches')->insert([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'import_type' => $type,
            'original_filename' => $filename,
            'stored_path' => $path,
            'file_hash' => $hash,
            'status' => 'uploaded',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * @param list<array<string, string>> $rows
     * @param list<string> $requiredColumns
     */
    public function stageRows(int $companyId, int $batchId, array $rows, array $requiredColumns): void
    {
        $now = date('Y-m-d H:i:s');
        $valid = 0;
        $error = 0;
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;
            $message = $this->validateRequired($row, $requiredColumns);
            $status = $message === null ? 'valid' : 'error';

            if ($status === 'valid') {
                $valid++;
            } else {
                $error++;
            }

            $this->db->table('master_import_rows')->insert([
                'company_id' => $companyId,
                'batch_id' => $batchId,
                'row_number' => $rowNumber,
                'row_status' => $status,
                'raw_data' => json_encode($row, JSON_THROW_ON_ERROR),
                'mapped_data' => json_encode($row, JSON_THROW_ON_ERROR),
                'error_message' => $message,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->db->table('master_import_batches')->where('id', $batchId)->update([
            'status' => $error > 0 ? 'validated_with_errors' : 'validated',
            'total_rows' => count($rows),
            'valid_rows' => $valid,
            'error_rows' => $error,
            'updated_at' => $now,
        ]);
    }

    public function commitBatch(int $companyId, int $batchId): int
    {
        $batch = $this->db->table('master_import_batches')
            ->where('company_id', $companyId)
            ->where('id', $batchId)
            ->get()
            ->getFirstRow('array');

        if ($batch === null || (int) $batch['error_rows'] > 0) {
            return 0;
        }

        $rows = $this->db->table('master_import_rows')
            ->where('company_id', $companyId)
            ->where('batch_id', $batchId)
            ->where('row_status', 'valid')
            ->orderBy('row_number', 'ASC')
            ->get()
            ->getResultArray();

        $imported = 0;
        foreach ($rows as $row) {
            $data = json_decode((string) $row['mapped_data'], true) ?: [];
            $targetId = match ((string) $batch['import_type']) {
                'units_of_measure' => $this->upsertUom($companyId, $data),
                'product_categories' => $this->upsertCategory($companyId, $data),
                default => 0,
            };

            if ($targetId <= 0) {
                continue;
            }

            $imported++;
            $this->db->table('master_import_rows')->where('id', $row['id'])->update([
                'row_status' => 'imported',
                'target_table' => (string) $batch['import_type'],
                'target_id' => $targetId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->table('master_import_batches')->where('id', $batchId)->update([
            'status' => 'imported',
            'imported_rows' => $imported,
            'finished_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $imported;
    }

    /**
     * @param array<string, string> $row
     * @param list<string> $requiredColumns
     */
    private function validateRequired(array $row, array $requiredColumns): ?string
    {
        $missing = [];
        foreach ($requiredColumns as $column) {
            if (! array_key_exists($column, $row) || trim((string) $row[$column]) === '') {
                $missing[] = $column;
            }
        }

        return $missing === [] ? null : 'Kolom wajib kosong: ' . implode(', ', $missing);
    }

    /** @param array<string, string> $data */
    private function upsertUom(int $companyId, array $data): int
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $existing = $this->db->table('units_of_measure')->where(['company_id' => $companyId, 'code' => $code])->get()->getFirstRow('array');
        $payload = [
            'company_id' => $companyId,
            'code' => $code,
            'name' => trim((string) ($data['name'] ?? '')),
            'precision' => (int) ($data['precision'] ?? 0),
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing !== null) {
            $this->db->table('units_of_measure')->where('id', $existing['id'])->update($payload);
            return (int) $existing['id'];
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('units_of_measure')->insert($payload);
        return (int) $this->db->insertID();
    }

    /** @param array<string, string> $data */
    private function upsertCategory(int $companyId, array $data): int
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $existing = $this->db->table('product_categories')->where(['company_id' => $companyId, 'code' => $code])->get()->getFirstRow('array');
        $payload = [
            'company_id' => $companyId,
            'code' => $code,
            'name' => trim((string) ($data['name'] ?? '')),
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing !== null) {
            $this->db->table('product_categories')->where('id', $existing['id'])->update($payload);
            return (int) $existing['id'];
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('product_categories')->insert($payload);
        return (int) $this->db->insertID();
    }
}
