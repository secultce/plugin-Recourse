<?php
/**
 * @var $registration \MapasCulturais\Entities\Registration
 * @var $isActivePeriod bool
 * @var $isRecourseSent bool
 */
?>

<style>
.swal2-input-label {
    padding: 10px !important;
    margin: 10px !important;
    text-align: center !important;
}
</style>

<div class="opportunity-claim-button">
    <?php if(!$isRecourseSent && $isActivePeriod): ?>
    <a class="btn btn-primary openRecourse registration-panel-button" data-entity-id-cr="<?= $registration->id; ?>" href="javascript:void(0)"
        data-opp="<?= $registration->opportunity->id ?>" data-agent="<?= $registration->owner->id ?>"
        data-entity-context-cr="<?= htmlspecialchars($cr->text ?? '', ENT_QUOTES); ?>"
        id="btn-recourse-<?php echo $registration->id; ?>" data-url="recursos/sendRecourse" data-action="create">
        Abrir Recurso
    </a>
    <?php endif; ?>
    <?php
    if($isRecourseSent): ?>
    <p>
        <span class="badge badge-info registration-panel-badge">Recurso enviado</span>
    </p>
    <?php endif; ?>
</div>