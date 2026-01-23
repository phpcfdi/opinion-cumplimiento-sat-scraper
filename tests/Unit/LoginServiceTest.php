<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\OpinionCumplimientoSatScraper\CaptchaExtractor;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\LoginPageNotLoadedException;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\RedirectUrlNotFoundException;
use PhpCfdi\OpinionCumplimientoSatScraper\HtmlParser;
use PhpCfdi\OpinionCumplimientoSatScraper\LoginService;
use PhpCfdi\OpinionCumplimientoSatScraper\URL;
use PHPUnit\Framework\TestCase;

final class LoginServiceTest extends TestCase
{
    public function testLoginSuccessfullyReturnsRedirectUrl(): void
    {
        $rfc = 'XAXX010101000';
        $password = 'Password123!';
        $expectedRedirectUrl = 'https://ptsc32d.clouda.sat.gob.mx/callback?code=abc123&state=xyz789';

        $loginPageHtml = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <form>
                    <div id="divCaptcha">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" alt="CAPTCHA">
                    </div>
                </form>
            </body>
            </html>
            HTML;

        $redirectPageHtml = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <script>
                    window.location.href='$expectedRedirectUrl';
                </script>
            </head>
            </html>
            HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(3))
            ->method('request')
            ->willReturnCallback(function (string $method, string $url) use ($loginPageHtml, $redirectPageHtml) {
                if ('GET' === $method) {
                    return new Response(200);
                }
                if (str_contains($url, URL::$login)) {
                    /** @phpstan-var int $callCount */
                    static $callCount = 0;
                    $callCount++;
                    if (1 === $callCount) {
                        return new Response(200, [], $loginPageHtml);
                    }
                    return new Response(200, [], $redirectPageHtml);
                }
                return new Response(200);
            });

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver->expects($this->once())
            ->method('resolve')
            ->willReturn(new CaptchaAnswer('ABC123'));

        $captchaExtractor = new CaptchaExtractor($captchaResolver);
        $htmlParser = new HtmlParser();

        $loginService = new LoginService($client, $captchaExtractor, $htmlParser);
        $result = $loginService->login($rfc, $password);

        $this->assertSame($expectedRedirectUrl, $result);
    }

    public function testLoginThrowsExceptionWhenNoCaptchaFound(): void
    {
        $rfc = 'XAXX010101000';
        $password = 'Password123!';

        $htmlWithoutCaptcha = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <form>
                    <input type="text" name="username">
                </form>
            </body>
            </html>
            HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function ($method) use ($htmlWithoutCaptcha) {
                if ('GET' === $method) {
                    return new Response(200);
                }
                return new Response(200, [], $htmlWithoutCaptcha);
            });

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaExtractor = new CaptchaExtractor($captchaResolver);
        $htmlParser = new HtmlParser();

        $loginService = new LoginService($client, $captchaExtractor, $htmlParser);

        $this->expectException(LoginPageNotLoadedException::class);
        $this->expectExceptionMessage('Unable to retrieve login form with captcha');

        $loginService->login($rfc, $password);
    }

    public function testLoginThrowsExceptionWhenNoRedirectUrlFound(): void
    {
        $rfc = 'CAAG850615LK7';
        $password = 'SecurePass456';

        $loginPageHtml = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <div id="divCaptcha">
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" alt="CAPTCHA">
                </div>
            </body>
            </html>
            HTML;

        $pageWithoutRedirect = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <p>Error en el login</p>
            </body>
            </html>
            HTML;

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(3))
            ->method('request')
            ->willReturnCallback(function ($method, $url) use ($loginPageHtml, $pageWithoutRedirect) {
                if ('GET' === $method) {
                    return new Response(200);
                }
                /** @phpstan-var int $callCount */
                static $callCount = 0;
                $callCount++;
                if (1 === $callCount) {
                    return new Response(200, [], $loginPageHtml);
                }
                return new Response(200, [], $pageWithoutRedirect);
            });

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver->method('resolve')->willReturn(new CaptchaAnswer('XYZ789'));

        $captchaExtractor = new CaptchaExtractor($captchaResolver);
        $htmlParser = new HtmlParser();

        $loginService = new LoginService($client, $captchaExtractor, $htmlParser);

        $this->expectException(RedirectUrlNotFoundException::class);
        $this->expectExceptionMessage('Unable to extract redirect URL after login');

        $loginService->login($rfc, $password);
    }
}
