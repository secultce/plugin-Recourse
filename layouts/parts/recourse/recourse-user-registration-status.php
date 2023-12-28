<?php

use MapasCulturais\i;

$app = \MapasCulturais\App::i();
$user = $app->user;
$baseUrl = $app->_config['base.url'];

dump($entity->opportunity->getMetadata('recourse_date_end'));
dump($entity->opportunity->getMetadata('recourse_time_end'));
dump($entity->opportunity->getMetadata('claimDisabled'));
?>

<div class="opportunity-claim-button">
    <?php if( $entity->opportunity->getMetadata('claimDisabled') == '0') : ?>
    <a class="btn btn-primary" href="<?php echo $baseUrl . 'painel/inscricoes/' . $entity->id .'#tab=enviadas'?>">
        Ir para página de recurso.
    </a>
    <?php endif; ?>
</div>
<script>
    $(function() {
        $(".opportunity-claim-box").hide();
        console.log('.opportunity-claim-box');

    });

</script>


