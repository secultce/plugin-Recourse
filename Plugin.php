<?php
namespace Recourse;
use MapasCulturais\App;
use MapasCulturais\i;
use Recourse\Controllers\Recourse as RecourseController;

class Plugin extends \MapasCulturais\Plugin {
    function _init () {
        $app = App::i();


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
            $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
            $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
            $opportunity = $this->controller->requestedEntity;        
            // dump($opportunity->canUser('@control'));
            // $this->part('singles/opportunity-resources', ['entity' => $opportunity]);

            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
                $this->part('singles/opportunity-resources', ['entity' => $opportunity, 'app' => $app]);
                // $this->part('tab', ['id' => 'resource', 'label' => i::__('Recursos')]);
            }
        });

        $app->hook('view.partial(claim-configuration).params', function($__data, &$__template) use ($app){
            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            //0 Está habilitado - 1 Não está habilitado
            $enableRecourse = $__data['opportunity']->getMetadata('claimDisabled');
            //Se alterar a configuração para desabilitar o recurso, faz uma verificação de metadata
            if($enableRecourse == '1'){
                RecourseController::verifyClaim($__data['opportunity']);
            }

            $app->view->enqueueScript(
                'app', // grupo de scripts
                'recourse',  // nome do script
                'js/recourse/recourse.js', // arquivo do script
                [] // dependências do script
            );
            //Todo o metadata da oportunidade
            $metadt = $__data['opportunity']->getMetadata();
            //Array gerado para enderezar o valores na view
            $confRecourse = [];
            //Preenchendo array
            foreach ($metadt as $key => $met)
            {
                switch ($key){
                    case 'recourse_date_initial':
                        $confRecourse['dt_initial'] = $met;
                        break;
                    case 'recourse_time_initial':
                        $confRecourse['tm_initial'] = $met;
                        break;
                    case 'recourse_date_end':
                        $confRecourse['dt_end'] = $met;
                        break;
                    case 'recourse_time_end':
                        $confRecourse['tm_end'] = $met;
                        break;
                }
            }

            $this->part('recourse/opportunity-recourse-form', [ 'enableRecourse' => $enableRecourse, 'confRecourse' => $confRecourse]);

        });
        $app->hook('template(opportunity.single.user-registration-table--registration--status):end', function($reg_args) use ($app){
            $entity = $reg_args;

            $app->view->enqueueScript(
                'app', // grupo de scripts
                'recourse',  // nome do script
                'js/recourse/recourse.js', // arquivo do script
                [] // dependências do script
            );
            //Período configurado para verificação de data e hora corrente
            $strToEnd = $entity->opportunity->getMetadata('recourse_date_end').' '.$entity->opportunity->getMetadata('recourse_time_end');
            $now = DateTime::createFromFormat('Y-m-d H:i', $strToEnd);//Convertendo para formato Datetime

            $this->part('recourse/recourse-user-registration-status', ['entity' => $entity]);
        });

        $app->hook('template(panel.registrations.panel-registration-meta):before', function($registration) use ($app){
        dump($registration->getMetadata('claimDisabled'));
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
       $this->registerOpportunityMetadata('recourse_date_end', [
           'label' => i::__('Hora Inicial'),
           'type' => 'date',
       ]);
       $this->registerOpportunityMetadata('recourse_time_end', [
           'label' => i::__('Hora Final'),
           'type' => 'time',
       ]);
   }
}