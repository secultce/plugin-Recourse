
<?php 
//Url de redirecionamento
$url = $app->createUrl('recursos/opportunities/' . $entity->id);
$this->jsObject['isAdmin'] = $entity->canUser("@control");
$visibledTab = false;
?>

<?php $this->applyTemplateHook('ource--tab', 'before'); ?>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'begin');
    if($entity->canUser('@control')):
?>
<li><a href="#recursos" onclick="redirectRecourse()"><?php \MapasCulturais\i::_e("Recursos");?></a></li>
<?php
    endif;// fim condição de administrador
$this->applyTemplateHook('opportunity-resource--tab', 'end'); ?>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'after'); ?>
<script>
    $(document).ready(function(){
        console.log('rec')
        var visibled = false;

        if(MapasCulturais.roles.include("admin")){
            console.log(MapasCulturais.roles);
        }
    })
    function redirectRecourse() {
        window.location.href=MapasCulturais.baseURL+'recursos/oportunidade/'+MapasCulturais.entity.id
    }
</script>
