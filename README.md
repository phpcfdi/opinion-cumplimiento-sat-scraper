# phpcfdi/opinion-cumplimiento-sat-scraper

[![Source Code][badge-source]][source]
[![PHP Version][badge-php-version]][php-version]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Reliability][badge-reliability]][reliability]
[![Maintainability][badge-maintainability]][maintainability]
[![Code Coverage][badge-coverage]][coverage]
[![Violations][badge-violations]][violations]
[![Total Downloads][badge-downloads]][downloads]

> Scraper para descargar la Opinión de Cumplimiento del SAT México

:us: The documentation of this project is in Spanish, as this is the natural language for the intended audience.

## Acerca de phpcfdi/opinion-cumplimiento-sat-scraper

Esta herramienta se conecta usando [*Guzzle*](https://docs.guzzlephp.org/) como cliente HTTP a la página del SAT
en México para descargar la Opinión de Cumplimiento usando los datos de RFC y clave CIEC.

Requiere un resolvedor de *Captcha*, para lo que se puede utilizar alguno de los que ya se encuentran implementados 
en [`phpcfdi/image-captcha-resolver`](https://github.com/phpcfdi/image-captcha-resolver).

## Instalación usando composer

```shell
composer require phpcfdi/opinion-cumplimiento-sat-scraper
```

## Uso Básico

```php
<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;
use PhpCfdi\OpinionCumplimientoSatScraper\Scraper;

$cookieJar = new CookieJar();

$client = new Client([
    'cookies' => $cookieJar,
    'curl' => [
        CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
    ],
    RequestOptions::VERIFY => false,
]);

$captchaSolver = new ConsoleResolver();

$scraper = new Scraper(
    $client,
    $captchaSolver,
    'TU_RFC',
    'TU_CIEC'
);

$content = $scraper->download();
file_put_contents('opinion.pdf', (string) $content);
```

**Importante:** El método `Scraper::download()` devuelve un objeto de tipo `Stringable`, por lo que se recomienda 
siempre hacer la conversión de tipos explícita para obtener el contenido.

## Soporte

Puedes obtener soporte abriendo un ticket en Github.

Adicionalmente, esta librería pertenece a la comunidad [PhpCfdi](https://www.phpcfdi.com),
así que puedes usar los canales oficiales de comunicación para obtener ayuda de la comunidad.

## Compatibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](docs/SEMVER.md) por lo que puedes usar esta librería
sin temor a romper tu aplicación.

| Versión | PHP Mínima         | Nota       |
|---------|--------------------|------------|
| 0.1.0   | 8.2, 8.3, 8.4, 8.5 | 2026-01-22 |

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el archivo [CHANGELOG][].

## Copyright and License

Autor original: Cesar Aguilera `cesargnu29@gmail.com`.

The `phpcfdi/opinion-cumplimiento-sat-scraper` tool is copyright © [PhpCfdi](https://www.phpcfdi.com/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper/blob/main/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper/blob/main/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper/blob/main/docs/TODO.md

[source]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper
[php-version]: https://packagist.org/packages/phpcfdi/opinion-cumplimiento-sat-scraper
[release]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper/releases
[license]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper/blob/main/LICENSE
[build]: https://github.com/phpcfdi/opinion-cumplimiento-sat-scraper/actions/workflows/build.yml?query=branch:main
[reliability]:https://sonarcloud.io/component_measures?id=phpcfdi_opinion-cumplimiento-sat-scraper&metric=Reliability
[maintainability]: https://sonarcloud.io/component_measures?id=phpcfdi_opinion-cumplimiento-sat-scraper&metric=Maintainability
[coverage]: https://sonarcloud.io/component_measures?id=phpcfdi_opinion-cumplimiento-sat-scraper&metric=Coverage
[violations]: https://sonarcloud.io/project/issues?id=phpcfdi_opinion-cumplimiento-sat-scraper&resolved=false
[downloads]: https://packagist.org/packages/phpcfdi/opinion-cumplimiento-sat-scraper

[badge-source]: https://img.shields.io/badge/source-phpcfdi/opinion--cumplimiento--sat--scraper-blue?logo=github
[badge-php-version]: https://img.shields.io/packagist/dependency-v/phpcfdi/opinion-cumplimiento-sat-scraper/php?logo=php
[badge-release]: https://img.shields.io/github/release/phpcfdi/opinion-cumplimiento-sat-scraper?logo=git
[badge-license]: https://img.shields.io/github/license/phpcfdi/opinion-cumplimiento-sat-scraper?logo=open-source-initiative
[badge-build]: https://img.shields.io/github/actions/workflow/status/phpcfdi/opinion-cumplimiento-sat-scraper/build.yml?branch=main&logo=github-actions
[badge-reliability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_opinion-cumplimiento-sat-scraper&metric=reliability_rating
[badge-maintainability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_opinion-cumplimiento-sat-scraper&metric=sqale_rating
[badge-coverage]: https://img.shields.io/sonar/coverage/phpcfdi_opinion-cumplimiento-sat-scraper/main?logo=sonarqubecloud&server=https%3A%2F%2Fsonarcloud.io
[badge-violations]: https://img.shields.io/sonar/violations/phpcfdi_opinion-cumplimiento-sat-scraper/main?format=long&logo=sonarqubecloud&server=https%3A%2F%2Fsonarcloud.io
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/opinion-cumplimiento-sat-scraper?logo=packagist
