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

        if (0 === $scriptNodes->count()) {
            return null;
        }

        $script = $scriptNodes->html();

        if (! preg_match("/(?:window|top)\.location\.href\s*=\s*[\"']([^\"']+)[\"']/", $script, $matches)) {
            return null;
        }

        return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
    }

    public function hasCaptcha(string $html): bool
    {
        return str_contains($html, 'divCaptcha');
    }
}
