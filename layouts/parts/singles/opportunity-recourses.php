
<?php 
//Url de redirecionamento
/** @var \MapasCulturais\App $app */
$url = $app->createUrl('recursos/opportunities/' . $entity->id);
$this->jsObject['isAdmin'] = $entity->canUser("@control");
$visibledTab = false;

$this->applyTemplateHook('ource--tab', 'before');
$this->applyTemplateHook('opportunity-recourse--tab', 'begin');
if($entity->canUser('@control')):
?>
    <li><a href="#recursos" onclick="redirectRecourse()"><?php \MapasCulturais\i::_e("Recursos");?></a></li>
<?php
endif;// fim condição de administrador

$this->applyTemplateHook('opportunity-recourse--tab', 'end');
$this->applyTemplateHook('opportunity-recourse--tab', 'after');
?>
<script>
    $(document).ready(function(){
        console.log('rec')
        let visibled = false;

        if(MapasCulturais.roles.include("admin")){
            console.log(MapasCulturais.roles);
        }
    })
    function redirectRecourse() {
        window.location.href=MapasCulturais.baseURL+'recursos/oportunidade/'+MapasCulturais.entity.id
    }
</script>
