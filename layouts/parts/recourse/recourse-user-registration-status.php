<?php
//Data atual do momento
$now = new DateTime();
/**
 * Só habilita o botão de recurso se o periodo final do recurso for igual ou maior que o configurado
 */

?>
<div class="opportunity-claim-button">
    <?php if(  $now >= $initialOfPeriod &&  $now <= $endOfPeriod )   : ?>
    <a class="btn btn-primary" href="<?php echo $baseUrl . 'painel/inscricoes/' . $entity->id .'#tab=enviadas'?>">
        Ir para página de recurso.
    </a>
    <?php endif; ?>
</div>
<script>
    $(function() {
        $(".opportunity-claim-box").hide();
    });

</script>


