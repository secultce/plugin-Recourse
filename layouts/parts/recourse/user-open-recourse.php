<?php
/**
 * @var $registration \MapasCulturais\Entities\Registration
 * @var $isActivePeriod bool
 * @var $isRecourseSent bool
 */
?>

<style>
    .swal2-input-label{
        padding: 10px !important;
        margin: 10px !important;
        text-align: center !important;
    }
</style>

<div class="opportunity-claim-button">
    <?php if(!$isRecourseSent && $isActivePeriod): ?>
    <a class="btn btn-primary"
            onclick="sendRecourse(<?php echo $registration->id .', '. $registration->opportunity->id .', '. $registration->owner->id ?>)"
            id="btn-recourse-<?php echo $registration->id; ?>">
        Abrir Recurso
    </a>
    <?php endif; ?>
    <?php
    if($isRecourseSent): ?>
    <p>
        <label class="info-btn-recourse">Recurso enviado</label>
    </p>
    <?php endif; ?>
</div>
