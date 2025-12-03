<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\PdfDownloadException;

readonly class PdfDownloader
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function download(string $rfc, string $referer): string
    {
        $response = $this->client->request('POST', URL::$pdf, [
            RequestOptions::JSON => [
                'canal' => 'G',
                'curp' => '',
                'idCorrelacion' => uniqid(), // Requerido: funciona como un identificador único
                'ip' => '127.0.0.1',
                'rfc' => $rfc,
                'tipoConsulta' => 'COMPLETA',
                'tipoReporte' => '32D',
                'usuario' => $rfc,
                'rfcCorto' => $rfc
            ],
            'headers' => Headers::merge([
                'Referer' => $referer,
            ]),
        ]);

        $data = json_decode((string)$response->getBody());

        if ($data->Respuesta->Exito !== true) {
            throw new PdfDownloadException('Error obtaining PDF: ' . $data->Respuesta->Mensaje);
        }

        return base64_decode((string)$data->ContenidoBase64);
    }
}
