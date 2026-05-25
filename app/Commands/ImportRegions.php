<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\RegionImportService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;

final class ImportRegions extends BaseCommand
{
    protected $group       = 'Pena ERP';
    protected $name        = 'regions:import';
    protected $description = 'Import master wilayah Indonesia dari empat file CSV versioned.';
    protected $usage       = 'regions:import <directory> <source_version>';
    protected $arguments   = [
        'directory'      => 'Folder berisi provinces.csv, regencies.csv, districts.csv, villages.csv.',
        'source_version' => 'Identitas regulasi/dataset sumber yang disetujui.',
    ];

    public function run(array $params): int
    {
        $directory = (string) ($params[0] ?? '');
        $version   = (string) ($params[1] ?? '');

        if ($directory === '' || $version === '') {
            CLI::error('Gunakan: php spark regions:import <directory> <source_version>');

            return EXIT_ERROR;
        }

        try {
            $counts = (new RegionImportService())->importDirectory($directory, $version);
        } catch (Throwable $e) {
            CLI::error($e instanceof RuntimeException ? $e->getMessage() : 'Import wilayah gagal.');

            return EXIT_ERROR;
        }

        CLI::write(sprintf(
            'Import selesai: %d provinsi, %d kabupaten/kota, %d kecamatan, %d desa/kelurahan.',
            $counts['provinces'],
            $counts['regencies'],
            $counts['districts'],
            $counts['villages'],
        ), 'green');

        return EXIT_SUCCESS;
    }
}
