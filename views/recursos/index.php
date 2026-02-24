<?php

use MapasCulturais\i;
use MapasCulturais\Utils;
use Recourse\Utils\Util;

/**
 * @var $app \MapasCulturais\App
 * @var $entity \MapasCulturais\Entities\Opportunity
 * @var $urlOpp string
 */

$this->layout = 'panel';
$app->view->jsObject['entity'] = $entity;

$hasSecultSeal = Utils::checkUserHasSeal(env('SECULT_SEAL_ID'));

?>
<?php $this->applyTemplateHook('recourse-index','begin'); ?>
<div ng-app="ng.recourse" class="panel-list panel-main-content">
    <div class="alert info">
        Lembramos que ao clicar em <strong>Responder <i class="fa fa-edit"></i></strong>, o recurso tem privilégio
        de edição antes da publicação, somente para você. Não poderá mais ser editado por ninguém.
        Lembramos que ao clicar em Responder, o recurso só poderá ser respondido e editado exclusivamente por você,
        antes da publicação. Não poderá ser editado por outra pessoa.
    </div>
    <div class="panel-header clearfix" ng-controller="RecourseController">
        <h5 style="color:#636161;">
            Recursos da Oportunidade
        </h5>
        <h5>
            <a href="<?php echo $urlOpp; ?>" target="_blank">
                <?php echo $entity->name; ?>
            </a>
        </h5>
        <p>
            Total de recursos: <small class="badge">{{data.recourses.length}}</small>
            Total rec. sem resposta: <small class="badge">{{countNotReply}}</small>
            <a
                href="<?= $this->controller->createUrl('exportResponses', ['oportunityId' => $entity->id]) ?>"
                class="btn btn-default download"
                style="float: right"
                title="<?= i::_e("Exportar todos recursos"); ?>">
                <?= i::_e("Exportar Recursos"); ?>
            </a>
        </p>
        <hr />
    </div>
    <div ng-controller="RecourseController">
        <div style="width: 100%;" ng-if="veriftRecourses">
            <label for="">{{textVerifyRecourses}}</label>
        </div>
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="tr-active">
                    <th>Inscrição</th>
                    <th>Aberto por</th>
                    <th>Recurso</th>
                    <th>Enviado em </th>
                    <th>Situação</th>
                    <th>Resposta</th>
                    <th>Respondido em </th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="recourses in data.recourses">

                    <td>
                        <a href="{{redirectRegistration(recourses.registration.id)}}" target="_blank">
                            {{recourses.registration.number}}
                        </a>
                    </td>
                    <td>{{recourses.agent.name}}</td>
                    <td>
                        <a href='#' class='btn btn-recourse' ng-click="dialogSecult('Recurso', recourses.recourseText)">
                            <i class="fas fa-eye" ></i>
                        </a>                       
                        <div class="recourse-attachments">
                            <a
                                ng-repeat="file in recourses.files"
                                href="{{file.url}}"
                                target="_blank"
                                class="recourse-attachment-item">
                                {{ file.name.length > 50 ? file.name.substring(0, 50) + '...' : file.name }}
                            </a>
                        </div>
                    </td>

                    <td>{{recourses.recourseSend}}</td>
                    <td>{{getSituation(recourses.status)}}</td>
                    <td>
                        <?php if (Util::isRecourseResponsePeriod($entity)): ?>
                            <a
                                class="btn btn-recourse sendReply"
                                data-entity-id-cr="{{recourses.id}}"
                                data-entity-context-cr="{{recourses.recourseReply}}"
                                data-status="{{recourses.status}}"
                                data-note="{{recourses.replyResult}}"
                                data-url="recursos/responder"
                                title="Responder recurso"
                                data-action="{{ recourses.recourseReply ? 'update' : 'create' }}"
                            >
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php endif; ?>
                        <a class="btn btn-recourse" title="Visualizar Resposta" ng-click="verifyView(recourses.recourseReply)">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                    <td>
                        {{recourses.recourseDateReply}}
                        <br>
                        {{recourses.replyAgent.name}}
                    </td>
                    <td>
                        <a
                            class="btn btn-recourse"
                            title="Visualizar pareceres"
                            data-id="<?= '{{ recourses.registration.id }}' ?>"
                            onclick="showOpinions(this.getAttribute('data-id'))">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($entity->canUser('@control') && $hasSecultSeal): ?>
                            <a
                                href="<?= $app->createUrl('recursos', 'printRecourse', ['recourseId' => '{{recourses.id}}']) ?>"
                                class="btn btn-recourse"
                                title="Imprimir recurso"
                                target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <?php if ($entity->canUser('@control') && Util::canPostResponses($entity)): ?>
                <div ng-if="!isPublish">
                    <button
                        class="btn btn-primary"
                        title="Publica esses recursos para os proponentes visualizem a resposta"
                        ng-click="clickPublish(<?php echo $entity->id; ?>)"
                        ng-if="data.recourses.length > 0"
                        type="button">
                        <?php \MapasCulturais\i::_e('Publicar Respostas'); ?>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            <?php endif; ?>
            <div ng-else class="alert success" ng-if="isPublish">
                <label for="">
                    <h5>Recursos publicados</h5>
                </label>
            </div>
        </div>
    </div>
</div>
<?php $this->applyTemplateHook('recourse-index','end'); ?>
