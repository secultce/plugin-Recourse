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
        //    $app->view->enqueueScript(
        //     'app', // grupo de scripts
        //     'ng-recourse',  // nome do script
        //     'js/recourse/ng.recourse.js', // arquivo do script
        //     [] // dependências do script
        // );
        // $app->view->jsObject['angularAppDependencies'][] = 'recourse/ng.recourse';
        // dump($app->view->jsObject);
        // die;
        $app->view->bodyProperties['ng-app'] = "recourse";
        $app->view->bodyProperties['ng-controller'] = "ItemController";
        $app->view->part('recourse/index', ['app' => $app]);
    }
}