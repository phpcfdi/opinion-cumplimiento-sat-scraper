<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

class Headers
{
    public static function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:104.0) Gecko/20100101 Firefox/104.0',
        ];
    }

    public static function merge(array $headers): array
    {
        return array_merge(self::defaultHeaders(), $headers);
    }
}
