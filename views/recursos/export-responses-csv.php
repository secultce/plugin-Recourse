<?php

use Recourse\Entities\Recourse;

$header = ['Inscrição', 'Agente', 'Data de envio do recurso', 'Situação', 'Nota'];

$recources = $app->repo(Recourse::class)->findBy(['opportunity' => $opportunity->id]);

$rows = [];
foreach ($recources as $recourse) {
    $status = (int)$recourse->status;
    $situation = Recourse::STATUS_DESCRIPTIONS[$status];

    $row = [
        $recourse->registration->number,
        $recourse->agent->name,
        date_format($recourse->recourseSend, "d/m/Y H:i"),
        $situation,
        $recourse->replyResult
    ];

    $rows[] = $row;
}

$fh = @fopen('php://output', 'w');

fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($fh, $header);

foreach ($rows as $row) {
    fputcsv($fh, $row);
}

fclose($fh);
