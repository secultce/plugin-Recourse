<?php
namespace Recourse\Controllers;

use DateTime;
use \MapasCulturais\App;
use MapasCulturais\Entities\OpportunityMeta;
// use Dompdf\Dompdf;
// require_once PROTECTED_PATH. 'vendor/dompdf/autoload.inc.php';

class Recourse extends \MapasCulturais\Controller{

    public function GET_index()
    {

        $app = App::i();
            $app->view->enqueueScript(
            'app', // grupo de scripts
            'ng-recourse',  // nome do script
            'js/ng.recourse.js', // arquivo do script
            [] // dependências do script
        );
        $this->render('index');
       
        // $app->view->jsObject['angularAppDependencies'][] = 'recourse/ng.recourse';
        // dump($app->view->jsObject);
        // die;
      
        
    }

    public function GET_oportunidade()
    {
        $app = App::i();
        $app->view->enqueueScript(
            'app', // grupo de scripts
            'ng-recourse',  // nome do script
            'js/ng.recourse.js', // arquivo do script
            [] // dependências do script
        );
        $entity = $app->repo('Opportunity')->find($this->data['id']);
        $this->render('index', ['entity' => $entity]);
        
    }
}