
<?php 
//Url de redirecionamento
$url = $app->createUrl('recursos/opportunities/' . $entity->id);
?>

<?php $this->applyTemplateHook('opportunity-resource--tab', 'before'); ?>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'begin'); ?>
<li><a href="#recursos" onclick="redirectRecourse()"><?php \MapasCulturais\i::_e("Recursos");?></a></li>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'end'); ?>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'after'); ?>
<script>
    function redirectRecourse() {
        console.log('redirectRecourse');
        window.location.href=MapasCulturais.baseURL+'recursos/oportunidade/'+MapasCulturais.entity.id
    }
</script>
