<?php

declare(strict_types=1);

namespace App\Services\DocumentProcessing;

interface OcrEngineInterface
{
    /**
     * @return array{
     *     engine:string,
     *     engine_version:?string,
     *     language:string,
     *     raw_text:string,
     *     raw_json:?array,
     *     page_count:?int,
     *     avg_confidence:?float
     * }
     */
    public function extract(string $absolutePath, array $options = []): array;
}
