<?php

namespace Recourse\Utils;

class Util
{
    public static function isRecoursePeriod($opportunity): bool
    {
        $strToEnd = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $endOfPeriod = \DateTime::createFromFormat('Y-m-d H:i', $strToEnd); // Convertendo para formato Datetime
        $now = new \DateTime();

        if ($opportunity->getMetadata('claimDisabled') == '0' && $endOfPeriod >= $now) return true;
        return false;
    }
}
