<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use Symfony\Component\DomCrawler\Crawler;

class HtmlParser
{
    public function extractRedirectUrl(string $html): ?string
    {
        $crawler = new Crawler($html);

        $scriptNodes = $crawler->filter('script');

        if ($scriptNodes->count() === 0) {
            return null;
        }

        $script = $scriptNodes->html();

        if (! $script) {
            return null;
        }

        if (preg_match("/window\.location\.href='([^']+)'/", $script, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function hasCaptcha(string $html): bool
    {
        return str_contains($html, 'divCaptcha');
    }
}
