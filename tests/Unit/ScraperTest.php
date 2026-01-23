<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\OpinionCumplimientoSatScraper\Scraper;
use PHPUnit\Framework\TestCase;

final class ScraperTest extends TestCase
{
    public function testDownloadSuccessfullyReturnsOpinionPdf(): void
    {
        $rfc = 'XAXX010101000';
        $password = 'MySecurePassword123';
        $pdfContent = '%PDF-1.4 Opinión de Cumplimiento de Obligaciones Fiscales';

        $loginPageHtml = $this->createLoginPageHtml();
        $redirectPageHtml = $this->createRedirectPageHtml();
        $samlFormHtml = $this->createSamlFormHtml();
        $pdfResponse = $this->createJsonResponse($pdfContent);

        $requestSequence = [
            ['GET', 200, ''],
            ['POST', 200, $loginPageHtml],
            ['POST', 200, $redirectPageHtml],
            ['GET', 200, $samlFormHtml],
            ['POST', 200, ''],
            ['POST', 200, $pdfResponse],
        ];

        $client = $this->createMockClientWithSequence($requestSequence);

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver->method('resolve')
            ->willReturn(new CaptchaAnswer('ABC123'));

        $scraper = new Scraper($client, $captchaResolver, $rfc, $password);
        $result = (string) $scraper->download();

        $this->assertSame($pdfContent, $result);
    }

    public function testDownloadWithRealWorldRfcAndPassword(): void
    {
        $rfc = 'CAAG850615LK7';
        $password = 'P@ssw0rd2025!';
        $expectedPdf = '%PDF-1.4\n1 0 obj\n<<\n/Title (Opinión de Cumplimiento 32-D)\n/Creator (SAT)\n>>\nendobj';

        $loginPageHtml = $this->createLoginPageHtml();
        $redirectPageHtml = $this->createRedirectPageHtml();
        $samlFormHtml = $this->createSamlFormHtml();
        $pdfResponse = $this->createJsonResponse($expectedPdf);

        $requestSequence = [
            ['GET', 200, ''],
            ['POST', 200, $loginPageHtml],
            ['POST', 200, $redirectPageHtml],
            ['GET', 200, $samlFormHtml],
            ['POST', 200, ''],
            ['POST', 200, $pdfResponse],
        ];

        $client = $this->createMockClientWithSequence($requestSequence);

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver->method('resolve')
            ->willReturn(new CaptchaAnswer('5K8M2P'));

        $scraper = new Scraper($client, $captchaResolver, $rfc, $password);
        $result = (string) $scraper->download();

        $this->assertStringStartsWith('%PDF-1.4', $result);
        $this->assertStringContainsString('Opinión de Cumplimiento', $result);
    }

    public function testScraperInstantiationWithDependencies(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $rfc = 'MAGG8901015J2';
        $password = 'TestPassword';

        $scraper = new Scraper($client, $captchaResolver, $rfc, $password);

        $this->assertInstanceOf(Scraper::class, $scraper);
    }

    public function testDownloadProcessExecutesInCorrectOrder(): void
    {
        $rfc = 'VECJ880326G61';
        $password = 'Secure123!';
        $pdfContent = '%PDF Test Document';

        $executionOrder = [];

        $loginPageHtml = $this->createLoginPageHtml();
        $redirectPageHtml = $this->createRedirectPageHtml();
        $samlFormHtml = $this->createSamlFormHtml();
        $pdfResponse = $this->createJsonResponse($pdfContent);

        $client = $this->createMock(ClientInterface::class);
        $client->method('request')
            ->willReturnCallback(function (string $method, string $url) use (&$executionOrder, $loginPageHtml, $redirectPageHtml, $samlFormHtml, $pdfResponse) {
                $executionOrder[] = $method . ':' . $this->extractUrlType($url);

                if ('GET' === $method && str_contains($url, 'reporteOpinion')) {
                    return new Response(200);
                }
                if ('POST' === $method && str_contains($url, 'login')) {
                    /** @phpstan-var int $loginCount */
                    static $loginCount = 0;
                    $loginCount++;
                    return new Response(200, [], 1 === $loginCount ? $loginPageHtml : $redirectPageHtml);
                }
                if ('GET' === $method && str_contains($url, 'callback')) {
                    return new Response(200, [], $samlFormHtml);
                }
                if ('POST' === $method && str_contains($url, 'ObtenerRespuestaCompletaPdf')) {
                    return new Response(200, [], $pdfResponse);
                }
                if ('POST' === $method && str_contains($url, 'clouda.sat.gob.mx')) {
                    return new Response(200);
                }
                return new Response(200);
            });

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver->method('resolve')
            ->willReturn(new CaptchaAnswer('XYZ789'));

        $scraper = new Scraper($client, $captchaResolver, $rfc, $password);
        $scraper->download();

        $this->assertGreaterThanOrEqual(5, count($executionOrder));
        $this->assertStringContainsString('GET', $executionOrder[0]);
        $this->assertStringContainsString('POST', $executionOrder[1]);
    }

    private function createLoginPageHtml(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head><title>SAT - Acceso</title></head>
            <body>
                <form method="POST">
                    <input type="text" name="Ecom_User_ID">
                    <input type="password" name="Ecom_Password">
                    <div id="divCaptcha">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" alt="Captcha">
                    </div>
                    <input type="text" name="userCaptcha">
                    <button type="submit" name="submit">Enviar</button>
                </form>
            </body>
            </html>
            HTML;
    }

    private function createRedirectPageHtml(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <script type="text/javascript">
                    window.location.href='https://ptsc32d.clouda.sat.gob.mx/callback?code=abc123xyz&state=def456';
                </script>
            </head>
            <body>Redirigiendo...</body>
            </html>
            HTML;
    }

    private function createSamlFormHtml(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <form method="POST" action="https://ptsc32d.clouda.sat.gob.mx/saml/SSO">
                    <input type="hidden" name="SAMLResponse" value="PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiPg==">
                    <input type="hidden" name="RelayState" value="ss:mem:7c3b2e8f8c7e4d5a9b6c1e0f2d3a4b5c">
                </form>
            </body>
            </html>
            HTML;
    }

    private function createJsonResponse(string $pdfContent): string
    {
        return json_encode([
            'Respuesta' => [
                'Exito' => true,
                'Mensaje' => 'La consulta se realizó correctamente',
                'Codigo' => '200',
            ],
            'ContenidoBase64' => base64_encode($pdfContent),
            'NombreArchivo' => 'OPINION.pdf',
        ], JSON_THROW_ON_ERROR);
    }

    /** @param list<array{string, int, string}> $sequence */
    private function createMockClientWithSequence(array $sequence): ClientInterface
    {
        $callIndex = 0;

        $client = $this->createMock(ClientInterface::class);
        $client->method('request')
            ->willReturnCallback(function () use ($sequence, &$callIndex) {
                $currentCall = $sequence[$callIndex] ?? $sequence[count($sequence) - 1];
                $callIndex++;

                return new Response($currentCall[1], [], $currentCall[2]);
            });

        return $client;
    }

    private function extractUrlType(string $url): string
    {
        if (str_contains($url, 'reporteOpinion')) {
            return 'main';
        }
        if (str_contains($url, 'login')) {
            return 'login';
        }
        if (str_contains($url, 'callback')) {
            return 'callback';
        }
        if (str_contains($url, 'ObtenerRespuestaCompletaPdf')) {
            return 'pdf';
        }
        return 'other';
    }
}
