<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
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
        //Validações
        if(!self::canReply($this->data['entityId'])) {
            $this->json(['message' => 'O recurso já foi respondido'], 403);
            return;
        };
        if($this->data['reply'] == ''){
            $this->json(['message' => 'Você não poderá enviar com o campo de resposta vazio'], 400);
            return;
        }
        if($this->data['status'] == '' || $this->data['status'] == 'Aberto'){
            $this->json(['message' => 'Informe a situação da resposta ao recurso'], 400);
            return;
        }

        //Formatando o status para gravar no banco
        $statusRecourse =  $this->data['status'];
        if($this->data['status'] == 'Deferido' || $this->data['status'] == 'Indeferido'){
            $statusRecourse = self::getSituationToStatus( $this->data['status'] );
        }

        $app = App::i();

        try {
            $app->em->beginTransaction();

            $recourse = $app->repo(EntityRecourse::class)->find($this->data['entityId']);
            $recourse->recourseReply = $this->data['reply'];
            $recourse->replyResult = $this->data['replyResult'] ?: null;
            $recourse->recourseDateReply = new DateTime;
            $recourse->status = $statusRecourse;
            $recourse->replyAgent = $app->getAuth()->getAuthenticatedUser()->profile;
            $recourse->createTimestamp = new DateTime();

            $app->applyHookBoundTo($this, 'recourse.reply', [&$recourse]);

            $recourseData = [
                'status' => $statusRecourse,
                'Resposta' => $this->data['reply'],
                'Respondido por: ' => $app->getAuth()->getAuthenticatedUser()->profile->id,
                'Alterado em: ' => $recourse->recourseDateReply,
            ];
            ($recourse->replyResult) && ($recourseData['Nota'] = $recourse->replyResult);
            //Gravando dados para log de atividades
            $revision = new Revision($recourseData, $recourse,Revision::ACTION_MODIFIED, 'Recurso respondido');

            $app->em->commit();
            $app->em->flush();

            $this->json(['message' => 'Recurso respondido com sucesso!'], 202);
        }catch (\PDOException $e) {
            $this->json([
                'message' => 'Ocorreu um erro inesperado!',
                'errorMessage' => $e->getMessage(),
            ], 500);
        }
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
            $this->json(['message' => 'Informe o recurso'], 400);
            return;
        }

        /** @var \MapasCulturais\Entities\Registration $registration */
        $registration = $app->repo('Registration')->find($this->data['registration']);
        $recourse = $app->repo('Recourse\Entities\Recourse')->findBy(['registration' => $registration]);
        if(count($recourse) > 0) {
            $this->json(['message' => 'Você já enviou um recurso para esta inscrição'], 400);
            return;
        }

        $agent = $registration->owner;

        if(!$agent->canUser('@control')) {
            $this->json(['message' => 'Você não tem permissão para realizar esta ação'], 401);
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

            $this->json(['message' => 'Recurso enviado com sucesso'], 201);
        } catch (\PDOException $e) {
            $recourse && $recourse->delete();
            $this->json([
                'message' => 'Erro inesperado, tente novamente',
                'errorMessage' => $e->getMessage(),
            ], 500);
        }
    }

    /*
     * Função para verificar se já tem resposta de um recurso
     * */
    public static function canReply($recourse): bool
    {
        $app = App::i();
        $rec = $app->repo('Recourse\Entities\Recourse')->find($recourse);
        if($rec->replyPublish) {
            return false;
        }
        if($rec->replyAgent && $rec->replyAgent->id !== $app->getAuth()->getAuthenticatedUser()->profile->id) {
            return false;
        }

        return true;
    }

    /**
     * Função que publica os recursos
     * @params $opportunity integer
     */
    public function POST_publish(): void
    {
        $app = App::i();
        try {
            $app->repo('Recourse\Entities\Recourse')->publish($this->postData['opportunity']);
            $this->json([
                'title' => 'Sucesso',
                'message' => 'Publicação realizada com sucesso',
            ]);
        } catch (Slim\Exception\Stop $stop) {
        } catch (\Exception $e) {
            $this->json([
                'title' => 'Error',
                'message' => 'Ocorreu um erro inesperado.',
                'type' => 'error',
                'errorMessage' => $e->getMessage(),
            ], 500);
            return;
        }
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
}
