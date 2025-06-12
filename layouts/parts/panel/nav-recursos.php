<?php
?>
<style>
    /* Garante que o Froala se adapte ao modal do SweetAlert */
    .swal2-popup .fr-box {
        width: 100% !important;
        margin-bottom: 10px;
    }

    /* Esconde o textarea original (já que usaremos o Froala) */
    .swal2-textarea.froala-editor {
        display: none !important;
    }
</style>
<li><a href="<?php echo $app->createUrl('recursos', 'agent/'.$idAgent) ?>"><span class="icon icon-opportunity"></span> <?php \MapasCulturais\i::_e("Meus Recursos");?></a></li>
