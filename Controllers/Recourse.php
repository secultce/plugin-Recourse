<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
use \MapasCulturais\Entities\EntityRevision;
use \MapasCulturais\Entities\EntityRevisionData;

use Recourse\Entities\Recourse as EntityRecourse;


class Recourse extends \MapasCulturais\Controller{

    public function GET_index()
    {

        $app = App::i();        
    }

    public function GET_oportunidade()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueStyle('app', 'recourse', 'css/recourse/recourse.css', ['main']);
        $app->view->enqueueScript(
            'app',
            'sweetalert2', 
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js'
        );
        $app->view->enqueueScript(
            'app', // grupo de scripts
            'ng-recourse',  // nome do script
            'js/ng.recourse.js', // arquivo do script
            [] // dependências do script
        );

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


}