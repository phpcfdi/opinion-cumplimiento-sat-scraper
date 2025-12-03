<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

readonly class FormExtractionResult
{
    public function __construct(private string $action, private array $fields)
    {
    }

    public function getAction(string $base = ''): string
    {
        return $base . $this->action;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
