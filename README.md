# phpcfdi/opinion-cumplimiento-sat-scraper

Un scraper en PHP para descargar la opinión de cumplimiento del SAT México.

### Instalación

```bash
composer install phpcfdi/opinion-cumplimiento-sat-scraper
```

### Uso Básico

```php
<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;
use PhpCfdi\OpinionCumplimientoSatScraper\Headers;
use PhpCfdi\OpinionCumplimientoSatScraper\Scraper;

$cookieJar = new CookieJar();

$client = new Client([
    'cookies' => $cookieJar,
    'curl' => [
        CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
    ],
    RequestOptions::VERIFY => false,
    'headers' => Headers::defaultHeaders()
]);

$captchaSolver = new ConsoleResolver();

$scraper = new Scraper(
    $client,
    $captchaSolver,
    'TU_RFC',
    'TU_CIEC'
);

$content = $scraper->download();
file_put_contents('opinion.pdf', $content);
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
composer test

# Con formato legible
vendor/bin/phpunit --testdox

# Sin cobertura (más rápido)
vendor/bin/phpunit --no-coverage

# Test específico
vendor/bin/phpunit tests/Unit/ScraperTest.php
```

### Cobertura de Código

```bash
composer test-coverage
open coverage/index.html
```

## 🛠️ Desarrollo

### Requisitos

- PHP 8.4+
- Composer
- Extensión cURL

### Dependencias Principales

- `guzzlehttp/guzzle` - Cliente HTTP
- `symfony/dom-crawler` - Parsing de HTML
- `phpcfdi/image-captcha-resolver` - Resolución de captchas

### Dependencias de Desarrollo

- `phpunit/phpunit` ^10.0 - Framework de testing

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Ejecutar Tests Antes de PR

```bash
composer test
```

## 📝 Licencia

MIT License

## 👤 Autor

Cesar Aguilera - cesargnu29@gmail.com

## 🙏 Agradecimientos

- PhpCfdi por image-captcha-resolver
- Comunidad de PHP por las herramientas

