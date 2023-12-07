<?php
namespace Recourse;
use MapasCulturais\App;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\Plugin {
   function _init () {
    $app = App::i();

   
    $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
        $opportunity = $this->controller->requestedEntity;        
        // dump($opportunity->canUser('@control'));
        // $this->part('singles/opportunity-resources', ['entity' => $opportunity]);
        if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
            $this->part('singles/opportunity-resources', ['entity' => $opportunity, 'app' => $app]);
            // $this->part('tab', ['id' => 'resource', 'label' => i::__('Recursos')]);
        }
    });
    $app->hook('template(opportunity.single.tabs-content):end', function () {
        $opportunity = $this->controller->requestedEntity;
      
        if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
             $this->part('recourse/content-recourse', ['entity' => $opportunity]);
        }
    });

    $app->hook('GET(registration.agent-recourse', function () use ($app) {
        dump('sddsfg');
    });
   }

   function register () {
    $app = App::i();
    $app->registerController('recursos', 'Recourse\Controllers\Recourse');
   }
}