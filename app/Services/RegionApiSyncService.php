<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use RuntimeException;
use Throwable;

final class RegionApiSyncService
{
    private ?Closure $fetcher;

    public function __construct(private ?RegionImportService $importer = null, ?callable $fetcher = null)
    {
        $this->importer ??= new RegionImportService();
        $this->fetcher = $fetcher !== null ? Closure::fromCallable($fetcher) : null;
    }

    /**
     * @return array{provinces: int, regencies: int, districts: int, villages: int}
     */
    public function sync(string $baseUrl, string $token, string $sourceVersion): array
    {
        if (trim($token) === '') {
            throw new RuntimeException('Token API wilayah belum dikonfigurasi pada regions.apiToken.');
        }

        $provinces = $this->mapProvinces($this->fetch($baseUrl, $token, '/provinsi/'));
        $regencies = $this->mapRegencies($this->fetch($baseUrl, $token, '/kabupaten/'));
        $districts = $this->mapDistricts($this->fetch($baseUrl, $token, '/kecamatan/'));
        $villages  = $this->mapVillages($this->fetch($baseUrl, $token, '/desa/'));

        return $this->importer->importRows($provinces, $regencies, $districts, $villages, $sourceVersion);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetch(string $baseUrl, string $token, string $path): array
    {
        if ($this->fetcher !== null) {
            return $this->payloadRows(($this->fetcher)($path));
        }

        try {
            $response = service('curlrequest')->get(rtrim($baseUrl, '/') . $path, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'allow_redirects' => true,
                'http_errors'     => false,
                'timeout'         => 120,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException("API wilayah tidak dapat diakses pada {$path}.", previous: $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("API wilayah mengembalikan HTTP {$response->getStatusCode()} pada {$path}.");
        }

        $payload = json_decode($response->getBody(), true);

        if (! is_array($payload)) {
            throw new RuntimeException("Respons API wilayah tidak valid pada {$path}.");
        }

        return $this->payloadRows($payload);
    }

    /**
     * @param mixed $payload
     *
     * @return list<array<string, mixed>>
     */
    private function payloadRows(mixed $payload): array
    {
        if (! is_array($payload)) {
            throw new RuntimeException('Payload API wilayah bukan array.');
        }

        foreach (['data', 'results'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                $payload = $payload[$key];
                break;
            }
        }

        if (! array_is_list($payload)) {
            throw new RuntimeException('Payload API wilayah tidak berisi daftar data.');
        }

        return $payload;
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array<string, string>>
     */
    private function mapProvinces(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'code' => $this->required($row, 'id'),
            'name' => $this->required($row, 'description'),
        ], $rows);
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array<string, string>>
     */
    private function mapRegencies(array $rows): array
    {
        return array_map(function (array $row): array {
            $name = $this->required($row, 'description');
            $type = str_starts_with(strtoupper($name), 'KOTA ') ? 'kota' : 'kabupaten';

            return [
                'code'          => $this->required($row, 'id'),
                'province_code' => $this->required($row, 'provinsi_id'),
                'name'          => preg_replace('/^(KOTA|KABUPATEN)\s+/i', '', $name) ?? $name,
                'type'          => $type,
            ];
        }, $rows);
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array<string, string>>
     */
    private function mapDistricts(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'code'         => $this->required($row, 'id'),
            'regency_code' => $this->required($row, 'kabupaten_id'),
            'name'         => $this->required($row, 'description'),
        ], $rows);
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array<string, string>>
     */
    private function mapVillages(array $rows): array
    {
        return array_map(function (array $row): array {
            $name = $this->required($row, 'description');
            $type = strtolower(trim((string) ($row['type'] ?? '')));

            if (! in_array($type, ['desa', 'kelurahan'], true)) {
                $type = match (true) {
                    str_starts_with(strtoupper($name), 'KELURAHAN ') => 'kelurahan',
                    str_starts_with(strtoupper($name), 'DESA ')      => 'desa',
                    default                                         => 'desa_kelurahan',
                };
            }

            return [
                'code'          => $this->required($row, 'id'),
                'district_code' => $this->required($row, 'kecamatan_id'),
                'name'          => preg_replace('/^(DESA|KELURAHAN)\s+/i', '', $name) ?? $name,
                'type'          => $type,
                'postal_code'   => isset($row['postal_code']) ? trim((string) $row['postal_code']) : '',
            ];
        }, $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function required(array $row, string $key): string
    {
        $value = trim((string) ($row[$key] ?? ''));

        if ($value === '') {
            throw new RuntimeException("Field {$key} tidak tersedia pada data API wilayah.");
        }

        return $value;
    }
}
