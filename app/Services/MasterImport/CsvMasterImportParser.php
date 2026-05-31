<?php

declare(strict_types=1);

namespace App\Services\MasterImport;

use RuntimeException;

final class CsvMasterImportParser
{
    /**
     * @return array{headers:list<string>, rows:list<array<string, string>>}
     */
    public function parse(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('File import tidak ditemukan.');
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('File import tidak dapat dibaca.');
        }

        $headers = [];
        $rows = [];
        $rowNumber = 0;

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;
            $data = array_map(static fn ($value): string => trim((string) $value), $data);

            if ($rowNumber === 1) {
                $headers = array_map(static fn (string $value): string => strtolower(trim($value)), $data);
                continue;
            }

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = $data[$index] ?? '';
            }

            $rows[] = $row;
        }

        fclose($handle);

        if ($headers === []) {
            throw new RuntimeException('Header CSV kosong.');
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @param list<string> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim($value) !== '') {
                return false;
            }
        }

        return true;
    }
}
