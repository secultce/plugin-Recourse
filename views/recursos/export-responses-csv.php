<?php

use Recourse\Entities\Recourse;

$header = [
    'INSCRIÇÃO',
    'AGENTE',
    'RECURSO',
    'ENVIADO EM',
    'SITUAÇÃO',
    'RESPOSTA',
    'RESPONDIDO EM',
    'RESPONDIDO POR',
    'NOTA',
];

$recources = $app->repo(Recourse::class)->findBy(['opportunity' => $opportunity->id]);

$rows = [];
foreach ($recources as $recourse) {
    $status = (int)$recourse->status;
    $situation = Recourse::STATUS_DESCRIPTIONS[$status];

    $row = [
        $recourse->registration->number,
        $recourse->agent->name,
        $recourse->recourseText,
        date_format($recourse->recourseSend, "d/m/Y H:i"),
        $situation,
        $recourse->recourseReply,
        $recourse->recourseDateReply ? date_format($recourse->recourseDateReply, "d/m/Y H:i") : '',
        $recourse->replyAgent ? $recourse->replyAgent->name : '',
        $recourse->replyResult,
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
