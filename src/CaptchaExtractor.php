<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\CaptchaSourceNotFoundException;
use Symfony\Component\DomCrawler\Crawler;

readonly class CaptchaExtractor
{
    public function __construct(
        private CaptchaResolverInterface $captchaResolver
    ) {
    }

    public function extractAndResolve(string $html): string
    {
        $crawler = new Crawler($html);
        $captchaImageSrc = $crawler->filter('#divCaptcha img')->attr('src');
        if ($captchaImageSrc === null) {
            throw new CaptchaSourceNotFoundException('Captcha image not found in the provided HTML');
        }
        $image = CaptchaImage::newFromInlineHtml($captchaImageSrc);
        $solution = $this->captchaResolver->resolve($image);

        return $solution->getValue();
    }
}
