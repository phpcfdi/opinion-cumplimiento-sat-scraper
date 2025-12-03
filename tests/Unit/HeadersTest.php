<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use PhpCfdi\OpinionCumplimientoSatScraper\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    public function testDefaultHeadersReturnsUserAgent(): void
    {
        $headers = Headers::defaultHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('Mozilla', $headers['User-Agent']);
        $this->assertStringContainsString('Firefox', $headers['User-Agent']);
    }

    public function testDefaultHeadersStructure(): void
    {
        $headers = Headers::defaultHeaders();

        $this->assertSame([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:104.0) Gecko/20100101 Firefox/104.0',
        ], $headers);
    }

    public function testMergeWithEmptyArray(): void
    {
        $result = Headers::merge([]);

        $this->assertEquals(Headers::defaultHeaders(), $result);
    }

    public function testMergeWithCustomHeaders(): void
    {
        $customHeaders = [
            'Referer' => 'https://sat.gob.mx',
            'Accept' => 'application/json',
        ];

        $result = Headers::merge($customHeaders);

        $this->assertArrayHasKey('User-Agent', $result);
        $this->assertArrayHasKey('Referer', $result);
        $this->assertArrayHasKey('Accept', $result);
        $this->assertSame('https://sat.gob.mx', $result['Referer']);
        $this->assertSame('application/json', $result['Accept']);
    }

    public function testMergeOverridesDefaultHeaders(): void
    {
        $customHeaders = [
            'User-Agent' => 'Custom User Agent',
        ];

        $result = Headers::merge($customHeaders);

        $this->assertSame('Custom User Agent', $result['User-Agent']);
        $this->assertNotEquals(Headers::defaultHeaders()['User-Agent'], $result['User-Agent']);
    }
}
