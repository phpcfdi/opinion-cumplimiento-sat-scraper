<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Exceptions;

class LoginPageNotLoadedException extends SatException
{
    public function __construct(string $message, private readonly string $html)
    {
        parent::__construct($message);
    }

    public function getHtml(): ?string
    {
        // leave this getter since this is common on Exceptions
        return $this->html;
    }
}
