<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\PdfDownloadException;
use PhpCfdi\OpinionCumplimientoSatScraper\PdfDownloader;
use PHPUnit\Framework\TestCase;

class PdfDownloaderTest extends TestCase
{
    public function testDownloadSuccessfullyReturnsPdfContent(): void
    {
        $rfc = 'XAXX010101000';
        $referer = 'https://ptsc32d.clouda.sat.gob.mx/dashboard';
        $pdfContent = '%PDF-1.4 test content';
        $base64Pdf = base64_encode($pdfContent);

        $responseData = [
            'Respuesta' => [
                'Exito' => true,
                'Mensaje' => 'Operación exitosa',
            ],
            'ContenidoBase64' => $base64Pdf,
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->anything(),
                $this->callback(function ($options) use ($rfc, $referer) {
                    $this->assertArrayHasKey('json', $options);
                    $this->assertArrayHasKey('headers', $options);

                    $json = $options['json'];
                    $this->assertSame('G', $json['canal']);
                    $this->assertSame('', $json['curp']);
                    $this->assertSame('127.0.0.1', $json['ip']);
                    $this->assertSame($rfc, $json['rfc']);
                    $this->assertSame('COMPLETA', $json['tipoConsulta']);
                    $this->assertSame('32D', $json['tipoReporte']);
                    $this->assertSame($rfc, $json['usuario']);
                    $this->assertSame($rfc, $json['rfcCorto']);
                    $this->assertNotEmpty($json['idCorrelacion']);

                    $this->assertSame($referer, $options['headers']['Referer']);

                    return true;
                })
            )
            ->willReturn(new Response(200, [], json_encode($responseData)));

        $downloader = new PdfDownloader($client);
        $result = $downloader->download($rfc, $referer);

        $this->assertSame($pdfContent, $result);
    }

    public function testDownloadThrowsExceptionWhenResponseNotSuccessful(): void
    {
        $rfc = 'MAGG8901015J2';
        $referer = 'https://ptsc32d.clouda.sat.gob.mx';

        $responseData = [
            'Respuesta' => [
                'Exito' => false,
                'Mensaje' => 'El RFC no tiene opinión de cumplimiento disponible',
            ],
            'ContenidoBase64' => null,
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], json_encode($responseData)));

        $downloader = new PdfDownloader($client);

        $this->expectException(PdfDownloadException::class);
        $this->expectExceptionMessage('Error obtaining PDF: El RFC no tiene opinión de cumplimiento disponible');

        $downloader->download($rfc, $referer);
    }

    public function testDownloadWithRealWorldPdfResponse(): void
    {
        $rfc = 'CAAG850615LK7';
        $referer = 'https://ptsc32d.clouda.sat.gob.mx/Assertion/Consumer/Service';

        $actualPdfHeader = '%PDF-1.4\n%âãÏÓ\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj';
        $base64Pdf = base64_encode($actualPdfHeader);

        $responseData = [
            'Respuesta' => [
                'Exito' => true,
                'Mensaje' => 'La consulta se realizó correctamente',
                'Codigo' => '200',
            ],
            'ContenidoBase64' => $base64Pdf,
            'NombreArchivo' => '32D_CAAG850615LK7_20251203.pdf',
            'TipoContenido' => 'application/pdf',
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], json_encode($responseData)));

        $downloader = new PdfDownloader($client);
        $result = $downloader->download($rfc, $referer);

        $this->assertStringStartsWith('%PDF-1.4', $result);
        $this->assertSame($actualPdfHeader, $result);
    }

    public function testDownloadSendsCorrectJsonPayload(): void
    {
        $rfc = 'VECJ880326G61';
        $referer = 'https://ptsc32d.clouda.sat.gob.mx/callback';

        $responseData = [
            'Respuesta' => ['Exito' => true, 'Mensaje' => 'OK'],
            'ContenidoBase64' => base64_encode('test'),
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->anything(),
                $this->callback(function ($options) use ($rfc) {
                    $json = $options['json'];

                    $this->assertArrayHasKey('canal', $json);
                    $this->assertArrayHasKey('curp', $json);
                    $this->assertArrayHasKey('idCorrelacion', $json);
                    $this->assertArrayHasKey('ip', $json);
                    $this->assertArrayHasKey('rfc', $json);
                    $this->assertArrayHasKey('tipoConsulta', $json);
                    $this->assertArrayHasKey('tipoReporte', $json);
                    $this->assertArrayHasKey('usuario', $json);
                    $this->assertArrayHasKey('rfcCorto', $json);

                    $this->assertSame('G', $json['canal']);
                    $this->assertSame('COMPLETA', $json['tipoConsulta']);
                    $this->assertSame('32D', $json['tipoReporte']);
                    $this->assertSame($rfc, $json['rfc']);
                    $this->assertSame($rfc, $json['usuario']);
                    $this->assertSame($rfc, $json['rfcCorto']);

                    return true;
                })
            )
            ->willReturn(new Response(200, [], json_encode($responseData)));

        $downloader = new PdfDownloader($client);
        $downloader->download($rfc, $referer);
    }
}
