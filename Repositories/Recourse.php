<?php

namespace Recourse\Repositories;

use MapasCulturais\App;
use Recourse\Entities\Recourse as EntityRecourse;

class Recourse extends \MapasCulturais\Repository
{
    public function publish(int $opportunityId): void
    {
        $app = App::i();

        $recourses = $this->findBy(['opportunity' => $opportunityId]);

        $app->em->beginTransaction();
        foreach ($recourses as $recourse) {
            $recourse->replyPublish = true;
            if(in_array($recourse->status, [EntityRecourse::STATUS_APPROVED, EntityRecourse::STATUS_PARTIALLY_APPROVED])) {
                if($recourse->opportunity->evaluationMethodConfiguration->type == 'technical') {
                    $query = $app->em->createQuery("UPDATE MapasCulturais\Entities\Registration r SET r.consolidatedResult = :result WHERE r.id = :id");
                    $query->setParameter('result', number_format($recourse->replyResult, 2));
                    $query->setParameter('id', $recourse->registration->id);
                    $query->getResult();
                }
            }
        }
        $app->em->commit();
        $app->em->flush();
    }
}
