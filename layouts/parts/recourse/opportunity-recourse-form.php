<?php

use \MapasCulturais\i;

//VERIFICANDO SE TEM RECURSO HABILITADO
$enabledDiv = 'hidden';
$selectedEnabled = 'selected';
$selectedDisabled = '';
$entity = $this->controller->requestedEntity;
//Verdadeiro ou Falso se o usuário tem permissão para modificar os campos dessa entidade
$can_edit = $entity->canUser('modifyRegistrationFields');
?>

<p>
<hr>
</p>

<div class="">
    <div class="panel panel-default ">
    <div class="panel-heading"> <h4 class="title-h4"><?php i::_e("Recurso"); ?></h4></div>
    <p class="registration-help mgt-16 mgb-16">
        Espaço para configurar se a oportunidade receberá a modalidade de recurso.
    </p>
        <p class="registration-help mgt-16 mgb-16">
            Se habilitar os recursos tem a possibilidade de adicionar um período onde o usuário enviará seus recursos.
        </p>
    <div class="panel-body">
        <form id="resourceFormData">
            <div id="insertData" class="">
                <table class="table-full">
                    <tr class="table-full">
                        <td>
                            <div class="form-group">
                                 <span class="label"><?php \MapasCulturais\i::_e("Data Inicial"); ?>:</span>
                                <span class="js-editable"
                                      data-type="date"
                                      data-value="<?php echo $confRecourse['dt_initial']; ?>"
                                      data-edit="recourse_date_initial"
                                      data-viewformat="dd/mm/yyyy"
                                      data-showbuttons="false"
                                      data-original-title="<?php \MapasCulturais\i::_e("Data Inicial"); ?>"
                                      data-emptytext="<?php \MapasCulturais\i::_e("Data do início do Recurso"); ?>">
                                   <?php echo $confRecourse['dt_initial']; ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <span class="label"><?php \MapasCulturais\i::_e("Hora Inicial"); ?>:</span>
                                <span class="js-editable"
                                      data-type="time"
                                      data-value="<?php echo $confRecourse['tm_initial']; ?>"
                                      data-edit="recourse_time_initial"
                                      data-viewformat="HH:mm"
                                      data-showbuttons="false"
                                      data-original-title="<?php \MapasCulturais\i::_e("Hora Inicial"); ?>"
                                      data-emptytext="<?php \MapasCulturais\i::_e("Horário do início do Recurso"); ?>">
                                  <?php echo $confRecourse['tm_initial']; ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <span class="label"><?php \MapasCulturais\i::_e("Data Final"); ?>:</span>
                                <span class="js-editable"
                                      data-type="date"
                                      data-value="<?php echo $confRecourse['dt_end']; ?>"
                                      data-edit="recourse_date_end"
                                      data-viewformat="dd/mm/yyyy"
                                      data-showbuttons="false"
                                      data-original-title="<?php \MapasCulturais\i::_e("Data Final"); ?>"
                                      data-emptytext="<?php \MapasCulturais\i::_e("Data do fim do recurso"); ?>">
                                   <?php echo $confRecourse['dt_end']; ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <span class="label"><?php \MapasCulturais\i::_e("Hora Final"); ?>:</span>
                                <span class="js-editable"
                                      data-type="time"
                                      data-value="<?php echo $confRecourse['tm_end']; ?>"
                                      data-edit="recourse_time_end"
                                      data-viewformat="HH:mm"
                                      data-showbuttons="false"
                                      data-original-title="<?php \MapasCulturais\i::_e("Hora Final"); ?>"
                                      data-emptytext="<?php \MapasCulturais\i::_e("Horário do fim do Recurso"); ?>">
                                   <?php echo $confRecourse['tm_end']; ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
    // Shorthand for $( document ).ready()
    $(function() {
        //Ocutando a div existente no modulo de oportunidades
        // $("#registration-claim-configuration").hide();

    });
</script>

