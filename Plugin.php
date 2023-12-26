<?php
namespace Recourse;
use MapasCulturais\App;
use MapasCulturais\i;
use Recourse\Controllers\Recourse as RecourseController;

class Plugin extends \MapasCulturais\Plugin {
    function _init () {
        $app = App::i();
        $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
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
    
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
            $opportunity = $this->controller->requestedEntity;        
            // dump($opportunity->canUser('@control'));
            // $this->part('singles/opportunity-resources', ['entity' => $opportunity]);
            if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
                $this->part('singles/opportunity-resources', ['entity' => $opportunity, 'app' => $app]);
                // $this->part('tab', ['id' => 'resource', 'label' => i::__('Recursos')]);
            }
        });

        $app->hook('view.partial(claim-configuration).params', function($__data, &$__template) use ($app){
//            dump($__data['opportunity']->getMetadata('claimDisabled'));
            //0 Está habilitado - 1 Não está habilitado
            $enableRecourse = $__data['opportunity']->getMetadata('claimDisabled');
            if($enableRecourse == 1){
                RecourseController::verifyClaim($__data['opportunity']);

            }
            dump($__template);
            $app->view->enqueueScript(
                'app', // grupo de scripts
                'recourse',  // nome do script
                'js/recourse/recourse.js', // arquivo do script
                [] // dependências do script
            );
            $dtInitial = $__data['opportunity']->getMetadata('recourse_date_initial');
            $this->part('recourse/opportunity-recourse-form', [ 'enableRecourse' => $enableRecourse, 'dtInitial' => $dtInitial]);
        });
        $app->hook('template(opportunity.edit.registration-config):after', function(){
            dump('registration-config - after');
        });


 

   }//fim _init

   function register () {
    $app = App::i();
    $app->registerController('recursos', 'Recourse\Controllers\Recourse');
       $this->registerOpportunityMetadata('recourse_date_initial', [
           'label' => i::__('Data Inicial'),
           'type' => 'date',
       ]);
       $this->registerOpportunityMetadata('recourse_time_initial', [
           'label' => i::__('Hora Inicial'),
           'type' => 'time',
       ]);
       $this->registerOpportunityMetadata('date-end', [
           'label' => i::__('Hora Inicial'),
           'type' => 'date',
       ]);
       $this->registerOpportunityMetadata('time-end', [
           'label' => i::__('Hora Final'),
           'type' => 'time',
       ]);
   }
}