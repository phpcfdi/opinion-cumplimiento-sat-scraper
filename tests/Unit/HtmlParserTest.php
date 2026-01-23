<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use PhpCfdi\OpinionCumplimientoSatScraper\HtmlParser;
use PHPUnit\Framework\TestCase;

final class HtmlParserTest extends TestCase
{
    private HtmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new HtmlParser();
    }

    public function testExtractRedirectUrlWithValidJavaScript(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <script type="text/javascript">
                    window.location.href = "https://ptsc32d.clouda.sat.gob.mx/sso/callback?code=abc123&state=xyz789";
                </script>
            </head>
            <body>
                <p>Redirigiendo...</p>
            </body>
            </html>
            HTML;

        $result = $this->parser->extractRedirectUrl($html);

        $this->assertSame('https://ptsc32d.clouda.sat.gob.mx/sso/callback?code=abc123&state=xyz789', $result);
    }

    public function testExtractRedirectUrlWithNoScript(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <p>No hay scripts aquí</p>
            </body>
            </html>
            HTML;

        $result = $this->parser->extractRedirectUrl($html);

        $this->assertNull($result);
    }

    public function testExtractRedirectUrlWithScriptButNoRedirect(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <script type="text/javascript">
                    console.log('No redirect here');
                    var x = 10;
                </script>
            </head>
            <body>
                <p>Página normal</p>
            </body>
            </html>
            HTML;

        $result = $this->parser->extractRedirectUrl($html);

        $this->assertNull($result);
    }

    public function testExtractRedirectUrlWithComplexUrl(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <script>
                    setTimeout(function() {
                        window.location.href='https://login.mat.sat.gob.mx/nidp/saml2/sso?SAMLRequest=fZJdb4IwFIb%2FSsN9KW2hgJuJmW5LtizRbMvufSCVUaAlpdX59%2FuAZC7eee1Jn%2Fe8z2lPZ1Y0r%2ByhVN%2FkFYowvAjCEFMiMdVZnFHKIqY1S7jSoL2QaUJ0OY4Hg%2F44Ho1Go%2FF4PB6NR%2BPxaDQejcfj0Xg0Go%2FH49F4PBqNx%2BPxeDwejcfj8Xg8Ho%2FH49F4PB6Px%2BPxeDwej8fj8Xg8Ho%2FH4%2FH4Pw%3D%3D&RelayState=ss%3Amem%3A7c3b2e8f8c7e4d5a9b6c1e0f2d3a4b5c';
                    }, 1000);
                </script>
            </head>
            </html>
            HTML;

        $result = (string) $this->parser->extractRedirectUrl($html);

        $this->assertStringContainsString('SAMLRequest=', $result);
        $this->assertStringContainsString('RelayState=', $result);
    }

    public function testHasCaptchaReturnsTrue(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <form>
                    <div id="divCaptcha">
                        <img src="captcha.png" alt="CAPTCHA">
                    </div>
                </form>
            </body>
            </html>
            HTML;

        $result = $this->parser->hasCaptcha($html);

        $this->assertTrue($result);
    }

    public function testHasCaptchaReturnsFalse(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <form>
                    <input type="text" name="username">
                    <input type="password" name="password">
                </form>
            </body>
            </html>
            HTML;

        $result = $this->parser->hasCaptcha($html);

        $this->assertFalse($result);
    }

    public function testHasCaptchaWithComplexHtml(): void
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <title>Portal SAT - Inicio de Sesión</title>
                <meta charset="UTF-8">
            </head>
            <body>
                <div class="container">
                    <div class="login-form">
                        <h1>Acceso al Sistema</h1>
                        <form method="POST" action="/login">
                            <div class="form-group">
                                <label for="rfc">RFC:</label>
                                <input type="text" id="rfc" name="Ecom_User_ID">
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña:</label>
                                <input type="password" id="password" name="Ecom_Password">
                            </div>
                            <div class="form-group" id="divCaptcha">
                                <label for="captcha">Código de seguridad:</label>
                                <img src="data:image/png;base64,iVBORw0KGg..." alt="CAPTCHA">
                                <input type="text" id="captcha" name="userCaptcha">
                            </div>
                            <button type="submit" name="submit">Enviar</button>
                        </form>
                    </div>
                </div>
            </body>
            </html>
            HTML;

        $result = $this->parser->hasCaptcha($html);

        $this->assertTrue($result);
    }
}
