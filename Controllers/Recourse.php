<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
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
        $this->render('index', ['entity' => $entity]);        
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
        $recourse = $app->repo(EntityRecourse::class)->find($this->data['entityId']);
        $recourse->recourseReply = $this->data['reply'];
        $recourse->recourseDateReply = new DateTime;
        $recourse->replyAgentId = $app->getAuth()->getAuthenticatedUser()->profile->id;
        // dump($app->getAuth()->getAuthenticatedUser()->profile->id);
       
        dump($recourse);


        // $hook_prefix = $this->getHookPrefix();
        // $app->applyHookBoundTo($this, "{$hook_prefix}.recourses", [&$return_recourses]);
    }

    public function GET_registration()
    {
        $app = App::i();
        dump($this->data);
        $reg = $app->repo('Registration')->find($this->data['id']);
        return $this->json(['resultConsolidate' => $reg->consolidatedResult]);

    }


}