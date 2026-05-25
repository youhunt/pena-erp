<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;
use SplFileObject;

final class RegionImportService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * @return array{provinces: int, regencies: int, districts: int, villages: int}
     */
    public function importDirectory(string $directory, string $sourceVersion): array
    {
        $resolvedDirectory = realpath($directory);

        if ($resolvedDirectory === false && ! str_starts_with($directory, ROOTPATH)) {
            $resolvedDirectory = realpath(ROOTPATH . ltrim($directory, '/\\'));
        }

        if ($resolvedDirectory === false || ! is_dir($resolvedDirectory)) {
            throw new RuntimeException("Direktori wilayah tidak ditemukan: {$directory}");
        }

        $directory = rtrim($resolvedDirectory, DIRECTORY_SEPARATOR . '/');

        return $this->importRows(
            $this->rows($directory . '/provinces.csv', ['code', 'name']),
            $this->rows($directory . '/regencies.csv', ['code', 'province_code', 'name', 'type']),
            $this->rows($directory . '/districts.csv', ['code', 'regency_code', 'name']),
            $this->rows($directory . '/villages.csv', ['code', 'district_code', 'name', 'type', 'postal_code']),
            $sourceVersion,
        );
    }

    /**
     * @param list<array<string, string>> $provinces
     * @param list<array<string, string>> $regencies
     * @param list<array<string, string>> $districts
     * @param list<array<string, string>> $villages
     *
     * @return array{provinces: int, regencies: int, districts: int, villages: int}
     */
    public function importRows(
        array $provinces,
        array $regencies,
        array $districts,
        array $villages,
        string $sourceVersion,
    ): array {
        if ($sourceVersion === '') {
            throw new RuntimeException('Source version wajib diisi.');
        }

        $counts = ['provinces' => 0, 'regencies' => 0, 'districts' => 0, 'villages' => 0];
        $now    = date('Y-m-d H:i:s');

        $this->db->transStart();

        foreach ($provinces as $row) {
            $this->upsert('provinces', $row['code'], [
                'name'           => $row['name'],
                'source_version' => $sourceVersion,
                'is_active'      => true,
            ], $now);
            $counts['provinces']++;
        }

        foreach ($regencies as $row) {
            $this->upsert('regencies', $row['code'], [
                'province_id'    => $this->idForCode('provinces', $row['province_code']),
                'name'           => $row['name'],
                'type'           => $row['type'],
                'source_version' => $sourceVersion,
                'is_active'      => true,
            ], $now);
            $counts['regencies']++;
        }

        foreach ($districts as $row) {
            $this->upsert('districts', $row['code'], [
                'regency_id'     => $this->idForCode('regencies', $row['regency_code']),
                'name'           => $row['name'],
                'source_version' => $sourceVersion,
                'is_active'      => true,
            ], $now);
            $counts['districts']++;
        }

        foreach ($villages as $row) {
            $this->upsert('villages', $row['code'], [
                'district_id'    => $this->idForCode('districts', $row['district_code']),
                'name'           => $row['name'],
                'type'           => $row['type'],
                'postal_code'    => $row['postal_code'] !== '' ? $row['postal_code'] : null,
                'source_version' => $sourceVersion,
                'is_active'      => true,
            ], $now);
            $counts['villages']++;
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Import wilayah gagal dan transaksi dibatalkan.');
        }

        return $counts;
    }

    /**
     * @param list<string> $requiredHeaders
     *
     * @return list<array<string, string>>
     */
    private function rows(string $path, array $requiredHeaders): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("File wilayah tidak ditemukan: {$path}");
        }

        $file    = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $headers = $file->fgetcsv();

        if (! is_array($headers)) {
            throw new RuntimeException("Header CSV tidak valid: {$path}");
        }

        $headers = array_map(static fn ($header): string => trim((string) $header), $headers);

        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw new RuntimeException("Header {$requiredHeader} tidak ditemukan pada {$path}");
            }
        }

        $rows = [];

        foreach ($file as $lineNumber => $values) {
            if ($lineNumber === 0) {
                continue;
            }

            if (! is_array($values) || $values === [null]) {
                continue;
            }

            $values = array_map(static fn ($value): string => trim((string) $value), $values);
            $row    = array_combine($headers, array_pad($values, count($headers), ''));

            if ($row === false || ($row['code'] ?? '') === '') {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<string, bool|int|string|null> $values
     */
    private function upsert(string $table, string $code, array $values, string $now): void
    {
        $existing = $this->db->table($table)->where('code', $code)->get()->getFirstRow('array');

        if ($existing === null) {
            $this->db->table($table)->insert(['code' => $code, 'created_at' => $now] + $values);

            return;
        }

        $this->db->table($table)->where('id', $existing['id'])->update(['updated_at' => $now] + $values);
    }

    private function idForCode(string $table, string $code): int
    {
        $row = $this->db->table($table)->select('id')->where('code', $code)->get()->getFirstRow('array');

        if ($row === null) {
            throw new RuntimeException("Parent wilayah {$table}:{$code} belum tersedia.");
        }

        return (int) $row['id'];
    }
}
