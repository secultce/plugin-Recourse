<?php

?>
<style>
    .swal2-input-label{
        padding: 10px !important;
        margin: 10px !important;
        text-align: center !important;
    }
</style>
<div class="opportunity-claim-button">
    <?php if(!$isSendrecourse && $validate): ?>
    <a class="btn btn-primary"
            onclick="sendRecourse(<?php echo $registration->id; ?>, <?php echo $registration->opportunity->id; ?>)"
            id="btn-recourse-<?php echo $registration->id; ?>">
        Abrir Recurso
    </a>
    <?php endif; ?>
    <?php
    if($isSendrecourse == 1): ?>
    <p>
        <label class="info-btn-recourse">Recurso enviado</label>
    </p>
    <?php endif; ?>
</div>

