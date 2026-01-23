<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use Stringable;

interface ScraperInterface
{
    public function download(): Stringable;
}
