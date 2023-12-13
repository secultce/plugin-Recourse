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
                                recourses.agent.id,
                                recourses.recourseText,
                                recourses.recourseSend.date,
                                recourses.recourseStatus)">Responder</small>
                        </button>
                    </td>
                    <td>{{recourses.recourseDateReply}}</td>                    
                </tr>
            </tbody>

        </table>
        <table class="table table-bordered" width='100%'>
            <tr>
                <td width="50%">
                    <table width="100%">
                        <tr>
                            <td>Inscrição: 887766</td>
                            <td>Recurso: Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! 
                                Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis!
                                Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis!
                                 Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis!
                                 Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! 
                                 Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis!
                                 Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis! Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis! Mussum Ipsum, cacilds vidis litro abertis. Mauris nec dolor in eros commodo tempor. Aenean aliquam molestie leo, vitae iaculis nisl. Quem num gosta di mim que vai caçá sua turmis!
                                  Quem manda na minha terra sou euzis! Eu nunca mais boto a boca num copo de cachaça, agora eu só uso canudis!
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    formulario
                </td>
            </tr>
        </table>
        
    </div>
</div>

</div>