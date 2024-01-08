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

    public function GET_agent()
    {
        dump($this->data);
    }

    public function GET_oportunidade()
    {
        $app = App::i();
        $this->requireAuthentication();

        $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
        $app->view->enqueueScript('app','ng-recourse','js/ng.recourse.js',[] );
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');

        $entity = $app->repo('Opportunity')->find($this->data['id']);
        //Se for administrador
        if($entity->canUser('@control')){
            $this->render('index', ['entity' => $entity, 'app' => $app]);
        }
    }

    public function GET_todos()
    {
        $app = App::i();
        $entity = $app->repo(EntityRecourse::class)->findBy(['opportunity' => $this->data['id']]);
        $this->json($entity, 200);
    }

    public function POST_responder()
    {
        //Verificando se o recurso foi respondido
        self::verifyReply($this->data['entityId']);

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
            $entityRevision = new EntityRevision($recourseData, $recourse, 'created' , 'Alterado resposta do recurso');
            $entityRevision->save();
            $this->json(['message' => 'Recurso respondido com sucesso!', 'status' => 200], 200);
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


    //Salva a alteração da habilitação de recurso
    function saveClaimDisabled($entity, $claimDisabled)
    {
        $app = App::i();
        $entity->claimDisabled = $claimDisabled;
        $entity->save(true);
        if($claimDisabled == '1'){
            self::verifyClaim($entity);
        }
    }

    /**
    Faz Verificação para qndo for desabilitado o recurso, excluir do metadata da conf. de recurso
     */
    public static function verifyClaim($entity)
    {
        $app = App::i();
        $metas = $app->repo('OpportunityMeta')->findBy([
            'owner' => $entity->id
        ]);
        //Excluindo meta data
        foreach ($metas as $meta){
            if($meta->key == 'recourse_date_initial' || $meta->key == 'recourse_time_initial' || $meta->key == 'recourse_date_end' || $meta->key == 'recourse_time_end' )
            {
                $meta->delete();
                $app->em->flush();
            }
        }
    }

    public function POST_sendRecourse()
    {
        $app = App::i();
        $registratrion = $app->repo('Registration')->find($this->data['registration']);
        $opportinuty = $app->repo('Opportunity')->find($this->data['opportunity']);
        $agent = $app->repo('Agent')->find($this->data['agent']);
        if(!is_null($this->data['recourse'])) {
            $recourse = new EntityRecourse;
            $recourse->recourseText = $this->data['recourse'];
            $recourse->recourseSend = new \DateTime();
            $recourse->recourseStatus = EntityRecourse::STATUS_DRAFT;
            $recourse->registration = $registratrion;
            $recourse->opportunity = $opportinuty;
            $recourse->agent = $agent ;
            $situ = $recourse->save(true);
            if(is_null($situ)){
                return $this->json(['message' => 'Recurso enviado com sucesso', 'status' => 200]);
            }
            return $this->errorJson('Erro Inesperado', 403);

        }
    }

    public function verifyReply($recourse)
    {
        $app = App::i();
        $rec = $app->repo('Recourse\Entities\Recourse')->find($recourse);
        if(!is_null($rec->recourseReply) && !is_null($rec->recourseDateReply) && $rec->replyAgentId !== $app->getAuth()->getAuthenticatedUser()->profile->id)
        {
            return $this->errorJson('Esse recurso foi respondido', 403);
        }
    }
}