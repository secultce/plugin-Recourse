<?php

namespace Recourse\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
* File
*
* @property-read int $id File Id
* @property-read string $md5 File MD5
* @property-read string $mimeType File Mime Type
* @property-read string $name File name
* @property-read string $group File Group (gallery|avatar|download|etc.)
* @property-read \MapasCulturais\Entity $owner File Owner
* @property-read \DateTime $createTimestamp File Create Timestamp
* @property-read \MapasCulturais\Entity $owner The Owner of this File
*
* @property bool $private Is this file private?
*
* @property-read array $tmpFile $_FILE
*
* @ORM\Table(name="file",indexes={
*      @ORM\Index(name="file_owner_index", columns={"object_type", "object_id"}),
*      @ORM\Index(name="file_group_index", columns={"grp"}),
* })
*
* @ORM\Entity
* @ORM\entity(repositoryClass="MapasCulturais\Repositories\File")
* @ORM\HasLifecycleCallbacks
*
* @ORM\InheritanceType("SINGLE_TABLE")
* @ORM\DiscriminatorColumn(name="object_type", type="object_type")
* @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Opportunity"                   = "\MapasCulturais\Entities\OpportunityFile",
        "MapasCulturais\Entities\Project"                       = "\MapasCulturais\Entities\ProjectFile",
        "MapasCulturais\Entities\Event"                         = "\MapasCulturais\Entities\EventFile",
        "MapasCulturais\Entities\Agent"                         = "\MapasCulturais\Entities\AgentFile",
        "MapasCulturais\Entities\Space"                         = "\MapasCulturais\Entities\SpaceFile",
        "MapasCulturais\Entities\Seal"                          = "\MapasCulturais\Entities\SealFile",
        "MapasCulturais\Entities\Registration"                  = "\MapasCulturais\Entities\RegistrationFile",
        "MapasCulturais\Entities\RegistrationFileConfiguration" = "\MapasCulturais\Entities\RegistrationFileConfigurationFile",
        "MapasCulturais\Entities\Subsite"                       = "\MapasCulturais\Entities\SubsiteFile",
        "Recourse\Entities\Recourse"                            = "\Recourse\Entities\RecourseFile"
  })
*/
abstract class File extends \MapasCulturais\Entities\File {}