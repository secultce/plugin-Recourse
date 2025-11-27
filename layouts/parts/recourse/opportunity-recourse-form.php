<?php

use \MapasCulturais\i;

$opportunity = $this->controller->requestedEntity;

?>

<hr>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="title-h4"><?php i::_e("Recurso"); ?></h4>
    </div>

    <p class="registration-help mgt-16 mgb-16">
        Espaço para configurar se a oportunidade receberá a modalidade de recurso.
    </p>
    <p class="registration-help mgt-16 mgb-16">
        Se habilitar os recursos tem a possibilidade de adicionar um período onde o usuário enviará seus recursos.
    </p>

    <p class="mgt-16" id="enabled-appeal-wrapper">
        <span class="label">Habilitar recurso:</span>
        <span
            class="js-editable"
            data-edit="appealEnabled"
            data-original-title="Habilitar recurso"
            data-value="<?= $opportunity->getMetadata('appealEnabled') ?>">
            <?= $opportunity->getMetadata('appealEnabled') ?>
        </span>
    </p>
    <div class="panel-body">
        <div class="d-none" id="appeal-period-wrapper">
            <table class="table-full">
                <td>
                    <div class="form-group">
                        <span class="label"><?php \MapasCulturais\i::_e("Data Inicial"); ?>:</span>
                        <span class="js-editable"
                            data-type="date"
                            data-value="<?= $confRecourse['dt_initial']; ?>"
                            data-edit="recourse_date_initial"
                            data-viewformat="dd/mm/yyyy"
                            data-showbuttons="false"
                            data-original-title="<?php \MapasCulturais\i::_e("Data Inicial"); ?>"
                            data-emptytext="<?php \MapasCulturais\i::_e("Data do início do Recurso"); ?>">
                            <?= $confRecourse['dt_initial']; ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <span class="label"><?php \MapasCulturais\i::_e("Hora Inicial"); ?>:</span>
                        <span class="js-editable"
                            data-type="time"
                            data-value="<?= $confRecourse['tm_initial']; ?>"
                            data-edit="recourse_time_initial"
                            data-viewformat="HH:mm"
                            data-showbuttons="false"
                            data-original-title="<?php \MapasCulturais\i::_e("Hora Inicial"); ?>"
                            data-emptytext="<?php \MapasCulturais\i::_e("Horário do início do Recurso"); ?>">
                            <?= $confRecourse['tm_initial']; ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <span class="label"><?php \MapasCulturais\i::_e("Data Final"); ?>:</span>
                        <span class="js-editable"
                            data-type="date"
                            data-value="<?= $confRecourse['dt_end']; ?>"
                            data-edit="recourse_date_end"
                            data-viewformat="dd/mm/yyyy"
                            data-showbuttons="false"
                            data-original-title="<?php \MapasCulturais\i::_e("Data Final"); ?>"
                            data-emptytext="<?php \MapasCulturais\i::_e("Data do fim do recurso"); ?>">
                            <?= $confRecourse['dt_end']; ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <span class="label"><?php \MapasCulturais\i::_e("Hora Final"); ?>:</span>
                        <span class="js-editable"
                            data-type="time"
                            data-value="<?= $confRecourse['tm_end']; ?>"
                            data-edit="recourse_time_end"
                            data-viewformat="HH:mm"
                            data-showbuttons="false"
                            data-original-title="<?php \MapasCulturais\i::_e("Hora Final"); ?>"
                            data-emptytext="<?php \MapasCulturais\i::_e("Horário do fim do Recurso"); ?>">
                            <?= $confRecourse['tm_end']; ?>
                        </span>
                    </div>
                </td>
            </table>
        </div>
    </div>
</div>

<hr>
