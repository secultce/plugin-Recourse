<?php

use \MapasCulturais\i;

//VERIFICANDO SE TEM RECURSO HABILITADO
$enabledDiv = 'hidden';
$selectedEnabled = 'selected';
$selectedDisabled = '';
$entity = $this->controller->requestedEntity;
//dump($entity );
//Verdadeiro ou Falso se o usuário tem permissão para modificar os campos dessa entidade
$can_edit = $entity->canUser('modifyRegistrationFields');
//dump($can_edit );
//dump($enableRecourse);
if ($enableRecourse == 0) {
    $enabledDiv = 'visible';
    $selectedEnabled = '';
    $selectedDisabled = 'selected';
}

?>

<p>
<hr>
<h4><?php i::_e("Recurso"); ?></h4>
</p>
<p class="registration-help">
    Espaço para configurar se a oportunidade receberá a modalidade de recurso.
</p>
<div class="panel panel-default">
    <div class="panel-heading"><label><?php i::_e("Formulário para recursos"); ?></label></div>
    <div class="panel-body">
        <form id="resourceFormData">
            <select id="resourceOptions" name="claimDisabled" class="form-control" name="resourceOptions">
                <option value="">--Selecione--</option>
                <option value="0" <?php echo $selectedDisabled; ?>>
                    <?php i::_e('Habilitar formulário de Recurso'); ?>
                </option>
                <option <?php echo $selectedEnabled; ?> value="1">
                    <?php i::_e('Desabilitar formulário de Recurso'); ?>
                </option>
            </select>
            <div id="insertData" class="<?php echo $enabledDiv; ?>">
                <table class="table-full">
                    <tr class="table-full">
                        <td>
                            <div class="form-group">
                                <!--                                <label for="hora-inicial">Data de início </label>-->
                                <!--                                <input type="date" class="date form-control dateResource" name="date-initial" value="">-->
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