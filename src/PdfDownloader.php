<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\PdfDownloadException;
use Psr\Http\Message\StreamInterface;
use StdClass;

/** @internal */
final readonly class PdfDownloader
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function download(string $rfc, string $referer): StreamInterface
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
                'rfcCorto' => $rfc,
            ],
            'headers' => Headers::merge([
                'Referer' => $referer,
            ]),
        ]);

        $data = json_decode((string) $response->getBody());

        if (! $data instanceof StdClass) {
            throw new PdfDownloadException('The response does not have a valid JSON object response.');
        }

        if (! isset($data->Respuesta) || ! $data->Respuesta instanceof StdClass) {
            throw new PdfDownloadException('The response does not have the "Respuesta" element.');
        }

        $result = isset($data->Respuesta->Exito) && is_bool($data->Respuesta->Exito) ? $data->Respuesta->Exito : false;

        if (true !== $result) {
            $message = isset($data->Respuesta->Mensaje) && is_string($data->Respuesta->Mensaje) ? $data->Respuesta->Mensaje : '(sin mensaje de error)';
            throw new PdfDownloadException($message);
        }

        if (! isset($data->ContenidoBase64) || ! is_string($data->ContenidoBase64)) {
            throw new PdfDownloadException('The response does not have the content on Base64.');
        }

        return Utils::streamFor((string) base64_decode($data->ContenidoBase64));
    }
}
