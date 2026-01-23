<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use GuzzleHttp\ClientInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use Stringable;

final class Scraper implements ScraperInterface
{
    private LoginService $loginService;

    private SamlFormHandler $samlFormHandler;

    private PdfDownloader $pdfDownloader;

    public function __construct(
        protected ClientInterface $client,
        protected CaptchaResolverInterface $captchaResolver,
        protected string $rfc,
        protected string $password,
    ) {
        $captchaExtractor = new CaptchaExtractor($captchaResolver);
        $htmlParser = new HtmlParser();
        $this->loginService = new LoginService($client, $captchaExtractor, $htmlParser);
        $this->samlFormHandler = new SamlFormHandler($client);
        $this->pdfDownloader = new PdfDownloader($client);
    }

    public function download(): Stringable
    {
        $redirectUrl = $this->loginService->login($this->rfc, $this->password);

        $samlReferer = $this->samlFormHandler->handleSamlRedirect($redirectUrl, URL::$login);

        return $this->pdfDownloader->download($this->rfc, $samlReferer);
    }
}
