<?php

use MapasCulturais\App;
use Recourse\Entities\Recourse as EntityRecourse;

$recourse = App::i()->repo(EntityRecourse::class)->find($this->controller->data["recourseId"]);

require THEMES_PATH . 'BaseV1/layouts/headpdf.php';

?>

<main>
    <h4><?= $recourse->opportunity->name ?></h4>
    <h4>PEDIDO DE RECURSO</h4>

    <table>
        <tr>
            <td style="width: 30%;"><b>NÚMERO DE INSCRIÇÃO</b></td>
            <td><?= $recourse->registration->number ?></td>
        </tr>
        <tr>
            <td><b>PROJETO</b></td>
            <td><?= $recourse->opportunity->ownerEntity->name ?></td>
        </tr>
		<?php if ($recourse->registration->category): ?>
        <tr>
            <td><b>CATEGORIA</b></td>
            <td><?= $recourse->registration->category ?></td>
        </tr>
		<?php endif; ?>
        <tr>
            <td><b>PROPONENTE</b></td>
			<td><?= $recourse->agent->getMetadata('nomeSocial') ?: $recourse->agent->name ?></td>
        </tr>
        <tr>
            <td><b>MOTIVOS DO RECURSO</b></td>
            <td>REVISÃO DA AVALIAÇÃO DE MÉRITO CULTURAL</td>
        </tr>
        <tr>
            <th colspan="2"><b>PEDIDO DE RECURSO (Mapa Cultural)</b></th>
        </tr>
    </table>
    <div class="recourse-text"><?= $recourse->recourseText ?></div>
    <table>
        <tr>
            <th colspan="2"><b>RESPOSTA DA COMISSÃO</b></th>
        </tr>
    </table>
    <div class="recourse-text"><?= $recourse->recourseReply ?? 'Sem resposta' ?></div>
    <table style="margin-bottom: 50px;">
        <tr>
            <td><b>RESULTADO</b></td>
            <td><?= EntityRecourse::STATUS_DESCRIPTIONS[$recourse->status]; ?></td>
        </tr>
    </table>
</main>

<?php require THEMES_PATH . 'BaseV1/views/pdf/footer-pdf.php'; ?>
