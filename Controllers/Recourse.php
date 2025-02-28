<?php

namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
use MapasCulturais\Entities\EntityRevision as Revision;
use MapasCulturais\Entities\RegistrationEvaluation;
use Recourse\Entities\Recourse as EntityRecourse;
use Recourse\Entities\RecourseFile;
use Recourse\Utils\Util;
use Slim\Exception\Stop;


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
        $app->view->enqueueStyle('app', 'opinionManagement','OpinionManagement/css/opinionManagement.css');
        $app->view->enqueueScript('app', 'opinion-management', 'OpinionManagement/js/opinionManagement.js');

        $entity = $app->repo('Opportunity')->find($this->data['id']);
        $evaluators = $entity->getEvaluationCommittee(false);
        $isEvaluator = array_filter($evaluators, function ($evaluator) use ($app) {
            return $evaluator->userId === $app->auth->authenticatedUser->id;
        });

        //Se for administrador
        if($entity->canUser('@control') || $isEvaluator){
            $urlOpp = $app->createUrl('oportunidade', $entity->id);
            $this->render('index', ['entity' => $entity, 'app' => $app, 'urlOpp' => $urlOpp]);
        }else{
            $app->redirect($app->createUrl('panel', 'index'), 401);
        }
    }

    public function GET_todos()
    {
        $app = App::i();
        $recources = $app->repo(EntityRecourse::class)->findBy(['opportunity' => $this->data['id']]);
        $opportunity = $app->repo('Opportunity')->find($this->data['id']);

        if ($opportunity->canUser('@control')) $this->json($recources, 200);

        $recources = array_filter($recources, function ($recource) use ($app) {
            $registrationEvaluation = $app->repo(RegistrationEvaluation::class)->findBy([
                'registration' => $recource->registration->id,
                'user' => $app->auth->authenticatedUser->id,
            ]);

            return boolval($registrationEvaluation);
        });

        $this->json(array_values($recources), 200);
    }

    public function POST_responder(): void
    {
        $app = App::i();
        $recourse = $app->repo(EntityRecourse::class)->find($this->data['entityId']);

        //Validações
        if(!self::canReply($this->data['entityId'])) {
            $this->json(['message' => 'Este recurso já foi respondido por outro parecerista.'], 403);
            return;
        };
        if($this->data['reply'] == ''){
            $this->json(['message' => 'Você não poderá enviar com o campo de resposta vazio'], 400);
            return;
        }
        if($this->data['status'] == '0' || $this->data['status'] == 'Aberto'){
            $this->json(['message' => 'Informe a situação da resposta ao recurso'], 400);
            return;
        }
        if (!Util::isRecourseResponsePeriod($recourse->opportunity)) {
            $this->json(['message' => 'Resposta não enviada. A inscrição ainda está no período de recurso'], 403);
            return;
        }

        //Formatando o status para gravar no banco
        $statusRecourse =  $this->data['status'];
        if (in_array($this->data['status'], ['Deferido', 'Deferido parcialmente', 'Indeferido'])) {
            $statusRecourse = self::getSituationToStatus($this->data['status']);
        }

        try {
            $app->em->beginTransaction();

            $recourse = $app->repo(EntityRecourse::class)->find($this->data['entityId']);
            $recourse->recourseReply = $this->data['reply'];
            $recourse->replyResult = $this->data['replyResult'] ?: null;
            $recourse->recourseDateReply = new DateTime;
            $recourse->status = $statusRecourse;
            $recourse->replyAgent = $app->getAuth()->getAuthenticatedUser()->profile;

            $app->applyHookBoundTo($this, 'recourse.reply', [&$recourse]);

            $recourseData = [
                'status' => $statusRecourse,
                'Resposta' => $this->data['reply'],
            ];
            ($recourse->replyResult) && ($recourseData['Nota'] = $recourse->replyResult);
            //Gravando dados para log de atividades
            $revision = new Revision($recourseData, $recourse,Revision::ACTION_MODIFIED, 'Recurso respondido');
            $revision->save(true);

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
            case 'Deferido parcialmente':
                $situ = EntityRecourse::STATUS_PARTIALLY_APPROVED;
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

    public function POST_updateRecourse(): void
    {
        $this->requireAuthentication();

        $app = App::i();
        $recourse = $app->repo(EntityRecourse::class)->findOneBy(['id' => $this->data['recourseId']]);

        if (!$recourse->registration->canUser('@control')) {
            $this->json(['message' => 'Você não tem permissão para realizar esta ação'], 401);
            return;
        }

        if (!Util::isRecoursePeriod($recourse->opportunity)) {
            $this->json(['message' => 'O período do recurso está encerrado'], 403);
            return;
        }

        try {
            foreach ($_FILES as $file) {
                $app->disableAccessControl();

                $newFile = new RecourseFile($file);
                $newFile->setGroup('recourse-attachment');
                $newFile->owner = $recourse;
                $newFile->makePrivate();
                $newFile->save();

                $app->enableAccessControl();
            }

            $recourse->recourseText = $this->data['recourseText'];
            $recourse->recourseSend = new \DateTime();
            $recourse->save(true);

            $app->em->flush();

            $this->json(['message' => 'Recurso atualizado com sucesso'], 201);
        } catch (\PDOException $e) {
            $this->json([
                'message' => 'Erro inesperado, tente novamente',
                'errorMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function POST_deleteFile(): void
    {
        $this->requireAuthentication();

        $app = App::i();
        $conn = $app->em->getConnection();

        $query = "SELECT * FROM file WHERE id = :id";
        $params = [
            "id" => $this->data['fileId'],
        ];
        $file = $conn->fetchAssociative($query, $params);

        $recourse = $app->repo(EntityRecourse::class)->findOneBy(['id' => $file['object_id']]);
        if (!$recourse->registration->canUser('@control')) {
            $this->json(['message' => 'Você não tem permissão para realizar esta ação'], 401);
            return;
        }

        if (!Util::isRecoursePeriod($recourse->opportunity)) {
            $this->json(['message' => 'Você não pode mais remover este arquivo, pois o período do recurso está encerrado'], 403);
            return;
        }

        try {
            $stmt = $conn->prepare('DELETE FROM file WHERE id = :id');
            $stmt->bindParam('id', $file["id"]);
            $stmt->executeStatement();

            unlink(PRIVATE_FILES_PATH . $file["path"]);

            $this->json(['message' => 'O arquivo foi removido do recurso com sucesso'], 201);
        } catch (\PDOException $e) {
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
        $opportunity = $app->repo('Opportunity')->find($this->postData['opportunity']);

        if (!$opportunity->canUser('@control')) {
            $this->json([
                'title' => 'Error',
                'message' => 'Você não tem permissão para realizar esta ação',
                'type' => 'error',
            ], 401);
            return;
        }

        if (!Util::canPostResponses($opportunity)) {
            $this->json(['message' => 'Respostas não publicadas. Há recursos não respondidos ou o período do recurso está aberto'], 403);
            return;
        }

        try {
            $app->repo('Recourse\Entities\Recourse')->publish($this->postData['opportunity']);

            $this->json([
                'title' => 'Sucesso',
                'message' => 'Publicação realizada com sucesso',
                'status' => 200,
            ]);
        } catch (Stop $stop) {
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
