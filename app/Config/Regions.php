<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class Regions extends BaseConfig
{
    public string $apiBaseUrl = 'https://api-wilayah.belajardisiniaja.com';

    public string $apiToken = '';
}
