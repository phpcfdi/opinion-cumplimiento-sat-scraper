<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper\Exceptions;

class LoginPageNotLoadedException extends SATException
{
    protected ?string $html = null;

    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }
}
