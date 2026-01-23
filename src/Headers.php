<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

/** @internal  */
final class Headers
{
    /** @return array<string, string> */
    public static function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:104.0) Gecko/20100101 Firefox/104.0',
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    public static function merge(array $headers): array
    {
        return array_merge(static::defaultHeaders(), $headers);
    }
}
