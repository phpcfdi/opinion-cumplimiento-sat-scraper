<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use PhpCfdi\OpinionCumplimientoSatScraper\FormExtractionResult;
use PHPUnit\Framework\TestCase;

class FormExtractionResultTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $action = 'https://ptsc32d.clouda.sat.gob.mx/saml/SSO';
        $fields = [
            'SAMLResponse' => 'PHNhbWxwOlJlc3BvbnNl',
            'RelayState' => 'ss:mem:7c3b2e8f',
        ];

        $result = new FormExtractionResult($action, $fields);

        $this->assertInstanceOf(FormExtractionResult::class, $result);
    }

    public function testGetActionReturnsAction(): void
    {
        $action = 'https://ptsc32d.clouda.sat.gob.mx/saml/SSO';

        $result = new FormExtractionResult($action, []);

        $this->assertSame($action, $result->getAction());
    }

    public function testGetActionWithBaseUrl(): void
    {
        $action = '/saml/SSO';
        $base = 'https://ptsc32d.clouda.sat.gob.mx';

        $result = new FormExtractionResult($action, []);

        $this->assertSame('https://ptsc32d.clouda.sat.gob.mx/saml/SSO', $result->getAction($base));
    }

    public function testGetActionWithEmptyBase(): void
    {
        $action = 'https://sat.gob.mx/process';

        $result = new FormExtractionResult($action, []);

        $this->assertSame($action, $result->getAction(''));
    }

    public function testGetFieldsReturnsFields(): void
    {
        $action = 'https://example.com';
        $fields = [
            'SAMLResponse' => 'PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiPg==',
            'RelayState' => 'ss:mem:7c3b2e8f8c7e4d5a9b6c1e0f2d3a4b5c',
        ];

        $result = new FormExtractionResult($action, $fields);

        $this->assertSame($fields, $result->getFields());
    }

    public function testGetFieldsReturnsEmptyArray(): void
    {
        $action = 'https://example.com';
        $fields = [];

        $result = new FormExtractionResult($action, $fields);

        $this->assertSame([], $result->getFields());
    }
}
