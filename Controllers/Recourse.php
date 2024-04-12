<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
use \MapasCulturais\Entities\EntityRevision;
use MapasCulturais\Entities\EntityRevision as Revision;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\Exceptions\WorkflowRequest;
use Recourse\Entities\Recourse as EntityRecourse;
use Recourse\Entities\RecourseFile;


class Recourse extends \MapasCulturais\Controller{

    public function GET_index()
    {

        $app = App::i();        
    }

    public function GET_agent()
    {
        $app = App::i();
        if($app->user->is('guest')){
            $app->redirect('/autenticacao');
        }

        $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
        $this->_publishAssets();

        $agent = $app->repo('Agent')->find($this->data['id']);
        $isOwner = $agent->canUser('@control');

        $allRecoursesUser = [];
        foreach($app->user->agents as $agent) {
            $agentRecourses = $app->repo('Recourse\Entities\Recourse')->findBy([
                'agent' => $agent,
            ]);
            $allRecoursesUser = array_merge($allRecoursesUser, $agentRecourses);
        }
        $this->render('recourses-user',[
            'isOwner' => $isOwner,
            'allRecoursesUser' => $allRecoursesUser,
        ]);
    }

    public function GET_arquivo(): void
    {
        $this->requireAuthentication();

        $app = App::i();

        $file = $app->repo('\Recourse\Entities\RecourseFile')->find($this->data['id']);

        $file_path = file_exists($file->getPath()) ? $file->getPath() : (string)str_replace('recourse-entities-recourse/'. $file->owner->id, 'recourse-entities-recourse', $file->getPath());

        if (file_exists($file_path)) {
            $headers = [
                'Content-Description' => 'File Transfer',
                'Content-Type' => mime_content_type($file_path),
                'Content-Disposition' => 'attachment; filename="' . $file->name . '"',
                'Content-Transfer-Encoding' => 'binary',
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'public',
                'Content-Length' => filesize($file_path)
            ];

            foreach($headers as $name => $value){
                header("{$name}: {$value}");
            }

            readfile($file_path);

            exit;
        }

        $app->pass();
    }

    public function GET_oportunidade(): void
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
            $app->redirect($app->createUrl('panel', 'index'), 401);
        }
    }

    public function GET_todos()
    {
        $app = App::i();
        $entity = $app->repo(EntityRecourse::class)->findBy(['opportunity' => $this->data['id']]);
        $this->json($entity, 200);
    }

    public function POST_responder(): void
    {
        //Verificando se o recurso foi respondido
        self::verifyReply($this->data['entityId']);

        $app = App::i();
        //Validações
        if($this->data['reply'] == ''){
            $this->json(['message' => 'Você não poderá enviar com o campo de resposta vazio'], 403);
            return;
        }
        if($this->data['status'] == '' || $this->data['status'] == 'Aberto'){
            $this->json(['message' => 'Informe a situação da resposta ao recurso'], 403);
            return;
        }
        //Formatando o status para gravar no banco
        $statusRecourse =  $this->data['status'];
        if($this->data['status'] == 'Deferido' || $this->data['status'] == 'Indeferido'){
            $statusRecourse = self::getSituationToStatus( $this->data['status'] );
        }

        $recourse = $app->repo(EntityRecourse::class)->find($this->data['entityId']);
        $recourse->recourseReply = $this->data['reply'];
        $recourse->replyResult = $this->data['replyResult'] ?: null;
        $recourse->recourseDateReply = new DateTime;
        $recourse->status = $statusRecourse;
        $recourse->replyAgentId = $app->getAuth()->getAuthenticatedUser()->profile->id;
        $recourse->createTimestamp = new DateTime();

        $recourseData = [
            'Resposta' => $this->data['reply'],
            'Respondido por: ' => $app->getAuth()->getAuthenticatedUser()->profile->id,
            'Alterado em: ' => $recourse->recourseDateReply,
        ];
        ($recourse->replyResult) && ($recourseData['Nota'] = $recourse->replyResult);
        //Gravando dados para log de atividades
        $revision = new Revision($recourseData,$recourse,Revision::ACTION_MODIFIED, 'Recurso respondido');
        try {
            $app->applyHookBoundTo($this, 'recourse.reply', [&$recourse]);

            $app->em->persist($recourse);
            $app->em->flush();
            $revision->save(true);

            http_response_code(202);
            echo json_encode(['message' => 'Recurso respondido com sucesso!']);
        }catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'message' => 'Ocorreu um erro inesperado!',
                'errorMessage' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function GET_registration()
    {
        $app = App::i();
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


    function POST_disabledRecourse(): void
    {
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

    /**
     * @throws WorkflowRequest
     * @throws PermissionDenied
     */
    public function POST_sendRecourse(): void
    {
        $app = App::i();

        if(is_null($this->data['recourse'])) {
            $this->errorJson('Informe o recurso', 400);
            return;
        }

        /** @var \MapasCulturais\Entities\Registration $registration */
        $registration = $app->repo('Registration')->find($this->data['registration']);
        $agent = $registration->owner;

        if(!$agent->canUser('@control')) {
            $this->errorJson(['message' => 'Você não tem permissão para realizar esta ação'], 401);
            return;
        }

        $opportunity = $app->repo('Opportunity')->find($this->data['opportunity']);

        $recourse = new EntityRecourse;

        try {
            $app->em->beginTransaction();

            $recourse->recourseText = $this->data['recourse'];
            $recourse->recourseSend = new \DateTime();
            $recourse->status = EntityRecourse::STATUS_DRAFT;
            $recourse->registration = $registration;
            $recourse->opportunity = $opportunity;
            $recourse->agent = $agent;
            $recourse->create_timestamp = new \DateTime();

            $app->applyHookBoundTo($this, 'recourse.send', [&$recourse]);
            $recourse->save(true);

            foreach($_FILES as $file) {
                $app->disableAccessControl();

                $newFile = new RecourseFile($file);
                $newFile->setGroup('recourse-attachment');
                $newFile->owner = $recourse;
                $newFile->makePrivate();

                $app->enableAccessControl();
            }

            $app->applyHookBoundTo($this, 'recourse.send', [&$recourse]);

            $app->em->commit(true);

            http_response_code(201);
            echo json_encode(['message' => 'Recurso enviado com sucesso']);
        } catch (\Exception $e) {
            $recourse && $recourse->delete();
            http_response_code(500);
            echo json_encode([
                'message' => 'Erro inesperado, tente novamente',
                'errorMessage' => $e->getMessage(),
            ]);
        }

        exit;
    }

    /*
     * Função para verificar se já tem resposta de um recurso
     * */
    public function verifyReply($recourse): void
    {
        $app = App::i();
        $rec = $app->repo('Recourse\Entities\Recourse')->find($recourse);
        if(!is_null($rec->recourseReply) && !is_null($rec->recourseDateReply) && $rec->replyAgentId !== $app->getAuth()->getAuthenticatedUser()->profile->id)
        {
            $this->errorJson('Esse recurso foi respondido', 403);
        }
    }

    /**
     * Função que publica os recursos
     * @params $opportunity integer
     */
    public function POST_publish(): void
    {
        $res = EntityRecourse::publishRecourse($this->postData['opportunity']);
        if($res > 0) {
            $this->json([ 'title' => 'Sucesso', 'message' => 'Publicação realizada com sucesso', 'status' => 200], 200);
            return;
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

    protected function createLogsRevision(array $dataRevision, \Recourse\Entities\Recourse $recourse): void
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
