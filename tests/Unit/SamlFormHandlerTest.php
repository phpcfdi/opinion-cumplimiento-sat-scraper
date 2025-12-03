<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\OpinionCumplimientoSatScraper\FormExtractionResult;
use PhpCfdi\OpinionCumplimientoSatScraper\SamlFormHandler;
use PHPUnit\Framework\TestCase;

class SamlFormHandlerTest extends TestCase
{
    public function testHandleSamlRedirectProcessesFormSuccessfully(): void
    {
        $redirectUrl = 'https://ptsc32d.clouda.sat.gob.mx/callback?code=abc123';
        $referer = 'https://login.mat.sat.gob.mx/nidp/app/login';
        $samlAction = 'https://ptsc32d.clouda.sat.gob.mx/saml/SSO';

        $samlFormHtml = <<<HTML
<!DOCTYPE html>
<html>
<body>
    <form method="POST" action="$samlAction">
        <input type="hidden" name="SAMLResponse" value="PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiPg==">
        <input type="hidden" name="RelayState" value="ss:mem:7c3b2e8f8c7e4d5a9b6c1e0f2d3a4b5c">
        <noscript>
            <button type="submit">Continuar</button>
        </noscript>
    </form>
</body>
</html>
HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options = []) use ($redirectUrl, $samlFormHtml, $samlAction) {
                if ($method === 'GET' && $url === $redirectUrl) {
                    $this->assertArrayHasKey('headers', $options);
                    return new Response(200, [], $samlFormHtml);
                }
                if ($method === 'POST' && $url === $samlAction) {
                    $this->assertArrayHasKey('form_params', $options);
                    $this->assertArrayHasKey('SAMLResponse', $options['form_params']);
                    $this->assertArrayHasKey('RelayState', $options['form_params']);
                    return new Response(200);
                }
                return new Response(200);
            });

        $handler = new SamlFormHandler($client);
        $result = $handler->handleSamlRedirect($redirectUrl, $referer);

        $this->assertSame($samlAction, $result);
    }

    public function testHandleSamlRedirectWithComplexSamlResponse(): void
    {
        $redirectUrl = 'https://ptsc32d.clouda.sat.gob.mx/auth/callback?state=xyz&session=123';
        $referer = 'https://login.mat.sat.gob.mx';
        $samlAction = 'https://ptsc32d.clouda.sat.gob.mx/Assertion/Consumer/Service';

        $complexSamlHtml = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Autenticación SAML</title>
</head>
<body>
    <div id="saml-form-container">
        <form id="samlForm" method="POST" action="$samlAction">
            <input type="hidden" name="SAMLResponse" value="PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c2FtbDJwOlJlc3BvbnNlIERlc3RpbmF0aW9uPSJodHRwczovL3B0c2MzMmQuY2xvdWRhLnNhdC5nb2IubXgiIElEPSJfZjc4MzJlYWQ4NzU2NDNiYWI5YzgxNGU1YTNkMTJmNzAiIElzc3VlSW5zdGFudD0iMjAyNS0xMi0wM1QxNjowMDowMFoiIFZlcnNpb249IjIuMCI+PHNhbWwyOklzc3Vlcj5odHRwczovL2xvZ2luLm1hdC5zYXQuZ29iLm14L25pZHA8L3NhbWwyOklzc3Vlcj48c2FtbDJwOlN0YXR1cz48c2FtbDJwOlN0YXR1c0NvZGUgVmFsdWU9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpzdGF0dXM6U3VjY2VzcyIvPjwvc2FtbDJwOlN0YXR1cz4=">
            <input type="hidden" name="RelayState" value="ss:mem:9e8d7c6b5a4f3e2d1c0b9a8f7e6d5c4b">
        </form>
        <script>
            document.getElementById('samlForm').submit();
        </script>
    </div>
</body>
</html>
HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function ($method, $url) use ($complexSamlHtml) {
                if ($method === 'GET') {
                    return new Response(200, [], $complexSamlHtml);
                }
                return new Response(302, ['Location' => 'https://ptsc32d.clouda.sat.gob.mx/dashboard']);
            });

        $handler = new SamlFormHandler($client);
        $result = $handler->handleSamlRedirect($redirectUrl, $referer);

        $this->assertSame($samlAction, $result);
    }

    public function testHandleSamlRedirectWithMultipleHiddenFields(): void
    {
        $redirectUrl = 'https://ptsc32d.clouda.sat.gob.mx/sso/callback';
        $referer = 'https://login.mat.sat.gob.mx/nidp/app/login';
        $samlAction = 'https://ptsc32d.clouda.sat.gob.mx/acs';

        $samlHtml = <<<HTML
<!DOCTYPE html>
<html>
<body>
    <form method="POST" action="$samlAction">
        <input type="hidden" name="SAMLResponse" value="PHNhbWwyOlJlc3BvbnNl">
        <input type="hidden" name="RelayState" value="ss:mem:abc123">
        <input type="hidden" name="SigAlg" value="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256">
        <input type="hidden" name="Signature" value="dGhpcyBpcyBhIHNpZ25hdHVyZQ==">
    </form>
</body>
</html>
HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options = []) use ($samlHtml, $samlAction) {
                if ($method === 'GET') {
                    return new Response(200, [], $samlHtml);
                }
                if ($method === 'POST') {
                    $this->assertCount(4, $options['form_params']);
                    $this->assertArrayHasKey('SAMLResponse', $options['form_params']);
                    $this->assertArrayHasKey('RelayState', $options['form_params']);
                    $this->assertArrayHasKey('SigAlg', $options['form_params']);
                    $this->assertArrayHasKey('Signature', $options['form_params']);
                    return new Response(200);
                }
                return new Response(200);
            });

        $handler = new SamlFormHandler($client);
        $result = $handler->handleSamlRedirect($redirectUrl, $referer);

        $this->assertSame($samlAction, $result);
    }

    public function testHandleSamlRedirectSendsCorrectHeaders(): void
    {
        $redirectUrl = 'https://ptsc32d.clouda.sat.gob.mx/callback';
        $referer = 'https://login.mat.sat.gob.mx/auth';
        $samlAction = 'https://ptsc32d.clouda.sat.gob.mx/saml/acs';

        $samlHtml = <<<HTML
<!DOCTYPE html>
<html>
<body>
    <form action="$samlAction">
        <input type="hidden" name="SAMLResponse" value="test">
        <input type="hidden" name="RelayState" value="state">
    </form>
</body>
</html>
HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options = []) use ($referer, $samlHtml, $redirectUrl) {
                if ($method === 'GET') {
                    $this->assertArrayHasKey('headers', $options);
                    $this->assertArrayHasKey('Referer', $options['headers']);
                    $this->assertSame($referer, $options['headers']['Referer']);
                    return new Response(200, [], $samlHtml);
                }
                if ($method === 'POST') {
                    $this->assertArrayHasKey('headers', $options);
                    $this->assertArrayHasKey('Referer', $options['headers']);
                    $this->assertSame($redirectUrl, $options['headers']['Referer']);
                    return new Response(200);
                }
                return new Response(200);
            });

        $handler = new SamlFormHandler($client);
        $handler->handleSamlRedirect($redirectUrl, $referer);
    }
}
