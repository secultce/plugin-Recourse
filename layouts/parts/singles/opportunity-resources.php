
<?php 
//Url de redirecionamento
$url = $app->createUrl('recursos/oportunidade/' . $entity->id);
?>

<?php $this->applyTemplateHook('opportunity-resource--tab', 'before'); ?>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'begin'); ?>
<li>
    <a href="<?php echo $url; ?>" rel='noopener noreferrer'>
        <?php \MapasCulturais\i::_e("Recursos");?>
    </a>
</li>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'end'); ?>
<?php $this->applyTemplateHook('opportunity-resource--tab', 'after'); ?>
