<?php

namespace Recourse\Utils;

use MapasCulturais\App;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Services\SentryService;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Recourse\Entities\Recourse;

class Util
{
    /**
     * Verifica se está no período de envio de recursos
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return boolean
     */
    public static function isRecoursePeriod($opportunity): bool
    {
        $finalPeriod = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $initialPeriod = $opportunity->getMetadata('recourse_date_initial') . ' ' . $opportunity->getMetadata('recourse_time_initial');
        $finalPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $finalPeriod); // Converte para formato Datetime
        $initialPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $initialPeriod); // Converte para formato Datetime
        $now = new \DateTime();

        if ($opportunity->getMetadata('appealEnabled') === 'Sim' && $finalPeriodFormatted >= $now && $now >= $initialPeriodFormatted) return true;
        return false;
    }

    /**
     * Verifica se o período de envio de recursos já passou, sendo assim, o período de resposta
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return boolean
     */
    public static function isRecourseResponsePeriod($opportunity): bool
    {
        $finalPeriod = $opportunity->getMetadata('recourse_date_end') . ' ' . $opportunity->getMetadata('recourse_time_end');
        $finalPeriodFormatted = \DateTime::createFromFormat('Y-m-d H:i', $finalPeriod); // Converte para formato Datetime
        $now = new \DateTime();
        if ($finalPeriodFormatted <= $now) return true;
        return false;
    }

    /**
     * Verifica se todos os recursos da oportunidade já foram respondidos para que as respostas possam ser publicadas
     * Verifica também se o período de recurso já passou
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return boolean
     */
    public static function canPostResponses($opportunity): bool
    {
        $app = App::i();
        $recourses = $app->repo(Recourse::class)->findBy(['opportunity' => $opportunity->id]);
        $unansweredRecourses = array_filter($recourses, function ($recourse) {
            return (int)$recourse->status === Recourse::STATUS_DRAFT;
        });

        if (!$unansweredRecourses && self::isRecourseResponsePeriod($opportunity)) return true;
        return false;
    }

    public static function addRecoursesToRabbitmqQueue($recourses): void
    {
        $app = App::i();
        $count = count($recourses);

        $connection = null;
        $channel = null;

        try {
            // Tentar conectar ao RabbitMQ
            $connection = new AMQPStreamConnection(
                $app->config['rabbitmq']['host'],
                $app->config['rabbitmq']['port'],
                $app->config['rabbitmq']['user'],
                $app->config['rabbitmq']['password']
            );

            $exchange = $app->config['rabbitmq']['exchange_default']; // Exchange padrão
            $queueName = $app->config['rabbitmq']['queues']['queue_published_recourses']; // Nome da fila de recursos publicados

            $channel = $connection->channel();

            // Declarando a exchange que irá usar
            $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

            // Ligando a fila à exchange
            $channel->queue_bind($queueName, $exchange, $app->config['rabbitmq']['routing']['plugin_published_recourses']);

            foreach ($recourses as $i => $recourse) {
                try {
                    $data = [
                        'email' => $recourse->agent->user->email,
                        'opportunityName' => $recourse->opportunity->name,
                        'agentId' => $recourse->agent->id,
                    ];
                    $msg = new AMQPMessage(json_encode($data), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

                    $channel->basic_publish($msg, $exchange, $app->config['rabbitmq']['routing']['plugin_published_recourses']);

                    $app->log->debug("Notificação " . ($i + 1) . "/$count enviada para o usuário {$recourse->agent->user->id} ({$recourse->agent->name})");
                } catch (Exception $e) {
                    // Logar erro individual por mensagem, mas continuar o loop
                    $app->log->error("Falha ao publicar mensagem para o recurso {$recourse->id} no RabbitMQ: " . $e->getMessage());
                }

                // Chamar notificação independentemente do sucesso do RabbitMQ
                self::notificationPublishedRecourse($recourse);
            }
        } catch (Exception $e) {
            // Logar erro geral (ex.: falha de conexão) e continuar fluxo
            SentryService::captureExceptions($e);
            $app->log->error("Falha ao conectar ou operar com RabbitMQ: " . $e->getMessage());
        } finally {
            // Fechar recursos se existirem
            if ($channel) {
                $channel->close();
            }
            if ($connection) {
                $connection->close();
            }
        }
    }

    public static function notificationPublishedRecourse($recourse): void
    {
        $notification = new Notification();
        $notification->user = $recourse->agent->user;
        $notification->message = sprintf(
            "Sua resposta de recurso da oportunidade <a style='font-weight:bold;' href='/oportunidade/{$recourse->opportunity->id}'>%s</a> foi publicada. " .
            "<a style='font-weight:bold;' href='/recursos/agent/{$recourse->agent->id}'>Ver resposta.</a>",
            $recourse->opportunity->name
        );

        $notification->save(true);
    }
}
