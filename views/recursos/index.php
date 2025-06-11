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
                title="<?= i::_e("Exportar todos recursos"); ?>"
            >
                <?= i::_e("Exportar Recursos"); ?>
            </a>
        </p>
        <hr />
    </div>
    <div ng-controller="RecourseController">
        <div style="width: 100%;" ng-if="veriftRecourses">
            <label for="">{{textVerifyRecourses}}</label>
        </div>
        <table class="table table-bordered table-hover" ng-show="tableRecourse">
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
                    <td >
                        <div ng-bind-html="trustHtmlPreview(recourses.recourseText)"></div>
                        <a ng-click="dialogSecult(1, 'Recurso', recourses.recourseText)"
                           ng-if="hasMoreThan100(recourses.recourseText)">
                            Ler mais
                        </a>

                        <div class="recourse-attachments">
                            <a
                                ng-repeat="file in recourses.files"
                                href="{{file.url}}"
                                target="_blank"
                                class="recourse-attachment-item"
                            >
                                {{ file.name.length > 50 ? file.name.substring(0, 50) + '...' : file.name }}
                            </a>
                        </div>
                    </td>

                    <td>{{recourses.recourseSend}}</td>
                    <td>{{getSituation(recourses.status)}}</td>
                    <td>
                        <p ng-if="recourses.recourseReply.length > 100">
                            <small> {{recourses.recourseReply.substr(0, 100)}}...</small>
                        </p>
                        <p ng-if="recourses.recourseReply.length < 100">
                            <small> {{recourses.recourseReply}}</small>
                        </p>
                        <?php if (Util::isRecourseResponsePeriod($entity)): ?>
                        <a class="btn btn-recourse" style="color: #0a766a" ng-if="!isPublish"
                            title="Responder ou editar o recurso do candidato"
                            ng-click="replyRecourse(
                                recourses.id,
                                recourses.registration.id,
                                recourses.agent,
                                recourses.recourseText,
                                recourses.recourseSend,
                                recourses.status,
                                recourses.replyAgent,
                                recourses.recourseReply,
                                recourses.replyResult
                            )">
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
                            onclick="showOpinions(this.getAttribute('data-id'))"
                        >
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($entity->canUser('@control') && $hasSecultSeal): ?>
                            <a 
                                href="<?= $app->createUrl('recursos', 'printRecourse', ['recourseId' => '{{recourses.id}}']) ?>"
                                class="btn btn-recourse"
                                title="Imprimir recurso"
                                target="_blank"
                            >
                                <i class="fas fa-print"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>

        </table>

        <div style="width: 100%;" ng-show="divReplyRecourse">
            <button class="btn btn-default" ng-click="backRecourse()" title="Voltar para lista que tem todos os recursos">
                <i class="fas fa-arrow-left"></i>
                Voltar para lista
            </button>
        </div>

        <table class="table table-bordered response-table" width='100%' ng-show="divReplyRecourse">
            <tr>
                <td width="50%">
                    <table width="100%">
                        <tr>
                            <td> <strong>Inscrição:</strong>
                                <a href="{{redirectRegistration(recourseAdmin.registration)}}" target="_blank">
                                    {{recourseAdmin.registration}}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Recurso: </strong>
                                {{recourseAdmin.recourseText}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Aberto por: </strong>
                                {{recourseAdmin.agent.name}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Enviado em: </strong>
                                {{recourseAdmin.recourseSend}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Situação: </strong>
                                {{getSituation(recourseAdmin.status)}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    <div class="reply-shadow">
                        <div class="form-group">
                            <label for="label-reply-form"><b>Responder ao recurso:</b></label>
                            <textarea name="reply" class="form-control resource-response__textarea" rows="10" ng-model="recourseAdmin.reply">{{recourseAdmin.reply}}</textarea>
                            <label for="label-reply-form"><b>Alterar a situação:</b></label>
                            <select name="situation" ng-change="changeSituation()" ng-model="recourseAdmin.status" id="" class="form-control">
                                <option value="" disabled selected>--Selecione--</option>
                                <option value="1">Deferido</option>
                                <option value="8">Deferido parcialmente</option>
                                <option value="-9">Indeferido</option>
                            </select>
                            <div class="form-group" ng-if="<?= $entity->evaluationMethodConfiguration->type == 'technical' ?> && (recourseAdmin.status == '1' || recourseAdmin.status == '8')">
                                <label for="">Nota Atual: {{currentGrade}}</label>
                                <p class="textcenter">
                                    <small>--</small>
                                </p>
                                <p>
                                    <label for="label-reply-form">
                                        <strong>Nova nota:</strong>
                                    </label>
                                    <input type="text" class="form-control" ng-model="recourseAdmin.replyResult">
                                </p>
                            </div>
                            <button
                                class="btn btn-primary btn-reply-recourse"
                                type="submit"
                                ng-click="sendReplyRecourse(
                                    recourseAdmin.idRecourse,
                                    recourseAdmin.status,
                                    recourseAdmin.reply,
                                    recourseAdmin.replyResult
                                )">
                                Enviar resposta
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
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

<script>



</script>
