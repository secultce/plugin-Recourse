<?php
$this->layout = 'panel';
$app->view->jsObject['entity'] = $entity;
// $app->view->bodyProperties['ng-app'] = "recourse";
// $app->view->bodyProperties['ng-controller'] = "ItemController";
// $app->view->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
// $app->view->part('recourse/index', ['app' => $app]);

// dump($this->jsObject);
?>
<div id="app-recourse">{{ message }}</div>

<div ng-app="ng.recourse" class="panel-list panel-main-content">
    <div class="panel-header clearfix" ng-controller="RecourseController">
        <p class="text-center">
        <h5 style="color:#636161;">
            Recursos da Oportunidade
        </h5>
        </p>
        <p>
        <h5>
            <?php echo $entity->name; ?>
        </h5>
        </p>
        <p>
            Total de recursos: <small class="badge">{{data.recourses.length}}</small>
            Total rec. respondido: <small class="badge">30</small>
        </p>
        <p>
            <hr>
        </p>
    </div>
    <div ng-controller="RecourseController">
        <div style="width: 100%;" ng-if="veriftRecourses">
          
            <label for="">{{textVerifyRecourses}}</label>
        </div>
        <table class="table table-bordered table-hover" ng-show="tableRecourse">
            <thead>
                <tr class="tr-active">
                    <!-- <th>Publicar</th> -->
                    <th>Inscrição</th>
                    <th>Aberto por</th>
                    <th>Recurso</th>
                    <th>Enviado em </th>
                    <th>Status</th>
                    <th>Resposta</th>
                    <th>Respondido em </th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="recourses in data.recourses">
                    <td>{{recourses.registration.id}}</td>
                    <td>{{recourses.agent.name}}</td>
                    <td>{{recourses.recourseText}}</td>
                    <td>{{recourses.recourseSend.date}}</td>
                    <td>{{recourses.recourseStatus}}</td>
                    <td>
                        <button class="btn btn-primary">
                            {{recourses.recourseReply}}
                            <small ng-click="replyRecourse(
                                recourses.id,
                                recourses.registration.id,
                                recourses.agent,
                                recourses.recourseText,
                                recourses.recourseSend.date,
                                recourses.recourseStatus)">Responder</small>
                        </button>
                    </td>
                    <td>{{recourses.recourseDateReply}}</td>
                </tr>
            </tbody>

        </table>
        <table class="table table-bordered" width='100%' ng-show="divReplyRecourse">
            <tr>
                <div style="width: 100%;"  ng-show="divReplyRecourse">
                    <button class="btn btn-default" ng-click="backRecourse()" title="Voltar para lista que tem todos os recursos">
                    <i class="fas fa-arrow-left"></i>
                        Voltar para lista
                    </button>
                </div>
            </tr>
            <tr>
                <td width="50%">
                    <table width="100%">
                        <tr>
                            <td> <strong>Inscrição:</strong> {{recourseAdmin.registration}}</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Recurso: </strong>
                                {{recourseAdmin.recourseText}}
                        
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Aberto por: </strong>  {{recourseAdmin.agent.name}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Enviado em: </strong> {{recourseAdmin.agent.name}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Situação: </strong> {{recourseAdmin.status}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    <div class="reply-shadow" >
                        <div class="form-group">
                            <label for="label-reply-form">Responder ao recurso</label>
                            <textarea name="" id="" class="form-control" rows="10"></textarea>
                            <label for="label-reply-form">Alterar a situação</label>
                            <select name="situation" ng-change="changeSituation()" ng-model="recourseAdmin.status" id="" class="form-control">
                                <option value="">--Selecione--</option>
                                <option value="Deferido">Deferido</option>
                                <option value="Indeferido">Indeferido</option>
                            </select>
                            <button class="btn btn-primary btn-reply-recourse">
                                Enviar resposta
                                <i class="fas fa-paper-plane"></i>
                            </button>

                        </div>

                    </div>
                </td>
            </tr>
        </table>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script !src="">
    var app = new Vue({
        el: '#app-recourse',
        data: {
            message: 'Hello Vue!'
        }
    });
</script>
<?php

$app->view->enqueueScript('app', 'recoursejs', 'js/recourse.js');
?>
