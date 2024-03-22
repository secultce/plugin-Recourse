<?php
namespace Recourse;
use MapasCulturais\App;
use MapasCulturais\i;
use Recourse\Controllers\Recourse as RecourseController;
use Recourse\Entities\Recourse as EntityRecourse;

class Plugin extends \MapasCulturais\Plugin {
    function _init () {
        $app = App::i();
        $plugin = $this;
        $app->hook('template(opportunity.single.tabs):end', function () use ($app,$plugin) {
            //Assets mais usados nas rotas
            $plugin->_publishAssets();
            //Arquivo Css do plugin
            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            $opportunity = $this->controller->requestedEntity;

            if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
                $this->part('singles/opportunity-resources', ['entity' => $opportunity, 'app' => $app]);
            }
        });

        $app->hook('view.partial(claim-configuration):after', function($__template, &$__html) use ($app,$plugin){
            //add assests
            $plugin->_publishAssets();
            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            //Entidade
            $opp = $this->controller->requestedEntity;

            //0 Está habilitado - 1 Não está habilitado
            $enableRecourse = $opp->getMetadata('claimDisabled');
             //Se alterar a configuração para desabilitar o recurso, faz uma verificação de metadata
            if($enableRecourse == '1'){
                RecourseController::verifyClaim($opp);
            }

            //Todo o metadata da oportunidade
            $metadt = $opp->getMetadata();
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

            $this->part('recourse/opportunity-recourse-form', [
                'enableRecourse' => $enableRecourse,
                'confRecourse' => $confRecourse,
                'template' => $__html
            ]);

        });

        $app->hook('template(opportunity.single.user-registration-table--registration--status):end', function($reg_args) use ($app){
            $entity = $reg_args;
            $app->view->enqueueScript('app','recourse','js/recourse/recourse.js',[]);
            //Período configurado para verificação de data e hora corrente
            $strToEnd = $entity->opportunity->getMetadata('recourse_date_end').' '.$entity->opportunity->getMetadata('recourse_time_end');
            $endOfPeriod = \DateTime::createFromFormat('Y-m-d H:i', $strToEnd);//Convertendo para formato Datetime
            $strToInitial = $entity->opportunity->getMetadata('recourse_date_initial').' '.$entity->opportunity->getMetadata('recourse_time_initial');
            $initialOfPeriod = \DateTime::createFromFormat('Y-m-d H:i', $strToInitial);//Convertendo para formato Datetime
            
            $baseUrl = $app->_config['base.url'];
            //So mostra o botão se o recurso tiver habilitado
            if($entity->opportunity->getMetadata('claimDisabled') == '0')
            {
                $this->part('recourse/recourse-user-registration-status', [
                    'entity' => $entity,
                    'endOfPeriod' => $endOfPeriod,
                    'initialOfPeriod' => $initialOfPeriod,
                    'baseUrl' => $baseUrl
                ]);
            }
        });

        $app->hook('template(panel.registrations.panel-registration-meta):before', function($registration) use ($app, $plugin){
            $validate = $plugin->verifyPeriodEnd($registration->opportunity);
            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            $plugin->_publishAssets();
            //Verificando se já houve envio de recurso
            $rec = $app->repo('Recourse\Entities\Recourse')->findBy([
                'agent' =>  $registration->owner->id,
                'opportunity' => $registration->opportunity->id
            ]);
            //Inicia com verdadeiro, em condições igual a 0, trona-se falso
            $isSendrecourse = true;
            count($rec) > 0 ?: $isSendrecourse = false;

            $this->part('recourse/user-open-recourse', [
                'registration' => $registration,
                'isSendrecourse' => $isSendrecourse,
                'validate' => $validate
            ]);
        });

        /**
         * Adiciona novos menus no painel
         */

        $app->hook('template(panel.<<*>>.nav.panel.registrations):after', function () use($app) {
            $idAgent = $app->getUser()->profile->id;
            $this->part('panel/nav-recursos', ['idAgent' => $idAgent]);
        });

        $app->hook('doctrine.emum(object_type).values', function(&$result) use ($app){
//              array_merge($result, ['Recourse' => 'Recourse\Entities\Recourse']);
            $result["Recourse"] = 'Recourse\Entities\Recourse';

        });
   }//fim _init

    /**
     * Publica todos os assets (css/js)
     *
     */
    protected function _publishAssets()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');
        $app->view->enqueueScript('app','ng-recourse','js/ng.recourse.js',[] );
        $app->view->enqueueScript('app','recourse','js/recourse/recourse.js',[]);
    }

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

   function verifyPeriodEnd($opportunity) {
       $strToEnd = $opportunity->getMetadata('recourse_date_end').' '.$opportunity->getMetadata('recourse_time_end');
       $endOfPeriod = \DateTime::createFromFormat('Y-m-d H:i', $strToEnd);//Convertendo para formato Datetime

       $now = new \DateTime();
       if($opportunity->getMetadata('claimDisabled') == '0'){
           if(  $endOfPeriod >= $now) {
              return true;
           }
       }
       return false;
   }
}