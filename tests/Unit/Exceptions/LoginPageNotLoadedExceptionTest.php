<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\LoginPageNotLoadedException;
use PHPUnit\Framework\TestCase;

final class LoginPageNotLoadedExceptionTest extends TestCase
{
    public function testExceptionProperties(): void
    {
        $exception = new LoginPageNotLoadedException('message', 'html');
        $this->assertSame('message', $exception->getMessage());
        $this->assertSame('html', $exception->getHtml());
    }
}
