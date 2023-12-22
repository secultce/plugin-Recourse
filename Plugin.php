<?php
namespace Recourse;
use MapasCulturais\App;
use MapasCulturais\i;

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
            dump($__template);
            $app->view->enqueueScript(
                'app', // grupo de scripts
                'recourse',  // nome do script
                'js/recourse/recourse.js', // arquivo do script
                [] // dependências do script
            );
            $this->part('recourse/opportunity-recourse-form', [ 'enableRecourse' => $enableRecourse]);
        });
        $app->hook('template(opportunity.edit.registration-config):after', function(){
            dump('registration-config - after');
        });


 

   }//fim _init

   function register () {
    $app = App::i();
    $app->registerController('recursos', 'Recourse\Controllers\Recourse');
   }
}