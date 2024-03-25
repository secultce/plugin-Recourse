<?php

declare(strict_types=1);

namespace Recourse;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Plugin as AbstractPlugin;
use Recourse\Controllers\RecourseController;
use Recourse\Entities\Recourse;

class Plugin extends AbstractPlugin
{
    private App $app;

    public function __construct(array $config = [])
    {
        $this->app = App::i();
        parent::__construct($config);
    }

    public function _init(): void
    {
        $this->app->hook(
            'template(opportunity.single.tabs):end',
            fn () => $this->templateOpportunityTabs()
        );

        $this->app->hook(
            'view.partial(claim-configuration):after',
            fn ($__template, $__html) => $this->viewPartialClaimConfiguration($__html)
        );

        $this->app->hook(
            'template(opportunity.single.user-registration-table--registration--status):end',
            fn ($entity) => $this->templateOpportunity($entity)
        );

        $this->app->hook(
            'template(panel.registrations.panel-registration-meta):before',
            fn ($reg) => $this->templatePanelRegistrations($reg)
        );

        $this->app->hook('template(panel.<<*>>.nav.panel.registrations):after', function () {
            $idAgent = $this->app->getUser()->profile->id;
            $this->part('panel/nav-recursos', ['idAgent' => $idAgent]);
        });

        $this->app->hook('doctrine.emum(object_type).values', function(&$result) {
            $result['Recourse'] = 'Recourse\Entities\Recourse';
        });
    }

    private function enqueueStylesheet(): void
    {
        $this->app->view->enqueueStyle('app', 'recoursecss', 'css/recourse/recourse.css', ['main']);
    }

    private function templateOpportunityTabs(): void
    {
        self::_publishAssets();
        $this->enqueueStylesheet();

        $opportunity = $this->controller->requestedEntity;

        if (($opportunity->canUser('viewEvaluations') || $opportunity->canUser('@control')) && false === $opportunity->claimDisabled) {
            $this->part('singles/opportunity-resources', [
                'entity' => $opportunity,
                'app' => $this->app,
            ]);
        }
    }

    private function viewPartialClaimConfiguration(mixed $html): void
    {
        self::_publishAssets();
        $this->enqueueStylesheet();

        $opportunity = $this->controller->requestedEntity;
        $enableRecourse = $opportunity->getMetadata('claimDisabled');

        if ($enableRecourse == '1') {
            RecourseController::verifyClaim($opportunity);
        }

        $metadt = $opportunity->getMetadata();

        $confRecourse = [];

        foreach ($metadt as $key => $met) {
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
            'template' => $html
        ]);
    }

    private function templateOpportunity(mixed $entity): void
    {
        $this->app->view->enqueueScript('app','recourse','js/recourse/recourse.js',[]);

        $strToEnd = $entity->opportunity->getMetadata('recourse_date_end').' '.$entity->opportunity->getMetadata('recourse_time_end');
        $endOfPeriod = \DateTime::createFromFormat('Y-m-d H:i', $strToEnd);
        $baseUrl = $this->app->_config['base.url'];

        if($entity->opportunity->getMetadata('claimDisabled') == '0') {
            $this->part('recourse/recourse-user-registration-status', [
                'entity' => $entity,
                'endOfPeriod' => $endOfPeriod,
                'baseUrl' => $baseUrl
            ]);
        }
    }

    private function templatePanelRegistrations(mixed $registration): void
    {
        $validate = self::verifyPeriodEnd($registration->opportunity);

        $this->app->hook('template(opportunity.single.user-registration-table--registration--status):end', function($reg_args) {
            $entity = $reg_args;
            $this->app->view->enqueueScript('app', 'recourse', 'js/recourse/recourse.js', []);

            $strToEnd = $entity->opportunity->getMetadata('recourse_date_end').' '.$entity->opportunity->getMetadata('recourse_time_end');
            $endOfPeriod = DateTime::createFromFormat('Y-m-d H:i', $strToEnd);//Convertendo para formato Datetime
            $strToInitial = $entity->opportunity->getMetadata('recourse_date_initial').' '.$entity->opportunity->getMetadata('recourse_time_initial');
            $initialOfPeriod = DateTime::createFromFormat('Y-m-d H:i', $strToInitial);//Convertendo para formato Datetime

            $baseUrl = $this->app->_config['base.url'];

            if ($entity->opportunity->getMetadata('claimDisabled') != '0') {
                return;
            }

            $this->part('recourse/recourse-user-registration-status', [
                'entity' => $entity,
                'endOfPeriod' => $endOfPeriod,
                'initialOfPeriod' => $initialOfPeriod,
                'baseUrl' => $baseUrl
            ]);
        });

        $this->enqueueStylesheet();
        self::_publishAssets();

        $registeredRecourses = $this->app->repo(Recourse::class)->findBy([
            'agent' =>  $registration->owner->id,
            'opportunity' => $registration->opportunity->id
        ]);

        $isSendrecourse = count($registeredRecourses) === 0;

        $this->part('recourse/user-open-recourse', [
            'registration' => $registration,
            'isSendrecourse' => $isSendrecourse,
            'validate' => $validate,
        ]);
    }

    protected function _publishAssets(): void
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
        $app->view->enqueueStyle('app', 'secultalert', 'css/recourse/secultce/dist/secultce.min.css');
        $app->view->enqueueScript('app','sweetalert2','https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js');
        $app->view->enqueueScript('app', 'ng-recourse', 'js/ng.recourse.js', []);
        $app->view->enqueueScript('app', 'recourse', 'js/recourse/recourse.js', []);
    }

   public function register(): void
   {
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
           'label' => i::__('Data Final'),
           'type' => 'date',
        ]);

        $this->registerOpportunityMetadata('recourse_time_end', [
           'label' => i::__('Hora Final'),
           'type' => 'time',
        ]);
   }

   public function verifyPeriodEnd(mixed $opportunity): bool
   {
       $strToEnd = $opportunity->getMetadata('recourse_date_end').' '.$opportunity->getMetadata('recourse_time_end');
       $endOfPeriod = DateTime::createFromFormat('Y-m-d H:i', $strToEnd);

       $now = new DateTime();

       return $opportunity->getMetadata('claimDisabled') == '0'
           && $endOfPeriod >= $now;
   }
}
