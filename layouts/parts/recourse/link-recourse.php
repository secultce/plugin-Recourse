<a class="btn btn-recourse openRecourse"
   title="Editar recurso" href="javascript:void(0)"
   data-entity-id-cr="<?= $recourse->id; ?>" data-opp="<?= $recourse->opportunity->id ?>"
   data-agent="<?= $recourse->agent->id ?>"
   data-entity-context-cr="<?= htmlspecialchars($recourse->recourseText ?? '', ENT_QUOTES); ?>"
   id="btn-recourse-edits-<?php echo $recourse->id; ?>" data-action="update"
   data-url="recursos/updateRecourse">
    <i class="fas fa-edit"></i>
</a>