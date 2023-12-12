<?php
$this->layout = 'panel';
$app->view->jsObject['entity'] = $entity;
// $app->view->bodyProperties['ng-app'] = "recourse";
// $app->view->bodyProperties['ng-controller'] = "ItemController";
// $app->view->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
// $app->view->part('recourse/index', ['app' => $app]);


// dump($this->jsObject);
?>
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
       
        <table class="table table-bordered table-hover">
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
                            <small ng-click="replyRecourse(recourses.id, recourses.recourseText)">Responder</small>
                        </button>
                    </td>
                    <td>{{recourses.recourseDateReply}}</td>                    
                </tr>
            </tbody>

        </table>
        
    </div>
</div>

</div>