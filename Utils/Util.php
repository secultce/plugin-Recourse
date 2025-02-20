<?php

namespace Recourse\Utils;

use MapasCulturais\App;
use Recourse\Entities\Recourse;

class Util
{
    public static function isRecoursePeriod($opportunity): bool
    {
        $finalPeriod = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $finalPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $finalPeriod); // Converte para formato Datetime
        $now = new \DateTime();

        if ($opportunity->getMetadata('claimDisabled') == '0' && $finalPeriodFormatted >= $now) return true;
        return false;
    }

    public static function isRecourseResponsePeriod($opportunity): bool
    {
        $finalPeriod = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $finalPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $finalPeriod); // Converte para formato Datetime
        $now = new \DateTime();

        if ($finalPeriodFormatted <= $now) return true;
        return false;
    }

    public static function canPostResponses($opportunity): bool
    {
        $app = App::i();
        $recourses = $app->repo(Recourse::class)->findBy(['opportunity' => $opportunity->id]);
        $unansweredRecourses = array_filter($recourses, function ($recourse) {
            return (int)$recourse->status === Recourse::STATUS_DRAFT;
        });

        if (!$unansweredRecourses && self::isRecourseResponsePeriod($opportunity)) return true;
        return false;
    }
}
