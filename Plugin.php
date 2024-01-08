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
            $plugin->_publishAssets();
            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            $opportunity = $this->controller->requestedEntity;

            if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && !$opportunity->claimDisabled) {
                $this->part('singles/opportunity-resources', ['entity' => $opportunity, 'app' => $app]);
                // $this->part('tab', ['id' => 'resource', 'label' => i::__('Recursos')]);
            }
        });

        $app->hook('view.partial(claim-configuration).params', function($__data, &$__template) use ($app,$plugin){
            $plugin->_publishAssets();
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
            $endOfPeriod = \DateTime::createFromFormat('Y-m-d H:i', $strToEnd);//Convertendo para formato Datetime
            $baseUrl = $app->_config['base.url'];
            //So mostra o botão se o recurso tiver habilitado
            if($entity->opportunity->getMetadata('claimDisabled') == '0')
            {
                $this->part('recourse/recourse-user-registration-status', [
                    'entity' => $entity,
                    'endOfPeriod' => $endOfPeriod,
                    'baseUrl' => $baseUrl
                ]);
            }
        });


        $app->hook('template(panel.registrations.panel-registration-meta):before', function($registration) use ($app, $plugin){
//            dump($registration->opportunity->getMetadata('claimDisabled'));
            $validadte = $plugin->verifyPeriodEnd($registration->opportunity);

            $app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
            $app->view->enqueueScript('app','recourse','js/recourse/recourse.js',[] );
            $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
            $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');

            //Verificando se já houve envio de recurso
            $rec = $app->repo('Recourse\Entities\Recourse')->findBy([
                'agent' =>  $registration->owner->id,
                'opportunity' => $registration->opportunity->id
            ]);
            //Inicia com verdadeiro, em condições igual a 0, trona-se falso
            $isSendrecourse = true;
            count($rec) > 0 ?: $isSendrecourse = false;

            if($validadte)
                $this->part('recourse/user-open-recourse', [
                    'registration' => $registration,
                    'isSendrecourse' => $isSendrecourse
                ]);
        });

        /**
         * Adiciona novos menus no painel
         */
        $app->hook('template(panel.registrations.nav.panel.registrations):after', function () use($app) {
            $idUser = $app->getUser()->id;
            $this->part('panel/nav-recursos', ['idUser' => $idUser]);
        });
 

   }//fim _init

    /**
     * Publica todos os assets (css/js)
     * @see \MapasCulturais\Themes\BaseV1\Theme::_publishAssets()
     */
    protected function _publishAssets()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');
        $app->view->enqueueScript('app','ng-recourse','js/ng.recourse.js',[] );

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
//       dump($strToEnd);
//       dump($endOfPeriod);
//       dump($opportunity);

       $now = new \DateTime();
       if($opportunity->getMetadata('claimDisabled') == '0'){
           if(  $endOfPeriod >= $now) {
              return true;
           }
       }
       return false;

   }
}