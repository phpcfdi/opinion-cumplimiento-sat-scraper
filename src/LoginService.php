<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

use GuzzleHttp\ClientInterface;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\LoginPageNotLoadedException;
use PhpCfdi\OpinionCumplimientoSatScraper\Exceptions\RedirectUrlNotFoundException;

readonly class LoginService
{
    public function __construct(
        private ClientInterface $client,
        private CaptchaExtractor $captchaExtractor,
        private HtmlParser $htmlParser
    ) {
    }

    /**
     * Realiza el proceso de login y devuelve la URL de redirección
     */
    public function login(string $rfc, string $password): string
    {
        $this->client->request('GET', URL::$main);

        $response = $this->client->request('POST', URL::$login, [
            'headers' => Headers::merge([
                'Referer' => URL::$main,
            ])
        ]);
        $html = (string)$response->getBody();

        if (! $this->htmlParser->hasCaptcha($html)) {
            $exception = new LoginPageNotLoadedException('Unable to retrieve login form with captcha');
            $exception->setHtml($html);
            throw $exception;
        }

        $captchaValue = $this->captchaExtractor->extractAndResolve($html);

        $response = $this->client->request('POST', URL::$login, [
            'headers' => Headers::merge([
                'Referer' => URL::$login,
            ]),
            'form_params' => [
                'Ecom_User_ID' => $rfc,
                'Ecom_Password' => $password,
                'userCaptcha' => $captchaValue,
                'submit' => 'Enviar',
            ],
        ]);

        $html = (string)$response->getBody();

        $redirectUrl = $this->htmlParser->extractRedirectUrl($html);
        if (null === $redirectUrl) {
            throw new RedirectUrlNotFoundException('Unable to extract redirect URL after login');
        }

        return $redirectUrl;
    }
}
