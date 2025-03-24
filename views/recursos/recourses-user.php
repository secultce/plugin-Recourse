<?php

use Recourse\Utils\Util;

/**
 * @var \MapasCulturais\App $app
 * @var bool $isOwner
 * @var \Recourse\Entities\Recourse[] $allRecoursesUser
 */

$this->layout = 'panel';

?>

<div class="panel-list panel-main-content">
    <?php $this->applyTemplateHook('panel-header','before'); ?>
    <header class="panel-header clearfix">

        <h2><?php \MapasCulturais\i::_e("Meus Recursos");?></h2>

    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>
    <div id="table-recourse" ng-app="ng.recourse">
        <?php if($isOwner): ?>
        <div class="table-responsive"  ng-controller="RecourseController">
            <table class="table table-bordered" id="tableAllRecourse" style="width:100%;">
                <thead>
                <tr>
                    <th>Oportunidade</th>
                    <th>Inscrição/Agente</th>
                    <th style="width:25%;">Recurso Solicitado</th>
                    <th>Enviado em</th>
                    <th>Situação</th>
                    <th style="width:25%;">Resposta</th>
                    <th>Ações</th>
                </tr>
                <tbody id="bodyAllRecourse">
                <?php foreach ($allRecoursesUser as $recourse): ?>
                    <tr>
                        <td>
                            <a href="<?= $app->createUrl('oportunidade', $recourse->opportunity->id ) ?>">
                                <?php echo $recourse->opportunity->name; ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= $app->createUrl('inscricao', $recourse->registration->id ) ?>">
                                <?php echo $recourse->registration->number; ?>
                            </a>
                            <br>
                            <a href="<?= $app->createUrl('agente', $recourse->registration->owner->id ) ?>">
                                <?php echo $recourse->registration->owner->name; ?>
                            </a>
                        </td>
                        <td>
                            <span>
                                <?php
                                echo substr($recourse->recourseText, 0,100);
                                if(strlen($recourse->recourseText) > 100):
                                    echo "...<br/>
                                          <a href='#' ng-click='infoUserRecourse(\"{$recourse->recourseText}\")'>Ler Recurso</a>";
                                endif;
                                ?>
                            </span>

                            <?php if(count($recourse->files) > 0): ?>
                            <div class="recourse-attachments">
                                <?php
                                    foreach ($recourse->files as $attachment) {
                                        $delBtn = "<span delete-recourse-file-btn class='icon icon-close hltip delete-file-btn' data-file-id='%s' title='Excluir arquivo'></span>";
                                        $showDelBtn = Util::isRecoursePeriod($recourse->opportunity) ? $delBtn : "";

                                        if (strlen($attachment->name) > 50) {
                                            $attachment->name = substr($attachment->name, 0, 50) . '...';
                                        }

                                        echo "<div style='margin-bottom: 2px;'>
                                                <a
                                                    href='{$attachment->url}'
                                                    target='_blank'
                                                    class='recourse-attachment-item'
                                                >{$attachment->name}</a>"
                                                . sprintf($showDelBtn, $attachment->id) .
                                            "</div>";
                                    }
                                ?>
                            </div>
                            <?php endif ?>
                        </td>
                        <td>
                            <?php echo $recourse->recourseSend->format('d/m/Y H:i'); ?>
                        </td>
                        <td>
                            <?php
                           
                            if($recourse->replyPublish){
                                switch ($recourse->status) {
                                    case '0':
                                        echo 'Aberto';
                                        break;
                                    case '1':
                                        echo 'Deferido';
                                        break;
                                    case '8':
                                        echo 'Deferido parcialmente';
                                        break;
                                    case '-9':
                                        echo 'Indeferido';
                                }
                            }else{
                                echo "Aguarde...";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($recourse->replyPublish){
                                if(strlen($recourse->recourseReply) > 30){
                                    echo substr($recourse->recourseReply, 0,100).'...<br/>';?>
                                    <a href="#" ng-click="infoUserRecourse('<?php echo $recourse->recourseReply; ?>')">Ler Resposta</a>
                                    <?php
                                }else{
                                    echo $recourse->recourseReply;
                                }
                            }else{
                                echo '<div class="alert info">Recurso ainda não respondido ou não foi publicado.</div>';
                            }
                            ?>
                        </td>
                        <td>
                            <a
                                class="btn btn-recourse <?= Util::isRecoursePeriod($recourse->opportunity) ? '' : 'disabled' ?>"
                                style="color: #0a766a"
                                title="Editar recurso"
                                edit-recourse-btn
                                data-recourse-id="<?= $recourse->id ?>"
                                data-recourse-text="<?= $recourse->recourseText ?>"
                            >
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </thead>
            </table>
        </div>
<!--        <form>-->
<!--            <button class="btn btn-primary" onClick="window.print()">-->
<!--                <i class="fa fa-print" aria-hidden="true"></i> Imprimir Recursos</button>-->
<!--        </form>-->
        <?php else: ?>
        <div class="alert danger">
            Ops! <br/>
            Você não tem permissão para visualizar os recursos que não são seus.
        </div>
        <?php endif; ?>

    </div>

</div>
