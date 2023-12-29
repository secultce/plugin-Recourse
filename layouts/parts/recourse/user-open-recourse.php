<?php
dump($registration->id);

?>
<style>
    .swal2-input-label{
        padding: 10px !important;
        margin: 10px !important;
        text-align: center !important;
    }
</style>
<div>
    <button class="btn btn-primary"
            onclick="sendRecourse(<?php echo $registration->id; ?>, <?php echo $registration->opportunity->id; ?>)"
            id="btn-recourse-<?php echo $registration->id; ?>">
        Abrir Recurso
    </button>

</div>

