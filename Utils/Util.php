<?php

namespace Recourse\Utils;

use MapasCulturais\App;
use Recourse\Entities\Recourse;

class Util
{
    /**
     * Verifica se está no período de envio de recursos
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return boolean
     */
    public static function isRecoursePeriod($opportunity): bool
    {
        $finalPeriod = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $finalPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $finalPeriod); // Converte para formato Datetime
        $now = new \DateTime();

        if ($opportunity->getMetadata('claimDisabled') == '0' && $finalPeriodFormatted >= $now) return true;
        return false;
    }

    /**
     * Verifica se o período de envio de recursos já passou, sendo assim, o período de resposta
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return boolean
     */
    public static function isRecourseResponsePeriod($opportunity): bool
    {
        $finalPeriod = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $finalPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $finalPeriod); // Converte para formato Datetime
        $now = new \DateTime();

        if ($finalPeriodFormatted <= $now) return true;
        return false;
    }

    /**
     * Verifica se todos os recursos da oportunidade já foram respondidos para que as respostas possam ser publicadas
     * Verifica também se o período de recurso já passou
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return boolean
     */
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
