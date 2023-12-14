<?php
namespace Recourse;
use MapasCulturais\App;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\Plugin {
    function _init () {
        $app = App::i();
        $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
    
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
            $opportunity = $this->controller->requestedEntity;        
            // dump($opportunity->canUser('@control'));
            // $this->part('singles/opportunity-resources', ['entity' => $opportunity]);
            if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
                $this->part('singles/opportunity-resources', ['entity' => $opportunity, 'app' => $app]);
                // $this->part('tab', ['id' => 'resource', 'label' => i::__('Recursos')]);
            }
        });
 

   }//fim _init

   function register () {
    $app = App::i();
    $app->registerController('recursos', 'Recourse\Controllers\Recourse');
   }
}