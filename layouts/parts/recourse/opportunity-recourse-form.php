<?php
use \MapasCulturais\i;

//VERIFICANDO SE TEM RECURSO HABILITADO
$enabledDiv = 'hidden';
$selectedEnabled  = 'selected';
$selectedDisabled = '';
$entity =  $this->controller->requestedEntity;
//dump($entity );
//Verdadeiro ou Falso se o usuário tem permissão para modificar os campos dessa entidade
$can_edit = $entity->canUser('modifyRegistrationFields');
dump($can_edit );
dump($enableRecourse);
if($enableRecourse == 0){
    $enabledDiv = 'visible';
    $selectedEnabled  = '';
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
    <div class="panel-heading"> <label><?php i::_e("Formulário para recursos"); ?></label></div>
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
            <div id="insertData"  class="<?php echo $enabledDiv; ?>">
                <table class="table-full">
                    <tr  class="table-full">
                        <td>
                            <div class="form-group">
                                <label for="hora-inicial">Data de início </label>
                                <input type="date" class="date form-control dateResource" name="date-initial" value="">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="hora-inicial">Hora de início </label>
                                <input type="time" class="date form-control dateResource" name="time-initial" value="12:00">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="hora-inicial">Data de fim </label>
                                <input type="date" class="date form-control dateResource" name="date-end" value="">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="hora-inicial">Hora de fim </label>
                                <input type="time" class="date form-control dateResource" name="time-end" value="12:00">
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="form-group">
                    <button class="btn btn-primary" id="buttonSendData">
                        <i class="fas fa-save"></i>
                        Salvar configuração
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>