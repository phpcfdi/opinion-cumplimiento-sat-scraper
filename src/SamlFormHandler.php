<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use GuzzleHttp\ClientInterface;

/** @internal */
final readonly class SamlFormHandler
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function handleSamlRedirect(string $redirectUrl, string $referer): string
    {
        $response = $this->client->request('GET', $redirectUrl, [
            'headers' => Headers::merge([
                'Referer' => $referer,
            ]),
        ]);

        $html = (string) $response->getBody();

        $form = FormUtils::extractForm($html);

        $this->client->request('POST', $form->action, [
            'headers' => Headers::merge([
                'Referer' => $redirectUrl,
            ]),
            'form_params' => $form->fields,
        ]);

        return $form->action;
    }
}
