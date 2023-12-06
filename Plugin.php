<?php
namespace Recourse;
use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin {
   function _init () {
    $app = App::i();
   
    $app->hook('template(opportunity.single.opportunity-registrations--tables):begin', function () use ($app) {
        dump($app);
    });
   }

   function register () {}
}