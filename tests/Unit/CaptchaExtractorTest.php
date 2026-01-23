<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\OpinionCumplimientoSatScraper\CaptchaExtractor;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\CaptchaSourceNotFoundException;
use PHPUnit\Framework\TestCase;

final class CaptchaExtractorTest extends TestCase
{
    public function testExtractAndResolveReturnsCaptchaValue(): void
    {
        $expectedSolution = 'ABC123';

        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver
            ->expects($this->once())
            ->method('resolve')
            ->willReturn(new CaptchaAnswer($expectedSolution));

        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <div id="divCaptcha">
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" alt="CAPTCHA">
                </div>
            </body>
            </html>
            HTML;

        $extractor = new CaptchaExtractor($captchaResolver);
        $result = $extractor->extractAndResolve($html);

        $this->assertSame($expectedSolution, $result);
    }

    public function testExtractAndResolveThrowsExceptionWhenNoScriptExists(): void
    {
        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver
            ->expects($this->never())
            ->method('resolve');

        $htmlWithoutCaptcha = <<<HTML
            <!DOCTYPE html>
            <html>
            <head><title>SAT Login</title></head>
            <body>
                <form>
                    <input type="text" name="username">
                    <input type="password" name="password">
                    <button type="submit">Enviar</button>
                </form>
            </body>
            </html>
            HTML;

        $extractor = new CaptchaExtractor($captchaResolver);

        $this->expectException(CaptchaSourceNotFoundException::class);

        $extractor->extractAndResolve($htmlWithoutCaptcha);
    }

    public function testExtractAndResolveThrowsExceptionWhenNoImageIsFound(): void
    {
        $captchaResolver = $this->createMock(CaptchaResolverInterface::class);
        $captchaResolver
            ->expects($this->never())
            ->method('resolve');

        $htmlWithoutCaptcha = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <div id="divCaptcha">
                    <img alt="CAPTCHA">
                </div>
            </body>
            </html>
            HTML;

        $extractor = new CaptchaExtractor($captchaResolver);

        $this->expectException(CaptchaSourceNotFoundException::class);

        $extractor->extractAndResolve($htmlWithoutCaptcha);
    }
}
