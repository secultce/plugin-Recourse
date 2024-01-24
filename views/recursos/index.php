<?php
$this->layout = 'panel';
$app->view->jsObject['entity'] = $entity;

$op = $app->repo('Opportunity')->find($entity->id);
?>
<div ng-app="ng.recourse" class="panel-list panel-main-content">
    <div class="alert info">
        Lembramos que ao clicar em <strong>Responder <i class="fa fa-edit"></i>  </strong>, o recurso tem privilégio
        de edição antes da publicação, somente para você. Não poderá mais ser editado por ninguém.
        Lembramos que ao clicar em Responder, o recurso só poderá ser respondido e editado exclusivamente por você,
        antes da publicação. Não poderá ser editado por outra pessoa.
    </div>
    <div class="panel-header clearfix" ng-controller="RecourseController">
        <p class="text-center">
        <h5 style="color:#636161;">
            Recursos da Oportunidade
        </h5>
        </p>
        <p>
        <h5>
            <a href="<?php echo $urlOpp; ?>" target="_blank">
                <?php echo $entity->name; ?>
            </a>
        </h5>
        </p>
        <p>
            Total de recursos: <small class="badge">{{data.recourses.length}}</small>
            Total rec. sem resposta: <small class="badge">{{countNotReply}}</small>
            <a href="#" class="btn btn-default" style="float: right" title="Imprimir todos recursos">Imprimir Recursos</a>
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
                    <td>
                        <a href="{{redirectRegistrarion(recourses.registration.id)}}" target="_blank">
                            {{recourses.registration.id}}
                        </a>
                    </td>
                    <td>{{recourses.agent.name}}</td>
                    <td>
                        {{recourses.recourseText.substring(0, 100)}}...
                        <a ng-click="dialogSecult(1, 'Recurso', recourses.recourseText)">Ler mais</a>
                    </td>

                    <td>{{recourses.recourseSend}}</td>
                    <td>{{recourses.recourseStatus}}</td>
                    <td>
                        <p ng-if="recourses.recourseReply.length > 100">
                            <small> {{recourses.recourseReply.substr(0, 100)}}...</small>
                        </p>
                        <p ng-if="recourses.recourseReply.length < 100">
                            <small> {{recourses.recourseReply}}</small>
                        </p>
                        <a class="btn btn-recourse" style="color: #0a766a"
                           title="Responder ou editar o recurso do candidato"
                           ng-click="replyRecourse(
                                recourses.id,
                                recourses.registration.id,
                                recourses.agent,
                                recourses.recourseText,
                                recourses.recourseSend,
                                recourses.recourseStatus,
                                recourses.replyAgentId,
                                recourses.recourseReply
                            )"
                        >
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="btn btn-recourse" title="Visualizar Resposta" ng-click="verifyView(recourses.recourseReply)">
                            <i class="fas fa-eye"></i>
                        </a>
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
                            <td> <strong>Inscrição:</strong>
                                <a href="{{redirectRegistrarion(recourseAdmin.registration)}}" target="_blank">
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
                                <strong>Aberto por: </strong>  {{recourseAdmin.agent.name}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Enviado em: </strong> {{recourseAdmin.recourseSend}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Situação: </strong> {{getSituation(recourseAdmin.status)}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    <div class="reply-shadow" >
                        <div class="form-group">
                            <label for="label-reply-form">Responder ao recurso</label>
                            <textarea name="reply" class="form-control" rows="10" ng-model="recourseAdmin.reply">{{recourseAdmin.reply}}</textarea>
                            <label for="label-reply-form">Alterar a situação {{recourseAdmin.status}}</label>
                            <select name="situation" ng-change="changeSituation()" ng-model="recourseAdmin.status" id="" class="form-control">
                                <option value="">--Selecione--</option>
                                <option value="1">Deferido</option>
                                <option value="-9" >Indeferido</option>
                            </select>
                            <div class="form-group" ng-if="noteActual > 0">
                                <label for="">Nota Atual: {{noteActual}}</label>
                                <p class="textcenter">
                                    <small>--</small>
                                </p>
                                <p>
                                    <label for="label-reply-form">
                                        <strong>Nova nota</strong>
                                    </label>
                                    <input type="text" class="form-control" ng-model="newNoteReply">
                                </p>
                            </div>
                            <button
                                class="btn btn-primary btn-reply-recourse"
                                type="submit"
                                ng-click="sendReplyRecourse(recourseAdmin.idRecourse, recourseAdmin.status,
                                 recourseAdmin.reply)"
                            >
                                Enviar resposta
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div>
           <div ng-if="!isPublish">
               <button
                       class="btn btn-primary"
                       title="Publica esses recursos para os proponentes visualizem a resposta"
                       ng-click="clickPublish(<?php echo $entity->id; ?>)"
                       ng-if="data.recourses.length > 0"
                       type="button"
               >
                   <?php \MapasCulturais\i::_e('Publicar Recursos'); ?>
                   <i class="fas fa-paper-plane"></i>
               </button>
           </div>
            <div ng-else class="alert success"  ng-if="isPublish">
                <label for="">
                    <h5>Recursos publicado</h5>
                </label>
            </div>
        </div>

    </div>
</div>

<script>



</script>
