<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
use \MapasCulturais\Entities\EntityRevision;
use MapasCulturais\Entities\EntityRevision as Revision;
use \MapasCulturais\Entities\EntityRevisionData;
use MapasCulturais\Traits;
use Recourse\Entities\Recourse as EntityRecourse;


class Recourse extends \MapasCulturais\Controller{

    public function GET_index()
    {

        $app = App::i();        
    }

    public function GET_agent()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
        $this->_publishAssets();
        //Convertendo o valor para inteiro para uma comparação, caso nao seja ids iguais lança a mensagem de permissão
        //Todo: Averiguar em situação que o owner tem vários agentes individuais
        $idAgent = (int) $this->data['id'];
        $isOwner = true;
        if(isset($app->getUser()->profile->id)){
            $idAgent !== $app->getUser()->profile->id ? $isOwner = false : $isOwner = true;
        }

        //Buscando todos os recursos publicados e do agente logado
        $agent = $app->repo('Agent')->find($idAgent);
        $allRecourceUser = $app->repo('Recourse\Entities\Recourse')->findBy([
            'agent' => $agent
        ]);
        $this->render('recources-user',[
            'isOwner' => $isOwner,
            'allRecourceUser' => $allRecourceUser
        ]);
    }

    public function GET_oportunidade()
    {
        $app = App::i();
        $this->requireAuthentication();
        $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');
        $app->view->enqueueScript('app','ng-recourse','js/ng.recourse.js',[] );

        $entity = $app->repo('Opportunity')->find($this->data['id']);

        //Se for administrador
        if($entity->canUser('@control')){
            $urlOpp = $app->createUrl('oportunidade', $entity->id);
            $this->render('index', ['entity' => $entity, 'app' => $app, 'urlOpp' => $urlOpp]);
        }else{
            return $app->redirect($app->createUrl('panel', 'index'), 401);
            dump($entity);
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
        $recourse->createTimestamp = new DateTime();
        $recourseData = [
            'Resposta' => $this->data['reply'],
            'Respondido por: ' => $app->getAuth()->getAuthenticatedUser()->profile->id,
            'Alterado em: ' => $recourse->recourseDateReply,

        ];
        //Gravando dados para log de atividades
        $revision = new Revision($recourseData,$recourse,Revision::ACTION_MODIFIED, 'Recurso respondido');
        try {
            $app->em->persist($recourse);
            $app->em->flush();
            $revision->save(true);
            $this->json(['message' => 'Recurso respondido com sucesso!', 'status' => 200], 200);
        }catch (Exception $e) {
            return $this->json(['message' => 'Ocorreu um erro inesperado!'], 400);
        }

         $hook_prefix = $this->getHookPrefix();
         $app->applyHookBoundTo($this, "{$hook_prefix}.recourses", [&$recourse]);
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
    * Faz Verificação para qndo for desabilitado o recurso, excluir do metadata da conf. de recurso
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
            $recourse->create_timestamp = new \DateTime();
            $situ = $recourse->save();
            if(is_null($situ)){
                return $this->json(['message' => 'Recurso enviado com sucesso', 'status' => 200]);
            }
            return $this->errorJson('Erro Inesperado', 403);

        }
    }

    /*
     * Função para verificar se já tem resposta de um recurso
     * */
    public function verifyReply($recourse)
    {
        $app = App::i();
        $rec = $app->repo('Recourse\Entities\Recourse')->find($recourse);
        if(!is_null($rec->recourseReply) && !is_null($rec->recourseDateReply) && $rec->replyAgentId !== $app->getAuth()->getAuthenticatedUser()->profile->id)
        {
            return $this->errorJson('Esse recurso foi respondido', 403);
        }
    }

    /*
     * Função que publica os recursos
     * @params $opportunity integer
     * return void
     */
    public function POST_publish()
    {
        $res = EntityRecourse::publishResource($this->postData['opportunity']);
        if($res > 0) {
            $this->json([ 'title' => 'Sucesso', 'message' => 'Publicação realizada com sucesso', 'status' => 200], 200);
        }
        $this->json([ 'title' => 'Error', 'message' => 'Ocorreu um erro inesperado.', 'type' => 'error'], 500);
    }

    protected function _publishAssets()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');
        $app->view->enqueueScript('app','ng-recourse','js/ng.recourse.js',[] );
        $app->view->enqueueScript('app','recourse','js/recourse/recourse.js',[]);
    }

    protected function createLogsRevision($dataRevision, $recourse)
    {
        $app = App::i();
        $conn = $app->em->getConnection();


        dump($recourse->getClassName());
        foreach ($dataRevision as $keysRevision => $revisionData)
        {
            $lastRevsisionDataId = $conn
                ->executeQuery('SELECT id FROM entity_revision_data ORDER BY "timestamp" DESC LIMIT 1')
                ->fetch()['id'];
            $sqlRevisionData = "INSERT INTO
            entity_revision_data (\"id\", \"timestamp\", \"key\", \"value\")
            VALUES (:id, :timestamp, :key, :value)";
            $conn->executeUpdate($sqlRevisionData, [
                'id' => $lastRevsisionDataId + 1,
                'timestamp' => (new \DateTime())->format(DATE_W3C),
                'key' => $keysRevision,
                'value' => $revisionData,
            ]);
//
            $lastEntityRevisionId = $conn
                ->executeQuery('SELECT id FROM entity_revision ORDER BY "create_timestamp" DESC LIMIT 1')
                ->fetch()['id'];
            $sqlEntityRevision = "INSERT INTO
                    entity_revision (id, user_id, object_id, object_type, create_timestamp, action, message)
                    VALUES (:id, :user_id, :object_id, :object_type, :create_timestamp, :action, :message)";
            $conn->executeUpdate($sqlEntityRevision, [
                'id' => $lastEntityRevisionId+1,
                'user_id' => $app->user->id,
                'object_id' => $recourse->id,
                'object_type' => $recourse->getClassName(),
                'create_timestamp' => (new \DateTime())->format(DATE_W3C),
                'action' => EntityRevision::ACTION_CREATED,
                'message' => 'Registro criado.',
            ]);

            $conn->executeUpdate("INSERT INTO entity_revision_revision_data VALUES (:revision_id, :revision_data_id)", [
                'revision_id' => $lastEntityRevisionId + 1,
                'revision_data_id' => $lastRevsisionDataId + 1
            ]);

        }




    }
}