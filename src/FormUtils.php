<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\SATException;

class FormUtils
{
    public static function extractForm(string $html): FormExtractionResult
    {
        if (! preg_match('/<form[^>]+action="([^"]+)"[^>]*>/i', $html, $m)) {
            throw new SATException('Not found SAML form in the response');
        }

        $action = html_entity_decode($m[1]);

        $fields = [];
        if (preg_match_all('/<input[^>]+type="hidden"[^>]*>/i', $html, $inputs)) {
            foreach ($inputs[0] as $inputHtml) {
                if (
                    preg_match('/name="([^"]+)"/i', $inputHtml, $n)
                    && preg_match('/value="([^"]*)"/i', $inputHtml, $v)
                ) {
                    $fields[$n[1]] = html_entity_decode($v[1]);
                }
            }
        }

        return new FormExtractionResult($action, $fields);
    }
}
