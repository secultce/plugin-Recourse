<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;


class Recourse extends \MapasCulturais\Controller{

    public function GET_index()
    {

        $app = App::i();        
    }

    public function GET_oportunidade()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'recourse', 'css/recourse/style.css', ['main']);
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
        $entity = $app->repo('Recourse\Entities\Recourse')->findBy(['opportunity' => $this->data['id']]);
        $this->json($entity, 200);
    }


}