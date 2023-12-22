<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
use \MapasCulturais\Entities\EntityRevision;
use \MapasCulturais\Entities\EntityRevisionData;
use MapasCulturais\Entities\OpportunityMeta;
use Recourse\Entities\Recourse as EntityRecourse;


class Recourse extends \MapasCulturais\Controller{

    public function GET_index()
    {

        $app = App::i();        
    }

    public function GET_oportunidade()
    {
        $app = App::i();


        $entity = $app->repo('Opportunity')->find($this->data['id']);

        $this->render('index', ['entity' => $entity, 'app' => $app]);
//        $app->view->enqueueScript('app', 'vendor', 'js/vendor.js');


    }

    public function GET_todos()
    {
        $app = App::i();
        $entity = $app->repo(EntityRecourse::class)->findBy(['opportunity' => $this->data['id']]);
        $this->json($entity, 200);
    }

    public function POST_responder()
    {
        $app = App::i();
        //Validações
        if($this->data['reply'] == ''){
            return $this->json(['message' => 'Você não poderá enviar sem antes responder ao candidato'], 403);
        }
        if($this->data['status'] == '' || $this->data['status'] == 'Aberto'){
            return $this->json(['message' => 'Informe a situação da resposta do seu recurso'], 403);
        }
        //Formatando o status para gravar no banco
        $statusRecourse =  $this->data['status'];
        if($this->data['status'] == 'Deferido' || $this->data['status'] == 'Indeferido'){
            $statusRecourse = self::getSituationToStatus( $this->data['status'] );
        }

        $recourse = $app->repo(EntityRecourse::class)->find($this->data['entityId']);
        $recourse->recourseReply = $this->data['reply'];
        $recourse->recourseDateReply = new DateTime;
        $recourse->recourseStatus = $statusRecourse;
        $recourse->replyAgentId = $app->getAuth()->getAuthenticatedUser()->profile->id;

        $recourseData = [
            'Resposta' => $this->data['reply'],
            'Respondido por: ' => $app->getAuth()->getAuthenticatedUser()->profile->id,
            'Alterado em: ' => $recourse->recourseDateReply
        ];
        try {
            $app->em->persist($recourse);
            $app->em->flush();
//            dump($recourse);
            $entityRevision = new EntityRevision($recourseData, $recourse, 'created' , 'Alterado resposta do recurso');
            $entityRevision->save();
//            $app->em->persist($entityRevision);
//            $app->em->flush();

            dump( $entityRevision);

//            return $this->json(['message' => 'Recurso respondido com sucesso!'], 200);
        }catch (Exception $e) {
            die($e->getMessage());
//            return $this->json(['message' => 'Ocorreu um erro inesperado!'], 400);
        }

//         $hook_prefix = $this->getHookPrefix();
//         $app->applyHookBoundTo($this, "{$hook_prefix}.recourses", [&$recourse]);
    }

    public function GET_registration()
    {
        $app = App::i();
        dump($this->data);
        $reg = $app->repo('Registration')->find($this->data['id']);
        return $this->json(['resultConsolidate' => $reg->consolidatedResult]);
    }

    /**
     * Funcção que recebe uma string em texto e altera para o status da classe da Entidade
     * @param string
     * @return string
     */
    public function getSituationToStatus($situation): string
    {
        $situ = '';
        switch ($situation) {
            case 'Deferido':
                $situ = EntityRecourse::STATUS_ENABLED;
                break;
            case 'Indeferido':
                $situ = EntityRecourse::STATUS_DISABLED;
                break;
        }
        return $situ;
    }

    public function POST_verifyPermission()
    {
        $app = App::i();
        //Entidade
        $oportunity = $app->repo('Opportunity')->find($this->data['id']);
        //Verifica permissao do usuário locado
        $permission = $oportunity->evaluationMethodConfiguration->canUser('@control');
        $this->json($permission, 200);
    }


    function POST_disabledResource(){
        $app = App::i();
        //Alterando o claimDisabled no metadata
        $opp = $app->repo('Opportunity')->find($this->postData['id']);
        //Valor recebido pela request é repassado para alteração
        if ($opp) {
            self::saveClaimDisabled($opp, $this->postData['claimDisabled']);
        }
    }

    function POST_opportunityEnabled(){
        $app = App::i();
        //id oportunidade
//        $id = $this->postData['opportunity'];
        //instancia da oportunidade
        $opportunity = $app->repo('Opportunity')->find($this->postData['opportunity']);
//        self::saveClaimDisabled($opportunity, 0);
        dump($this->postData);

        $idOp = $this->postData['opportunity'];

//        $upOpMeta = $app->repo('OpportunityMeta')->findby(['owner' => $idOp]);
        foreach ($this->postData as $key => $value) {

            if($key !== 'opportunity' && $key !== 'claimDisabled'){
                dump($key);
                dump($value);
                $newOpMeta = new OpportunityMeta;
                $newOpMeta->owner = $opportunity;
                $newOpMeta->key = $key;
                $newOpMeta->value = $value;
                $app->em->persist($newOpMeta);
                $newOpMeta->save(true);


//                $upOpMeta->owner = $idOp;
//                $upOpMeta->key = $key;
//                $upOpMeta->value = $value;
//                $upOpMeta->save(true);
            }

        }
    }
    //Salva a alteração da habilitação de recurso
    function saveClaimDisabled($entity, $claimDisabled)
    {
        $entity->claimDisabled = $claimDisabled;
        $entity->save(true);

        if($claimDisabled == '1'){
            dump($entity->getMetadata('date-initial'));
        }

    }

}