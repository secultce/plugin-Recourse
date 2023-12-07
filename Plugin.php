<?php
namespace Recourse;
use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin {
   function _init () {
    $app = App::i();
    $app->view->enqueueScript('app', 'ng-recourse', 'js/ng.recourse.js', ['entity.module.opportunity']);
    $app->view->jsObject['angularAppDependencies'][] = 'ng.recourse';
    
    $app->hook('template(opportunity.single.opportunity-registrations--tables):begin', function () use ($app) {
        // dump($app);
        
    
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