<?php

declare(strict_types=1);

namespace App\Services\DocumentProcessing;

final class NullOcrEngine implements OcrEngineInterface
{
    public function extract(string $absolutePath, array $options = []): array
    {
        return [
            'engine'         => 'null',
            'engine_version' => 'dev',
            'language'       => (string) ($options['language'] ?? 'ind+eng'),
            'raw_text'       => '',
            'raw_json'       => null,
            'page_count'     => null,
            'avg_confidence' => null,
        ];
    }
}
