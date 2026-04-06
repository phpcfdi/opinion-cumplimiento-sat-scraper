<?php

declare(strict_types=1);

namespace PhpCfdi\OpinionCumplimientoSatScraper;

/** @internal  */
final class URL
{
    public static string $main = 'https://ptsc32d.clouda.sat.gob.mx/?/reporteOpinion32DContribuyente';

    public static string $login = 'https://loginda.siat.sat.gob.mx/nidp/app/login'
        . '?id=contr-dual-totp-eFirma&sid=0&option=credential&sid=0';

    public static string $pdf = 'https://ptsc32d.clouda.sat.gob.mx/RespuestaCompleta/ObtenerRespuestaCompletaPdf';
}
