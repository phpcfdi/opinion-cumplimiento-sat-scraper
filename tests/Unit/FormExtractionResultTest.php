<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Tests\Unit;

use PhpCfdi\OpinionCumplimientoSatScraper\FormExtractionResult;
use PHPUnit\Framework\TestCase;

final class FormExtractionResultTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $action = 'https://ptsc32d.clouda.sat.gob.mx/saml/SSO';
        $fields = [
            'SAMLResponse' => 'PHNhbWxwOlJlc3BvbnNl',
            'RelayState' => 'ss:mem:7c3b2e8f',
        ];

        $result = new FormExtractionResult($action, $fields);

        $this->assertSame($action, $result->action);
        $this->assertSame($fields, $result->fields);
    }
}
