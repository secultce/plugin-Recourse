<?php

use function MapasCulturais\__exec;

$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return [
    'create table recourse' => function() {
        __exec("CREATE SEQUENCE recourse_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("CREATE TABLE recourse (
            id INT NOT NULL, 
            recourse_text TEXT NULL, 
            recourse_send timestamp, 
            recourse_status VARCHAR(32) NOT NULL,
            recourse_reply TEXT NULL,
            recourse_date_reply timestamp,
            registration_id integer NOT NULL,
            opportunity_id integer NOT NULL,
            agent_id integer NOT NULL,
            reply_agent_id integer NOT NULL,
            PRIMARY KEY(id));");
        __exec("ALTER TABLE recourse ADD
        CONSTRAINT resource_registration_fk
        FOREIGN KEY (registration_id) REFERENCES registration (id)
        ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        
        __exec("ALTER TABLE recourse ADD
        CONSTRAINT resource_opportunity_fk
        FOREIGN KEY (opportunity_id) REFERENCES opportunity (id)
        ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");

        __exec("ALTER TABLE recourse ADD
        CONSTRAINT resource_agent_fk
        FOREIGN KEY (agent_id) REFERENCES agent (id)
        ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        }

];




